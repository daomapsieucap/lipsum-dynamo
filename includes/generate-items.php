<?php
// Exit if accessed directly
if(!defined('ABSPATH')){
	exit;
}

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
		wp_enqueue_script('lipnamo-generate-items', LIPNAMO_ASSETS_URL . 'js/lipnamo-generate-items', array('jquery-ui-sortable'), LIPNAMO_VERSION, true);
		wp_localize_script(
			'lipnamo-generate-items',
			'lipnamo_items',
			array('ajax_url' => admin_url('admin-ajax.php'))
		);
	}
	
	public function lipnamo_generate_items(){
		
		if(!$_POST || (!$_POST['cpo_data'] && !$_POST['post_type'])){
			return false;
		}
		
		parse_str($_POST['cpo_data'], $cpo_order);
		
		global $wpdb;
		
		$post_type = $_POST['post_type'];
		
		$post_status = $_POST['post_status'] ? : 'publish';
		$post_status = $post_status == 'all' ? 'any' : $post_status;
		
		$post_ids = $cpo_order['post'];
		
		if($post_ids){
			$order_start = 0;
			
			// Get minimum post order
			$pre_posts_args  = array(
				'post_type'        => $post_type,
				'posts_per_page'   => 1,
				'post_status'      => $post_status,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'post__in'         => $post_ids,
				'suppress_filters' => false,
				'fields'           => 'ids'
			);
			$pre_posts_query = new WP_Query($pre_posts_args);
			if($pre_posts_query->have_posts()){
				$order_start_id   = $pre_posts_query->posts[0];
				$order_start_post = get_post($order_start_id);
				$order_start      = $order_start_post->menu_order;
			}
			
			// Update post order
			$update_posts_args  = array(
				'post_type'        => $post_type,
				'posts_per_page'   => - 1,
				'post_status'      => $post_status,
				'orderby'          => 'post__in',
				'order'            => 'ASC',
				'post__in'         => $post_ids,
				'suppress_filters' => false,
				'fields'           => 'ids'
			);
			$update_posts_query = new WP_Query($update_posts_args);
			if($update_posts_query->have_posts()){
				foreach($update_posts_query->posts as $id){
					$wpdb->update($wpdb->posts, array('menu_order' => $order_start), array('ID' => intval($id)));
					$order_start ++;
				}
			}
		}
		
		die();
	}
}

new Lipsum_Dynamo_Generate();