<?php
/**
 * Plugin Name:       Lipsum Dynamo
 * Plugin URI:        https://wordpress.org/plugins/lipsum-dynamo/
 * Description:       🖨 Dummy text for demo purpose
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Dao Chau
 * Author URI:        https://daochau.com/
 * Text Domain:       lipnamo
 */

// If this file is called directly, abort.
if(!defined('WPINC')){
	die;
}

// Exit if accessed directly
if(!defined('ABSPATH')){
	exit;
}

/**
 * Definitions
 */

define('LIPNAMO_VERSION', '1.0.0');
define("LIPNAMO_DIR", plugin_dir_path(__FILE__));
define("LIPNAMO_ASSETS_URL", plugin_dir_url(__FILE__) . 'assets/');

/**
 * Init Functions
 */

add_action('init', 'lipnamo_init');
function lipnamo_init(){
	// helper functions
	//include_once(LIPNAMO_DIR . 'includes/helper.php');
	
	// options pages
	include_once(LIPNAMO_DIR . 'includes/setting.php');
	
	// functions
	include_once(LIPNAMO_DIR . 'includes/generate-items.php');
}

/**
 * Database creation
 */

register_activation_hook( __FILE__, 'lipnamo_install' );
function lipnamo_install(){
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'lipnamo';
	
	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		post_id bigint(20) NOT NULL,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	add_option( 'lipnamo_db_version', LIPNAMO_VERSION );
}

add_action( 'plugins_loaded', 'lipnamo_update_db_check' );
function lipnamo_update_db_check() {
	if ( get_site_option( 'lipnamo_db_version' ) != LIPNAMO_VERSION ) {
		lipnamo_install();
	}
}