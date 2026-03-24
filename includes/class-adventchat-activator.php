<?php
/**
 * Plugin activation handler.
 *
 * Creates database tables and sets default options.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Activator
 */
class AdventChat_Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate(): void {
		self::create_tables();
		self::set_defaults();

		// Store DB version for future upgrade checks.
		update_option( 'adventchat_db_version', ADVENTCHAT_DB_VERSION );

		// Flush rewrite rules for REST endpoints.
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables.
	 */
	private static function create_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}adventchat_offline_messages (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(200) NOT NULL,
			email varchar(200) NOT NULL,
			message text NOT NULL,
			department varchar(100) DEFAULT '',
			page_url varchar(500) DEFAULT '',
			status varchar(20) DEFAULT 'unread',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) {$charset_collate};

		CREATE TABLE {$wpdb->prefix}adventchat_chat_logs (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			session_id varchar(100) NOT NULL,
			site_id varchar(100) NOT NULL,
			visitor_email varchar(200) DEFAULT '',
			visitor_name varchar(200) DEFAULT '',
			agent_id bigint(20) DEFAULT 0,
			agent_name varchar(200) DEFAULT '',
			department varchar(100) DEFAULT '',
			started_at datetime DEFAULT CURRENT_TIMESTAMP,
			ended_at datetime DEFAULT NULL,
			duration_seconds int DEFAULT 0,
			message_count int DEFAULT 0,
			rating tinyint DEFAULT 0,
			rating_comment text,
			PRIMARY KEY  (id),
			KEY session_id (session_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Set default option values.
	 */
	private static function set_defaults(): void {
		$defaults = array(
			'adventchat_firebase_config'    => '',
			'adventchat_primary_color'      => '#0066ff',
			'adventchat_secondary_color'    => '#ffffff',
			'adventchat_position'           => 'bottom-right',
			'adventchat_launcher_style'     => 'bubble',
			'adventchat_welcome_title'      => __( 'Hi there! 👋', 'adventchat' ),
			'adventchat_welcome_subtitle'   => __( 'How can we help you?', 'adventchat' ),
			'adventchat_input_placeholder'  => __( 'Type a message…', 'adventchat' ),
			'adventchat_prechat_enabled'    => '1',
			'adventchat_offline_enabled'    => '1',
			'adventchat_gdpr_enabled'       => '0',
			'adventchat_sound_enabled'      => '1',
			'adventchat_auto_open_enabled'  => '0',
			'adventchat_auto_open_delay'    => '5',
			'adventchat_routing_mode'       => 'round-robin',
			'adventchat_transcript_enabled' => '1',
			'adventchat_csat_enabled'       => '1',
			'adventchat_file_sharing'       => '1',
			'adventchat_custom_css'         => '',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				update_option( $key, $value );
			}
		}
	}
}
