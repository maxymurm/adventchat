<?php
/**
 * Custom operator role and capabilities.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Roles
 */
class AdventChat_Roles {

	/**
	 * Initialize — add custom capability to administrator.
	 */
	public static function init(): void {
		// Only run the role setup once (on first init after activation).
		if ( get_option( 'adventchat_roles_version' ) === ADVENTCHAT_VERSION ) {
			return;
		}

		self::create_roles();
		update_option( 'adventchat_roles_version', ADVENTCHAT_VERSION );
	}

	/**
	 * Create the custom operator role and add capabilities to admin.
	 */
	public static function create_roles(): void {
		// Add custom role.
		add_role(
			'adventchat_operator',
			__( 'Chat Operator', 'adventchat' ),
			array(
				'read'                 => true,
				'adventchat_operator'  => true,
			)
		);

		// Grant operator capability to administrators.
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'adventchat_operator' );
		}
	}
}
