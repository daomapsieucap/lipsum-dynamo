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
		add_action('admin_init', array($this, 'lipnamo_admin_page_init'));
		
		// enqueue assets
		add_action("admin_enqueue_scripts", array($this, 'lipnamo_assets'));
	}
	
	public function lipnamo_assets(){
		wp_enqueue_style('lipnamo-admin', LIPNAMO_ASSETS_URL . 'css/lipnamo-admin.css', false, LIPNAMO_VERSION, 'all');
		
		// Upload field
		wp_enqueue_media();
		
		// Plugin scripts
		wp_enqueue_script('lipnamo-admin', LIPNAMO_ASSETS_URL . 'js/lipnamo-admin.js', array('jquery'), LIPNAMO_VERSION);
	}
	
	public function lipnamo_setting(){
		add_submenu_page(
			'tools.php',
			'Lipsum Dynamo',
			'Lipsum Dynamo',
			'manage_options',
			'lipsum-dynamo',
			array($this, 'lipnamo_admin_page')
		);
	}
	
	public function lipnamo_admin_page(){
		?>
        <div class="wrap">
            <h2>Lipsum Dynamo</h2>
			<?php settings_errors(); ?>

            <form class="lipnamo" method="post" action="options.php">
				<?php do_settings_sections('lipsum-dynamo'); ?>
                <input name="lipnamo-generate__step" type="hidden" value="1"/>
                <div class="lipnamo-generate__progress-wrapper" style="display:none;">
                    <div class="lipnamo-generate__progress">
                        <div class="lipnamo-generate__progress-bar" style="width:0"></div>
                    </div>
                    <div class="lipnamo-generate__progress-text">
                        Processing <span class="lipnamo-generate__progress-step">1</span> of <span
                                class="lipnamo-generate__progress-total">10</span>
                    </div>
                </div>
                <a href="#"
                   class="lipnamo-generate button button-primary"><?php echo __('Generate', 'lipsum-dynamo'); ?></a>
            </form>
            <form class="lipnamo" method="post" action="<?php echo get_admin_url() . 'options.php'; ?>">
				<?php
				settings_fields('lipnamo_group');
				do_settings_sections('lipsum-dynamo-setting');
				submit_button();
				?>
            </form>
        </div>
		<?php
	}
	
	public function lipnamo_admin_page_init(){
		register_setting(
			'lipnamo_group',
			'lipsum_dynamo',
			array($this, 'sanitize_text_field')
		);
		
		add_settings_section(
			'lipnamo_section',
			'<span class="dashicons dashicons-list-view"></span> General',
			array($this, 'lipnamo_admin_section_info'),
			'lipsum-dynamo'
		);
		
		add_settings_field(
			'lipnamo_post_total', // id
			'Number of items', // title
			array($this, 'lipnamo_post_total'), // callback
			'lipsum-dynamo', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'post_type', // id
			'Select post type', // title
			array($this, 'lipnamo_post_type'), // callback
			'lipsum-dynamo', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'post_author', // id
			'Post author', // title
			array($this, 'lipnamo_post_author'), // callback
			'lipsum-dynamo', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'post_status', // id
			'New Item status', // title
			array($this, 'lipnamo_post_status'), // callback
			'lipsum-dynamo', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'post_thumbnail', // id
			'New Item Thumbnails', // title
			array($this, 'lipnamo_post_thumbnail'), // callback
			'lipsum-dynamo', // page
			'lipnamo_section' // section
		);
		
		add_settings_field(
			'length_control', // id
			'Length Control', // title
			array($this, 'lipnamo_length_control'), // callback
			'lipsum-dynamo', // page
			'lipnamo_section' // section
		);
		
		add_settings_section(
			'lipnamo_setting_section',
			'<span class="dashicons dashicons-admin-settings"></span> Setting',
			array($this, 'lipnamo_admin_section_info'),
			'lipsum-dynamo-setting'
		);
		
		add_settings_field(
			'setting_delete_generated', // id
			'Delete generated items after uninstall', // title
			array($this, 'lipnamo_setting_delete_generated'), // callback
			'lipsum-dynamo-setting', // page
			'lipnamo_setting_section' // section
		);
		
		add_settings_field(
			'setting_delete_data', // id
			'Cleanup plugin data after uninstall', // title
			array($this, 'lipnamo_setting_delete'), // callback
			'lipsum-dynamo-setting', // page
			'lipnamo_setting_section' // section
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
		$post_types = get_post_types(array('public' => true), 'objects');
		?>
        <fieldset>
            <label for="post_type">
                <select id="post_type" name='lipnamo_post_type'>
					<?php
					if($post_types){
						foreach($post_types as $slug => $post_type){
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
		$users = get_users(array('role__in' => array('administrator'), 'fields' => array('ID')));
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
							?>
                            <option value="<?php echo esc_attr($user_id); ?>"><?php echo esc_attr($name); ?></option>
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
	
	public function lipnamo_setting_delete_generated(){
		?>
        <fieldset>
            <label for="setting_delete_generated">
                <input type="checkbox" name="lipsum_dynamo[setting_delete_generated]" id="setting_delete_generated"
                       value="yes" <?php checked(esc_attr(lipnamo_get_option('setting_delete_generated')), 'yes'); ?> />
            </label>
            <p class="description"><?php echo __("This setting will delete all generated dummy items when uninstalling plugin. It can't be reverted, be careful to use.", "lipsum-dynamo"); ?></p>
        </fieldset>
		<?php
	}
	
	public function lipnamo_setting_delete(){
		?>
        <fieldset>
            <label for="setting_delete">
                <input type="checkbox" name="lipsum_dynamo[setting_delete]" id="setting_delete"
                       value="yes" <?php checked(esc_attr(lipnamo_get_option('setting_delete')), 'yes'); ?> />
            </label>
            <p class="description"><?php echo __("This setting will the plugin database when uninstalling plugin. It can't be reverted, be careful to use.", "lipsum-dynamo"); ?></p>
        </fieldset>
		<?php
	}
}

new Lipsum_Dynamo_Setting();