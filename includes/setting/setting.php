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
		add_action('admin_menu', [$this, 'lipnamo_setting']);
		add_action('admin_init', [$this, 'lipnamo_setting_init']);
		
		add_action("admin_enqueue_scripts", [$this, 'lipnamo_assets']);
	}
	
	public function lipnamo_assets(){
		$screen = get_current_screen();
		if(strpos($screen->id, 'lipsum-dynamo') !== false){
			wp_enqueue_style('lipnamo-admin', LIPNAMO_ASSETS_URL . 'css/lipnamo-admin.css', false, DUMMIE_VERSION);
			
			// Upload field
			wp_enqueue_media();
			
			// Plugin scripts
			wp_enqueue_script('lipnamo-admin', LIPNAMO_ASSETS_URL . 'js/lipnamo-admin.js', ['jquery'], DUMMIE_VERSION);
		}
	}
	
	public function lipnamo_setting_init(){
		if(isset($_POST['lipsum-dynamo-submit'])){
			check_admin_referer("lipsum-dynamo");
			$this->lipnamo_save_options();
			$updated_parameters = 'updated=true';
			if(isset($_GET['tab'])){
				$updated_parameters = 'updated=true&tab=' . $_GET['tab'];
			}
			wp_redirect(admin_url('tools.php?page=lipsum-dynamo&' . $updated_parameters));
			exit;
		}
	}
	
	public function lipnamo_setting(){
		add_submenu_page(
			'tools.php',
			'Dummie',
			'Dummie',
			'manage_options',
			'lipsum-dynamo',
			[$this, 'lipnamo_setting_html'],
		);
	}
	
	public function lipnamo_setting_html(){
		// check user capabilities
		if(!current_user_can('manage_options')){
			return;
		}
		
		$tab         = esc_attr(lipnamo_array_key_exists('tab', $_GET));
		$form_action = $tab ? admin_url("tools.php?page=lipsum-dynamo&tab=" . $tab) : admin_url("tools.php?page=lipsum-dynamo");
		
		echo '<div class="wrap">';
		
		echo '<h1>Dummie</h1>';
		
		// nav
		echo '<nav class="nav-tab-wrapper">';
		if($tab){
			$this->lipnamo_setting_tab_navs($tab);
		}else{
			$this->lipnamo_setting_tab_navs();
		}
		echo '</nav>';
		
		// content
		echo '<div class="tab-content">';
		
		echo '<form class="lipsum-dynamo" method="POST" action="' . $form_action . '">';
		
		wp_nonce_field("lipsum-dynamo");
		
		$current_tab = lipnamo_array_key_exists('tab', $_GET) ? : 'general';
		if($current_tab == 'uninstall'){
			echo '<p class="description">' . __("When you uninstall this plugin, what do you want to do with your settings and the generated dummy items? Be careful to use this option. It can't be reverted.", "lipsum-dynamo") . '</p>';
		}
		
		$this->lipnamo_setting_tab_content($current_tab);
		
		echo '</form>';
		echo '</div>';
		
		echo '</div>'; // wrap
	}
	
	public function lipnamo_setting_tabs(): array{
		$tabs = [
			'general'   => 'General',
			'cleanup'   => 'Cleanup',
			'uninstall' => 'Uninstall',
		];
		
		return $tabs;
	}
	
	public function lipnamo_setting_tab_navs($current = 'general'){
		$tabs = $this->lipnamo_setting_tabs();
		foreach($tabs as $tab => $name){
			$class = ($tab == $current) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=lipsum-dynamo&tab=$tab' title='$name'>$name</a>";
			
		}
	}
	
	public function lipnamo_setting_tab_content($current = 'general'){
		switch($current){
			case 'cleanup':
				$cleanup = new Lipsum_Dynamo_Cleanup_Setting();
				$cleanup->lipnamo_cleanup_page_init();
				break;
			case 'uninstall':
				$data = new Lipsum_Dynamo_Data_Setting();
				$data->lipnamo_data_page_init();
				break;
			default:
				$general = new Lipsum_Dynamo_General_Setting();
				$general->lipnamo_general_page_init();
				break;
		}
		
		do_settings_sections('lipsum-dynamo-' . $current);
		
		if($current == 'general' || $current == 'cleanup'){
			$btn = $current == 'general' ? 'generate' : $current;
			?>
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
               class="lipnamo-<?php echo $btn; ?> button button-primary"><?php echo ucfirst($btn); ?></a>
			<?php
		}
		
		if($current == 'cleanup'){
			?>
            <input type="hidden" name="lipnamo_post_total" value=""/>
			<?php
		}elseif($current == 'uninstall'){
			submit_button(null, 'primary', 'lipsum-dynamo-submit');
		}
	}
	
	public function lipnamo_save_options(){
		global $pagenow;
		if($pagenow == 'tools.php' && esc_attr(lipnamo_array_key_exists('page', $_GET)) == 'lipsum-dynamo'){
			$option_key = 'lipsum-dynamo';
			if(isset($_POST[$option_key])){
				$options = $new_options = $_POST[$option_key];
				foreach($options as $key => $value){
					$new_options[$key] = sanitize_text_field($value);
				}
			}else{
				$new_options = [];
			}
			
			update_option($option_key, $new_options);
		}
	}
}

new Lipsum_Dynamo_Setting();