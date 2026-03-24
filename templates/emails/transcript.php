<?php
/**
 * Chat transcript email template.
 *
 * @var string $name
 * @var array  $messages
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
		Chat Transcript — <?php echo esc_html( $date ); ?>
	</td></tr>
	<tr><td style="padding:24px;">
		<p style="margin:0 0 16px;color:#374151;">Hi <?php echo esc_html( $name ); ?>, here is your chat transcript:</p>

		<?php foreach ( $messages as $msg ) : ?>
			<?php if ( ( $msg['senderType'] ?? '' ) === 'system' ) : ?>
				<p style="text-align:center;color:#9ca3af;font-size:12px;font-style:italic;margin:8px 0;"><?php echo esc_html( $msg['text'] ?? '' ); ?></p>
			<?php else :
				$is_visitor = ( $msg['senderType'] ?? '' ) === 'visitor';
				$bg         = $is_visitor ? '#eff6ff' : '#f8fafc';
				$align      = $is_visitor ? 'right' : 'left';
				$time       = $msg['timestamp'] ?? '';
			?>
				<div style="margin:6px 0;text-align:<?php echo esc_attr( $align ); ?>;">
					<div style="display:inline-block;max-width:80%;padding:10px 14px;border-radius:12px;background:<?php echo esc_attr( $bg ); ?>;font-size:14px;color:#374151;text-align:left;">
						<strong style="font-size:12px;color:#64748b;"><?php echo esc_html( $msg['senderName'] ?? '' ); ?></strong><br>
						<?php echo esc_html( $msg['text'] ?? '' ); ?>
						<?php if ( $time ) : ?>
							<div style="font-size:10px;color:#9ca3af;margin-top:4px;"><?php echo esc_html( $time ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

		<p style="margin-top:24px;font-size:12px;color:#9ca3af;">Powered by AdventChat · <?php echo esc_html( $site_name ); ?></p>
	</td></tr>
</table>
</body>
</html>
