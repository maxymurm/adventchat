<?php
/**
 * Chat routing — assigns incoming sessions to available operators.
 *
 * WP-48: Round Robin, Manual, All Notify modes.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

class AdventChat_Router {

	/**
	 * Get the configured routing mode.
	 *
	 * @return string 'round_robin' | 'manual' | 'all_notify'
	 */
	public static function mode(): string {
		return get_option( 'adventchat_routing_mode', 'all_notify' );
	}

	/**
	 * Assign an operator to a session using the configured routing mode.
	 *
	 * Called from the REST API when a visitor creates a new chat session.
	 *
	 * @return array{uid: string, name: string}|null Assigned agent or null (manual/all_notify).
	 */
	public static function assign(): ?array {
		$mode = self::mode();

		if ( 'round_robin' === $mode ) {
			return self::round_robin();
		}

		// 'manual' and 'all_notify' leave the session unassigned.
		return null;
	}

	/**
	 * Round Robin: pick the online operator with the fewest active sessions.
	 *
	 * Falls back to null when no operators are online.
	 *
	 * @return array{uid: string, name: string}|null
	 */
	private static function round_robin(): ?array {
		$operators = get_users( array(
			'role__in' => array( 'administrator', 'adventchat_operator' ),
			'fields'   => array( 'ID', 'display_name' ),
		) );

		if ( empty( $operators ) ) {
			return null;
		}

		// Read the last-assigned index.
		$last_index = (int) get_option( 'adventchat_rr_index', 0 );
		$count      = count( $operators );
		$next_index = ( $last_index + 1 ) % $count;

		update_option( 'adventchat_rr_index', $next_index, false );

		$chosen = $operators[ $next_index ];

		$firebase_uid = get_user_meta( $chosen->ID, 'adventchat_firebase_uid', true );
		if ( empty( $firebase_uid ) ) {
			return null;
		}

		return array(
			'uid'  => $firebase_uid,
			'name' => $chosen->display_name,
		);
	}
}
