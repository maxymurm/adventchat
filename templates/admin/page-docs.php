<?php
/**
 * Admin documentation page.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

$docs_dir = ADVENTCHAT_PLUGIN_DIR . 'docs/';

$guides = array(
	'self-hosted-setup' => array(
		'title'       => __( 'Self-Hosted Setup Guide', 'adventchat' ),
		'description' => __( 'Set up AdventChat with your own Firebase project. Covers Firebase configuration, Firestore, authentication, and all WordPress plugin settings.', 'adventchat' ),
		'file'        => 'self-hosted-setup.md',
		'icon'        => 'dashicons-admin-home',
		'status'      => 'available',
	),
	'mobile-setup'     => array(
		'title'       => __( 'Mobile App Setup', 'adventchat' ),
		'description' => __( 'Install and configure the AdventChat mobile operator app for iOS and Android.', 'adventchat' ),
		'file'        => 'mobile-setup.md',
		'icon'        => 'dashicons-smartphone',
		'status'      => 'coming-soon',
	),
	'pro-hosting'      => array(
		'title'       => __( 'Pro Hosting Setup', 'adventchat' ),
		'description' => __( 'Activate a Pro or Agency license for fully managed Firebase hosting — no manual setup required.', 'adventchat' ),
		'file'        => 'pro-hosting-setup.md',
		'icon'        => 'dashicons-cloud',
		'status'      => 'coming-soon',
	),
);

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_guide = isset( $_GET['guide'] ) ? sanitize_key( $_GET['guide'] ) : '';
?>
<div class="wrap adventchat-docs">
	<h1><?php esc_html_e( 'AdventChat Documentation', 'adventchat' ); ?></h1>

	<?php if ( '' === $current_guide || ! isset( $guides[ $current_guide ] ) ) : ?>
		<p><?php esc_html_e( 'Welcome to the AdventChat documentation. Select a guide below to get started.', 'adventchat' ); ?></p>

		<div class="adventchat-docs-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;margin-top:20px;">
			<?php foreach ( $guides as $slug => $guide ) : ?>
				<div class="adventchat-doc-card" style="border:1px solid #c3c4c7;background:#fff;border-radius:4px;padding:20px;position:relative;">
					<?php if ( 'coming-soon' === $guide['status'] ) : ?>
						<span style="position:absolute;top:10px;right:10px;background:#dba617;color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;">
							<?php esc_html_e( 'COMING SOON', 'adventchat' ); ?>
						</span>
					<?php endif; ?>
					<div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
						<span class="dashicons <?php echo esc_attr( $guide['icon'] ); ?>" style="font-size:28px;width:28px;height:28px;color:#0066ff;"></span>
						<h2 style="margin:0;font-size:16px;"><?php echo esc_html( $guide['title'] ); ?></h2>
					</div>
					<p style="color:#50575e;margin:0 0 16px;">
						<?php echo esc_html( $guide['description'] ); ?>
					</p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=adventchat-docs&guide=' . $slug ) ); ?>"
					   class="button <?php echo 'available' === $guide['status'] ? 'button-primary' : ''; ?>">
						<?php esc_html_e( 'Read Guide', 'adventchat' ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>

	<?php else : ?>
		<?php
		$guide = $guides[ $current_guide ];
		$file  = $docs_dir . $guide['file'];
		?>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=adventchat-docs' ) ); ?>"
			   class="button" style="margin-bottom:15px;">
				&larr; <?php esc_html_e( 'Back to all guides', 'adventchat' ); ?>
			</a>
		</p>

		<div class="adventchat-doc-content" style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:30px 40px;max-width:900px;">
			<?php
			if ( file_exists( $file ) ) {
				$markdown = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

				// Convert Markdown to HTML (simple conversion).
				$html = adventchat_markdown_to_html( $markdown );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is generated from trusted plugin files.
				echo $html;
			} else {
				echo '<p>' . esc_html__( 'Guide file not found.', 'adventchat' ) . '</p>';
			}
			?>
		</div>
	<?php endif; ?>
</div>
<?php

/**
 * Convert Markdown to basic HTML for doc display.
 *
 * @param string $md Markdown content.
 * @return string HTML.
 */
