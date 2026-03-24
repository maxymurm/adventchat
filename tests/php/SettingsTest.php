<?php
/**
 * Tests for AdventChat_Settings.
 *
 * @package AdventChat\Tests
 */

class SettingsTest extends WP_UnitTestCase {

	/**
	 * Register fires without errors.
	 */
	public function test_register_does_not_throw(): void {
		AdventChat_Settings::register();
		$this->assertTrue( true );
	}

	/**
	 * Tabs include expected keys.
	 */
	public function test_tabs_include_all_expected(): void {
		$tabs = AdventChat_Settings::get_tabs();
		$this->assertArrayHasKey( 'general', $tabs );
		$this->assertArrayHasKey( 'firebase', $tabs );
		$this->assertArrayHasKey( 'appearance', $tabs );
		$this->assertArrayHasKey( 'display', $tabs );
		$this->assertArrayHasKey( 'chat', $tabs );
		$this->assertArrayHasKey( 'offline', $tabs );
		$this->assertArrayHasKey( 'privacy', $tabs );
		$this->assertCount( 7, $tabs );
	}

	/**
	 * Active tab defaults to general.
	 */
	public function test_active_tab_defaults_to_general(): void {
		$this->assertSame( 'general', AdventChat_Settings::get_active_tab() );
	}

	/**
	 * Primary color default option.
	 */
	public function test_primary_color_default(): void {
		$this->assertSame( '#0066ff', get_option( 'adventchat_primary_color', '#0066ff' ) );
	}
}
