<?php
// Exit if accessed directly
if(!defined('ABSPATH')){
	exit;
}

/**
 * Cleanup Setting Page
 */
class Lipsum_Dynamo_Cleanup_Setting{
	
	public function __construct(){
		add_action('admin_menu', array($this, 'lipnamo_general_setting'));
		add_action('admin_init', array($this, 'lipnamo_general_page_init'));
	}
	
	public function lipnamo_general_setting(){
		add_submenu_page(
			'lipsum-dynamo',
			'Lipsum Dynamo Cleanup',
			'Cleanup Tool',
			'manage_options',
			'lipsum-dynamo-cleanup',
			array($this, 'lipnamo_admin_page')
		);
	}
	
	public function lipnamo_admin_page(){
		?>
        <div class="wrap">
            <h2>Cleanup generated items</h2>
			<?php settings_errors(); ?>

            <form class="lipnamo" method="post" action="options.php">
				<?php do_settings_sections('lipsum-dynamo-cleanup'); ?>
                <input name="lipnamo-generate__step" type="hidden" value="1"/>
                <div class="lipnamo-progress-wrapper" style="display:none;">
                    <div class="lipnamo-progress">
                        <div class="lipnamo-progress-bar" style="width:0"></div>
                    </div>
                    <div class="lipnamo-progress-text">
                        Processing <span class="lipnamo-progress-step">1</span> of <span
                                class="lipnamo-progress-total">10</span>
                    </div>
                </div>
                <a href="#"
                   class="lipnamo-cleanup button button-primary"><?php echo __('Cleanup', 'lipsum-dynamo'); ?></a>
            </form>
        </div>
		<?php
	}
	
	public function lipnamo_general_page_init(){
		register_setting(
			'lipnamo_group',
			'lipsum_dynamo-cleanup',
			array($this, 'sanitize_text_field')
		);
		
		add_settings_section(
			'lipnamo_cleanup_section',
			'',
			array($this, 'lipnamo_admin_section_info'),
			'lipsum-dynamo-cleanup'
		);
		
		add_settings_field(
			'post_type', // id
			'Select post type', // title
			array($this, 'lipnamo_post_type'), // callback
			'lipsum-dynamo-cleanup', // page
			'lipnamo_cleanup_section' // section
		);
	}
	
	public function lipnamo_admin_section_info(){
	}
	
	public function lipnamo_post_type(){
		$post_types = get_post_types(array('public' => true), 'objects');
		?>
        <fieldset>
            <label for="post_type">
                <select id="post_type" name='lipnamo_post_type'>
					<?php
					if($post_types){
						?>
                        <option value="any"><?php echo __('All', 'lipsum-dynamo'); ?></option>
						<?php
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
            <input type="hidden" name="lipnamo_post_total" value=""/>
        </fieldset>
		<?php
	}
}

new Lipsum_Dynamo_Cleanup_Setting();