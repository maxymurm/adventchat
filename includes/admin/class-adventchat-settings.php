<?php
/**
 * Settings framework — tabbed admin settings page.
 *
 * Tabs: General, Firebase, Appearance, Chat, Offline, Privacy
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Settings
 */
class AdventChat_Settings {

	/**
	 * Available tabs.
	 *
	 * @var array<string, string>
	 */
	private static array $tabs = array();

	/**
	 * Register settings, sections, and fields for every tab.
	 */
	public static function register(): void {
		self::$tabs = array(
			'general'    => __( 'General', 'adventchat' ),
			'firebase'   => __( 'Firebase', 'adventchat' ),
			'appearance' => __( 'Appearance', 'adventchat' ),
			'chat'       => __( 'Chat', 'adventchat' ),
			'offline'    => __( 'Offline', 'adventchat' ),
			'privacy'    => __( 'Privacy', 'adventchat' ),
		);

		self::register_general_settings();
		self::register_firebase_settings();
		self::register_appearance_settings();
		self::register_chat_settings();
		self::register_offline_settings();
		self::register_privacy_settings();
	}

	/**
	 * Get all tabs.
	 *
	 * @return array<string, string>
	 */
	public static function get_tabs(): array {
		if ( empty( self::$tabs ) ) {
			self::register();
		}
		return self::$tabs;
	}

	/**
	 * Get the currently active tab.
	 *
	 * @return string
	 */
	public static function current_tab(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		return array_key_exists( $tab, self::get_tabs() ) ? $tab : 'general';
	}

	/* ------------------------------------------------------------------
	 * Tab: General
	 * ----------------------------------------------------------------*/

	private static function register_general_settings(): void {
		$group   = 'adventchat_general';
		$section = 'adventchat_general_section';

		register_setting( $group, 'adventchat_welcome_title', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => __( 'Hi there! 👋', 'adventchat' ),
		) );

