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
		// Check if we have stored credentials — try signing in to verify the user exists.
		// The accounts:lookup endpoint requires an idToken, so we use a sign-in probe instead.
		// This is a lightweight check; the sync flow handles EMAIL_EXISTS as a fallback.

		$api_key = self::get_api_key();
		if ( ! $api_key ) {
			return new WP_Error( 'no_firebase_config', __( 'Firebase configuration not found.', 'adventchat' ) );
		}

		// Use createAuthUri to check if email exists without needing an idToken.
		$url      = 'https://identitytoolkit.googleapis.com/v1/accounts:createAuthUri?key=' . rawurlencode( $api_key );
		$response = wp_remote_post( $url, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array(
				'identifier'  => $email,
				'continueUri' => home_url(),
			) ),
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		// If registered is true, user exists. But we can't get localId from this endpoint.
		// Return null to let the sync flow try create_user and handle EMAIL_EXISTS gracefully.
		if ( ! empty( $body['registered'] ) ) {
			// User exists but we can't get their UID without signing in.
			// Return null so sync_operator falls through to create_user → EMAIL_EXISTS → fallback.
			return null;
		}

		return null;
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

	/**
	 * Sign in a Firebase user with email and password.
	 *
	 * @param string $email    User email.
	 * @param string $password User password.
	 * @return array{localId: string, idToken: string}|WP_Error
	 */
	public static function sign_in( string $email, string $password ): array|WP_Error {
		$api_key = self::get_api_key();
		if ( ! $api_key ) {
			return new WP_Error( 'no_firebase_config', __( 'Firebase configuration not found.', 'adventchat' ) );
		}

		$url      = 'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=' . rawurlencode( $api_key );
		$response = wp_remote_post( $url, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array(
				'email'             => $email,
				'password'          => $password,
				'returnSecureToken' => true,
			) ),
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			$msg = $body['error']['message'] ?? __( 'Firebase sign-in failed', 'adventchat' );
			return new WP_Error( 'firebase_signin_failed', $msg );
		}

		return array(
			'localId' => $body['localId'],
			'idToken' => $body['idToken'],
		);
	}
}
