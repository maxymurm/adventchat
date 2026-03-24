<?php
/**
 * Visitor information collector.
 *
 * Gathers browser, OS, IP (anonymizable), and basic geolocation via
 * server-side headers. Used for session enrichment and analytics.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Visitor_Info
 */
class AdventChat_Visitor_Info {

	/**
	 * Collect visitor info from the current request.
	 *
	 * @return array{ip: string, browser: string, os: string, country: string}
	 */
	public static function collect(): array {
		return array(
			'ip'      => self::get_ip(),
			'browser' => self::parse_browser(),
			'os'      => self::parse_os(),
			'country' => self::get_country(),
		);
	}

	/**
	 * Get visitor IP address (supports proxies).
	 *
	 * @return string Anonymized IP (last octet zeroed for IPv4).
	 */
	private static function get_ip(): string {
		$headers = array(
			'HTTP_CF_CONNECTING_IP',    // Cloudflare
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		$ip = '';
		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				// X-Forwarded-For can contain comma-separated list; take first.
				$ip = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) )[0];
				$ip = trim( $ip );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					break;
				}
			}
		}

		// Anonymize: zero the last octet (IPv4) or last 80 bits (IPv6).
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$parts    = explode( '.', $ip );
			$parts[3] = '0';
			return implode( '.', $parts );
		}

		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			// Anonymize last 5 groups of IPv6.
			$full = inet_ntop( inet_pton( $ip ) );
			$parts = explode( ':', $full );
			for ( $i = 3; $i < 8; $i++ ) {
				$parts[ $i ] = '0000';
			}
			return implode( ':', $parts );
		}

		return '';
	}

	/**
	 * Parse browser name from User-Agent.
	 *
	 * @return string
	 */
	private static function parse_browser(): string {
		$ua = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
		if ( empty( $ua ) ) {
			return 'Unknown';
		}

		$browsers = array(
			'Edg'     => 'Edge',
			'OPR'     => 'Opera',
			'Chrome'  => 'Chrome',
			'Safari'  => 'Safari',
			'Firefox' => 'Firefox',
			'MSIE'    => 'IE',
			'Trident' => 'IE',
		);

		foreach ( $browsers as $key => $name ) {
			if ( str_contains( $ua, $key ) ) {
				return $name;
			}
		}

		return 'Other';
	}

	/**
	 * Parse OS from User-Agent.
	 *
	 * @return string
	 */
	private static function parse_os(): string {
		$ua = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
		if ( empty( $ua ) ) {
			return 'Unknown';
		}

		$os_map = array(
			'Windows NT 10' => 'Windows 10+',
			'Windows NT 6.3' => 'Windows 8.1',
			'Windows NT 6.1' => 'Windows 7',
			'Mac OS X'       => 'macOS',
			'Android'        => 'Android',
			'iPhone'         => 'iOS',
			'iPad'           => 'iPadOS',
			'Linux'          => 'Linux',
			'CrOS'           => 'Chrome OS',
		);

		foreach ( $os_map as $key => $name ) {
			if ( str_contains( $ua, $key ) ) {
				return $name;
			}
		}

		return 'Other';
	}

	/**
	 * Get country from Cloudflare header (if available).
	 *
	 * @return string ISO 3166-1 alpha-2 country code or empty.
	 */
	private static function get_country(): string {
		$country = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '' ) );
		if ( ! empty( $country ) && 2 === strlen( $country ) ) {
			return strtoupper( $country );
		}
		return '';
	}
}
