<?php
/**
 * Plugin deactivation handler.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Deactivator
 */
class AdventChat_Deactivator {

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate(): void {
		// Remove the custom operator role.
		remove_role( 'adventchat_operator' );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
