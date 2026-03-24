<?php
/**
 * Tests for AdventChat_Mailer.
 *
 * @package AdventChat\Tests
 */

class MailerTest extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		// Enable offline messaging.
		update_option( 'adventchat_offline_enabled', '1' );
		update_option( 'adventchat_offline_email', 'test@example.com' );
		update_option( 'adventchat_offline_autoreply', '1' );
	}

	/**
	 * Offline notification sends email.
	 */
	public function test_offline_notification_sends(): void {
		// Override wp_mail to capture.
		$sent = false;
		add_filter( 'pre_wp_mail', function () use ( &$sent ) {
			$sent = true;
			return true;
		} );

		AdventChat_Mailer::send_offline_notification( array(
			'name'    => 'Jane Doe',
			'email'   => 'jane@example.com',
			'message' => 'Hello, need help.',
		) );

		$this->assertTrue( $sent );
	}

	/**
	 * Autoreply sends email when enabled.
	 */
	public function test_autoreply_sends_when_enabled(): void {
		$sent_to = null;
		add_filter( 'pre_wp_mail', function ( $null, $atts ) use ( &$sent_to ) {
			$sent_to = $atts['to'] ?? '';
			return true;
		}, 10, 2 );

		AdventChat_Mailer::send_offline_autoreply( 'visitor@example.com', 'Test Visitor' );

		$this->assertSame( 'visitor@example.com', $sent_to );
	}

	/**
	 * Transcript sends email.
	 */
	public function test_transcript_sends(): void {
		$sent = false;
		add_filter( 'pre_wp_mail', function () use ( &$sent ) {
			$sent = true;
			return true;
		} );

		AdventChat_Mailer::send_transcript( 'user@example.com', array(
			array( 'sender' => 'visitor', 'text' => 'Hi', 'timestamp' => time() ),
			array( 'sender' => 'agent', 'text' => 'Hello!', 'timestamp' => time() ),
		) );

		$this->assertTrue( $sent );
	}
}
