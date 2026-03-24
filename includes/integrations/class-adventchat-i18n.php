<?php
/**
 * WPML + Polylang integration.
 *
 * WP-73: Register translatable strings with WPML/Polylang.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_I18n
 */
class AdventChat_I18n {

	/**
	 * Translatable string keys and their defaults.
	 *
	 * @var array<string, string>
	 */
	private static array $strings = array(
		'adventchat_welcome_title'    => 'Hi there! 👋',
		'adventchat_welcome_subtitle' => 'How can we help you?',
		'adventchat_input_placeholder'=> 'Type a message…',
	);

	/**
	 * Initialize i18n hooks.
	 */
	public static function init(): void {
		// Polylang string registration.
		if ( function_exists( 'pll_register_string' ) ) {
			add_action( 'init', array( __CLASS__, 'register_polylang_strings' ), 20 );
		}

		// Filter widget config to apply translated strings.
		add_filter( 'adventchat_widget_config', array( __CLASS__, 'translate_config' ) );
	}

	/**
	 * Register strings with Polylang.
	 */
	public static function register_polylang_strings(): void {
		foreach ( self::$strings as $key => $default ) {
			$value = get_option( $key, $default );
			pll_register_string( $key, $value, 'AdventChat' );
		}
	}

	/**
	 * Apply translations to widget config.
	 *
	 * Works with both WPML (via wpml-config.xml) and Polylang.
	 *
	 * @param array $config Widget config.
	 * @return array
	 */
	public static function translate_config( array $config ): array {
		if ( function_exists( 'pll__' ) ) {
			$config['settings']['welcomeTitle']    = pll__( get_option( 'adventchat_welcome_title', self::$strings['adventchat_welcome_title'] ) );
			$config['settings']['welcomeSubtitle'] = pll__( get_option( 'adventchat_welcome_subtitle', self::$strings['adventchat_welcome_subtitle'] ) );
			$config['settings']['placeholder']     = pll__( get_option( 'adventchat_input_placeholder', self::$strings['adventchat_input_placeholder'] ) );
		}

		return $config;
	}
}
