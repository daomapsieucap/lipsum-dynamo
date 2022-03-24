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
	}
	
	public function lipnamo_cleanup_page_init(){
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
							// skip attachment post type
							if($slug == 'attachment'){
								continue;
							}
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
}

new Lipsum_Dynamo_Cleanup_Setting();