function adventchat_markdown_to_html( string $md ): string {
	$lines  = explode( "\n", $md );
	$html   = '';
	$in_table   = false;
	$in_code    = false;
	$in_list    = false;
	$in_blockquote = false;
	$table_rows = array();

	foreach ( $lines as $line ) {
		// Fenced code blocks.
		if ( preg_match( '/^```/', $line ) ) {
			if ( $in_code ) {
				$html   .= '</code></pre>';
				$in_code = false;
			} else {
				$html   .= '<pre style="background:#f0f0f1;border:1px solid #c3c4c7;padding:12px;border-radius:4px;overflow-x:auto;"><code>';
				$in_code = true;
			}
			continue;
		}
		if ( $in_code ) {
			$html .= esc_html( $line ) . "\n";
			continue;
		}

		// Blank line — close open lists.
		if ( '' === trim( $line ) ) {
			if ( $in_list ) {
				$html   .= '</ul>';
				$in_list = false;
			}
			if ( $in_blockquote ) {
				$html .= '</blockquote>';
				$in_blockquote = false;
			}
			if ( $in_table ) {
				$html    .= adventchat_render_table( $table_rows );
				$in_table = false;
				$table_rows = array();
			}
			continue;
		}

		// Blockquote.
		if ( preg_match( '/^>\s*(.*)$/', $line, $m ) ) {
			if ( ! $in_blockquote ) {
				$html .= '<blockquote style="border-left:4px solid #0066ff;margin:16px 0;padding:8px 16px;background:#f0f6ff;">';
				$in_blockquote = true;
			}
			$html .= '<p>' . adventchat_inline_md( $m[1] ) . '</p>';
			continue;
		}

		// Headings.
		if ( preg_match( '/^(#{1,6})\s+(.*)$/', $line, $m ) ) {
			$level = strlen( $m[1] );
			$text  = adventchat_inline_md( $m[2] );
			$style = $level <= 2 ? ' style="border-bottom:1px solid #eee;padding-bottom:8px;"' : '';
			$html .= "<h{$level}{$style}>{$text}</h{$level}>";
			continue;
		}

		// Horizontal rule.
		if ( preg_match( '/^---+$/', trim( $line ) ) ) {
			$html .= '<hr style="border:0;border-top:1px solid #eee;margin:24px 0;">';
			continue;
		}

		// Table row.
		if ( preg_match( '/^\|/', $line ) ) {
			// Skip separator rows.
			if ( preg_match( '/^\|[\s\-:|]+\|$/', $line ) ) {
				continue;
			}
			$cells = array_map( 'trim', explode( '|', trim( $line, '| ' ) ) );
			$table_rows[] = $cells;
			$in_table     = true;
			continue;
		}

		// Flush pending table if line is not a table row.
		if ( $in_table ) {
			$html      .= adventchat_render_table( $table_rows );
			$in_table   = false;
			$table_rows = array();
		}

		// Unordered list.
		if ( preg_match( '/^[\-\*]\s+(.*)$/', $line, $m ) ) {
			if ( ! $in_list ) {
				$html   .= '<ul style="list-style:disc;padding-left:24px;">';
				$in_list = true;
			}
			$html .= '<li>' . adventchat_inline_md( $m[1] ) . '</li>';
			continue;
		}

		// Ordered list.
		if ( preg_match( '/^\d+\.\s+(.*)$/', $line, $m ) ) {
			if ( ! $in_list ) {
				$html   .= '<ul style="list-style:decimal;padding-left:24px;">';
				$in_list = true;
			}
			$html .= '<li>' . adventchat_inline_md( $m[1] ) . '</li>';
			continue;
		}

		// Paragraph.
		$html .= '<p>' . adventchat_inline_md( $line ) . '</p>';
	}

	// Close any open blocks.
	if ( $in_list ) {
		$html .= '</ul>';
	}
	if ( $in_blockquote ) {
		$html .= '</blockquote>';
	}
	if ( $in_code ) {
		$html .= '</code></pre>';
	}
	if ( $in_table ) {
		$html .= adventchat_render_table( $table_rows );
	}

	return $html;
}

/**
 * Render a Markdown table as HTML.
 *
 * @param array $rows Table rows (first row is header).
 * @return string HTML table.
 */
function adventchat_render_table( array $rows ): string {
	if ( empty( $rows ) ) {
		return '';
	}
	$out  = '<table class="widefat striped" style="margin:12px 0;"><thead><tr>';
	$head = array_shift( $rows );
	foreach ( $head as $cell ) {
		$out .= '<th>' . adventchat_inline_md( $cell ) . '</th>';
	}
	$out .= '</tr></thead><tbody>';
	foreach ( $rows as $row ) {
		$out .= '<tr>';
		foreach ( $row as $cell ) {
			$out .= '<td>' . adventchat_inline_md( $cell ) . '</td>';
		}
		$out .= '</tr>';
	}
	$out .= '</tbody></table>';
	return $out;
}

/**
 * Convert inline Markdown: bold, italic, code, links, images.
 *
 * @param string $text Inline markdown text.
 * @return string HTML.
 */
function adventchat_inline_md( string $text ): string {
	// Inline code.
	$text = preg_replace( '/`([^`]+)`/', '<code style="background:#f0f0f1;padding:1px 5px;border-radius:3px;">$1</code>', $text );
	// Bold.
	$text = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text );
	// Italic.
	$text = preg_replace( '/\*(.+?)\*/', '<em>$1</em>', $text );
	// Links: [text](url).
	$text = preg_replace( '/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text );
	// Checkbox checked.
	$text = str_replace( '✅', '<span style="color:#00a32a;">✅</span>', $text );
	// Checkbox unchecked.
	$text = str_replace( '❌', '<span style="color:#cc1818;">❌</span>', $text );

	return $text;
}
