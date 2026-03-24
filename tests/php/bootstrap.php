<?php
/**
 * PHPUnit bootstrap for AdventChat tests.
 *
 * Loads the WordPress test suite and the plugin.
 *
 * @package AdventChat\Tests
 */

// Composer autoloader.
$autoloader = dirname( __DIR__, 2 ) . '/vendor/autoload.php';
if ( file_exists( $autoloader ) ) {
	require_once $autoloader;
}

// Locate WP test suite (from WP_TESTS_DIR env or default path).
$wp_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $wp_tests_dir ) {
	$wp_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $wp_tests_dir . '/includes/functions.php' ) ) {
	// Fall back: define stubs for unit tests that don't need WP loaded.
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', '/tmp/' );
	}
	return;
}

// Load WP test suite functions.
require_once $wp_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin.
 */
tests_add_filter( 'muplugins_loaded', static function () {
	require dirname( __DIR__, 2 ) . '/adventchat.php';
} );

// Start the WP testing environment.
require $wp_tests_dir . '/includes/bootstrap.php';
