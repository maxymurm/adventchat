<?php
/**
 * PSR-4-style autoloader for AdventChat classes.
 *
 * Maps class name prefixes to file paths inside includes/.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	function ( string $class_name ): void {
		// Only handle our own classes.
		$prefix = 'AdventChat';
		if ( strncmp( $class_name, $prefix, strlen( $prefix ) ) !== 0 ) {
			return;
		}

		$relative = strtolower( str_replace( '_', '-', $class_name ) );
		$file     = 'class-' . $relative . '.php';
		$base     = ADVENTCHAT_PLUGIN_DIR . 'includes/';

		// Check subdirectories first, then root includes/.
		$subdirs = array( 'admin/', 'api/', 'integrations/' );

		foreach ( $subdirs as $sub ) {
			$path = $base . $sub . $file;
			if ( file_exists( $path ) ) {
				require_once $path;
				return;
			}
		}

		// Root includes/.
		$path = $base . $file;
		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);
