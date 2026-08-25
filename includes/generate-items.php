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
		if(str_contains($hook_suffix, 'lipsum-dynamo')){
			wp_enqueue_script('lipnamo-generate-items', LIPNAMO_ASSETS_URL . 'js/lipnamo-generate-items.js', false, LIPNAMO_VERSION, true);
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
		$post_total          = (int) lipnamo_array_key_exists('post_total', $_POST, 10);
		$post_type           = sanitize_text_field(lipnamo_array_key_exists('post_type', $_POST, 'post'));
		$post_author         = (int) lipnamo_array_key_exists('post_author', $_POST);
		$post_status         = sanitize_text_field(lipnamo_array_key_exists('post_status', $_POST, 'publish'));
		$post_thumbnails     = sanitize_text_field(lipnamo_array_key_exists('post_thumbnails', $_POST));
		$post_title_length   = sanitize_text_field(lipnamo_array_key_exists('post_title_length', $_POST));
		$post_excerpt_length = sanitize_text_field(lipnamo_array_key_exists('post_excerpt_length', $_POST));
		$post_step           = (int) lipnamo_array_key_exists('post_step', $_POST);
		
		// Exit if invalid post type
		$valid_post_types = get_post_types(['public' => true], 'objects');
		if(!in_array($post_type, array_keys($valid_post_types))){
			return;
		}
		
		// Validate data
		$post_title_min = $post_title_max = 1;
		if($post_title_length){
			$post_title_array = explode(',', $post_title_length);
			$post_title_min   = (int) $post_title_array[0];
			$post_title_max   = (int) $post_title_array[1];
		}
		$post_excerpt_min = $post_excerpt_max = 1;
		if($post_excerpt_length){
			$post_excerpt_array = explode(',', $post_excerpt_length);
			$post_excerpt_min   = (int) $post_excerpt_array[0];
			$post_excerpt_max   = (int) $post_excerpt_array[1];
		}

		if($post_step <= $post_total){
			$generator = new LoremIpsum();

			// Set dummy variables
			$title_words   = rand($post_title_min, $post_title_max);
			$excerpt_sentences = rand($post_excerpt_min, $post_excerpt_max);

			$thumbnail_pool = [];
			if($post_thumbnails){
				$thumbnail_pool = array_values(array_unique(array_filter(
					array_map('intval', explode(',', $post_thumbnails))
				)));
			}
			$thumbnail_id     = $thumbnail_pool ? $thumbnail_pool[array_rand($thumbnail_pool)] : 0;
			$content_image_id = $thumbnail_pool ? $thumbnail_pool[array_rand($thumbnail_pool)] : 0;
			$gallery_ids      = $this->lipnamo_pick_gallery_ids($thumbnail_pool);

			// prevent lorem ipsum from generating the same word twice in a row
			if($post_step > 1){
				$generator->word();
			}

			// variables
			$post_title   = ucfirst($generator->words($title_words));
			$post_excerpt = $generator->sentences($excerpt_sentences);
			$post_content = $this->lipnamo_build_post_content($generator, $content_image_id, $gallery_ids);

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

	/**
	 * Build a fixed-structure demo page: intro, heading + text, single image, heading + text, gallery, closing heading + text.
	 */
	private function lipnamo_build_post_content($generator, $content_image_id, array $gallery_ids){
		$blocks = [];

		$blocks[] = $this->lipnamo_short_paragraph($generator);

		$blocks[] = $this->lipnamo_build_heading($generator, 'h2', 3, 6);
		$blocks[] = $this->lipnamo_paragraph_with_link($generator);

		$blocks[] = $this->lipnamo_build_heading($generator, 'h3', 3, 5);
		$blocks[] = $this->lipnamo_short_paragraph($generator);

		$blocks[] = $this->lipnamo_build_heading($generator, 'h3', 3, 5);
		$blocks[] = $this->lipnamo_short_paragraph($generator);
		$blocks[] = $this->lipnamo_build_list($generator, 'ul', 3, 5);

		$image_html = $this->lipnamo_build_image_html($content_image_id);
		if($image_html){
			$blocks[] = $image_html;
		}

		$blocks[] = '<hr>';

		$blocks[] = $this->lipnamo_build_heading($generator, 'h2', 3, 6);
		$blocks[] = $this->lipnamo_short_paragraph($generator);

		$blocks[] = $this->lipnamo_build_heading($generator, 'h3', 3, 5);
		$blocks[] = $this->lipnamo_short_paragraph($generator);

		$blocks[] = $this->lipnamo_build_heading($generator, 'h3', 3, 5);
		$blocks[] = $this->lipnamo_short_paragraph($generator);
		$blocks[] = $this->lipnamo_build_list($generator, 'ol', 3, 5);

		$gallery_shortcode = $this->lipnamo_build_gallery_shortcode($gallery_ids);
		if($gallery_shortcode){
			$blocks[] = $gallery_shortcode;
		}

		$blocks[] = $this->lipnamo_build_heading($generator, 'h2', 2, 4);
		$blocks[] = $this->lipnamo_short_paragraph($generator);

		return implode("\n\n", $blocks);
	}

	/**
	 * Build a heading tag with a short randomized title.
	 */
	private function lipnamo_build_heading($generator, $tag, $min_words, $max_words){
		return '<' . $tag . '>' . ucwords($generator->words(rand($min_words, $max_words))) . '</' . $tag . '>';
	}

	/**
	 * Build a short paragraph (2-3 sentences) instead of the library's long gaussian-length default.
	 */
	private function lipnamo_short_paragraph($generator){
		return '<p>' . $generator->sentences(rand(2, 3)) . '</p>';
	}

	/**
	 * Build a short paragraph that ends with an inline link, so demo content always has one.
	 */
	private function lipnamo_paragraph_with_link($generator){
		$sentences = $generator->sentences(rand(2, 3));
		$link_text = ucfirst($generator->words(rand(2, 3)));

		return '<p>' . $sentences . ' <a href="#">' . $link_text . '</a>.</p>';
	}

	/**
	 * Build a ul/ol list with randomized short item text, nesting a second-level list under one random item.
	 */
	private function lipnamo_build_list($generator, $tag, $min_items, $max_items){
		$item_count  = rand($min_items, $max_items);
		$nested_item = rand(1, $item_count);

		$items = '';
		foreach(range(1, $item_count) as $i){
			$items .= '<li>' . ucfirst($generator->words(rand(3, 6)));
			if($i === $nested_item){
				$items .= $this->lipnamo_build_sublist($generator, $tag);
			}
			$items .= '</li>';
		}

		return '<' . $tag . '>' . $items . '</' . $tag . '>';
	}

	/**
	 * Build a second-level ul/ol list nested inside a parent list item.
	 */
	private function lipnamo_build_sublist($generator, $tag){
		$items = '';
		foreach(range(1, rand(2, 3)) as $i){
			$items .= '<li>' . ucfirst($generator->words(rand(3, 6))) . '</li>';
		}

		return '<' . $tag . '>' . $items . '</' . $tag . '>';
	}

	/**
	 * Build a classic-editor-style image tag for a single attachment ID.
	 */
	private function lipnamo_build_image_html($attachment_id){
		if(!$attachment_id){
			return '';
		}

		$image = wp_get_attachment_image_src($attachment_id, 'full');
		if(!$image){
			return '';
		}

		[$src, $width, $height] = $image;

		return sprintf(
			'<p><img class="alignnone size-full wp-image-%1$d" src="%2$s" alt="" width="%3$d" height="%4$d" /></p>',
			$attachment_id, esc_url($src), $width, $height
		);
	}

	/**
	 * Build a classic [gallery] shortcode from a list of attachment IDs.
	 */
	private function lipnamo_build_gallery_shortcode(array $ids){
		if(!$ids){
			return '';
		}

		return '[gallery ids="' . implode(',', $ids) . '"]';
	}

	/**
	 * Pick a random subset (up to 3) of attachment IDs from the thumbnail pool for the gallery.
	 */
	private function lipnamo_pick_gallery_ids(array $pool){
		if(!$pool){
			return [];
		}

		$gallery_size = min(3, count($pool));
		$keys         = (array) array_rand($pool, $gallery_size);

		return array_values(array_map(function($key) use ($pool){
			return $pool[$key];
		}, $keys));
	}
}

new Lipsum_Dynamo_Generate();