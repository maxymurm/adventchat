<?php
/**
 * Lemon Squeezy license validation + premium feature gating.
 *
 * WP-79: License validate() calls adventchat.com/.../validate-license.
 * WP-80: is_pro() and is_agency() static methods; upsell UI for locked features.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_License
 */
class AdventChat_License {

	/**
	 * Option key for cached license data.
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'adventchat_license_cache';

	/**
	 * Cache duration in seconds (24 hours).
	 *
	 * @var int
	 */
	private const CACHE_TTL = 86400;

	/**
	 * Initialize license hooks.
	 */
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'wp_ajax_adventchat_activate_license', array( __CLASS__, 'ajax_activate' ) );
		add_action( 'wp_ajax_adventchat_deactivate_license', array( __CLASS__, 'ajax_deactivate' ) );
	}

	/**
	 * Register license settings.
	 */
	public static function register_settings(): void {
		register_setting( 'adventchat_general', 'adventchat_license_key', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );
	}

	/**
	 * Validate the stored license key against the API.
	 *
	 * @param bool $force Skip cache and re-validate.
	 * @return array{ valid: bool, plan: string, expires_at: string }
	 */
	public static function validate( bool $force = false ): array {
		$default = array( 'valid' => false, 'plan' => 'free', 'expires_at' => '' );

		$license_key = get_option( 'adventchat_license_key', '' );
		if ( '' === $license_key ) {
			delete_transient( self::CACHE_KEY );
			return $default;
		}

		if ( ! $force ) {
			$cached = get_transient( self::CACHE_KEY );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$response = wp_remote_post( 'https://adventchat.com/api/validate-license', array(
			'timeout' => 15,
			'body'    => array(
				'license_key' => $license_key,
				'site_url'    => get_site_url(),
			),
		) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			// On network error, use cached data if available, else default.
			$cached = get_transient( self::CACHE_KEY );
			return is_array( $cached ) ? $cached : $default;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return $default;
		}

		$result = array(
			'valid'      => ! empty( $body['valid'] ),
			'plan'       => sanitize_key( $body['plan'] ?? 'free' ),
			'expires_at' => sanitize_text_field( $body['expires_at'] ?? '' ),
		);

		set_transient( self::CACHE_KEY, $result, self::CACHE_TTL );

		return $result;
	}

	/**
	 * Check if the current license is Pro or higher.
	 *
	 * @return bool
	 */
	public static function is_pro(): bool {
		$license = self::validate();
		return $license['valid'] && in_array( $license['plan'], array( 'pro', 'agency' ), true );
	}

	/**
	 * Check if the current license is Agency tier.
	 *
	 * @return bool
	 */
	public static function is_agency(): bool {
		$license = self::validate();
		return $license['valid'] && 'agency' === $license['plan'];
	}

	/**
	 * Get the current plan name.
	 *
	 * @return string
	 */
	public static function get_plan(): string {
		$license = self::validate();
		return $license['valid'] ? $license['plan'] : 'free';
	}

	/**
	 * AJAX: Activate license.
	 */
	public static function ajax_activate(): void {
		check_ajax_referer( 'adventchat_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'adventchat' ) ) );
		}

		$key = sanitize_text_field( wp_unslash( $_POST['license_key'] ?? '' ) );
		if ( '' === $key ) {
			wp_send_json_error( array( 'message' => __( 'License key is required.', 'adventchat' ) ) );
		}

		update_option( 'adventchat_license_key', $key );
		delete_transient( self::CACHE_KEY );

		$result = self::validate( true );

		if ( $result['valid'] ) {
			wp_send_json_success( array(
				'message' => sprintf(
					/* translators: %s: plan name */
					__( 'License activated! Plan: %s', 'adventchat' ),
					ucfirst( $result['plan'] )
				),
				'plan'       => $result['plan'],
				'expires_at' => $result['expires_at'],
			) );
		} else {
			delete_option( 'adventchat_license_key' );
			wp_send_json_error( array( 'message' => __( 'Invalid license key.', 'adventchat' ) ) );
		}
	}

	/**
	 * AJAX: Deactivate license.
	 */
	public static function ajax_deactivate(): void {
		check_ajax_referer( 'adventchat_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'adventchat' ) ) );
		}

		delete_option( 'adventchat_license_key' );
		delete_transient( self::CACHE_KEY );

		wp_send_json_success( array( 'message' => __( 'License deactivated.', 'adventchat' ) ) );
	}

	/**
	 * Render an upsell notice for locked features.
	 *
	 * @param string $feature Feature name.
	 * @param string $tier    Required tier (pro|agency).
	 */
	public static function render_upsell( string $feature, string $tier = 'pro' ): void {
		printf(
			'<div class="adventchat-upsell"><p>%s</p><a href="%s" target="_blank" class="button button-primary">%s</a></div>',
			esc_html(
				sprintf(
					/* translators: 1: Feature name, 2: Tier name */
					__( '%1$s requires the %2$s plan.', 'adventchat' ),
					$feature,
					ucfirst( $tier )
				)
			),
			'https://adventchat.com/pricing',
			esc_html__( 'Upgrade Now', 'adventchat' )
		);
	}
}
