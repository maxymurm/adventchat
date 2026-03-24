<?php
/**
 * Plugin Name:       AdventChat
 * Plugin URI:        https://adventchat.com
 * Description:       Real-time live chat for WordPress powered by Firebase. Free forever with your own Firebase, or upgrade for hosted infrastructure and a mobile operator app.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            AdventChat
 * Author URI:        https://adventchat.com
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       adventchat
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'ADVENTCHAT_VERSION', '1.0.0' );
define( 'ADVENTCHAT_PLUGIN_FILE', __FILE__ );
define( 'ADVENTCHAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADVENTCHAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ADVENTCHAT_SLUG', 'adventchat' );
define( 'ADVENTCHAT_DB_VERSION', '1.0' );
define( 'ADVENTCHAT_API_NAMESPACE', 'adventchat/v1' );
define( 'ADVENTCHAT_VALIDATION_URL', 'https://adventchat.com/wp-json/adventchat/v1/validate-license' );

// Autoloader.
require_once ADVENTCHAT_PLUGIN_DIR . 'includes/class-adventchat-autoloader.php';

// Activation / deactivation hooks.
register_activation_hook( __FILE__, array( 'AdventChat_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AdventChat_Deactivator', 'deactivate' ) );

/**
 * Returns the main plugin instance.
 *
 * @return AdventChat
 */
function adventchat() {
	return AdventChat::instance();
}

// Boot the plugin.
adventchat();
