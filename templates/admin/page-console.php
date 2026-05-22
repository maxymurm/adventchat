<?php
/**
 * Operator console page template.
 *
 * The React SPA mounts into #adventchat-console-root.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'AdventChat — Live Chat', 'adventchat' ); ?></h1>
	<div id="adventchat-console-root"></div>

	<?php if ( ! AdventChat_License::is_pro() ) : ?>
	<div class="adventchat-console-upsell">
		<div class="adventchat-console-upsell__icon">
			<span class="dashicons dashicons-superhero-alt"></span>
		</div>
		<div class="adventchat-console-upsell__body">
			<h3><?php esc_html_e( 'Upgrade to Pro — manage chats from your phone', 'adventchat' ); ?></h3>
			<p>
				<?php esc_html_e( 'You already have unlimited operators on the free plan. Upgrade to Pro for hosted Firebase (zero setup), the iOS & Android operator app, push notifications, and priority support — starting at $24/mo.', 'adventchat' ); ?>
			</p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=adventchat-plans' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'View Plans', 'adventchat' ); ?>
			</a>
			<a href="https://adventchat.com/pricing" target="_blank" class="button button-secondary">
				<?php esc_html_e( 'Learn More', 'adventchat' ); ?>
			</a>
		</div>
	</div>
	<style>
		.adventchat-console-upsell {
			display: flex; align-items: flex-start; gap: 16px;
			background: linear-gradient(135deg, #f0f6fc 0%, #e8f5e9 100%);
			border: 1px solid #c3c4c7; border-left: 4px solid #2271b1;
			border-radius: 4px; padding: 20px 24px; margin-top: 20px; max-width: 720px;
		}
		.adventchat-console-upsell__icon .dashicons {
			font-size: 32px; width: 32px; height: 32px; color: #2271b1;
		}
		.adventchat-console-upsell__body h3 { margin: 0 0 6px; font-size: 15px; color: #1d2327; }
		.adventchat-console-upsell__body p { margin: 0 0 12px; font-size: 13px; color: #50575e; line-height: 1.5; }
		.adventchat-console-upsell__body .button { margin-right: 8px; }
	</style>
	<?php endif; ?>
</div>
