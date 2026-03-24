<?php
/**
 * REST API base controller for AdventChat.
 *
 * Provides namespace, auth middleware, and response helpers.
 * All concrete endpoints extend this class.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Api_Controller
 */
class AdventChat_Api_Controller extends WP_REST_Controller {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	protected $namespace = ADVENTCHAT_API_NAMESPACE;

	/**
	 * Register API routes — override in child classes.
	 */
	public function register_routes(): void {
		// Base controller registers no routes.
		// Concrete endpoint classes override this method.
	}

	/**
	 * Permission callback: public (no auth required).
	 *
	 * @return true
	 */
	public function public_access(): true {
		return true;
	}

	/**
	 * Permission callback: requires authenticated WP user with operator capability.
	 *
	 * @return bool|WP_Error
	 */
	public function operator_access(): bool|WP_Error {
		if ( ! current_user_can( 'adventchat_operator' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this resource.', 'adventchat' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Permission callback: requires administrator.
	 *
	 * @return bool|WP_Error
	 */
	public function admin_access(): bool|WP_Error {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this resource.', 'adventchat' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Return a success response.
	 *
	 * @param mixed $data    Response data.
	 * @param int   $status  HTTP status code.
	 * @return WP_REST_Response
	 */
	protected function success( mixed $data = null, int $status = 200 ): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $data,
			),
			$status
		);
	}

	/**
	 * Return an error response.
	 *
	 * @param string $code    Error code.
	 * @param string $message Human-readable message.
	 * @param int    $status  HTTP status code.
	 * @return WP_Error
	 */
	protected function error( string $code, string $message, int $status = 400 ): WP_Error {
		return new WP_Error( $code, $message, array( 'status' => $status ) );
	}

	/**
	 * Validate and sanitize a required string parameter.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @param string          $key     Parameter key.
	 * @return string|WP_Error Sanitized value or error.
	 */
	protected function require_string( WP_REST_Request $request, string $key ): string|WP_Error {
		$value = $request->get_param( $key );
		if ( empty( $value ) || ! is_string( $value ) ) {
			return $this->error(
				'missing_param',
				/* translators: %s: parameter name */
				sprintf( __( 'Missing required parameter: %s', 'adventchat' ), $key )
			);
		}
		return sanitize_text_field( $value );
	}
}
