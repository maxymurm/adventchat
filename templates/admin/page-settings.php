<?php
/**
 * Admin settings page template (tabbed).
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

$tabs        = AdventChat_Settings::get_tabs();
$current_tab = AdventChat_Settings::current_tab();
?>
<div class="wrap">
	<h1><?php esc_html_e( 'AdventChat Settings', 'adventchat' ); ?></h1>

	<nav class="nav-tab-wrapper">
		<?php foreach ( $tabs as $slug => $label ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=adventchat-settings&tab=' . $slug ) ); ?>"
			   class="nav-tab <?php echo $current_tab === $slug ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'adventchat_' . $current_tab );
		do_settings_sections( 'adventchat_' . $current_tab );
		submit_button();
		?>
	</form>
</div>
