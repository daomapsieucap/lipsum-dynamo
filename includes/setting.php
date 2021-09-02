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

            <form class="fiber-admin" method="post" action="options.php">
				<?php
				do_settings_sections('lipsum-dynamo');
				?>
                <input name="lipnamo-generate__progress" type="hidden" value=""/>
                <a href="#" class="lipnamo-generate button button-primary"><?php echo __('Generate', 'lipnamo'); ?></a>
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
			'<span class="dashicons dashicons-admin-settings"></span> General',
			array($this, 'lipnamo_admin_section_info'),
			'lipsum-dynamo'
		);
		
		add_settings_field(
			'post_items', // id
			'Number of items', // title
			array($this, 'lipnamo_post_items'), // callback
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
	}
	
	public function lipnamo_admin_section_info(){
	}
	
	public function lipnamo_post_items(){
		?>
        <fieldset>
            <label for="post_items">
                <input name="lipnamo_post_items" type="number" id="post_items" min="1" max="50" value="10"/>
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
                            <option value="<?php echo $slug; ?>"><?php echo $post_type->label; ?></option>
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
                            <option value="<?php echo $user_id; ?>"><?php echo $name; ?></option>
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
                            <option value="<?php echo $slug; ?>" <?php echo $selected; ?>><?php echo $post_status; ?></option>
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
            <button class="button lipnamo-upload"><?php echo __('Add Thumbnails', 'lipnamo'); ?></button>
        </fieldset>
		<?php
	}
}

new Lipsum_Dynamo_Setting();