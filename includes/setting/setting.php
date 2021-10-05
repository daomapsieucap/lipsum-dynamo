<?php
// Exit if accessed directly
if(!defined('ABSPATH')){
	exit;
}

/**
 * Setting Page
 */
class Lipsum_Dynamo_Setting{
	
	public function __construct(){
		add_action('admin_menu', array($this, 'lipnamo_setting'));
		add_action("admin_enqueue_scripts", array($this, 'lipnamo_assets'));
	}
	
	public function lipnamo_assets(){
		wp_enqueue_style('lipnamo-admin', LIPNAMO_ASSETS_URL . 'css/lipnamo-admin.css', false, LIPNAMO_VERSION);
		
		// Upload field
		wp_enqueue_media();
		
		// Plugin scripts
		wp_enqueue_script('lipnamo-admin', LIPNAMO_ASSETS_URL . 'js/lipnamo-admin.js', array('jquery'), LIPNAMO_VERSION);
	}
	
	public function lipnamo_setting(){
		add_menu_page(
			'Lipsum Dynamo',
			'Lipsum Dynamo',
			'manage_options',
			'lipsum-dynamo',
			'',
			'dashicons-editor-justify',
			85
		);
	}
}

new Lipsum_Dynamo_Setting();