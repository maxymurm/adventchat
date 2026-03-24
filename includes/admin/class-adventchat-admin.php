<?php
/**
 * Admin-facing functionality for AdventChat.
 *
 * Handles AJAX endpoints (e.g., Firebase config test).
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Admin
 */
class AdventChat_Admin {

	/**
	 * Initialize admin hooks.
	 */
	public static function init(): void {
		add_action( 'wp_ajax_adventchat_test_firebase', array( __CLASS__, 'ajax_test_firebase' ) );
	}

	/**
	 * AJAX: Test Firebase configuration by calling the Firebase Auth REST endpoint.
	 */
	public static function ajax_test_firebase(): void {
		check_ajax_referer( 'adventchat_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'adventchat' ) ), 403 );
		}

		$config_json = AdventChat_Options::get( 'firebase_config' );
		if ( empty( $config_json ) ) {
			wp_send_json_error( array( 'message' => __( 'No Firebase config found. Save your config first.', 'adventchat' ) ) );
		}

		$config = json_decode( $config_json, true );
		if ( empty( $config['apiKey'] ) || empty( $config['projectId'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Firebase config is missing apiKey or projectId.', 'adventchat' ) ) );
		}

		// Test connectivity by calling the Firebase Auth REST API (signUp anonymously endpoint).
		$url      = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . rawurlencode( $config['apiKey'] );
		$response = wp_remote_post(
			$url,
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array( 'returnSecureToken' => true ) ),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array(
				'message' => sprintf(
					/* translators: %s: error message */
					__( 'Connection failed: %s', 'adventchat' ),
					$response->get_error_message()
				),
			) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 === $code && ! empty( $body['localId'] ) ) {
			wp_send_json_success( array(
				'message'   => __( 'Firebase connection successful! Anonymous auth is working.', 'adventchat' ),
				'projectId' => $config['projectId'],
			) );
		}

		// Parse Firebase error.
		$error_message = $body['error']['message'] ?? __( 'Unknown error', 'adventchat' );

		if ( str_contains( $error_message, 'ADMIN_ONLY_OPERATION' ) || str_contains( $error_message, 'OPERATION_NOT_ALLOWED' ) ) {
			wp_send_json_error( array(
				'message' => __( 'API key is valid but Anonymous Auth is not enabled. Please enable it in Firebase Console → Authentication → Sign-in method.', 'adventchat' ),
			) );
		}

		wp_send_json_error( array(
			'message' => sprintf(
				/* translators: %s: Firebase error message */
				__( 'Firebase error: %s', 'adventchat' ),
				$error_message
			),
		) );
	}
}
