<?php
/**
 * Mirrors website offline messages into Firestore so operators see them in the apps and get push notifications.
 *
 * The visitor widget stores offline messages in WordPress only. Firestore's rules let any signed-in user create an
 * offline message for this site, so WordPress signs in anonymously (cached, reused until expiry) and writes the
 * document through the Firestore REST API. Failures never block the visitor's submission.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Firestore_Mirror
 */
class AdventChat_Firestore_Mirror {

	/**
	 * Transient holding the cached anonymous ID token.
	 *
	 * @var string
	 */
	private const TOKEN_TRANSIENT = 'adventchat_fs_anon_token';

	/**
	 * Write an offline message to Firestore.
	 *
	 * @param array $data {name, email, message, department}.
	 * @return bool Whether the document was written.
	 */
	public static function offline_message( array $data ): bool {
		$config = json_decode( (string) AdventChat_Options::get( 'firebase_config' ), true );
		if ( empty( $config['apiKey'] ) || empty( $config['projectId'] ) ) {
			return false;
		}

		$fields = array(
			'siteId'     => array( 'stringValue' => md5( get_site_url() ) ),
			'name'       => array( 'stringValue' => (string) ( $data['name'] ?? '' ) ),
			'email'      => array( 'stringValue' => (string) ( $data['email'] ?? '' ) ),
			'message'    => array( 'stringValue' => (string) ( $data['message'] ?? '' ) ),
			'department' => array( 'stringValue' => (string) ( $data['department'] ?? '' ) ),
			'pageUrl'    => array( 'stringValue' => home_url( '/' ) ),
			'status'     => array( 'stringValue' => 'unread' ),
			'source'     => array( 'stringValue' => 'website' ),
			'createdAt'  => array( 'timestampValue' => gmdate( 'Y-m-d\TH:i:s\Z' ) ),
		);

		// A cached token can be revoked early, so retry once with a fresh one.
		for ( $attempt = 0; $attempt < 2; $attempt++ ) {
			$token = self::anonymous_token( $config['apiKey'], 0 === $attempt );
			if ( ! $token ) {
				return false;
			}

			$response = wp_remote_post(
				sprintf( 'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/offlineMessages', rawurlencode( $config['projectId'] ) ),
				array(
					'timeout' => 8,
					'headers' => array(
						'Authorization' => 'Bearer ' . $token,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( array( 'fields' => $fields ) ),
				)
			);

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $code ) {
				return true;
			}
			if ( 401 !== $code && 403 !== $code ) {
				return false;
			}
			delete_transient( self::TOKEN_TRANSIENT );
		}

		return false;
	}

	/**
	 * Get an anonymous Firebase ID token, using the cache when allowed.
	 *
	 * @param string $api_key   Firebase web API key.
	 * @param bool   $use_cache Whether a cached token may be returned.
	 * @return string Empty string on failure.
	 */
	private static function anonymous_token( string $api_key, bool $use_cache ): string {
		if ( $use_cache ) {
			$cached = get_transient( self::TOKEN_TRANSIENT );
			if ( is_string( $cached ) && '' !== $cached ) {
				return $cached;
			}
		}

		$response = wp_remote_post(
			'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . rawurlencode( $api_key ),
			array(
				'timeout' => 8,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array( 'returnSecureToken' => true ) ),
			)
		);
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}

		$body  = json_decode( wp_remote_retrieve_body( $response ), true );
		$token = (string) ( $body['idToken'] ?? '' );
		if ( '' !== $token ) {
			// ID tokens last an hour; keep a safety margin.
			set_transient( self::TOKEN_TRANSIENT, $token, 50 * MINUTE_IN_SECONDS );
		}

		return $token;
	}
}
