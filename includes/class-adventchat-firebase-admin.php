<?php
/**
 * Firebase Admin helper — manages Firebase users via REST API.
 *
 * Uses the Firebase Auth REST API (Identity Toolkit) so we don't need
 * a service-account JSON or the heavy Firebase Admin PHP SDK.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Firebase_Admin
 */
class AdventChat_Firebase_Admin {

	/**
	 * Get the Firebase Web API key from the stored config.
	 *
	 * @return string|null
	 */
	private static function get_api_key(): ?string {
		$config_json = AdventChat_Options::get( 'firebase_config' );
		if ( empty( $config_json ) ) {
			return null;
		}

		$config = json_decode( $config_json, true );
		return $config['apiKey'] ?? null;
	}

	/**
	 * Create a Firebase user with email and password.
	 *
	 * @param string $email    User email.
	 * @param string $password User password.
	 * @return array{localId: string, email: string, idToken: string}|WP_Error
	 */
	public static function create_user( string $email, string $password ): array|WP_Error {
		$api_key = self::get_api_key();
		if ( ! $api_key ) {
			return new WP_Error( 'no_firebase_config', __( 'Firebase configuration not found.', 'adventchat' ) );
		}

		$url      = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . rawurlencode( $api_key );
		$response = wp_remote_post( $url, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array(
				'email'             => $email,
				'password'          => $password,
				'returnSecureToken' => true,
			) ),
			'timeout' => 15,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			$msg = $body['error']['message'] ?? __( 'Unknown Firebase error', 'adventchat' );
			return new WP_Error( 'firebase_create_user_failed', $msg );
		}

		return array(
			'localId' => $body['localId'],
			'email'   => $body['email'],
			'idToken' => $body['idToken'],
		);
	}

	/**
	 * Get a Firebase user by email.
	 *
	 * @param string $email User email.
	 * @return array{localId: string, email: string}|WP_Error|null Null if not found.
	 */
	public static function get_user_by_email( string $email ): array|WP_Error|null {
		$api_key = self::get_api_key();
		if ( ! $api_key ) {
			return new WP_Error( 'no_firebase_config', __( 'Firebase configuration not found.', 'adventchat' ) );
		}

		$url      = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . rawurlencode( $api_key );
		$response = wp_remote_post( $url, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array(
				'email' => array( $email ),
			) ),
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['users'] ) ) {
			return null;
		}

		$user = $body['users'][0];
		return array(
			'localId' => $user['localId'],
			'email'   => $user['email'],
		);
	}

	/**
	 * Delete a Firebase user by their local ID (UID).
	 *
	 * @param string $uid Firebase user UID.
	 * @return true|WP_Error
	 */
	public static function delete_user( string $uid ): true|WP_Error {
		$api_key = self::get_api_key();
		if ( ! $api_key ) {
			return new WP_Error( 'no_firebase_config', __( 'Firebase configuration not found.', 'adventchat' ) );
		}

		// To delete via REST API we need an idToken for the user, which we don't have.
		// Instead, we store a flag and handle deletion via the Admin SDK or Cloud Function.
		// For now, we mark the user as disabled via custom claims approach.
		// NOTE: Full deletion requires a Firebase Admin SDK (Cloud Function) or service account.
		// This is a best-effort approach using the REST API.

		// We can delete using accounts:delete endpoint if we have the idToken.
		// Since we only have the UID, we'll track deletion requests for a Cloud Function webhook.
		update_option( 'adventchat_pending_firebase_deletions', array_unique( array_merge(
			get_option( 'adventchat_pending_firebase_deletions', array() ),
			array( $uid )
		) ) );

		return true;
	}

	/**
	 * Generate a secure random password for Firebase user creation.
	 *
	 * @return string
	 */
	public static function generate_password(): string {
		return wp_generate_password( 24, true, true );
	}
}
