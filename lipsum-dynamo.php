<?php
/**
 * Plugin Name:       Dummie (formerly Lipsum Dynamo)
 * Plugin URI:        https://wordpress.org/plugins/lipsum-dynamo/
 * Description:       🖨 Generate dummy content for demo purpose
 * Version:           3.1.0
 * Requires at least: 5.2
 * Requires PHP:      8.0
 * Author:            Dao
 * Author URI:        https://daochau.com/
 * Text Domain:       lipsum-dynamo
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

const LIPNAMO_VERSION      = '3.1.0';
const LIPNAMO_ENVIRONMENT  = 'production';
const LIPNAMO_CSSJS_SUFFIX = (LIPNAMO_ENVIRONMENT !== 'development') ? '.min' : '';
define("LIPNAMO_DIR", plugin_dir_path(__FILE__));
define("LIPNAMO_ASSETS_URL", plugin_dir_url(__FILE__) . 'assets/');

// libraries
require_once('vendor/LoremIpsum.php');

// helper functions
require_once(LIPNAMO_DIR . 'includes/helpers.php');

/**
 * Init Functions
 */

// options pages
require_once(LIPNAMO_DIR . 'includes/setting/setting.php');
require_once(LIPNAMO_DIR . 'includes/setting/general.php');
require_once(LIPNAMO_DIR . 'includes/setting/cleanup.php');
require_once(LIPNAMO_DIR . 'includes/setting/data.php');

// functions
require_once(LIPNAMO_DIR . 'includes/generate-items.php');
require_once(LIPNAMO_DIR . 'includes/cleanup.php');

/**
 * Create / upgrade database
 */

register_activation_hook(__FILE__, 'lipnamo_install');
function lipnamo_install(){
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'lipnamo';
	
	$charset_collate = $wpdb->get_charset_collate();
	
	$query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
	if(!$wpdb->get_var($query) == $table_name){
		$sql = "CREATE TABLE $table_name (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					post_id bigint(20) NOT NULL,
					post_type varchar(20) NOT NULL,
					PRIMARY KEY  (id)
				) $charset_collate;";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		add_option('lipnamo_db_version', LIPNAMO_VERSION);
	}
}

add_action('plugins_loaded', 'lipnamo_update_db_check');
function lipnamo_update_db_check(){
	if(get_site_option('lipnamo_db_version') !== LIPNAMO_VERSION){
		lipnamo_install();
	}
}

/**
 * Delete data after uninstall
 */

register_uninstall_hook(__FILE__, 'lipnamo_uninstall');
function lipnamo_uninstall(){
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'lipnamo';
	
	if(lipnamo_get_option('setting_delete_generated')){
		// Delete created posts
		$posts = $wpdb->get_results("SELECT * FROM $table_name");
		if($posts){
			foreach($posts as $post){
				$post_id = $post->post_id;
				if(get_post_status($post_id)){
					wp_delete_post($post_id);
				}
			}
		}
	}
	
	// Delete plugin data
	$sql = "DROP TABLE IF EXISTS $table_name";
	$wpdb->query($sql);
	delete_option('lipsum_dynamo');
}