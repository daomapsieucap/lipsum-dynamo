<?php
// Exit if accessed directly
if(!defined('ABSPATH')){
	exit;
}

use joshtronic\LoremIpsum;

/**
 * Generate Dummy Items
 */
class Lipsum_Dynamo_Generate{
	public function __construct(){
		add_action('admin_init', array($this, 'lipnamo_generate_scripts'));
		
		add_action("wp_ajax_lipnamo_generate_items", array($this, 'lipnamo_generate_items'));
		add_action("wp_ajax_nopriv_lipnamo_generate_items", array($this, 'lipnamo_generate_items'));
	}
	
	public function lipnamo_generate_scripts(){
		wp_enqueue_script('lipnamo-generate-items', LIPNAMO_ASSETS_URL . 'js/lipnamo-generate-items.js', array('jquery'), LIPNAMO_VERSION, true);
		wp_localize_script(
			'lipnamo-generate-items',
			'lipnamo_items',
			array('ajax_url' => admin_url('admin-ajax.php'))
		);
	}
	
	public function lipnamo_generate_items(){
		if(!$_POST){
			return;
		}
		
		// Get AJAX data
		$post_items      = lipnamo_array_key_exists('post_items', $_POST, 10);
		$post_type       = lipnamo_array_key_exists('post_type', $_POST, 'post');
		$post_author     = lipnamo_array_key_exists('post_author', $_POST);
		$post_status     = lipnamo_array_key_exists('post_status', $_POST, 'publish');
		$post_thumbnails = lipnamo_array_key_exists('post_thumbnails', $_POST);
		
		$generator = new LoremIpsum();
		
		// Set dummy variables
		$text         = $generator->word();
		$post_title   = ucfirst($generator->words(10));
		$post_excerpt = $generator->sentences(2);
		$post_content = $generator->paragraphs(5);
		
		// Create post
		$new_post = array(
			'post_type'    => $post_type,
			'post_title'   => wp_strip_all_tags($post_title),
			'post_excerpt' => $post_excerpt,
			'post_content' => $post_content,
			'post_status'  => $post_status,
			'post_author'  => $post_author
		);
		wp_insert_post($new_post);
		
		die();
	}
}

new Lipsum_Dynamo_Generate();