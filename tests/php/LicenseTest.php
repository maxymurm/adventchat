<?php
/**
 * Tests for AdventChat_License.
 *
 * @package AdventChat\Tests
 */

class LicenseTest extends WP_UnitTestCase {

	public function tear_down(): void {
		delete_option( 'adventchat_license_key' );
		delete_transient( 'adventchat_license_cache' );
		parent::tear_down();
	}

	/**
	 * No license key returns free plan.
	 */
	public function test_validate_without_key_returns_free(): void {
		delete_option( 'adventchat_license_key' );
		$result = AdventChat_License::validate();
		$this->assertFalse( $result['valid'] );
		$this->assertSame( 'free', $result['plan'] );
	}

	/**
	 * is_pro returns false on free plan.
	 */
	public function test_is_pro_returns_false_on_free(): void {
		delete_option( 'adventchat_license_key' );
		delete_transient( 'adventchat_license_cache' );
		$this->assertFalse( AdventChat_License::is_pro() );
	}

	/**
	 * is_agency returns false on free plan.
	 */
	public function test_is_agency_returns_false_on_free(): void {
		$this->assertFalse( AdventChat_License::is_agency() );
	}

	/**
	 * Cached pro license returns is_pro true.
	 */
	public function test_cached_pro_license_returns_pro(): void {
		set_transient( 'adventchat_license_cache', array(
			'valid'      => true,
			'plan'       => 'pro',
			'expires_at' => '2030-01-01',
		), 3600 );
		update_option( 'adventchat_license_key', 'test-key-123' );

		$this->assertTrue( AdventChat_License::is_pro() );
		$this->assertFalse( AdventChat_License::is_agency() );
	}

	/**
	 * Cached agency license returns is_agency true.
	 */
	public function test_cached_agency_license(): void {
		set_transient( 'adventchat_license_cache', array(
			'valid'      => true,
			'plan'       => 'agency',
			'expires_at' => '2030-01-01',
		), 3600 );
		update_option( 'adventchat_license_key', 'agency-key-123' );

		$this->assertTrue( AdventChat_License::is_pro() );
		$this->assertTrue( AdventChat_License::is_agency() );
	}

	/**
	 * get_plan returns correct plan from cache.
	 */
	public function test_get_plan_from_cache(): void {
		set_transient( 'adventchat_license_cache', array(
			'valid'      => true,
			'plan'       => 'pro',
			'expires_at' => '2030-01-01',
		), 3600 );
		update_option( 'adventchat_license_key', 'test-key' );

		$this->assertSame( 'pro', AdventChat_License::get_plan() );
	}
}
