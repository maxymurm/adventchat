<?php
/**
 * REST API endpoints for operator authentication.
 *
 * Operators sign in to Firebase through the `agentSignIn` Cloud Function, which verifies a short-lived WordPress
 * token issued here. No Firebase passwords are created or stored by WordPress.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Api_Operators
 */
class AdventChat_Api_Operators extends AdventChat_Api_Controller {

	/**
	 * Lifetime of the token handed to the console, in seconds.
	 *
	 * @var int
	 */
	private const TOKEN_TTL = 120;

	/**
	 * Register operator auth routes.
	 */
	public function register_routes(): void {
		register_rest_route( $this->namespace, '/operators/agent-token', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'get_agent_token' ),
			'permission_callback' => array( $this, 'operator_access' ),
		) );

		register_rest_route( $this->namespace, '/operators/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_operator' ),
			'permission_callback' => array( $this, 'admin_access' ),
			'args'                => array(
				'id' => array(
					'required'          => true,
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				),
			),
		) );
	}

	/**
	 * Issue a short-lived WordPress JWT for the current operator.
	 *
	 * The console exchanges it for a Firebase session via the `agentSignIn` Cloud Function, which re-verifies the
	 * token and the operator role against this site. The token uses the same format as the JWT Authentication
	 * plugin (HS256, same secret), so it is also accepted by the REST API.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_agent_token( WP_REST_Request $request ) {
		if ( ! defined( 'JWT_AUTH_SECRET_KEY' ) || '' === JWT_AUTH_SECRET_KEY ) {
			return $this->error( 'jwt_not_configured', __( 'Operator sign-in is not configured on this site.', 'adventchat' ), 500 );
		}

		$now     = time();
		$payload = array(
			'iss'  => get_bloginfo( 'url' ),
			'iat'  => $now,
			'nbf'  => $now,
			'exp'  => $now + self::TOKEN_TTL,
			'data' => array( 'user' => array( 'id' => get_current_user_id() ) ),
		);

		return $this->success( array(
			'wpToken'   => self::sign_jwt( $payload, JWT_AUTH_SECRET_KEY ),
			'expiresIn' => self::TOKEN_TTL,
		) );
	}

	/**
	 * Delete operator's Firebase account.
	 *
	 * @param WP_REST_Request $request Request with user ID.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_operator( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$wp_user_id = (int) $request->get_param( 'id' );

		$firebase_uid = get_user_meta( $wp_user_id, 'adventchat_firebase_uid', true );
		if ( empty( $firebase_uid ) ) {
			return $this->error( 'not_synced', __( 'No Firebase user linked to this operator.', 'adventchat' ), 404 );
		}

		$result = AdventChat_Firebase_Admin::delete_user( $firebase_uid );
		if ( is_wp_error( $result ) ) {
			return $this->error( 'firebase_delete_failed', $result->get_error_message(), 500 );
		}

		// Clean up WP meta.
		delete_user_meta( $wp_user_id, 'adventchat_firebase_uid' );
		AdventChat_Options::delete( "operator_firebase_pw_{$wp_user_id}" );

		return $this->success( array( 'deleted' => true ) );
	}

	/**
	 * Sign a JWT with HS256.
	 *
	 * @param array  $payload Claims.
	 * @param string $secret  Signing secret.
	 * @return string
	 */
	private static function sign_jwt( array $payload, string $secret ): string {
		$encode = static function ( string $data ): string {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
		};

		$header  = $encode( wp_json_encode( array( 'typ' => 'JWT', 'alg' => 'HS256' ) ) );
		$body    = $encode( wp_json_encode( $payload ) );
		$signing = $header . '.' . $body;

		return $signing . '.' . $encode( hash_hmac( 'sha256', $signing, $secret, true ) );
	}
}
