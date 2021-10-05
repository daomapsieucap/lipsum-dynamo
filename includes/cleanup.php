<?php
// Exit if accessed directly
if(!defined('ABSPATH')){
	exit;
}

/**
 * Cleanup Dummy Items
 */
class Lipsum_Dynamo_Cleanup{
	public function __construct(){
		add_action('admin_init', array($this, 'lipnamo_cleanup_scripts'));
		
		add_action("wp_ajax_lipnamo_cleanup_items", array($this, 'lipnamo_cleanup_items'));
		add_action("wp_ajax_nopriv_lipnamo_cleanup_items", array($this, 'lipnamo_cleanup_items'));
	}
	
	public function lipnamo_cleanup_scripts(){
		wp_enqueue_script('lipnamo-cleanup-items', LIPNAMO_ASSETS_URL . 'js/lipnamo-cleanup-items.js', array('jquery'), LIPNAMO_VERSION, true);
		wp_localize_script('lipnamo-cleanup-items', 'lipnamo_items',
			array(
				'ajax_url'   => admin_url('admin-ajax.php'),
				'ajax_nonce' => wp_create_nonce('lipnamo_ajax_nonce'),
			)
		);
	}
	
	public function lipnamo_cleanup_items(){
		// Bail if there is no parameters passed
		if(!$_POST){
			return;
		}
		
		// Bail if not authorized.
		if(!check_admin_referer('lipnamo_ajax_nonce', 'lipnamo_ajax_nonce')){
			return;
		}
		
		// Get AJAX data
		$post_total = intval(lipnamo_array_key_exists('post_total', $_POST, 10));
		$post_type  = sanitize_text_field(lipnamo_array_key_exists('post_type', $_POST, 'any'));
		$post_step  = intval(lipnamo_array_key_exists('post_step', $_POST));
		
		// Exit if invalid post type
		if($post_type !== 'any'){
			$valid_post_types = get_post_types(array('public' => true), 'objects');
			if(!in_array($post_type, array_keys($valid_post_types))){
				return;
			}
		}
		
		if($post_step <= $post_total){
			
			$limit_from = $post_step;
			$limit_to   = $post_step + 1;
			
			// delete posts
			global $wpdb;
			
			$table_name = $wpdb->prefix . 'lipnamo';
			if($post_type = "any"){
				$posts = $wpdb->get_results("SELECT * FROM $table_name");
			}else{
				$posts = $wpdb->get_results("SELECT * FROM $table_name WHERE post_type = '$post_type'");
			}
			if($posts){
				foreach($posts as $post){
					$post_id = $post->post_id;
					if(get_post_status($post_id)){
						wp_delete_post($post_id);
					}
				}
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

new Lipsum_Dynamo_Cleanup();