<?php
/**
 * REST API — Visitor-facing endpoints (offline message, transcript, file upload).
 *
 * WP-55: Offline message submission.
 * WP-57: Chat transcript email.
 * WP-60: File upload to Firebase Storage.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

class AdventChat_Api_Visitor extends AdventChat_Api_Controller {

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		// WP-55: Offline message.
		register_rest_route( ADVENTCHAT_API_NAMESPACE, '/offline-message', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'submit_offline_message' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'name'       => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
				'email'      => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_email' ),
				'message'    => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ),
				'department' => array( 'required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ),
				'consent'    => array( 'required' => false, 'type' => 'boolean', 'default' => false ),
			),
		) );

		// WP-57: Transcript email.
		register_rest_route( ADVENTCHAT_API_NAMESPACE, '/transcript', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'send_transcript' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'email'    => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_email' ),
				'name'     => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
				'messages' => array( 'required' => true, 'type' => 'array' ),
			),
		) );
	}

	/**
	 * WP-55: Submit offline message → save to DB + email notification.
	 */
	public function submit_offline_message( WP_REST_Request $request ): WP_REST_Response {
		$data = array(
			'name'       => $request->get_param( 'name' ),
			'email'      => $request->get_param( 'email' ),
			'message'    => $request->get_param( 'message' ),
			'department' => $request->get_param( 'department' ),
		);

		if ( empty( $data['name'] ) || empty( $data['email'] ) || empty( $data['message'] ) ) {
			return new WP_REST_Response( array( 'error' => 'Missing required fields.' ), 400 );
		}

		if ( ! is_email( $data['email'] ) ) {
			return new WP_REST_Response( array( 'error' => 'Invalid email address.' ), 400 );
		}

		// Save to DB.
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'adventchat_offline_messages',
			array(
				'name'       => $data['name'],
				'email'      => $data['email'],
				'message'    => $data['message'],
				'department' => $data['department'],
				'status'     => 'unread',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		// Send notifications.
		AdventChat_Mailer::send_offline_notification( $data );

		return new WP_REST_Response( array( 'success' => true ), 201 );
	}

	/**
	 * WP-57: Send chat transcript email.
	 */
	public function send_transcript( WP_REST_Request $request ): WP_REST_Response {
		$email    = $request->get_param( 'email' );
		$name     = $request->get_param( 'name' );
		$messages = $request->get_param( 'messages' );

		if ( empty( $email ) || ! is_email( $email ) ) {
			return new WP_REST_Response( array( 'error' => 'Invalid email.' ), 400 );
		}

		// Sanitize messages array.
		$clean = array();
		foreach ( (array) $messages as $msg ) {
			$clean[] = array(
				'senderName' => sanitize_text_field( $msg['senderName'] ?? '' ),
				'senderType' => sanitize_text_field( $msg['senderType'] ?? '' ),
				'text'       => sanitize_text_field( $msg['text'] ?? '' ),
				'timestamp'  => sanitize_text_field( $msg['timestamp'] ?? '' ),
			);
		}

		AdventChat_Mailer::send_transcript( $email, $name, $clean );

		return new WP_REST_Response( array( 'success' => true ) );
	}
}
