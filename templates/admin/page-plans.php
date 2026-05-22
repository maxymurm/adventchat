<?php
/**
 * Plans & Upgrade admin page.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

$current_plan = AdventChat_License::get_plan();
$license_key  = get_option( 'adventchat_license_key', '' );
$license_data = AdventChat_License::validate();
?>
<div class="wrap adventchat-plans-wrap">
	<h1><?php esc_html_e( 'AdventChat Plans', 'adventchat' ); ?></h1>
	<p class="adventchat-plans-subtitle">
		<?php esc_html_e( 'You are currently on the', 'adventchat' ); ?>
		<strong class="adventchat-plan-badge adventchat-plan-badge--<?php echo esc_attr( $current_plan ); ?>">
			<?php echo esc_html( ucfirst( $current_plan ) ); ?>
		</strong>
		<?php esc_html_e( 'plan.', 'adventchat' ); ?>
		<?php if ( $license_data['valid'] && ! empty( $license_data['expires_at'] ) ) : ?>
			<span class="adventchat-plan-expires">
				<?php
				printf(
					/* translators: %s: expiration date */
					esc_html__( 'Renews %s', 'adventchat' ),
					esc_html( date_i18n( get_option( 'date_format' ), strtotime( $license_data['expires_at'] ) ) )
				);
				?>
			</span>
		<?php endif; ?>
	</p>

	<!-- Plan cards -->
	<div class="adventchat-plans-grid">

		<!-- Free -->
		<div class="adventchat-plan-card<?php echo 'free' === $current_plan ? ' adventchat-plan-card--current' : ''; ?>">
			<div class="adventchat-plan-card__header">
				<h2><?php esc_html_e( 'Free', 'adventchat' ); ?></h2>
				<div class="adventchat-plan-card__price">
					<span class="adventchat-plan-card__amount">$0</span>
					<span class="adventchat-plan-card__period">/<?php esc_html_e( 'month', 'adventchat' ); ?></span>
				</div>
			</div>
			<ul class="adventchat-plan-card__features">
				<li class="included"><?php esc_html_e( 'All chat features', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'Unlimited chats', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'Unlimited operators', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'Operator console', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'GDPR / pre-chat forms', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'File sharing & CSAT', 'adventchat' ); ?></li>
				<li class="included highlight"><?php esc_html_e( 'Message Sneak Peek — see visitor typing live', 'adventchat' ); ?></li>
				<li class="included highlight"><?php esc_html_e( 'Unlimited websites', 'adventchat' ); ?></li>
				<li class="included highlight"><?php esc_html_e( 'Bring your own Firebase', 'adventchat' ); ?></li>
				<li class="excluded"><?php esc_html_e( 'No mobile app access', 'adventchat' ); ?></li>
				<li class="excluded"><?php esc_html_e( '"Powered by" branding shown', 'adventchat' ); ?></li>
			</ul>
			<?php if ( 'free' === $current_plan ) : ?>
				<div class="adventchat-plan-card__action">
					<span class="button button-secondary disabled"><?php esc_html_e( 'Current Plan', 'adventchat' ); ?></span>
				</div>
			<?php endif; ?>
		</div>

		<!-- Pro -->
		<div class="adventchat-plan-card adventchat-plan-card--featured<?php echo 'pro' === $current_plan ? ' adventchat-plan-card--current' : ''; ?>">
			<div class="adventchat-plan-card__ribbon"><?php esc_html_e( 'Most Popular', 'adventchat' ); ?></div>
			<div class="adventchat-plan-card__header">
				<h2><?php esc_html_e( 'Pro', 'adventchat' ); ?></h2>
				<div class="adventchat-plan-card__price">
					<span class="adventchat-plan-card__amount">$24</span>
					<span class="adventchat-plan-card__period">/<?php esc_html_e( 'month', 'adventchat' ); ?></span>
				</div>
			</div>
			<ul class="adventchat-plan-card__features">
				<li class="included"><?php esc_html_e( 'Everything in Free', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'Unlimited operators', 'adventchat' ); ?></li>
				<li class="included highlight"><?php esc_html_e( 'Message Sneak Peek — see visitor typing live', 'adventchat' ); ?></li>
				<li class="included highlight"><?php esc_html_e( 'Hosted Firebase — no setup needed', 'adventchat' ); ?></li>
				<li class="included highlight"><?php esc_html_e( 'Mobile operator app (iOS & Android)', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'Push notifications', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'Priority email support', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( '1 website per license', 'adventchat' ); ?></li>
				<li class="excluded"><?php esc_html_e( '"Powered by" branding shown', 'adventchat' ); ?></li>
			</ul>
			<div class="adventchat-plan-card__action">
				<?php if ( 'pro' === $current_plan ) : ?>
					<span class="button button-secondary disabled"><?php esc_html_e( 'Current Plan', 'adventchat' ); ?></span>
				<?php else : ?>
					<a href="https://adventchat.com/pricing" target="_blank" class="button button-primary button-hero">
						<?php echo 'agency' === $current_plan ? esc_html__( 'Downgrade', 'adventchat' ) : esc_html__( 'Upgrade to Pro', 'adventchat' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Agency -->
		<div class="adventchat-plan-card<?php echo 'agency' === $current_plan ? ' adventchat-plan-card--current' : ''; ?>">
			<div class="adventchat-plan-card__header">
				<h2><?php esc_html_e( 'Agency', 'adventchat' ); ?></h2>
				<div class="adventchat-plan-card__price">
					<span class="adventchat-plan-card__amount">$59</span>
					<span class="adventchat-plan-card__period">/<?php esc_html_e( 'month', 'adventchat' ); ?></span>
				</div>
			</div>
			<ul class="adventchat-plan-card__features">
				<li class="included"><?php esc_html_e( 'Everything in Pro', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'Unlimited operators', 'adventchat' ); ?></li>
				<li class="included highlight"><?php esc_html_e( 'Unlimited websites', 'adventchat' ); ?></li>
				<li class="included highlight"><?php esc_html_e( 'Remove "Powered by" branding', 'adventchat' ); ?></li>
				<li class="included highlight"><?php esc_html_e( 'White-label widget', 'adventchat' ); ?></li>
				<li class="included"><?php esc_html_e( 'Priority support', 'adventchat' ); ?></li>
			</ul>
			<div class="adventchat-plan-card__action">
				<?php if ( 'agency' === $current_plan ) : ?>
					<span class="button button-secondary disabled"><?php esc_html_e( 'Current Plan', 'adventchat' ); ?></span>
				<?php else : ?>
					<a href="https://adventchat.com/pricing" target="_blank" class="button button-primary button-hero">
						<?php esc_html_e( 'Upgrade to Agency', 'adventchat' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

	</div><!-- .adventchat-plans-grid -->

	<!-- License key section -->
	<div class="adventchat-license-box">
		<h2><?php esc_html_e( 'License Key', 'adventchat' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Enter your license key from adventchat.com to activate Pro or Agency features.', 'adventchat' ); ?></p>

		<div class="adventchat-license-form">
			<input
				type="text"
				id="adventchat-license-key"
				class="regular-text"
				value="<?php echo esc_attr( $license_key ); ?>"
				placeholder="<?php esc_attr_e( 'XXXX-XXXX-XXXX-XXXX', 'adventchat' ); ?>"
			/>
			<?php if ( $license_data['valid'] ) : ?>
				<button type="button" id="adventchat-deactivate-license" class="button button-secondary">
					<?php esc_html_e( 'Deactivate', 'adventchat' ); ?>
				</button>
			<?php else : ?>
				<button type="button" id="adventchat-activate-license" class="button button-primary">
					<?php esc_html_e( 'Activate License', 'adventchat' ); ?>
				</button>
			<?php endif; ?>
			<span id="adventchat-license-result"></span>
		</div>
	</div>

	<!-- FAQ -->
	<div class="adventchat-plans-faq">
		<h2><?php esc_html_e( 'Frequently Asked Questions', 'adventchat' ); ?></h2>
		<div class="adventchat-faq-item">
			<h3><?php esc_html_e( 'Can I use Free on unlimited websites?', 'adventchat' ); ?></h3>
			<p><?php esc_html_e( 'Yes. Free includes unlimited websites. Each site needs its own Firebase project (free tier) or you can share one Firebase project across multiple sites. You control everything.', 'adventchat' ); ?></p>
		</div>
		<div class="adventchat-faq-item">
			<h3><?php esc_html_e( 'Why is Pro limited to 1 website?', 'adventchat' ); ?></h3>
			<p><?php esc_html_e( 'Pro includes hosted Firebase infrastructure. Each Pro license gets one dedicated Firebase backend mapped to one site URL. For multiple sites, purchase multiple Pro licenses or upgrade to Agency for unlimited sites under one license.', 'adventchat' ); ?></p>
		</div>
		<div class="adventchat-faq-item">
			<h3><?php esc_html_e( 'What happens to my chats if I upgrade?', 'adventchat' ); ?></h3>
			<p><?php esc_html_e( 'Nothing changes. Your existing chat history stays in your Firestore database. If you upgrade from Free to Pro, we can help migrate your Firebase data to our hosted infrastructure.', 'adventchat' ); ?></p>
		</div>
		<div class="adventchat-faq-item">
			<h3><?php esc_html_e( 'Can I downgrade later?', 'adventchat' ); ?></h3>
			<p><?php esc_html_e( 'Yes. Downgrading removes hosted Firebase access and mobile app. All core chat features continue to work with your own Firebase project.', 'adventchat' ); ?></p>
		</div>
		<div class="adventchat-faq-item">
			<h3><?php esc_html_e( 'What is Message Sneak Peek?', 'adventchat' ); ?></h3>
			<p><?php esc_html_e( 'Message Sneak Peek lets agents see what a visitor is typing in real time — before they hit Send. This helps agents prepare faster, more accurate replies and reduces response times. It is available on all plans and can be toggled in Settings → Chat Behavior.', 'adventchat' ); ?></p>
		</div>
		<div class="adventchat-faq-item">
			<h3><?php esc_html_e( 'Do I still need my own Firebase on Pro?', 'adventchat' ); ?></h3>
			<p><?php esc_html_e( 'No. Pro and Agency plans include fully managed Firebase infrastructure. You can optionally use your own if you prefer.', 'adventchat' ); ?></p>
		</div>
	</div>
</div>

<script>
(function(){
	var ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
	var nonce   = '<?php echo esc_js( wp_create_nonce( 'adventchat_admin_nonce' ) ); ?>';

	var activateBtn   = document.getElementById('adventchat-activate-license');
	var deactivateBtn = document.getElementById('adventchat-deactivate-license');
	var keyInput      = document.getElementById('adventchat-license-key');
	var result        = document.getElementById('adventchat-license-result');

	function doAjax(action, data) {
		result.textContent = '';
		result.style.color = '';
		var fd = new FormData();
		fd.append('action', action);
		fd.append('nonce', nonce);
		for (var k in data) fd.append(k, data[k]);
		fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function(r){ return r.json(); })
			.then(function(r){
				result.textContent = r.data && r.data.message ? r.data.message : 'Done';
				result.style.color = r.success ? '#00a32a' : '#d63638';
				if (r.success) setTimeout(function(){ location.reload(); }, 1200);
			})
			.catch(function(){ result.textContent = 'Network error'; result.style.color = '#d63638'; });
	}

	if (activateBtn) {
		activateBtn.addEventListener('click', function(){
			var k = keyInput.value.trim();
			if (!k) { result.textContent = 'Enter a license key.'; result.style.color = '#d63638'; return; }
			doAjax('adventchat_activate_license', { license_key: k });
		});
	}
	if (deactivateBtn) {
		deactivateBtn.addEventListener('click', function(){
			if (!confirm('<?php echo esc_js( __( 'Deactivate this license?', 'adventchat' ) ); ?>')) return;
			doAjax('adventchat_deactivate_license', {});
		});
	}
})();
</script>

<style>
.adventchat-plans-wrap { max-width: 1100px; }
.adventchat-plans-subtitle { font-size: 15px; margin-bottom: 24px; }
.adventchat-plan-badge {
	display: inline-block; padding: 2px 10px; border-radius: 12px;
	font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px;
}
.adventchat-plan-badge--free { background: #f0f0f1; color: #50575e; }
.adventchat-plan-badge--pro { background: #dff0d8; color: #3c763d; }
.adventchat-plan-badge--agency { background: #d9edf7; color: #31708f; }
.adventchat-plan-expires { color: #787c82; font-size: 13px; margin-left: 8px; }

/* Grid */
.adventchat-plans-grid {
	display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px;
}
@media (max-width: 960px) { .adventchat-plans-grid { grid-template-columns: 1fr; max-width: 400px; } }

/* Card */
.adventchat-plan-card {
	background: #fff; border: 1px solid #dcdcde; border-radius: 8px;
	padding: 28px 24px; position: relative; display: flex; flex-direction: column;
}
.adventchat-plan-card--current { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; }
.adventchat-plan-card--featured { border-color: #00a32a; box-shadow: 0 0 0 1px #00a32a; }
.adventchat-plan-card__ribbon {
	position: absolute; top: -1px; right: 20px;
	background: #00a32a; color: #fff; font-size: 11px; font-weight: 600;
	padding: 4px 12px; border-radius: 0 0 6px 6px; text-transform: uppercase; letter-spacing: .5px;
}
.adventchat-plan-card__header { text-align: center; margin-bottom: 20px; }
.adventchat-plan-card__header h2 { margin: 0 0 8px; font-size: 22px; }
.adventchat-plan-card__amount { font-size: 36px; font-weight: 700; color: #1d2327; }
.adventchat-plan-card__period { font-size: 14px; color: #787c82; }

/* Features list */
.adventchat-plan-card__features {
	list-style: none; padding: 0; margin: 0 0 20px; flex: 1;
}
.adventchat-plan-card__features li {
	padding: 6px 0 6px 24px; position: relative; font-size: 13px; line-height: 1.5; color: #50575e;
}
.adventchat-plan-card__features li.included::before { content: "✓"; color: #00a32a; position: absolute; left: 0; font-weight: 700; }
.adventchat-plan-card__features li.excluded::before { content: "—"; color: #a7aaad; position: absolute; left: 0; }
.adventchat-plan-card__features li.highlight { font-weight: 600; color: #1d2327; }

/* Action */
.adventchat-plan-card__action { text-align: center; margin-top: auto; }
.adventchat-plan-card__action .button-hero { font-size: 14px; padding: 8px 28px; }
.adventchat-plan-card__action .disabled { pointer-events: none; opacity: .6; }

/* License box */
.adventchat-license-box {
	background: #fff; border: 1px solid #dcdcde; border-radius: 8px;
	padding: 24px; margin-bottom: 24px;
}
.adventchat-license-box h2 { margin-top: 0; }
.adventchat-license-form { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.adventchat-license-form input { max-width: 340px; }
#adventchat-license-result { font-size: 13px; font-weight: 500; }

/* FAQ */
.adventchat-plans-faq {
	background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 24px;
}
.adventchat-plans-faq h2 { margin-top: 0; }
.adventchat-faq-item { margin-bottom: 16px; }
.adventchat-faq-item:last-child { margin-bottom: 0; }
.adventchat-faq-item h3 { margin: 0 0 4px; font-size: 14px; }
.adventchat-faq-item p { margin: 0; color: #50575e; font-size: 13px; }
</style>
