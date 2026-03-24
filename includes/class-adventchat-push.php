<?php
/**
 * Mobile FCM push notification dispatch.
 *
 * WP-83: Sends FCM HTTP v1 push to operator devices when new session created.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Push
 */
class AdventChat_Push {

	/**
	 * FCM API endpoint.
	 *
	 * @var string
	 */
	private const FCM_URL = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		// Trigger push on new session (called from session creation REST or Firestore).
		add_action( 'adventchat_new_session', array( __CLASS__, 'dispatch' ), 10, 2 );
	}

	/**
	 * Dispatch FCM push to all online operators.
	 *
	 * @param string $session_id   Firestore session document ID.
	 * @param array  $session_data Session data.
	 */
	public static function dispatch( string $session_id, array $session_data ): void {
		if ( ! AdventChat_License::is_pro() ) {
			return;
		}

		$tokens = self::get_operator_tokens();
		if ( empty( $tokens ) ) {
			return;
		}

		$access_token = self::get_access_token();
		if ( ! $access_token ) {
			return;
		}

		$project_id = self::get_project_id();
		if ( ! $project_id ) {
			return;
		}

		$visitor_name = sanitize_text_field( $session_data['visitorName'] ?? __( 'Visitor', 'adventchat' ) );
		$page_url     = esc_url_raw( $session_data['visitorInfo']['pageUrl'] ?? '' );

		$invalid_tokens = array();

		foreach ( $tokens as $token ) {
			$payload = array(
				'message' => array(
					'token'        => $token,
					'notification' => array(
						'title' => sprintf(
							/* translators: %s: Visitor name */
							__( 'New chat from %s', 'adventchat' ),
							$visitor_name
						),
						'body'  => $page_url ? $page_url : __( 'A visitor started a new chat.', 'adventchat' ),
					),
					'data' => array(
						'sessionId' => $session_id,
						'type'      => 'new_session',
					),
					'android' => array(
						'priority' => 'high',
					),
					'apns' => array(
						'payload' => array(
							'aps' => array(
								'sound' => 'default',
								'badge' => 1,
							),
						),
					),
				),
			);

			$url      = sprintf( self::FCM_URL, $project_id );
			$response = wp_remote_post( $url, array(
				'timeout' => 10,
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
			) );

			$code = wp_remote_retrieve_response_code( $response );
			if ( 404 === $code || 410 === $code ) {
				$invalid_tokens[] = $token;
			}
		}

		// Clean up invalid tokens.
		if ( ! empty( $invalid_tokens ) ) {
			self::remove_tokens( $invalid_tokens );
		}
	}

	/**
	 * Get FCM tokens for all online operators from Firestore.
	 *
	 * @return string[]
	 */
	private static function get_operator_tokens(): array {
		$firebase_admin = new AdventChat_Firebase_Admin();
		$site_id        = md5( get_site_url() );

		$agents_url = sprintf(
			'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/sites/%s/agents',
			$firebase_admin->get_project_id(),
			$site_id
		);

		$response = $firebase_admin->authenticated_request( $agents_url );
		if ( is_wp_error( $response ) ) {
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$tokens = array();

		if ( ! empty( $body['documents'] ) ) {
			foreach ( $body['documents'] as $doc ) {
				$fields = $doc['fields'] ?? array();
				$status = $fields['status']['stringValue'] ?? '';
				$fcm    = $fields['fcmToken']['stringValue'] ?? '';

				if ( 'online' === $status && '' !== $fcm ) {
					$tokens[] = $fcm;
				}
			}
		}

		return $tokens;
	}

	/**
	 * Get a Google OAuth2 access token for FCM.
	 *
	 * @return string|false
	 */
	private static function get_access_token(): string|false {
		$firebase_admin = new AdventChat_Firebase_Admin();
		return $firebase_admin->get_access_token();
	}

	/**
	 * Get the Firebase project ID.
	 *
	 * @return string
	 */
	private static function get_project_id(): string {
		$config = AdventChat_Options::get( 'firebase_config' );
		if ( empty( $config ) ) {
			return '';
		}

		$decoded = json_decode( $config, true );
		return $decoded['projectId'] ?? '';
	}

	/**
	 * Remove invalid FCM tokens from Firestore agent docs.
	 *
	 * @param string[] $invalid_tokens Tokens to remove.
	 */
	private static function remove_tokens( array $invalid_tokens ): void {
		$firebase_admin = new AdventChat_Firebase_Admin();
		$site_id        = md5( get_site_url() );

		$agents_url = sprintf(
			'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/sites/%s/agents',
			$firebase_admin->get_project_id(),
			$site_id
		);

		$response = $firebase_admin->authenticated_request( $agents_url );
		if ( is_wp_error( $response ) ) {
			return;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['documents'] ) ) {
			return;
		}

		foreach ( $body['documents'] as $doc ) {
			$fields   = $doc['fields'] ?? array();
			$fcm      = $fields['fcmToken']['stringValue'] ?? '';
			$doc_name = $doc['name'] ?? '';

			if ( in_array( $fcm, $invalid_tokens, true ) && '' !== $doc_name ) {
				$update_url = 'https://firestore.googleapis.com/v1/' . $doc_name . '?updateMask.fieldPaths=fcmToken';
				$firebase_admin->authenticated_request( $update_url, 'PATCH', array(
					'fields' => array(
						'fcmToken' => array( 'stringValue' => '' ),
					),
				) );
			}
		}
	}
}
