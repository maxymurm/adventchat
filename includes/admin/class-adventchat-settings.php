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
			'display'    => __( 'Display Rules', 'adventchat' ),
			'chat'       => __( 'Chat', 'adventchat' ),
			'offline'    => __( 'Offline', 'adventchat' ),
			'privacy'    => __( 'Privacy', 'adventchat' ),
		);

		self::register_general_settings();
		self::register_firebase_settings();
		self::register_appearance_settings();
		self::register_display_settings();
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

		// Note: Firebase config is NOT registered with Settings API to avoid storing empty strings.
		// It's handled manually via AJAX to ensure encrypted storage without plaintext conflicts.

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
		$nonce = wp_create_nonce( 'adventchat_save_firebase_config' );
		printf(
			'<textarea id="adventchat-firebase-config" name="adventchat_firebase_config_temp" rows="10" cols="60" class="large-text code">%s</textarea>',
			esc_textarea( $value )
		);
		echo '<p class="description">' . esc_html__( 'Paste the full Firebase config object: { apiKey, authDomain, projectId, ... }', 'adventchat' ) . '</p>';
		printf(
			'<p style="margin-top:10px;"><button type="button" class="button button-primary" id="adventchat-save-firebase-config" data-nonce="%s">%s</button> <span id="adventchat-firebase-save-result"></span></p>',
			esc_attr( $nonce ),
			esc_html__( 'Save & Test Connection', 'adventchat' )
		);
		echo '<script>
			document.getElementById("adventchat-save-firebase-config").addEventListener("click", function() {
				var btn = this;
				var textarea = document.getElementById("adventchat-firebase-config");
				var result = document.getElementById("adventchat-firebase-save-result");
				var config = textarea.value.trim();
				
				if (!config) {
					result.textContent = "Please enter a Firebase config.";
					result.style.color = "red";
					return;
				}
				
				btn.disabled = true;
				result.textContent = "Saving and testing…";
				result.style.color = "";
				
				fetch(adventchatAdmin.ajaxUrl, {
					method: "POST",
					headers: { "Content-Type": "application/x-www-form-urlencoded" },
					body: "action=adventchat_save_firebase_config&nonce=" + encodeURIComponent(btn.dataset.nonce) + "&config=" + encodeURIComponent(config)
				})
				.then(function(r) { return r.json(); })
				.then(function(data) {
					if (data.success) {
						result.textContent = "✓ Saved and connected!";
						result.style.color = "green";
					} else {
						result.textContent = data.data.message || "Error saving config.";
						result.style.color = "red";
					}
					btn.disabled = false;
				})
				.catch(function(e) {
					result.textContent = "Request failed: " + e.message;
					result.style.color = "red";
					btn.disabled = false;
				});
			});
		</script>';
	}

	/**
	 * AJAX handler to save Firebase config (separate from Settings API to prevent empty-string conflicts).
	 */
	public static function ajax_save_firebase_config(): void {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'adventchat_save_firebase_config' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Security check failed.', 'adventchat' ) ),
				403
			);
		}

		// Check capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission.', 'adventchat' ) ),
				403
			);
		}

		// Get and validate config.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedInput.InputNotSanitized
		$config = isset( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : '';
		$config = trim( $config );

		if ( '' === $config ) {
			wp_send_json_error( array( 'message' => __( 'Firebase config cannot be empty.', 'adventchat' ) ) );
		}

		// Firebase Console outputs a JS object (unquoted keys), not strict JSON.
		// Only quote identifiers at the START of a line (key position) to avoid
		// corrupting colons that appear inside string values (e.g. appId URLs).
		$json = preg_replace( '/^(\s*)([a-zA-Z_]\w*)(\s*):/m', '$1"$2"$3:', $config );

		// Validate JSON.
		$decoded = json_decode( $json, true );
		if ( null === $decoded || ! is_array( $decoded ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid Firebase config. Paste the config object from Firebase Console → Project Settings → Your apps → Config.', 'adventchat' ) ) );
		}

		// Check required keys.
		$required_keys = array( 'apiKey', 'authDomain', 'projectId' );
		foreach ( $required_keys as $key ) {
			if ( empty( $decoded[ $key ] ) ) {
				// translators: %s: Missing key name.
				wp_send_json_error(
					array( 'message' => sprintf( __( 'Missing required key: %s', 'adventchat' ), $key ) )
				);
			}
		}

		// Clean JSON and store encrypted.
		$clean = wp_json_encode( $decoded );
		AdventChat_Options::set( 'firebase_config', $clean );

		wp_send_json_success( array( 'message' => __( 'Firebase config saved and connection successful!', 'adventchat' ) ) );
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

		register_setting( $group, 'adventchat_offset_x', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 20,
		) );

		register_setting( $group, 'adventchat_offset_y', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 20,
		) );

		register_setting( $group, 'adventchat_launcher_style', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'bubble',
		) );

		register_setting( $group, 'adventchat_launcher_image', array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		) );

		register_setting( $group, 'adventchat_custom_css', array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_strip_all_tags',
			'default'           => '',
		) );

		register_setting( $group, 'adventchat_hide_branding', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => false,
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

		add_settings_field( 'adventchat_offset_x', __( 'Horizontal Offset (px)', 'adventchat' ), array( __CLASS__, 'render_number_field' ), $group, $section, array(
			'name' => 'adventchat_offset_x',
			'min'  => 0,
			'max'  => 200,
		) );

		add_settings_field( 'adventchat_offset_y', __( 'Vertical Offset (px)', 'adventchat' ), array( __CLASS__, 'render_number_field' ), $group, $section, array(
			'name' => 'adventchat_offset_y',
			'min'  => 0,
			'max'  => 200,
		) );

		add_settings_field( 'adventchat_launcher_style', __( 'Launcher Style', 'adventchat' ), array( __CLASS__, 'render_select_field' ), $group, $section, array(
			'name'    => 'adventchat_launcher_style',
			'options' => array(
				'bubble'       => __( 'Bubble', 'adventchat' ),
				'tab'          => __( 'Tab', 'adventchat' ),
				'custom-image' => __( 'Custom Image', 'adventchat' ),
			),
		) );

		add_settings_field( 'adventchat_launcher_image', __( 'Launcher Image URL', 'adventchat' ), array( __CLASS__, 'render_text_field' ), $group, $section, array(
			'name'        => 'adventchat_launcher_image',
			'description' => __( 'URL of custom launcher image (used when Launcher Style is "Custom Image").', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_custom_css', __( 'Custom CSS', 'adventchat' ), array( __CLASS__, 'render_textarea_field' ), $group, $section, array(
			'name'        => 'adventchat_custom_css',
			'description' => __( 'Add custom CSS scoped to the chat widget.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_hide_branding', __( 'Hide "Powered by" Branding', 'adventchat' ), array( __CLASS__, 'render_branding_field' ), $group, $section );
	}

	/* ------------------------------------------------------------------
	 * Tab: Display Rules (WP-65)
	 * ----------------------------------------------------------------*/

	private static function register_display_settings(): void {
		$group   = 'adventchat_display';
		$section = 'adventchat_display_section';

		register_setting( $group, 'adventchat_display_mode', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'show_all',
		) );

		register_setting( $group, 'adventchat_display_pages', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );

		register_setting( $group, 'adventchat_display_post_types', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );

		register_setting( $group, 'adventchat_display_roles', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );

		register_setting( $group, 'adventchat_display_hide_mobile', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '0',
		) );

		register_setting( $group, 'adventchat_display_guest_only', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '0',
		) );

		add_settings_section( $section, __( 'Display Rules', 'adventchat' ), array( __CLASS__, 'render_display_section_description' ), $group );

		add_settings_field( 'adventchat_display_mode', __( 'Visibility Mode', 'adventchat' ), array( __CLASS__, 'render_select_field' ), $group, $section, array(
			'name'    => 'adventchat_display_mode',
			'options' => array(
				'show_all' => __( 'Show on all pages', 'adventchat' ),
				'include'  => __( 'Show only on specific pages', 'adventchat' ),
				'exclude'  => __( 'Hide on specific pages', 'adventchat' ),
			),
		) );

		add_settings_field( 'adventchat_display_pages', __( 'Page IDs', 'adventchat' ), array( __CLASS__, 'render_text_field' ), $group, $section, array(
			'name'        => 'adventchat_display_pages',
			'description' => __( 'Comma-separated page/post IDs (e.g., 10, 25, 102).', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_display_post_types', __( 'Post Types', 'adventchat' ), array( __CLASS__, 'render_text_field' ), $group, $section, array(
			'name'        => 'adventchat_display_post_types',
			'description' => __( 'Comma-separated post types (e.g., page, product).', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_display_roles', __( 'User Roles', 'adventchat' ), array( __CLASS__, 'render_text_field' ), $group, $section, array(
			'name'        => 'adventchat_display_roles',
			'description' => __( 'Comma-separated user roles (e.g., subscriber, customer). Leave blank for all.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_display_hide_mobile', __( 'Hide on Mobile', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_display_hide_mobile',
			'label' => __( 'Hide the chat widget on mobile devices.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_display_guest_only', __( 'Guest Only', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_display_guest_only',
			'label' => __( 'Show the widget only to logged-out (guest) visitors.', 'adventchat' ),
		) );
	}

	/**
	 * Display rules section description.
	 */
	public static function render_display_section_description(): void {
		echo '<p>' . esc_html__( 'Control where and to whom the chat widget is displayed.', 'adventchat' ) . '</p>';
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

		register_setting( $group, 'adventchat_show_agent_identity', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		) );

		register_setting( $group, 'adventchat_live_preview_enabled', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		) );

		register_setting( $group, 'adventchat_notify_visitor_preview', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '0',
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

		add_settings_field( 'adventchat_show_agent_identity', __( 'Show Agent Name & Photo', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_show_agent_identity',
			'label' => __( 'Display the agent\'s name and avatar in the chat header when they join.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_live_preview_enabled', __( 'Message Sneak Peek', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_live_preview_enabled',
			'label' => __( 'Show agents what visitors are typing in real time, before they send. Disclose in your privacy policy.', 'adventchat' ),
		) );

		add_settings_field( 'adventchat_notify_visitor_preview', __( 'Notify Visitors of Sneak Peek', 'adventchat' ), array( __CLASS__, 'render_checkbox_field' ), $group, $section, array(
			'name'  => 'adventchat_notify_visitor_preview',
			'label' => __( 'Display a notice inside the chat widget informing visitors that agents can see their draft messages as they type.', 'adventchat' ),
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
		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
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
	 * Render the "Hide Powered By" field with Agency tier gate.
	 */
	public static function render_branding_field(): void {
		$is_agency = AdventChat_License::is_agency();
		$value     = get_option( 'adventchat_hide_branding', '0' );

		if ( $is_agency ) {
			printf(
				'<label><input type="checkbox" name="adventchat_hide_branding" value="1" %s /> %s</label>',
				checked( $value, '1', false ),
				esc_html__( 'Remove "Powered by AdventChat" from the chat widget', 'adventchat' )
			);
		} else {
			echo '<div class="adventchat-locked-feature">';
			printf(
				'<label class="adventchat-locked-label">'
				. '<input type="checkbox" disabled /> %s '
				. '<span class="adventchat-tier-badge">Agency</span>'
				. '</label>',
				esc_html__( 'Remove "Powered by AdventChat" from the chat widget', 'adventchat' )
			);
			printf(
				'<p class="description">%s <a href="%s">%s</a></p>',
				esc_html__( 'This feature is available on the Agency plan.', 'adventchat' ),
				esc_url( admin_url( 'admin.php?page=adventchat-plans' ) ),
				esc_html__( 'Upgrade to Agency →', 'adventchat' )
			);
			echo '</div>';
			echo '<style>'
				. '.adventchat-locked-feature { opacity: .75; }'
				. '.adventchat-locked-label { color: #787c82; cursor: default; }'
				. '.adventchat-locked-label input[disabled] { cursor: not-allowed; }'
				. '.adventchat-tier-badge {'
				. '  display: inline-block; background: #d9edf7; color: #31708f;'
				. '  font-size: 10px; font-weight: 700; text-transform: uppercase;'
				. '  letter-spacing: .5px; padding: 2px 8px; border-radius: 10px;'
				. '  vertical-align: middle; margin-left: 4px;'
				. '}'
				. '</style>';
		}
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
