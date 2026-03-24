<?php
/**
 * Tests for AdventChat_Display_Rules.
 *
 * @package AdventChat\Tests
 */

class DisplayRulesTest extends WP_UnitTestCase {

	public function tear_down(): void {
		delete_option( 'adventchat_display_hide_mobile' );
		delete_option( 'adventchat_display_guest_only' );
		delete_option( 'adventchat_display_roles' );
		delete_option( 'adventchat_display_mode' );
		delete_option( 'adventchat_display_pages' );
		delete_option( 'adventchat_display_post_types' );
		parent::tear_down();
	}

	/**
	 * Default settings show the widget.
	 */
	public function test_default_settings_show_widget(): void {
		$this->assertTrue( AdventChat_Display_Rules::should_display() );
	}

	/**
	 * show_all mode returns true.
	 */
	public function test_show_all_mode(): void {
		update_option( 'adventchat_display_mode', 'show_all' );
		$this->assertTrue( AdventChat_Display_Rules::should_display() );
	}

	/**
	 * Guest-only hides widget for logged-in users.
	 */
	public function test_guest_only_hides_for_logged_in(): void {
		update_option( 'adventchat_display_guest_only', '1' );

		// Simulate logged-in user.
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$this->assertFalse( AdventChat_Display_Rules::should_display() );

		wp_set_current_user( 0 );
	}

	/**
	 * Guest-only shows widget for guests.
	 */
	public function test_guest_only_shows_for_guests(): void {
		update_option( 'adventchat_display_guest_only', '1' );
		wp_set_current_user( 0 );

		$this->assertTrue( AdventChat_Display_Rules::should_display() );
	}

	/**
	 * Role restriction hides widget for excluded roles.
	 */
	public function test_role_restriction(): void {
		update_option( 'adventchat_display_roles', 'administrator' );

		// Create subscriber (not in allowed roles).
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$this->assertFalse( AdventChat_Display_Rules::should_display() );

		wp_set_current_user( 0 );
	}

	/**
	 * Role match shows widget.
	 */
	public function test_role_match_shows_widget(): void {
		update_option( 'adventchat_display_roles', 'administrator,subscriber' );

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( AdventChat_Display_Rules::should_display() );

		wp_set_current_user( 0 );
	}
}