		register_setting( $group, 'adventchat_welcome_subtitle', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => __( 'How can we help you?', 'adventchat' ),
		) );

		register_setting( $group, 'adventchat_input_placeholder', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => __( 'Type a message…', 'adventchat' ),
		) );

		add_settings_section( $section, __( 'General Settings', 'adventchat' ), '__return_null', $group );

		add_settings_field( 'adventchat_welcome_title', __( 'Welcome Title', 'adventchat' ), array( __CLASS__, 'render_text_field' ), $group, $section, array(
			'name' => 'adventchat_welcome_title',
		) );

		add_settings_field( 'adventchat_welcome_subtitle', __( 'Welcome Subtitle', 'adventchat' ), array( __CLASS__, 'render_text_field' ), $group, $section, array(
			'name' => 'adventchat_welcome_subtitle',
		) );

		add_settings_field( 'adventchat_input_placeholder', __( 'Input Placeholder', 'adventchat' ), array( __CLASS__, 'render_text_field' ), $group, $section, array(
			'name' => 'adventchat_input_placeholder',
		) );
	}

	/* ------------------------------------------------------------------
	 * Tab: Firebase
	 * ----------------------------------------------------------------*/

	private static function register_firebase_settings(): void {
		$group   = 'adventchat_firebase';
		$section = 'adventchat_firebase_section';

		register_setting( $group, 'adventchat_firebase_config', array(
			'type'              => 'string',
			'sanitize_callback' => array( __CLASS__, 'sanitize_firebase_config' ),
			'default'           => '',
		) );

		add_settings_section( $section, __( 'Firebase Configuration', 'adventchat' ), array( __CLASS__, 'render_firebase_section_description' ), $group );

		add_settings_field( 'adventchat_firebase_config', __( 'Web App Config (JSON)', 'adventchat' ), array( __CLASS__, 'render_firebase_config_field' ), $group, $section );

		// Security Rules display section (read-only, no setting stored).
		$rules_section = 'adventchat_firebase_rules_section';
		add_settings_section( $rules_section, __( 'Firestore Security Rules', 'adventchat' ), array( __CLASS__, 'render_firestore_rules_section' ), $group );
	}

	/**
	 * Render Firebase section description.
	 */
	public static function render_firebase_section_description(): void {
		echo '<p>' . esc_html__( 'Paste your Firebase Web App configuration JSON below. You can find this in your Firebase Console → Project Settings → General → Your apps → Config.', 'adventchat' ) . '</p>';
	}

	/**
	 * Render Firestore security rules section with copy button.
	 */
	public static function render_firestore_rules_section(): void {
		$rules_file = ADVENTCHAT_PLUGIN_DIR . 'assets/firestore.rules';
		if ( ! file_exists( $rules_file ) ) {
			echo '<p>' . esc_html__( 'Security rules file not found.', 'adventchat' ) . '</p>';
			return;
		}

		$rules = file_get_contents( $rules_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		echo '<p>' . esc_html__( 'Copy these rules into your Firebase Console → Firestore → Rules tab:', 'adventchat' ) . '</p>';
		echo '<div style="position:relative;">';
		echo '<button type="button" class="button button-small" id="adventchat-copy-rules" style="position:absolute;top:5px;right:5px;z-index:1;">' . esc_html__( 'Copy Rules', 'adventchat' ) . '</button>';
		printf(
			'<textarea id="adventchat-firestore-rules" rows="20" cols="80" class="large-text code" readonly>%s</textarea>',
			esc_textarea( $rules )
		);
		echo '</div>';
		echo '<script>
			document.getElementById("adventchat-copy-rules").addEventListener("click", function() {
				var textarea = document.getElementById("adventchat-firestore-rules");
				navigator.clipboard.writeText(textarea.value).then(function() {
					var btn = document.getElementById("adventchat-copy-rules");
					btn.textContent = "' . esc_js( __( 'Copied!', 'adventchat' ) ) . '";
					setTimeout(function() { btn.textContent = "' . esc_js( __( 'Copy Rules', 'adventchat' ) ) . '"; }, 2000);
				});
			});
		</script>';
	}

	/**
	 * Render the Firebase JSON config textarea.
	 */
	public static function render_firebase_config_field(): void {
		$value = AdventChat_Options::get( 'firebase_config' );
		printf(
			'<textarea name="adventchat_firebase_config" rows="10" cols="60" class="large-text code">%s</textarea>',
			esc_textarea( $value )
		);
		echo '<p class="description">' . esc_html__( 'Paste the full Firebase config object: { apiKey, authDomain, projectId, ... }', 'adventchat' ) . '</p>';
		echo '<p style="margin-top:10px;"><button type="button" class="button" id="adventchat-test-firebase">' . esc_html__( 'Test Connection', 'adventchat' ) . '</button> <span id="adventchat-firebase-test-result"></span></p>';
		echo '<script>
			document.getElementById("adventchat-test-firebase").addEventListener("click", function() {
				var btn = this;
				var result = document.getElementById("adventchat-firebase-test-result");
				btn.disabled = true;
				result.textContent = "Testing…";
				result.style.color = "";
				fetch(adventchatAdmin.ajaxUrl, {
					method: "POST",
					headers: { "Content-Type": "application/x-www-form-urlencoded" },
					body: "action=adventchat_test_firebase&nonce=" + adventchatAdmin.nonce
				})
				.then(function(r) { return r.json(); })
				.then(function(data) {
					result.textContent = data.data.message;
					result.style.color = data.success ? "green" : "red";
					btn.disabled = false;
				})
				.catch(function() {
					result.textContent = "Request failed.";
					result.style.color = "red";
					btn.disabled = false;
				});
			});
		</script>';
	}

	/**
	 * Sanitize and validate Firebase config JSON.
	 *
	 * @param string $input Raw input.
	 * @return string Sanitized JSON string.
	 */
	public static function sanitize_firebase_config( string $input ): string {
		$input = wp_unslash( $input );
		$input = trim( $input );

		if ( '' === $input ) {
			return '';
		}

		$decoded = json_decode( $input, true );
		if ( null === $decoded || ! is_array( $decoded ) ) {
			add_settings_error( 'adventchat_firebase_config', 'invalid_json', __( 'Invalid JSON. Please paste a valid Firebase config object.', 'adventchat' ) );
			return AdventChat_Options::get( 'firebase_config' );
		}

		$required_keys = array( 'apiKey', 'authDomain', 'projectId' );
		foreach ( $required_keys as $key ) {
			if ( empty( $decoded[ $key ] ) ) {
				add_settings_error(
					'adventchat_firebase_config',
					'missing_key',
					/* translators: %s: Missing key name */
					sprintf( __( 'Firebase config is missing required key: %s', 'adventchat' ), $key )
				);
				return AdventChat_Options::get( 'firebase_config' );
			}
		}

		// Re-encode to ensure clean JSON.
		$clean = wp_json_encode( $decoded );

		// Store encrypted via Options helper.
		AdventChat_Options::set( 'firebase_config', $clean );

		// Return empty string — we've already stored it encrypted.
		// This prevents WP from also storing it in plaintext.
		return '';
	}

	/* ------------------------------------------------------------------
	 * Tab: Appearance
	 * ----------------------------------------------------------------*/

	private static function register_appearance_settings(): void {
		$group   = 'adventchat_appearance';
		$section = 'adventchat_appearance_section';

		register_setting( $group, 'adventchat_primary_color', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_hex_color',
			'default'           => '#0066ff',
		) );

		register_setting( $group, 'adventchat_secondary_color', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_hex_color',
			'default'           => '#ffffff',
		) );

		register_setting( $group, 'adventchat_position', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'bottom-right',
		) );

		register_setting( $group, 'adventchat_launcher_style', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'bubble',
		) );

		register_setting( $group, 'adventchat_custom_css', array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_strip_all_tags',
			'default'           => '',
		) );

		add_settings_section( $section, __( 'Widget Appearance', 'adventchat' ), '__return_null', $group );

		add_settings_field( 'adventchat_primary_color', __( 'Primary Color', 'adventchat' ), array( __CLASS__, 'render_color_field' ), $group, $section, array(
			'name' => 'adventchat_primary_color',
		) );

		add_settings_field( 'adventchat_secondary_color', __( 'Secondary Color', 'adventchat' ), array( __CLASS__, 'render_color_field' ), $group, $section, array(
			'name' => 'adventchat_secondary_color',
		) );

		add_settings_field( 'adventchat_position', __( 'Widget Position', 'adventchat' ), array( __CLASS__, 'render_select_field' ), $group, $section, array(
			'name'    => 'adventchat_position',
			'options' => array(
				'bottom-right' => __( 'Bottom Right', 'adventchat' ),
				'bottom-left'  => __( 'Bottom Left', 'adventchat' ),
			),
		) );

		add_settings_field( 'adventchat_launcher_style', __( 'Launcher Style', 'adventchat' ), array( __CLASS__, 'render_select_field' ), $group, $section, array(
			'name'    => 'adventchat_launcher_style',
			'options' => array(
				'bubble' => __( 'Bubble', 'adventchat' ),
				'tab'    => __( 'Tab', 'adventchat' ),
			),
		) );

		add_settings_field( 'adventchat_custom_css', __( 'Custom CSS', 'adventchat' ), array( __CLASS__, 'render_textarea_field' ), $group, $section, array(
			'name'        => 'adventchat_custom_css',
			'description' => __( 'Add custom CSS scoped to the chat widget.', 'adventchat' ),
		) );
	}

	/* ------------------------------------------------------------------
	 * Tab: Chat
	 * ----------------------------------------------------------------*/

	private static function register_chat_settings(): void {
		$group   = 'adventchat_chat';
		$section = 'adventchat_chat_section';

		register_setting( $group, 'adventchat_sound_enabled', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		) );

		register_setting( $group, 'adventchat_auto_open_enabled', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '0',
		) );

		register_setting( $group, 'adventchat_auto_open_delay', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 5,
		) );

		register_setting( $group, 'adventchat_routing_mode', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'round-robin',
		) );

		register_setting( $group, 'adventchat_transcript_enabled', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		) );

		register_setting( $group, 'adventchat_csat_enabled', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		) );

		register_setting( $group, 'adventchat_file_sharing', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		) );

		add_settings_section( $section, __( 'Chat Behavior', 'adventchat' ), '__return_null', $group );

		add_settings_field( 'adventchat_sound_enabled', __( 'Sound Notifications', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_sound_enabled',
			'label' => __( 'Play a sound when a new message arrives.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_auto_open_enabled', __( 'Auto-open Widget', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_auto_open_enabled',
			'label' => __( 'Automatically open the chat widget after a delay.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_auto_open_delay', __( 'Auto-open Delay (seconds)', 'adventchat' ), array( __CLASS__, 'render_number_field' ), $group, $section, array(
			'name' => 'adventchat_auto_open_delay',
			'min'  => 1,
			'max'  => 120,
		) );

		add_settings_field( 'adventchat_routing_mode', __( 'Chat Routing', 'adventchat' ), array( __CLASS__, 'render_select_field' ), $group, $section, array(
			'name'    => 'adventchat_routing_mode',
			'options' => array(
				'round-robin' => __( 'Round Robin', 'adventchat' ),
				'manual'      => __( 'Manual (agents accept)', 'adventchat' ),
				'all-notify'  => __( 'Notify All', 'adventchat' ),
			),
		) );

		add_settings_field( 'adventchat_transcript_enabled', __( 'Email Transcript', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_transcript_enabled',
			'label' => __( 'Allow visitors to email themselves a chat transcript.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_csat_enabled', __( 'Chat Rating (CSAT)', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_csat_enabled',
			'label' => __( 'Ask visitors to rate the chat after it ends.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_file_sharing', __( 'File Sharing', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_file_sharing',
			'label' => __( 'Allow file and image sharing in chat.', 'adventchat' ),
		) );
	}

	/* ------------------------------------------------------------------
	 * Tab: Offline
	 * ----------------------------------------------------------------*/

	private static function register_offline_settings(): void {
		$group   = 'adventchat_offline';
		$section = 'adventchat_offline_section';

		register_setting( $group, 'adventchat_offline_enabled', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		) );

		register_setting( $group, 'adventchat_offline_email', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'default'           => get_option( 'admin_email' ),
		) );

		add_settings_section( $section, __( 'Offline Messages', 'adventchat' ), '__return_null', $group );

		add_settings_field( 'adventchat_offline_enabled', __( 'Enable Offline Form', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_offline_enabled',
			'label' => __( 'Show an offline message form when no agents are available.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_offline_email', __( 'Notification Email', 'adventchat' ), array( __CLASS__, 'render_email_field' ), $group, $section, array(
			'name'        => 'adventchat_offline_email',
			'description' => __( 'Email address to receive offline message notifications.', 'adventchat' ),
		) );
	}

	/* ------------------------------------------------------------------
	 * Tab: Privacy
	 * ----------------------------------------------------------------*/

	private static function register_privacy_settings(): void {
		$group   = 'adventchat_privacy';
		$section = 'adventchat_privacy_section';

		register_setting( $group, 'adventchat_gdpr_enabled', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '0',
		) );

		register_setting( $group, 'adventchat_prechat_enabled', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		) );

		register_setting( $group, 'adventchat_privacy_page', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 0,
		) );

		add_settings_section( $section, __( 'Privacy & Consent', 'adventchat' ), '__return_null', $group );

		add_settings_field( 'adventchat_gdpr_enabled', __( 'GDPR Consent', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_gdpr_enabled',
			'label' => __( 'Require consent checkbox before starting a chat.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_prechat_enabled', __( 'Pre-chat Form', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_prechat_enabled',
			'label' => __( 'Require name and email before starting a chat.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_privacy_page', __( 'Privacy Policy Page', 'adventchat' ), array( __CLASS__, 'render_page_select_field' ), $group, $section, array(
			'name' => 'adventchat_privacy_page',
		) );
	}

	/* ------------------------------------------------------------------
	 * Field renderers
	 * ----------------------------------------------------------------*/

	/**
	 * Render a text input field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_text_field( array $args ): void {
		$value = get_option( $args['name'], '' );
		printf(
			'<input type="text" name="%s" value="%s" class="regular-text" />',
			esc_attr( $args['name'] ),
			esc_attr( $value )
		);
	}

	/**
	 * Render a color picker field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_color_field( array $args ): void {
		$value = get_option( $args['name'], '#0066ff' );
		printf(
			'<input type="text" name="%s" value="%s" class="adventchat-color-picker" data-default-color="%s" />',
			esc_attr( $args['name'] ),
			esc_attr( $value ),
			esc_attr( $value )
		);
	}

	/**
	 * Render a select dropdown.
	 *
	 * @param array $args Field arguments with 'options'.
	 */
	public static function render_select_field( array $args ): void {
		$value = get_option( $args['name'], '' );
		printf( '<select name="%s">', esc_attr( $args['name'] ) );
		foreach ( $args['options'] as $key => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $key ),
				selected( $value, $key, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	/**
	 * Render a checkbox field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_checkbox_field( array $args ): void {
		$value = get_option( $args['name'], '0' );
		printf(
			'<label><input type="checkbox" name="%s" value="1" %s /> %s</label>',
			esc_attr( $args['name'] ),
			checked( $value, '1', false ),
			esc_html( $args['label'] ?? '' )
		);
	}

	/**
	 * Render a number input field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_number_field( array $args ): void {
		$value = get_option( $args['name'], '' );
		printf(
			'<input type="number" name="%s" value="%s" min="%d" max="%d" class="small-text" />',
			esc_attr( $args['name'] ),
			esc_attr( $value ),
			intval( $args['min'] ?? 0 ),
			intval( $args['max'] ?? 9999 )
		);
	}

	/**
	 * Render an email input field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_email_field( array $args ): void {
		$value = get_option( $args['name'], '' );
		printf(
			'<input type="email" name="%s" value="%s" class="regular-text" />',
			esc_attr( $args['name'] ),
			esc_attr( $value )
		);
		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render a textarea field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_textarea_field( array $args ): void {
		$value = get_option( $args['name'], '' );
		printf(
			'<textarea name="%s" rows="6" cols="60" class="large-text code">%s</textarea>',
			esc_attr( $args['name'] ),
			esc_textarea( $value )
		);
		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render a page select dropdown.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_page_select_field( array $args ): void {
		$value = get_option( $args['name'], 0 );
		wp_dropdown_pages( array(
			'name'              => $args['name'],
			'selected'          => $value,
			'show_option_none'  => __( '— Select —', 'adventchat' ),
			'option_none_value' => 0,
		) );
	}
}
