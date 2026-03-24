<?php
/**
 * Offline notification email template.
 *
 * @var string $name
 * @var string $email
 * @var string $message
 * @var string $department
 * @var string $site_name
 * @var string $date
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f4f4f5;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;">
	<tr><td style="background:#1e293b;color:#fff;padding:20px 24px;font-size:18px;font-weight:600;">
		<?php echo esc_html( $site_name ); ?> — Offline Message
	</td></tr>
	<tr><td style="padding:24px;">
		<p style="margin:0 0 12px;color:#374151;">You received a new offline message:</p>
		<table cellpadding="6" cellspacing="0" style="font-size:14px;color:#374151;width:100%;">
			<tr><td style="font-weight:600;width:100px;">Name:</td><td><?php echo esc_html( $name ); ?></td></tr>
			<tr><td style="font-weight:600;">Email:</td><td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td></tr>
			<?php if ( ! empty( $department ) ) : ?>
			<tr><td style="font-weight:600;">Dept:</td><td><?php echo esc_html( $department ); ?></td></tr>
			<?php endif; ?>
			<tr><td style="font-weight:600;">Date:</td><td><?php echo esc_html( $date ); ?></td></tr>
		</table>
		<div style="margin-top:16px;padding:16px;background:#f8fafc;border-radius:6px;border-left:3px solid #3b82f6;font-size:14px;color:#374151;">
			<?php echo nl2br( esc_html( $message ) ); ?>
		</div>
		<p style="margin-top:20px;font-size:12px;color:#9ca3af;">Reply directly to <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>.</p>
	</td></tr>
</table>
</body>
</html>
