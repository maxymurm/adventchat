<?php
/**
 * Options helper class.
 *
 * Provides a clean API for getting/setting/deleting plugin options
 * with optional encryption for sensitive values.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Options
 */
class AdventChat_Options {

	/**
	 * Option prefix.
	 *
	 * @var string
	 */
	private const PREFIX = 'adventchat_';

	/**
	 * Keys whose values should be encrypted at rest.
	 *
	 * @var string[]
	 */
	private const ENCRYPTED_KEYS = array(
		'firebase_config',
		'license_key',
	);

	/**
	 * Get an option value.
	 *
	 * @param string $key     Option key (without prefix).
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get( string $key, mixed $default = '' ): mixed {
		$value = get_option( self::PREFIX . $key, $default );

		if ( in_array( $key, self::ENCRYPTED_KEYS, true ) && ! empty( $value ) && is_string( $value ) ) {
			$decrypted = self::decrypt( $value );
			return ( false !== $decrypted ) ? $decrypted : $default;
		}

		return $value;
	}

	/**
	 * Set an option value.
	 *
	 * @param string $key   Option key (without prefix).
	 * @param mixed  $value Value to store.
	 * @return bool
	 */
	public static function set( string $key, mixed $value ): bool {
		if ( in_array( $key, self::ENCRYPTED_KEYS, true ) && ! empty( $value ) && is_string( $value ) ) {
			$value = self::encrypt( $value );
		}

		return update_option( self::PREFIX . $key, $value );
	}

	/**
	 * Delete an option.
	 *
	 * @param string $key Option key (without prefix).
	 * @return bool
	 */
	public static function delete( string $key ): bool {
		return delete_option( self::PREFIX . $key );
	}

	/**
	 * Encrypt a value using the WordPress AUTH_KEY.
	 *
	 * @param string $value Plaintext value.
	 * @return string Base64-encoded ciphertext.
	 */
	private static function encrypt( string $value ): string {
		$key    = self::encryption_key();
		$iv     = openssl_random_pseudo_bytes( 16 );
		$cipher = openssl_encrypt( $value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $cipher ) {
			return $value; // Fallback to plaintext if encryption fails.
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $iv . $cipher );
	}

	/**
	 * Decrypt a value.
	 *
	 * @param string $value Base64-encoded ciphertext.
	 * @return string|false Decrypted value or false on failure.
	 */
	private static function decrypt( string $value ): string|false {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$raw = base64_decode( $value, true );
		if ( false === $raw || strlen( $raw ) < 17 ) {
			// Not encrypted — return as-is (handles pre-existing plaintext values).
			return $value;
		}

		$key = self::encryption_key();
		$iv  = substr( $raw, 0, 16 );

		return openssl_decrypt( substr( $raw, 16 ), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
	}

	/**
	 * Derive a 256-bit encryption key from AUTH_KEY.
	 *
	 * @return string
	 */
	private static function encryption_key(): string {
		return hash( 'sha256', AUTH_KEY . 'adventchat', true );
	}
}
