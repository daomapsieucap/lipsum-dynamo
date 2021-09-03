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
		wp_localize_script('lipnamo-generate-items', 'lipnamo_items',
			array(
				'ajax_url'   => admin_url('admin-ajax.php'),
				'ajax_nonce' => wp_create_nonce('lipnamo_ajax_nonce'),
			)
		);
	}
	
	public function lipnamo_generate_items(){
		if(!$_POST){
			return;
		}
		
		// Bail if not authorized.
		if(!check_admin_referer('lipnamo_ajax_nonce', 'lipnamo_ajax_nonce')){
			return;
		}
		
		// Get AJAX data
		$post_total      = intval(lipnamo_array_key_exists('post_total', $_POST, 10));
		$post_type       = lipnamo_array_key_exists('post_type', $_POST, 'post');
		$post_author     = intval(lipnamo_array_key_exists('post_author', $_POST));
		$post_status     = lipnamo_array_key_exists('post_status', $_POST, 'publish');
		$post_thumbnails = lipnamo_array_key_exists('post_thumbnails', $_POST);
		$post_step       = intval(lipnamo_array_key_exists('post_step', $_POST));
		
		if($post_step <= $post_total){
			$generator = new LoremIpsum();
			
			// Set dummy variables
			$title_words        = rand(10, 15);
			$excerpt_sentences  = rand(1, 3);
			$content_paragraphs = rand(1, 10);
			$thumbnail_id       = 0;
			if($post_thumbnails){
				$post_thumbnails = array_map('intval', explode(',', $post_thumbnails));
				$thumbnail_rand  = array_rand($post_thumbnails);
				$thumbnail_id    = $post_thumbnails[$thumbnail_rand];
			}
			
			if($post_step > 1){
				$generator->word();
			}
			$post_title   = ucfirst($generator->words($title_words));
			$post_excerpt = $generator->sentences($excerpt_sentences);
			$post_content = $generator->paragraphs($content_paragraphs);
			
			// Create post
			$new_post = array(
				'post_type'    => $post_type,
				'post_title'   => wp_strip_all_tags($post_title),
				'post_excerpt' => $post_excerpt,
				'post_content' => $post_content,
				'post_status'  => $post_status,
				'post_author'  => $post_author
			);
			$post_id  = wp_insert_post($new_post);
			if(!is_wp_error($post_id) && $thumbnail_id){
				set_post_thumbnail($post_id, $thumbnail_id);
			}
			
			$post_step ++;
		}
		
		if($post_step > $post_total){
			$post_step = $post_total;
		}
		
		// Store results in an array.
		$result = array(
			'step' => $post_step
		);
		
		if($post_step >= $post_total){
			$result['message'] = 'Finished creating total ' . $post_total . ' items';
		}
		
		// Send output as JSON for processing via AJAX.
		echo json_encode($result);
		exit;
	}
}

new Lipsum_Dynamo_Generate();