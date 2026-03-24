<?php
/**
 * Tests for REST API endpoints.
 *
 * @package AdventChat\Tests
 */

class RestApiTest extends WP_UnitTestCase {

	/**
	 * Widget config endpoint returns 503 when Firebase not configured.
	 */
	public function test_widget_config_returns_503_without_firebase(): void {
		delete_option( 'adventchat_options' );
		delete_transient( 'adventchat_widget_config' );

		$request  = new WP_REST_Request( 'GET', '/adventchat/v1/widget-config' );
		$response = rest_do_request( $request );

		$this->assertSame( 503, $response->get_status() );
	}

	/**
	 * Widget config endpoint returns 200 with proper Firebase config.
	 */
	public function test_widget_config_returns_200_with_config(): void {
		// Set up encrypted Firebase config.
		$mock_config = wp_json_encode( array(
			'apiKey'            => 'test-key',
			'authDomain'        => 'test.firebaseapp.com',
			'projectId'         => 'test-project',
			'storageBucket'     => 'test.appspot.com',
			'messagingSenderId' => '123456',
			'appId'             => '1:123456:web:abc',
		) );

		// Store via options directly for testing.
		AdventChat_Options::set( 'firebase_config', $mock_config );
		delete_transient( 'adventchat_widget_config' );

		$request  = new WP_REST_Request( 'GET', '/adventchat/v1/widget-config' );
		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'firebase', $data );
		$this->assertArrayHasKey( 'siteId', $data );
		$this->assertArrayHasKey( 'settings', $data );
	}

	/**
	 * Visitor offline endpoint requires name and email.
	 */
	public function test_visitor_offline_requires_fields(): void {
		$request = new WP_REST_Request( 'POST', '/adventchat/v1/visitor/offline' );
		$request->set_body_params( array() );

		$response = rest_do_request( $request );

		// Should return 400 for missing required fields.
		$this->assertContains( $response->get_status(), array( 400, 422 ) );
	}

	/**
	 * Operators endpoint requires authentication.
	 */
	public function test_operators_endpoint_requires_auth(): void {
		$request  = new WP_REST_Request( 'GET', '/adventchat/v1/operators' );
		$response = rest_do_request( $request );

		$this->assertSame( 401, $response->get_status() );
	}
}
