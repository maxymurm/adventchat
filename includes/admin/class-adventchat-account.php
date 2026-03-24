<?php
/**
 * Account connection UI — license key input, plan badge, expiry.
 *
 * WP-82: Settings section for license management.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Account
 */
class AdventChat_Account {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'register_section' ) );
	}

	/**
	 * Register the account section in General settings tab.
	 */
	public static function register_section(): void {
		$group   = 'adventchat_general';
		$section = 'adventchat_account_section';

		add_settings_section( $section, __( 'Account & License', 'adventchat' ), array( __CLASS__, 'render_section' ), $group );

		add_settings_field( 'adventchat_license_key', __( 'License Key', 'adventchat' ), array( __CLASS__, 'render_license_field' ), $group, $section );

		add_settings_field( 'adventchat_hosted_firebase', __( 'Hosted Firebase', 'adventchat' ), array( 'AdventChat_Provisioning', 'render_status' ), $group, $section );
	}

	/**
	 * Render section description.
	 */
	public static function render_section(): void {
		$license = AdventChat_License::validate();
		$plan    = $license['valid'] ? ucfirst( $license['plan'] ) : __( 'Free', 'adventchat' );

		echo '<div class="adventchat-account-info">';
		printf(
			'<p>%s <span class="adventchat-plan-badge adventchat-plan-badge--%s">%s</span></p>',
			esc_html__( 'Current Plan:', 'adventchat' ),
			esc_attr( strtolower( $plan ) ),
			esc_html( $plan )
		);

		if ( $license['valid'] && $license['expires_at'] ) {
			printf(
				'<p class="description">%s %s</p>',
				esc_html__( 'Expires:', 'adventchat' ),
				esc_html( $license['expires_at'] )
			);
		}

		if ( $license['valid'] ) {
			printf(
				'<p><a href="%s" target="_blank">%s</a></p>',
				'https://adventchat.com/account',
				esc_html__( 'Manage Subscription →', 'adventchat' )
			);
		}

		echo '</div>';

		// Inline styles for plan badge.
		echo '<style>
			.adventchat-plan-badge { display:inline-block; padding:2px 10px; border-radius:4px; font-size:12px; font-weight:700; color:#fff; }
			.adventchat-plan-badge--free { background:#6b7280; }
			.adventchat-plan-badge--pro { background:#3b82f6; }
			.adventchat-plan-badge--agency { background:#8b5cf6; }
			.adventchat-upsell { background:#fef3c7; border:1px solid #fbbf24; border-radius:6px; padding:12px 16px; margin:8px 0; }
			.adventchat-status--connected { color:#16a34a; font-weight:600; }
		</style>';
	}

	/**
	 * Render license key field with activate/deactivate buttons.
	 */
	public static function render_license_field(): void {
		$key     = get_option( 'adventchat_license_key', '' );
		$license = AdventChat_License::validate();

		printf(
			'<input type="text" id="adventchat-license-key" name="adventchat_license_key" value="%s" class="regular-text" placeholder="%s" />',
			esc_attr( $key ),
			esc_attr__( 'Enter your license key', 'adventchat' )
		);

		if ( $key && $license['valid'] ) {
			printf(
				' <button type="button" class="button" id="adventchat-deactivate-license">%s</button>',
				esc_html__( 'Deactivate', 'adventchat' )
			);
		} else {
			printf(
				' <button type="button" class="button button-primary" id="adventchat-activate-license">%s</button>',
				esc_html__( 'Activate', 'adventchat' )
			);
		}

		echo ' <span id="adventchat-license-result"></span>';

		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				var activateBtn = document.getElementById("adventchat-activate-license");
				var deactivateBtn = document.getElementById("adventchat-deactivate-license");
				var keyInput = document.getElementById("adventchat-license-key");
				var result = document.getElementById("adventchat-license-result");

				function doLicenseAjax(action, extra) {
					result.textContent = "Processing...";
					var body = "action=" + action + "&nonce=" + adventchatAdmin.nonce;
					if (extra) body += "&" + extra;
					fetch(adventchatAdmin.ajaxUrl, {
						method: "POST",
						headers: { "Content-Type": "application/x-www-form-urlencoded" },
						body: body
					}).then(function(r){return r.json();}).then(function(d){
						result.textContent = d.data.message;
						result.style.color = d.success ? "green" : "red";
						if (d.success) setTimeout(function(){location.reload();}, 1500);
					});
				}

				if (activateBtn) activateBtn.addEventListener("click", function(){
					doLicenseAjax("adventchat_activate_license", "license_key=" + encodeURIComponent(keyInput.value));
				});
				if (deactivateBtn) deactivateBtn.addEventListener("click", function(){
					doLicenseAjax("adventchat_deactivate_license");
				});
			});
		</script>';
	}
}
