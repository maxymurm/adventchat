<?php
/**
 * REST: GET adventchat/v1/widget-config
 *
 * WP-76: Public endpoint returning Firebase config, agent status, display rules, siteId.
 * Cached for 60 seconds, rate-limited to 10 req/min per IP.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Api_Widget_Config
 */
class AdventChat_Api_Widget_Config extends AdventChat_Api_Controller {

	/**
	 * Rate limit transient prefix.
	 *
	 * @var string
	 */
	private const RATE_PREFIX = 'ac_rl_';

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		register_rest_route( ADVENTCHAT_API_NAMESPACE, '/widget-config', array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_config' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Return the public widget configuration.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_config( \WP_REST_Request $request ) {
		// Rate limiting: 10 req/min per IP.
		$ip   = $this->get_client_ip( $request );
		$key  = self::RATE_PREFIX . md5( $ip );
		$hits = (int) get_transient( $key );

		if ( $hits >= 10 ) {
			return new \WP_Error(
				'rate_limited',
				__( 'Rate limit exceeded. Try again later.', 'adventchat' ),
				array( 'status' => 429 )
			);
		}

		set_transient( $key, $hits + 1, 60 );

		// Check cache.
		$cached = get_transient( 'adventchat_widget_config' );
		if ( false !== $cached ) {
			return new \WP_REST_Response( $cached, 200 );
		}

		$firebase_config = AdventChat_Options::get( 'firebase_config' );
		if ( empty( $firebase_config ) ) {
			return new \WP_Error(
				'not_configured',
				__( 'Chat is not configured.', 'adventchat' ),
				array( 'status' => 503 )
			);
		}

		$config_data = json_decode( $firebase_config, true );

		$data = array(
			'firebase' => $config_data,
			'siteId'   => md5( get_site_url() ),
			'settings' => array(
				'position'        => get_option( 'adventchat_position', 'bottom-right' ),
				'offsetX'         => absint( get_option( 'adventchat_offset_x', 20 ) ),
				'offsetY'         => absint( get_option( 'adventchat_offset_y', 20 ) ),
				'primaryColor'    => get_option( 'adventchat_primary_color', '#0066ff' ),
				'secondaryColor'  => get_option( 'adventchat_secondary_color', '#ffffff' ),
				'launcherStyle'   => get_option( 'adventchat_launcher_style', 'bubble' ),
				'welcomeTitle'    => get_option( 'adventchat_welcome_title', 'Hi there! 👋' ),
				'welcomeSubtitle' => get_option( 'adventchat_welcome_subtitle', 'How can we help you?' ),
				'placeholder'     => get_option( 'adventchat_input_placeholder', 'Type a message…' ),
				'soundEnabled'    => get_option( 'adventchat_sound_enabled', '1' ),
				'prechatEnabled'  => get_option( 'adventchat_prechat_enabled', '1' ),
				'gdprEnabled'     => get_option( 'adventchat_gdpr_enabled', '0' ),
				'offlineEnabled'  => get_option( 'adventchat_offline_enabled', '1' ),
				'csatEnabled'     => get_option( 'adventchat_csat_enabled', '1' ),
				'fileSharing'     => get_option( 'adventchat_file_sharing', '1' ),
			),
		);

		// Cache for 60 seconds.
		set_transient( 'adventchat_widget_config', $data, 60 );

		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Get client IP address.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return string
	 */
	private function get_client_ip( \WP_REST_Request $request ): string {
		$ip = $request->get_header( 'X-Forwarded-For' );
		if ( $ip ) {
			$parts = explode( ',', $ip );
			return trim( $parts[0] );
		}
		return sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1' );
	}
}
