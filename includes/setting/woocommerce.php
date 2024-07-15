<?php
// Exit if accessed directly
if(!defined('ABSPATH')){
	exit;
}

/**
 * General Setting Page
 */
class Lipsum_Dynamo_Woocommerce_Setting{
	
	public function __construct(){
	}
	
	public function lipnamo_woocommerce_page_init(){
		register_setting(
			'lipnamo_group',
			'lipsum-dynamo-woocommerce',
			[$this, 'sanitize_text_field']
		);
		
		add_settings_section(
			'lipnamo_section',
			'',
			[$this, 'lipnamo_admin_section_info'],
			'lipsum-dynamo-woocommerce'
		);
		
		add_settings_field(
			'lipnamo_post_total', // id
			'Number of items', // title
			[$this, 'lipnamo_post_total'], // callback
			'lipsum-dynamo-woocommerce', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'post_type', // id
			'Select post type', // title
			[$this, 'lipnamo_post_type'], // callback
			'lipsum-dynamo-woocommerce', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'post_author', // id
			'Post author', // title
			[$this, 'lipnamo_post_author'], // callback
			'lipsum-dynamo-woocommerce', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'post_status', // id
			'New Item status', // title
			[$this, 'lipnamo_post_status'], // callback
			'lipsum-dynamo-woocommerce', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'post_thumbnail', // id
			'New Item Thumbnails', // title
			[$this, 'lipnamo_post_thumbnail'], // callback
			'lipsum-dynamo-woocommerce', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'length_control', // id
			'Length Control', // title
			[$this, 'lipnamo_length_control'], // callback
			'lipsum-dynamo-woocommerce', // page
			'lipnamo_section' // section
		);
	}
	
	public function lipnamo_admin_section_info(){
	}
	
	public function lipnamo_post_total(){
		?>
        <fieldset>
            <label for="post_total">
                <input name="lipnamo_post_total" type="number" id="post_total" min="1" max="50" value="10"/>
            </label>
        </fieldset>
		<?php
	}
	
	public function lipnamo_post_type(){
		$post_types = get_post_types(['public' => true], 'objects');
		?>
        <fieldset>
            <label for="post_type">
                <select id="post_type" name='lipnamo_post_type'>
					<?php
					if($post_types){
						foreach($post_types as $slug => $post_type){
							// skip attachment post type
							if($slug == 'attachment'){
								continue;
							}
							$list[$slug] = $post_type->label;
							?>
                            <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_attr($post_type->label); ?></option>
							<?php
						}
					}
					?>
                </select>
            </label>
        </fieldset>
		<?php
	}
	
	public function lipnamo_post_author(){
		$users       = get_users(['role__in' => ['administrator'], 'fields' => ['ID']]);
		$admin_email = get_option('new_admin_email');
		?>
        <fieldset>
            <label for="post_author">
                <select id="post_author" name='lipnamo_post_author'>
					<?php
					if($users){
						foreach($users as $user_id){
							$user_id   = $user_id->ID;
							$user_info = get_userdata($user_id);
							$name      = $user_info->user_login;
							$email     = $user_info->user_email;
							$selected  = $admin_email == $email ? 'selected' : '';
							?>
                            <option value="<?php echo esc_attr($user_id); ?>" <?php echo $selected; ?>>
								<?php echo esc_attr($name); ?>
                            </option>
							<?php
						}
					}
					?>
                </select>
            </label>
        </fieldset>
		<?php
	}
	
	public function lipnamo_post_status(){
		$post_statuses = get_post_statuses();
		?>
        <fieldset>
            <label for="post_status">
                <select id="post_status" name='lipnamo_post_status'>
					<?php
					if($post_statuses){
						foreach($post_statuses as $slug => $post_status){
							$selected = '';
							if($slug == 'publish'){
								$selected = 'selected';
							}
							?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php echo esc_attr($selected); ?>>
								<?php echo esc_attr($post_status); ?>
                            </option>
							<?php
						}
					}
					?>
                </select>
            </label>
        </fieldset>
		<?php
	}
	
	public function lipnamo_post_thumbnail(){
		?>
        <fieldset class="lipnamo-input__img">
            <div class="lipnamo-preview">
                <ul class="lipnamo-preview__list"></ul>
            </div>
            <label>
                <input id="lipnamo-thumbnails" type="hidden" name="lipnamo_thumbnails" value=""/>
            </label>
            <button class="button lipnamo-upload"><?php echo __('Add Thumbnails', 'lipsum-dynamo'); ?></button>
        </fieldset>
		<?php
	}
	
	public function lipnamo_length_control(){
		?>
        <fieldset class="lipnamo-length-control lipnamo-length-control__customize">
            <label for="length_title_min"><?php echo __("Title", "lipsum-dynamo"); ?></label>
			
			<?php echo __("From", "lipsum-dynamo"); ?>
            <input class="small-text" id="length_title_min" type="number" min="1" value="8"
                   name="length_title_min"/> <?php echo __("word(s)", "lipsum-dynamo"); ?>
			<?php echo __("to", "lipsum-dynamo"); ?>
            <input class="small-text" id="length_title_max" type="number" min="1" value="15"
                   name="length_title_max"/> <?php echo __("word(s)", "lipsum-dynamo"); ?>

            <br/>

            <label for="length_excerpt_min"><?php echo __("Excerpt", "lipsum-dynamo"); ?></label>
			
			<?php echo __("From", "lipsum-dynamo"); ?>
            <input class="small-text" id="length_excerpt_min" type="number" min="1" value="1"
                   name="length_excerpt_min"/> <?php echo __("sentence(s)", "lipsum-dynamo"); ?>
			<?php echo __("to", "lipsum-dynamo"); ?>
            <input class="small-text" id="length_excerpt_max" type="number" min="1" value="2"
                   name="length_excerpt_max"/> <?php echo __("sentence(s)", "lipsum-dynamo"); ?>

            <br/>

            <label for="length_content_min"><?php echo __("Content", "lipsum-dynamo"); ?></label>
			
			<?php echo __("From", "lipsum-dynamo"); ?>
            <input class="small-text" id="length_content_min" type="number" min="1" value="1"
                   name="length_content_min"/> <?php echo __("paragraph(s)", "lipsum-dynamo"); ?>
			<?php echo __("to", "lipsum-dynamo"); ?>
            <input class="small-text" id="length_content_max" min="1" type="number" value="10"
                   name="length_content_max"/> <?php echo __("paragraph(s)", "lipsum-dynamo"); ?>
        </fieldset>
		<?php
	}
}

new Lipsum_Dynamo_Woocommerce_Setting();