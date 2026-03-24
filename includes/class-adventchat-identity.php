<?php
/**
 * Identity verification for logged-in WordPress users.
 *
 * WP-77: HMAC-based identity verification.
 * Generates hash_hmac('sha256', user_id, secret) passed to widget.
 * Console shows "Verified" badge for verified visitors.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Identity
 */
class AdventChat_Identity {

	/**
	 * Option key for the identity verification secret.
	 *
	 * @var string
	 */
	private const SECRET_OPTION = 'adventchat_identity_secret';

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_filter( 'adventchat_widget_config', array( __CLASS__, 'add_identity_hash' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Register the identity verification settings.
	 */
	public static function register_settings(): void {
		$group   = 'adventchat_privacy';
		$section = 'adventchat_privacy_section';

		register_setting( $group, 'adventchat_identity_verification', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '0',
		) );

		add_settings_field(
			'adventchat_identity_verification',
			__( 'Identity Verification', 'adventchat' ),
			array( __CLASS__, 'render_field' ),
			$group,
			$section
		);
	}

	/**
	 * Render the identity verification checkbox + secret display.
	 */
	public static function render_field(): void {
		$enabled = get_option( 'adventchat_identity_verification', '0' );
		printf(
			'<label><input type="checkbox" name="adventchat_identity_verification" value="1" %s /> %s</label>',
			checked( $enabled, '1', false ),
			esc_html__( 'Verify logged-in user identity via HMAC hash.', 'adventchat' )
		);

		$secret = self::get_secret();
		printf(
			'<p class="description">%s <code>%s</code></p>',
			esc_html__( 'Secret key (do not share):', 'adventchat' ),
			esc_html( $secret )
		);
	}

	/**
	 * Get or generate the HMAC secret.
	 *
	 * @return string
	 */
	public static function get_secret(): string {
		$secret = AdventChat_Options::get( self::SECRET_OPTION );
		if ( empty( $secret ) ) {
			$secret = wp_generate_password( 64, true, true );
			AdventChat_Options::set( self::SECRET_OPTION, $secret );
		}
		return $secret;
	}

	/**
	 * Add identity hash to widget config for logged-in users.
	 *
	 * @param array $config Widget config.
	 * @return array
	 */
	public static function add_identity_hash( array $config ): array {
		if ( '1' !== get_option( 'adventchat_identity_verification', '0' ) ) {
			return $config;
		}

		if ( ! is_user_logged_in() ) {
			return $config;
		}

		$user   = wp_get_current_user();
		$secret = self::get_secret();
		$hash   = hash_hmac( 'sha256', (string) $user->ID, $secret );

		$config['identity'] = array(
			'userId'   => $user->ID,
			'name'     => $user->display_name,
			'email'    => $user->user_email,
			'hash'     => $hash,
			'verified' => true,
		);

		return $config;
	}
}
