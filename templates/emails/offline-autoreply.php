<?php
/**
 * Offline auto-reply email template.
 *
 * @var string $name
 * @var string $site_name
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f4f4f5;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;">
	<tr><td style="background:#1e293b;color:#fff;padding:20px 24px;font-size:18px;font-weight:600;">
		<?php echo esc_html( $site_name ); ?>
	</td></tr>
	<tr><td style="padding:24px;">
		<p style="margin:0 0 12px;color:#374151;">Hi <?php echo esc_html( $name ); ?>,</p>
		<p style="color:#374151;">Thank you for reaching out. We received your message and will get back to you as soon as possible.</p>
		<p style="color:#374151;">Our team typically responds within a few hours during business hours.</p>
		<p style="margin-top:20px;color:#374151;">Best regards,<br><?php echo esc_html( $site_name ); ?> Team</p>
	</td></tr>
</table>
</body>
</html>
