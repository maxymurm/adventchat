<?php
/**
 * Display rules — determines whether the chat widget should render.
 *
 * WP-65: Evaluates page ID, post type, user role, mobile, and guest-only rules.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Display_Rules
 */
class AdventChat_Display_Rules {

	/**
	 * Evaluate all display rules and return true if the widget should show.
	 *
	 * @return bool
	 */
	public static function should_display(): bool {
		// Mobile check.
		if ( '1' === get_option( 'adventchat_display_hide_mobile', '0' ) && wp_is_mobile() ) {
			return false;
		}

		// Guest-only check.
		if ( '1' === get_option( 'adventchat_display_guest_only', '0' ) && is_user_logged_in() ) {
			return false;
		}

		// User role check.
		$roles_raw = trim( get_option( 'adventchat_display_roles', '' ) );
		if ( '' !== $roles_raw && is_user_logged_in() ) {
			$allowed = array_map( 'trim', explode( ',', $roles_raw ) );
			$user    = wp_get_current_user();
			$match   = array_intersect( $user->roles, $allowed );
			if ( empty( $match ) ) {
				return false;
			}
		}

		// Page / post-type based rules.
		$mode = get_option( 'adventchat_display_mode', 'show_all' );

		if ( 'show_all' === $mode ) {
			return true;
		}

		$page_ids_raw   = trim( get_option( 'adventchat_display_pages', '' ) );
		$post_types_raw = trim( get_option( 'adventchat_display_post_types', '' ) );

		$on_list = false;

		// Check current page/post ID.
		if ( '' !== $page_ids_raw ) {
			$ids = array_map( 'absint', explode( ',', $page_ids_raw ) );
			$current_id = get_queried_object_id();
			if ( in_array( $current_id, $ids, true ) ) {
				$on_list = true;
			}
		}

		// Check current post type.
		if ( ! $on_list && '' !== $post_types_raw ) {
			$types = array_map( 'trim', explode( ',', $post_types_raw ) );
			$current_type = get_post_type();
			if ( $current_type && in_array( $current_type, $types, true ) ) {
				$on_list = true;
			}
		}

		// Include mode: show only if on list.
		if ( 'include' === $mode ) {
			return $on_list;
		}

		// Exclude mode: hide if on list.
		if ( 'exclude' === $mode ) {
			return ! $on_list;
		}

		return true;
	}
}
