<?php
/**
 * Main AdventChat bootstrap class (singleton).
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat
 */
final class AdventChat {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public string $version = ADVENTCHAT_VERSION;

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Return the singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — runs once.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register core WordPress hooks.
	 */
	private function init_hooks(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'init_components' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'adventchat', false, dirname( plugin_basename( ADVENTCHAT_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Initialize plugin components.
	 */
	public function init_components(): void {
		AdventChat_Roles::init();

		if ( is_admin() ) {
			AdventChat_Admin::init();
		}
	}

	/**
	 * Register top-level admin menu and submenus.
	 */
	public function register_admin_menu(): void {
		add_menu_page(
			__( 'AdventChat', 'adventchat' ),
			__( 'AdventChat', 'adventchat' ),
			'manage_options',
			'adventchat',
			array( $this, 'render_console_page' ),
			'dashicons-format-chat',
			26
		);

		add_submenu_page(
			'adventchat',
			__( 'Live Chat', 'adventchat' ),
			__( 'Live Chat', 'adventchat' ),
			'manage_options',
			'adventchat',
			array( $this, 'render_console_page' )
		);

		add_submenu_page(
			'adventchat',
			__( 'Settings', 'adventchat' ),
			__( 'Settings', 'adventchat' ),
			'manage_options',
			'adventchat-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings via Settings API.
	 */
	public function register_settings(): void {
		AdventChat_Settings::register();
	}

	/**
	 * Render the operator console page.
	 */
	public function render_console_page(): void {
		$template = ADVENTCHAT_PLUGIN_DIR . 'templates/admin/page-console.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page(): void {
		$template = ADVENTCHAT_PLUGIN_DIR . 'templates/admin/page-settings.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function admin_assets( string $hook_suffix ): void {
		// Only load on AdventChat admin pages.
		if ( ! str_contains( $hook_suffix, 'adventchat' ) ) {
			return;
		}

		wp_enqueue_style(
			'adventchat-admin',
			ADVENTCHAT_PLUGIN_URL . 'assets/css/dist/admin.css',
			array(),
			ADVENTCHAT_VERSION
		);

		wp_localize_script( 'jquery', 'adventchatAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'adventchat_admin_nonce' ),
		) );

		// Load Firebase SDK + console app on the Live Chat (console) page only.
		if ( 'toplevel_page_adventchat' === $hook_suffix ) {
			$firebase_config = AdventChat_Options::get( 'firebase_config' );
			if ( ! empty( $firebase_config ) ) {
				$firebase_version = '10.14.1';
				wp_enqueue_script( 'firebase-app', "https://www.gstatic.com/firebasejs/{$firebase_version}/firebase-app-compat.js", array(), $firebase_version, true );
				wp_enqueue_script( 'firebase-auth', "https://www.gstatic.com/firebasejs/{$firebase_version}/firebase-auth-compat.js", array( 'firebase-app' ), $firebase_version, true );
				wp_enqueue_script( 'firebase-firestore', "https://www.gstatic.com/firebasejs/{$firebase_version}/firebase-firestore-compat.js", array( 'firebase-app' ), $firebase_version, true );

				wp_enqueue_script(
					'adventchat-console',
					ADVENTCHAT_PLUGIN_URL . 'assets/js/dist/console.js',
					array( 'firebase-app', 'firebase-auth', 'firebase-firestore' ),
					ADVENTCHAT_VERSION,
					true
				);

				wp_enqueue_style(
					'adventchat-console',
					ADVENTCHAT_PLUGIN_URL . 'assets/css/dist/console.css',
					array(),
					ADVENTCHAT_VERSION
				);

				$config = json_decode( $firebase_config, true );
				wp_localize_script( 'adventchat-console', 'adventchatConfig', array(
					'firebase' => $config,
					'siteId'   => md5( get_site_url() ),
					'restUrl'  => esc_url_raw( rest_url( ADVENTCHAT_API_NAMESPACE ) ),
					'restNonce'=> wp_create_nonce( 'wp_rest' ),
				) );
			}
		}
	}

	/**
	 * Enqueue frontend widget scripts and styles.
	 */
	public function frontend_assets(): void {
		// Don't load in admin or if Firebase config is missing.
		if ( is_admin() ) {
			return;
		}

		$firebase_config = AdventChat_Options::get( 'firebase_config' );
		if ( empty( $firebase_config ) ) {
			return;
		}

		// Firebase SDK (compat builds for simple global access).
		$firebase_version = '10.14.1';
		wp_enqueue_script( 'firebase-app', "https://www.gstatic.com/firebasejs/{$firebase_version}/firebase-app-compat.js", array(), $firebase_version, true );
		wp_enqueue_script( 'firebase-auth', "https://www.gstatic.com/firebasejs/{$firebase_version}/firebase-auth-compat.js", array( 'firebase-app' ), $firebase_version, true );
		wp_enqueue_script( 'firebase-firestore', "https://www.gstatic.com/firebasejs/{$firebase_version}/firebase-firestore-compat.js", array( 'firebase-app' ), $firebase_version, true );

		wp_enqueue_style(
			'adventchat-widget',
			ADVENTCHAT_PLUGIN_URL . 'assets/css/dist/widget.css',
			array(),
			ADVENTCHAT_VERSION
		);

		wp_enqueue_script(
			'adventchat-widget',
			ADVENTCHAT_PLUGIN_URL . 'assets/js/dist/widget.js',
			array( 'firebase-app', 'firebase-auth', 'firebase-firestore' ),
			ADVENTCHAT_VERSION,
			true
		);

		$config = json_decode( $firebase_config, true );
		wp_localize_script( 'adventchat-widget', 'adventchatConfig', array(
			'firebase'    => $config,
			'siteId'      => md5( get_site_url() ),
			'restUrl'     => esc_url_raw( rest_url( ADVENTCHAT_API_NAMESPACE ) ),
			'restNonce'   => wp_create_nonce( 'wp_rest' ),
			'settings'    => array(
				'position'       => get_option( 'adventchat_position', 'bottom-right' ),
				'primaryColor'   => get_option( 'adventchat_primary_color', '#0066ff' ),
				'secondaryColor' => get_option( 'adventchat_secondary_color', '#ffffff' ),
				'welcomeTitle'   => get_option( 'adventchat_welcome_title', __( 'Hi there! 👋', 'adventchat' ) ),
				'welcomeSubtitle'=> get_option( 'adventchat_welcome_subtitle', __( 'How can we help you?', 'adventchat' ) ),
				'placeholder'    => get_option( 'adventchat_input_placeholder', __( 'Type a message…', 'adventchat' ) ),
				'soundEnabled'   => get_option( 'adventchat_sound_enabled', '1' ),
				'offlineEnabled' => get_option( 'adventchat_offline_enabled', '1' ),
				'gdprEnabled'    => get_option( 'adventchat_gdpr_enabled', '0' ),
				'prechatEnabled' => get_option( 'adventchat_prechat_enabled', '1' ),
				'csatEnabled'    => get_option( 'adventchat_csat_enabled', '1' ),
				'fileSharing'    => get_option( 'adventchat_file_sharing', '1' ),
			),
		) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		$controller = new AdventChat_Api_Controller();
		$controller->register_routes();

		$operators = new AdventChat_Api_Operators();
		$operators->register_routes();
	}

	/**
	 * Display admin notices (e.g., missing Firebase config).
	 */
	public function admin_notices(): void {
		$screen = get_current_screen();
		if ( null === $screen || ! str_contains( $screen->id, 'adventchat' ) ) {
			return;
		}

		$firebase_config = AdventChat_Options::get( 'firebase_config' );
		if ( empty( $firebase_config ) ) {
			printf(
				'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
				esc_html__( 'AdventChat requires Firebase configuration.', 'adventchat' ),
				esc_url( admin_url( 'admin.php?page=adventchat-settings&tab=firebase' ) ),
				esc_html__( 'Set up Firebase →', 'adventchat' )
			);
		}
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization.
	 */
	public function __wakeup() {
		throw new \RuntimeException( 'Cannot unserialize singleton.' );
	}
}
