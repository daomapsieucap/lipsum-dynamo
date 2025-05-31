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
		add_action('admin_enqueue_scripts', [$this, 'lipnamo_cleanup_scripts']);
		
		add_action("wp_ajax_lipnamo_cleanup_items", [$this, 'lipnamo_cleanup_items']);
		add_action("wp_ajax_nopriv_lipnamo_cleanup_items", [$this, 'lipnamo_cleanup_items']);
		
		add_action("wp_ajax_lipnamo_total_items", [$this, 'lipnamo_update_total_items']);
		add_action("wp_ajax_nopriv_lipnamo_total_items", [$this, 'lipnamo_update_total_items']);
	}
	
	public function lipnamo_cleanup_scripts($hook_suffix){
		if(str_contains($hook_suffix, 'lipsum-dynamo')){
			wp_enqueue_script('lipnamo-cleanup-items', LIPNAMO_ASSETS_URL . 'js/lipnamo-cleanup-items.js', ['jquery'], LIPNAMO_VERSION, true);
			wp_localize_script('lipnamo-cleanup-items', 'lipnamo_items',
				[
					'ajax_url'   => admin_url('admin-ajax.php'),
					'ajax_nonce' => wp_create_nonce('lipnamo_ajax_nonce'),
				]
			);
		}
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
		$post_total = (int) lipnamo_array_key_exists('post_total', $_POST, 10);
		$post_type  = sanitize_text_field(lipnamo_array_key_exists('post_type', $_POST, 'any'));
		$post_step  = (int) lipnamo_array_key_exists('post_step', $_POST);
		
		// Exit if invalid post type
		if($post_type !== 'any'){
			$valid_post_types = get_post_types(['public' => true], 'objects');
			if(!in_array($post_type, array_keys($valid_post_types))){
				return;
			}
		}
		
		if($post_step <= $post_total){
			// delete posts
			global $wpdb;
			
			$table_name = $wpdb->prefix . 'lipnamo';
			$post_table = $wpdb->prefix . 'posts';
			if($post_type = "any"){
				$mysql_query = "SELECT lipnamo.post_id FROM $table_name as lipnamo,$post_table as posts WHERE lipnamo.post_id = posts.ID LIMIT 1";
			}else{
				$mysql_query = "SELECT lipnamo.post_id FROM $table_name as lipnamo,$post_table as posts WHERE lipnamo.post_id = posts.ID AND post_type = '$post_type' LIMIT 1";
			}
			$posts = $wpdb->get_results($mysql_query);
			if($posts){
				foreach($posts as $post){
					$post_id = (int) $post->post_id;
					if(get_post_status($post_id)){
						wp_delete_post($post_id, true);
						$wpdb->query("DELETE FROM $table_name WHERE post_id = $post_id");
					}
				}
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
			$result['message'] = 'Deleted total ' . $post_total . ' items';
		}
		
		// Send output as JSON for processing via AJAX.
		echo json_encode($result, JSON_THROW_ON_ERROR);
		exit;
	}
	
	public function lipnamo_update_total_items(){
		// Bail if there is no parameters passed
		if(!$_POST){
			return;
		}
		
		// Bail if not authorized.
		if(!check_admin_referer('lipnamo_ajax_nonce', 'lipnamo_ajax_nonce')){
			return;
		}
		
		// Get AJAX data
		$post_type = sanitize_text_field(lipnamo_array_key_exists('post_type', $_POST, 'any'));
		
		// Exit if invalid post type
		if($post_type !== 'any'){
			$valid_post_types = get_post_types(['public' => true], 'objects');
			if(!in_array($post_type, array_keys($valid_post_types))){
				return;
			}
		}
		
		global $wpdb;
		$post_total = 0;
		
		$table_name = $wpdb->prefix . 'lipnamo';
		if($post_type === "any"){
			$mysql_query = "SELECT * FROM $table_name";
		}else{
			$mysql_query = "SELECT * FROM $table_name WHERE post_type = '$post_type'";
		}
		$posts = $wpdb->get_results($mysql_query);
		if($posts){
			$post_total = count($posts);
		}
		
		$result['post_total'] = $post_total;
		
		// Send output as JSON for processing via AJAX.
		echo json_encode($result, JSON_THROW_ON_ERROR);
		exit;
	}
}

new Lipsum_Dynamo_Cleanup();