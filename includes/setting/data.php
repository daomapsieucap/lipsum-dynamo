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
		add_action('admin_menu', array($this, 'lipnamo_data_setting'));
		add_action('admin_init', array($this, 'lipnamo_data_page_init'));
	}
	
	public function lipnamo_data_setting(){
		add_submenu_page(
			'lipsum-dynamo',
			'Lipsum Dynamo Data',
			'Data Settings',
			'manage_options',
			'lipsum-dynamo-data',
			array($this, 'lipnamo_admin_page')
		);
	}
	
	public function lipnamo_admin_page(){
		?>
        <div class="wrap">
            <h2>Uninstall Data</h2>
            <p class="description"><?php echo __("When you uninstall this plugin, what do you want to do with your settings and the generated dummy items? Be carefull to use these options below. It can't be reverted.", "lipsum-dynamo"); ?></p>
			
			<?php settings_errors(); ?>

            <form class="lipnamo" method="post" action="<?php echo get_admin_url() . 'options.php'; ?>">
				<?php
				settings_fields('lipnamo_data_group');
				do_settings_sections('lipsum-dynamo-data');
				submit_button();
				?>
            </form>
        </div>
		<?php
	}
	
	public function lipnamo_data_page_init(){
		register_setting(
			'lipnamo_data_group',
			'lipsum_dynamo_data',
			array($this, 'sanitize_text_field')
		);
		
		add_settings_section(
			'lipnamo_data_setting_section',
			'',
			array($this, 'lipnamo_admin_section_info'),
			'lipsum-dynamo-data'
		);
		
		add_settings_field(
			'setting_delete_generated', // id
			'Delete all generated items', // title
			array($this, 'lipnamo_data_setting_delete_generated'), // callback
			'lipsum-dynamo-data', // page
			'lipnamo_data_setting_section' // section
		);
		
		add_settings_field(
			'setting_delete_data', // id
			'Cleanup plugin data', // title
			array($this, 'lipnamo_data_setting_delete'), // callback
			'lipsum-dynamo-data', // page
			'lipnamo_data_setting_section' // section
		);
	}
	
	public function lipnamo_admin_section_info(){
	}
	
	public function lipnamo_data_setting_delete_generated(){
		?>
        <fieldset>
            <label for="setting_delete_generated">
                <input type="checkbox" name="lipsum_dynamo[setting_delete_generated]" id="setting_delete_generated"
                       value="yes" <?php checked(esc_attr(lipnamo_get_option('setting_delete_generated')), 'yes'); ?> />
            </label>
        </fieldset>
		<?php
	}
	
	public function lipnamo_data_setting_delete(){
		?>
        <fieldset>
            <label for="setting_delete">
                <input type="checkbox" name="lipsum_dynamo[setting_delete]" id="setting_delete"
                       value="yes" <?php checked(esc_attr(lipnamo_get_option('setting_delete')), 'yes'); ?> />
            </label>
            <p class="description"><?php echo __("This setting will the plugin database. It will keep your generated dummy data.", "lipsum-dynamo"); ?></p>
        </fieldset>
		<?php
	}
}

new Lipsum_Dynamo_Data_Setting();