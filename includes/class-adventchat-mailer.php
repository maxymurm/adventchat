<?php
/**
 * Mailer — chat transcript and offline message emails.
 *
 * WP-57: Email transcript.
 * WP-55: Offline message email & auto-reply.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

class AdventChat_Mailer {

	/**
	 * Send offline message notification to admins + auto-reply to visitor.
	 *
	 * @param array $data {name, email, message, department}.
	 */
	public static function send_offline_notification( array $data ): void {
		$recipients = get_option( 'adventchat_offline_email', get_option( 'admin_email' ) );
		$site_name  = get_bloginfo( 'name' );

		// Notify admin.
		$subject = sprintf(
			/* translators: %s site name */
			__( '[%s] New Offline Message', 'adventchat' ),
			$site_name
		);

		$body = self::load_template( 'offline-notification', array(
			'name'       => $data['name'],
			'email'      => $data['email'],
			'message'    => $data['message'],
			'department' => $data['department'] ?? '',
			'site_name'  => $site_name,
			'date'       => wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
		) );

		self::send( $recipients, $subject, $body );

		// Auto-reply to visitor.
		if ( ! empty( $data['email'] ) ) {
			$reply_subject = sprintf(
				/* translators: %s site name */
				__( 'We received your message — %s', 'adventchat' ),
				$site_name
			);

			$reply_body = self::load_template( 'offline-autoreply', array(
				'name'      => $data['name'],
				'site_name' => $site_name,
			) );

			self::send( $data['email'], $reply_subject, $reply_body );
		}
	}

	/**
	 * Send chat transcript email to visitor.
	 *
	 * @param string $email     Visitor email.
	 * @param string $name      Visitor name.
	 * @param array  $messages  Array of message arrays {senderName, senderType, text, timestamp}.
	 */
	public static function send_transcript( string $email, string $name, array $messages ): void {
		if ( empty( $email ) ) {
			return;
		}

		$site_name = get_bloginfo( 'name' );
		$subject   = sprintf(
			/* translators: %s site name */
			__( 'Your Chat Transcript — %s', 'adventchat' ),
			$site_name
		);

		$body = self::load_template( 'transcript', array(
			'name'      => $name,
			'messages'  => $messages,
			'site_name' => $site_name,
			'date'      => wp_date( get_option( 'date_format' ) ),
		) );

		self::send( $email, $subject, $body );
	}

	/**
	 * Send HTML email via wp_mail.
	 */
	private static function send( string $to, string $subject, string $body ): bool {
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);
		return wp_mail( $to, $subject, $body, $headers );
	}

	/**
	 * Load an email template.
	 *
	 * @param string $template Template slug.
	 * @param array  $vars     Variables to extract into template scope.
	 * @return string Compiled HTML.
	 */
	private static function load_template( string $template, array $vars = array() ): string {
		// Prevent directory traversal.
		if ( preg_match( '/[^a-zA-Z0-9_-]/', $template ) ) {
			return '';
		}

		$file = ADVENTCHAT_PLUGIN_DIR . "templates/emails/{$template}.php";
		if ( ! file_exists( $file ) ) {
			return '';
		}
		extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		ob_start();
		include $file;
		return ob_get_clean();
	}
}
