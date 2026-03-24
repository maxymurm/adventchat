<?php
/**
 * REST API endpoints for operator authentication.
 *
 * Handles syncing WordPress operators to Firebase Auth (Email/Password).
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Api_Operators
 */
class AdventChat_Api_Operators extends AdventChat_Api_Controller {

	/**
	 * Register operator auth routes.
	 */
	public function register_routes(): void {
		register_rest_route( $this->namespace, '/operators/sync', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'sync_operator' ),
			'permission_callback' => array( $this, 'admin_access' ),
		) );

		register_rest_route( $this->namespace, '/operators/token', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'get_operator_token' ),
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
	 * Sync a WP user to Firebase Auth — creates a Firebase user if one doesn't exist.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function sync_operator( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$wp_user_id = $request->get_param( 'user_id' );

		if ( empty( $wp_user_id ) ) {
			// Default to current user.
			$wp_user_id = get_current_user_id();
		}

		$wp_user = get_userdata( (int) $wp_user_id );
		if ( ! $wp_user ) {
			return $this->error( 'user_not_found', __( 'WordPress user not found.', 'adventchat' ), 404 );
		}

		$email = $wp_user->user_email;

		// Check if Firebase user already exists.
		$existing = AdventChat_Firebase_Admin::get_user_by_email( $email );
		if ( is_wp_error( $existing ) ) {
			return $this->error( 'firebase_error', $existing->get_error_message(), 500 );
		}

		if ( null !== $existing ) {
			// Already synced — update WP user meta with Firebase UID.
			update_user_meta( $wp_user_id, 'adventchat_firebase_uid', $existing['localId'] );
			return $this->success( array(
				'firebase_uid' => $existing['localId'],
				'email'        => $email,
				'created'      => false,
			) );
		}

		// Create Firebase user.
		$password = AdventChat_Firebase_Admin::generate_password();
		$result   = AdventChat_Firebase_Admin::create_user( $email, $password );

		if ( is_wp_error( $result ) ) {
			return $this->error( 'firebase_create_failed', $result->get_error_message(), 500 );
		}

		// Store Firebase UID in WP user meta.
		update_user_meta( $wp_user_id, 'adventchat_firebase_uid', $result['localId'] );

		// Store encrypted password so we can sign the operator in later.
		AdventChat_Options::set( "operator_firebase_pw_{$wp_user_id}", $password );

		return $this->success( array(
			'firebase_uid' => $result['localId'],
			'email'        => $email,
			'created'      => true,
		), 201 );
	}

	/**
	 * Get a Firebase custom token / sign-in credentials for the current operator.
	 *
	 * The console JS will use this to sign in to Firebase.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_operator_token( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id = get_current_user_id();
		$email   = wp_get_current_user()->user_email;

		$firebase_uid = get_user_meta( $user_id, 'adventchat_firebase_uid', true );
		if ( empty( $firebase_uid ) ) {
			return $this->error( 'not_synced', __( 'Operator not synced to Firebase. Please sync first.', 'adventchat' ), 400 );
		}

		$password = AdventChat_Options::get( "operator_firebase_pw_{$user_id}" );
		if ( empty( $password ) ) {
			return $this->error( 'no_credentials', __( 'Firebase credentials not found. Re-sync the operator.', 'adventchat' ), 400 );
		}

		// Sign in via REST API to get an idToken.
		$config_json = AdventChat_Options::get( 'firebase_config' );
		$config      = json_decode( $config_json, true );
		$api_key     = $config['apiKey'] ?? '';

		if ( empty( $api_key ) ) {
			return $this->error( 'no_config', __( 'Firebase config not found.', 'adventchat' ), 500 );
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
			return $this->error( 'firebase_auth_failed', $response->get_error_message(), 500 );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			$msg = $body['error']['message'] ?? __( 'Firebase sign-in failed', 'adventchat' );
			return $this->error( 'firebase_signin_failed', $msg, 500 );
		}

		return $this->success( array(
			'idToken'      => $body['idToken'],
			'refreshToken' => $body['refreshToken'],
			'expiresIn'    => $body['expiresIn'],
			'firebase_uid' => $firebase_uid,
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
}
