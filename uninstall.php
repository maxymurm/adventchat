<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package AdventChat
 */

// Abort if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Drop custom tables.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}adventchat_offline_messages" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}adventchat_chat_logs" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

// Delete all adventchat options.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		'adventchat_%'
	)
); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

// Remove custom role.
remove_role( 'adventchat_operator' );

// Remove operator capability from admin role.
$admin = get_role( 'administrator' );
if ( $admin ) {
	$admin->remove_cap( 'adventchat_operator' );
}
