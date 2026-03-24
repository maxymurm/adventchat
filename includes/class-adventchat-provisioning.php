<?php
/**
 * Hosted Firebase provisioning.
 *
 * WP-81: On license activation, calls adventchat.com provisioning API
 * to get a hosted Firebase config. Stores it encrypted.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Provisioning
 */
class AdventChat_Provisioning {

	/**
	 * Option key for provisioning status.
	 *
	 * @var string
	 */
	private const STATUS_OPTION = 'adventchat_hosted_status';

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'wp_ajax_adventchat_provision_firebase', array( __CLASS__, 'ajax_provision' ) );
		add_action( 'wp_ajax_adventchat_disconnect_firebase', array( __CLASS__, 'ajax_disconnect' ) );
	}

	/**
	 * Check if site is connected to hosted Firebase.
	 *
	 * @return bool
	 */
	public static function is_connected(): bool {
		return 'connected' === get_option( self::STATUS_OPTION, '' );
	}

	/**
	 * AJAX: Provision hosted Firebase.
	 */
	public static function ajax_provision(): void {
		check_ajax_referer( 'adventchat_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'adventchat' ) ) );
		}

		if ( ! AdventChat_License::is_pro() ) {
			wp_send_json_error( array( 'message' => __( 'A Pro or Agency license is required.', 'adventchat' ) ) );
		}

		$license_key = get_option( 'adventchat_license_key', '' );

		$response = wp_remote_post( 'https://adventchat.com/api/provision-firebase', array(
			'timeout' => 30,
			'body'    => array(
				'license_key' => $license_key,
				'site_url'    => get_site_url(),
				'site_name'   => get_bloginfo( 'name' ),
			),
		) );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			wp_send_json_error( array( 'message' => __( 'Provisioning failed. Please try again.', 'adventchat' ) ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || empty( $body['firebase_config'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid provisioning response.', 'adventchat' ) ) );
		}

		// Store the hosted Firebase config encrypted.
		$config_json = wp_json_encode( $body['firebase_config'] );
		AdventChat_Options::set( 'firebase_config', $config_json );

		// Store service account key if provided.
		if ( ! empty( $body['service_account'] ) ) {
			AdventChat_Options::set( 'firebase_service_account', wp_json_encode( $body['service_account'] ) );
		}

		update_option( self::STATUS_OPTION, 'connected' );

		wp_send_json_success( array(
			'message' => __( 'Connected to Hosted Firebase!', 'adventchat' ),
		) );
	}

	/**
	 * AJAX: Disconnect hosted Firebase.
	 */
	public static function ajax_disconnect(): void {
		check_ajax_referer( 'adventchat_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'adventchat' ) ) );
		}

		delete_option( self::STATUS_OPTION );

		wp_send_json_success( array( 'message' => __( 'Disconnected from Hosted Firebase.', 'adventchat' ) ) );
	}

	/**
	 * Render the connection status UI for the account settings page.
	 */
	public static function render_status(): void {
		$connected = self::is_connected();
		$license   = AdventChat_License::validate();

		echo '<div class="adventchat-provisioning">';

		if ( $connected ) {
			printf(
				'<p class="adventchat-status adventchat-status--connected">%s</p>',
				esc_html__( '✓ Connected to Hosted Firebase', 'adventchat' )
			);
			printf(
				'<button type="button" class="button" id="adventchat-disconnect-firebase">%s</button>',
				esc_html__( 'Disconnect', 'adventchat' )
			);
		} elseif ( $license['valid'] && in_array( $license['plan'], array( 'pro', 'agency' ), true ) ) {
			printf(
				'<p>%s</p>',
				esc_html__( 'Use our hosted Firebase — no setup required.', 'adventchat' )
			);
			printf(
				'<button type="button" class="button button-primary" id="adventchat-provision-firebase">%s</button>',
				esc_html__( 'Connect to Hosted Firebase', 'adventchat' )
			);
		} else {
			printf(
				'<p class="description">%s</p>',
				esc_html__( 'Hosted Firebase is available with a Pro or Agency license.', 'adventchat' )
			);
		}

		echo '<span id="adventchat-provision-result" style="margin-left:10px;"></span>';
		echo '</div>';

		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				var provision = document.getElementById("adventchat-provision-firebase");
				var disconnect = document.getElementById("adventchat-disconnect-firebase");
				var result = document.getElementById("adventchat-provision-result");

				function doAjax(action) {
					result.textContent = "Processing...";
					fetch(adventchatAdmin.ajaxUrl, {
						method: "POST",
						headers: { "Content-Type": "application/x-www-form-urlencoded" },
						body: "action=" + action + "&nonce=" + adventchatAdmin.nonce
					}).then(function(r){return r.json();}).then(function(d){
						result.textContent = d.data.message;
						result.style.color = d.success ? "green" : "red";
						if (d.success) setTimeout(function(){location.reload();}, 1500);
					});
				}

				if (provision) provision.addEventListener("click", function(){ doAjax("adventchat_provision_firebase"); });
				if (disconnect) disconnect.addEventListener("click", function(){ doAjax("adventchat_disconnect_firebase"); });
			});
		</script>';
	}
}
