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
		add_action('admin_enqueue_scripts', [$this, 'lipnamo_generate_scripts']);
		
		add_action("wp_ajax_lipnamo_generate_items", [$this, 'lipnamo_generate_items']);
		add_action("wp_ajax_nopriv_lipnamo_generate_items", [$this, 'lipnamo_generate_items']);
	}
	
	public function lipnamo_generate_scripts($hook_suffix){
		if(strpos($hook_suffix, 'lipsum-dynamo') !== false){
			wp_enqueue_script('lipnamo-generate-items', LIPNAMO_ASSETS_URL . 'js/lipnamo-generate-items.js', ['jquery'], DUMMIE_VERSION, true);
			wp_localize_script('lipnamo-generate-items', 'lipnamo_items',
				[
					'ajax_url'   => admin_url('admin-ajax.php'),
					'ajax_nonce' => wp_create_nonce('lipnamo_ajax_nonce'),
				]
			);
		}
	}
	
	public function lipnamo_generate_items(){
		// Bail if there is no parameters passed
		if(!$_POST){
			return;
		}
		
		// Bail if not authorized.
		if(!check_admin_referer('lipnamo_ajax_nonce', 'lipnamo_ajax_nonce')){
			return;
		}
		
		// Get AJAX data
		$post_total          = intval(lipnamo_array_key_exists('post_total', $_POST, 10));
		$post_type           = sanitize_text_field(lipnamo_array_key_exists('post_type', $_POST, 'post'));
		$post_author         = intval(lipnamo_array_key_exists('post_author', $_POST));
		$post_status         = sanitize_text_field(lipnamo_array_key_exists('post_status', $_POST, 'publish'));
		$post_thumbnails     = sanitize_text_field(lipnamo_array_key_exists('post_thumbnails', $_POST));
		$post_title_length   = sanitize_text_field(lipnamo_array_key_exists('post_title_length', $_POST));
		$post_excerpt_length = sanitize_text_field(lipnamo_array_key_exists('post_excerpt_length', $_POST));
		$post_body_length    = sanitize_text_field(lipnamo_array_key_exists('post_body_length', $_POST));
		$post_step           = intval(lipnamo_array_key_exists('post_step', $_POST));
		
		// Exit if invalid post type
		$valid_post_types = get_post_types(['public' => true], 'objects');
		if(!in_array($post_type, array_keys($valid_post_types))){
			return;
		}
		
		// Validate data
		$post_title_min = $post_title_max = 1;
		if($post_title_length){
			$post_title_array = explode(',', $post_title_length);
			$post_title_min   = intval($post_title_array[0]);
			$post_title_max   = intval($post_title_array[1]);
		}
		$post_excerpt_min = $post_excerpt_max = 1;
		if($post_excerpt_length){
			$post_excerpt_array = explode(',', $post_excerpt_length);
			$post_excerpt_min   = intval($post_excerpt_array[0]);
			$post_excerpt_max   = intval($post_excerpt_array[1]);
		}
		$post_body_min = $post_body_max = 1;
		if($post_body_length){
			$post_body_array = explode(',', $post_body_length);
			$post_body_min   = intval($post_body_array[0]);
			$post_body_max   = intval($post_body_array[1]);
		}
		
		if($post_step <= $post_total){
			$generator = new LoremIpsum();
			
			// Set dummy variables
			$title_words        = rand($post_title_min, $post_title_max);
			$excerpt_sentences  = rand($post_excerpt_min, $post_excerpt_max);
			$content_paragraphs = rand($post_body_min, $post_body_max);
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
			$new_post = [
				'post_type'    => $post_type,
				'post_title'   => wp_strip_all_tags($post_title),
				'post_excerpt' => $post_excerpt,
				'post_content' => $post_content,
				'post_status'  => $post_status,
				'post_author'  => $post_author,
			];
			$post_id  = wp_insert_post($new_post);
			if(!is_wp_error($post_id) && $thumbnail_id){
				set_post_thumbnail($post_id, $thumbnail_id);
			}
			
			if(!is_wp_error($post_id)){
				global $wpdb;
				$table_name = $wpdb->prefix . 'lipnamo';
				$wpdb->insert($table_name, [
					'post_id'   => $post_id,
					'post_type' => $post_type,
				]);
			}
			
			$post_step ++;
		}
		
		if($post_step > $post_total){
			$post_step = $post_total;
		}
		
		// Store results in an array.
		$result = [
			'step' => $post_step,
		];
		
		if($post_step >= $post_total){
			$result['message'] = 'Created total ' . $post_total . ' items';
		}
		
		// Send output as JSON for processing via AJAX.
		echo json_encode($result);
		exit;
	}
}

new Lipsum_Dynamo_Generate();