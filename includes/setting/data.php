<?php
// Exit if accessed directly
if(!defined('ABSPATH')){
	exit;
}

/**
 * Data Setting Page
 */
class Lipsum_Dynamo_Data_Setting{
	
	public function __construct(){
	}
	
	public function lipnamo_data_page_init(){
		register_setting(
			'lipnamo_data_group',
			'lipsum-dynamo-uninstall',
			array($this, 'sanitize_text_field')
		);
		
		add_settings_section(
			'lipnamo_data_setting_section',
			'',
			array($this, 'lipnamo_admin_section_info'),
			'lipsum-dynamo-uninstall'
		);
		
		add_settings_field(
			'setting_delete_generated', // id
			'Delete all generated items', // title
			array($this, 'lipnamo_data_setting_delete_generated'), // callback
			'lipsum-dynamo-uninstall', // page
			'lipnamo_data_setting_section' // section
		);
	}
	
	public function lipnamo_admin_section_info(){
	}
	
	public function lipnamo_data_setting_delete_generated(){
		?>
        <fieldset>
            <label for="setting_delete_generated">
                <input type="checkbox" name="lipsum-dynamo[setting_delete_generated]" id="setting_delete_generated"
                       value="yes" <?php checked(esc_attr(lipnamo_get_option('setting_delete_generated')), 'yes'); ?> />
            </label>
        </fieldset>
		<?php
	}
}

new Lipsum_Dynamo_Data_Setting();