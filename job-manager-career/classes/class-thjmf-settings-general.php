<?php
/**
 * Job Manager Settings General
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 * @package    job-manager-career
 * @subpackage job-manager-career/classes
 */

defined( 'ABSPATH' ) || exit;

if(!class_exists('THJMF_Settings_General')):

	class THJMF_Settings_General {
		protected static $_instance = null;
		
		private $cell_props_L = array();
		private $cell_props_CB = array();
		private $field_props = array();
		private $field_props_display = array();
		private $settings_fields = array();
		private $swtich_array = array();
		private $cb_array = array();
		private $list_styling = array();
		private $load_styling = array();

		
		public function __construct() {
			$this->init_constants();
		}
		
		public static function instance() {
			if(is_null(self::$_instance)){
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		public function init_constants(){
			$this->cell_props_L = array( 
				'label_cell_props' => 'width="33%" style="padding:8px 0px 8px 15px;"', 
				'input_cell_props' => 'width="62%" style="padding:8px 0px 8px 15px;"',
				'input_width' => '160px',  
				'input_height' => '30px',
				'input_bod_rad' => '4px',
			);

			$this->cell_props_NOCELL_L = array(
				'input_margin'	=>	'0px 15px 0 0',
				'input_width' => '160px',  
				'input_height' => '30px',
				'input_bod_rad' => '4px',
			);
			
			$this->cell_props_XL = array( 
				'label_cell_props' => 'width="33%" style="padding:8px 0px 8px 15px;"', 
				'input_cell_props' => 'width="57%" style="padding:8px 0px 8px 15px;"',
				'input_width' => '160px',  
				'input_height' => '30px',
				'input_bod_rad' => '4px',
			);

			$this->cell_props_SP = array( 
				'label_cell_props' => 'width="33%" style="padding:8px 0px 8px 15px;"', 
				'input_cell_props' => 'width="62%" style="padding:8px 0px 8px 15px;"',
				'input_width' => '170px',
				'input_height' => '30px' 
			);
			
			$this->cell_props_CB = array( 
				'label_cell_props' => 'width="33%" style="padding:8px 0px 8px 15px;"', 
				'input_cell_props' => 'width="57%" style="padding:8px 0px 8px 15px;"',
				'input_margin' => '0 45px 0 0',  
			);

			$this->tab_calls = array('general'=>'general_tab_settings', 'data_management'=>'data_management_settings');
			$this->field_props = $this->get_field_form_props();
			$this->settings_fields = $this->get_settings_field_labels();
			$this->swtich_array = array('job_expiration', 'job_hide_expired','job_hide_filled', 'job_display_post_date', 'enable_apply_form', 'delete_data_uninstall');
			$this->cb_array = array('search_category', 'search_type', 'search_location');
			$this->def_jb_feature_fields = array('job_def_feature');
		} 

		public function is_posted_switch($sw_name){
			if(in_array($sw_name, $this->swtich_array)){
				return true;
			}
			return false;
		}

		public function is_posted_checkbox($cb_name){
			if(in_array($cb_name, $this->cb_array)){
				return true;
			}
			return false;
		}

		public function get_field_form_props(){
			return array(
				'def_feature_title' 	=> array('type'=>'seperator', 'title'=> __('DEFAULT JOB FEATURES','job-manager-career'), 'colspan' => '3'),
				'job_def_feature' 		=> array('type'=>'text', 'name'=>'job_def_feature[]', 'placeholder' => 'Feature' , 'post_require' => 'general', 'loop' => 1, 'value' => '', 'save_require' => 'job_detail'),
				'job_detail_settings' 	=> array('type'=>'seperator', 'title'=> __('SETTINGS','job-manager-career'), 'colspan' => '3'),
				'job_feature_add' 		=> array('type'=>'button', 'name'=>'job_feature_add', 'text'=> __('Add Feature','job-manager-career'), 'class' => 'button thjmf-button', 'onclick' => 'thjmAddFeatureJobDetail(this)'),
				'job_expiration'		=> array('type' => 'switch', 'name' => 'job_expiration', 'label' => __('Job Expiration', 'job-manager-career'), 'label_for'=>false, 'checked' => 1, 'post_require' => 'general', 'onchange' => 'thjmSwitchCbChangeListener(this)', 'save_require' => 'job_detail'),
				'job_hide_expired'		=> array('type' => 'switch', 'name' => 'job_hide_expired', 'label' => __('Hide Expired Jobs', 'job-manager-career'), 'label_for'=>false, 'checked' => 0, 'post_require' => 'general', 'onchange' => 'thjmSwitchCbChangeListener(this)', 'save_require' => 'job_detail'),
				'job_hide_filled'		=> array('type' => 'switch', 'name' => 'job_hide_filled', 'label' => __('Hide Filled in Job','job-manager-career'), 'label_for'=>false, 'checked' => 1, 'post_require' => 'general', 'onchange' => 'thjmSwitchCbChangeListener(this)', 'save_require' => 'job_detail'),
				'job_display_post_date'	=> array('type' => 'switch', 'name' => 'job_display_post_date', 'label' => __( 'Display Job Posted Date', 'job-manager-career'), 'label_for'=>false, 'checked' => 1, 'post_require' => 'general', 'onchange' => 'thjmSwitchCbChangeListener(this)', 'save_require' => 'job_detail'),
				'enable_apply_form'		=> array('type' => 'switch', 'name' => 'enable_apply_form', 'label' => __( 'Enable Apply Now & Form', 'job-manager-career'), 'label_for' => false, 'checked' => 1, 'post_require' => 'general', 'onchange' => 'thjmSwitchCbChangeListener(this)', 'save_require' => 'job_submission'),
				'apply_form_disabled_msg' => array('type'=>'text', 'name'=>'apply_form_disabled_msg', 'placeholder'=> __('Message Text', 'job-manager-career'), 'label' => 'Message Text', 'post_require' => 'general', 'save_require' => 'job_submission'),
				'data_management' 		=> array('type'=>'seperator', 'title'=>__( 'DATA MANAGEMENT', 'job-manager-career'), 'colspan' => '3'),
				'delete_data_uninstall' => array('type'=>'switch', 'name'=>'delete_data_uninstall', 'value'=>'', 'label' => 'Delete all data on plugin Uninstall', 'label_for' => false, 'onchange' => 'thjmSwitchCbChangeListener(this)', 'post_require' => 'data_management', 'save_require' => 'advanced', 'checked' => 0),
				
				'search_criteria' 		=> array('type'=>'seperator', 'title'=> __('SEARCH FILTERS', 'job-manager-career'), 'colspan' => '3'),
				'search_category' 		=> array('type'=>'checkbox', 'name'=>'search_category', 'label' => __('Category', 'job-manager-career'), 'label_for' => false, 'form_field' => true, 'post_require' => 'general', 'checked' =>0, 'onchange' => 'thjmSwitchCbChangeListener(this)', 'save_require' => 'search_and_filt'),
				'search_type' 			=> array('type'=>'checkbox', 'name'=>'search_type', 'label' => __('Job Type', 'job-manager-career'), 'label_for' => false, 'form_field' => true, 'post_require' => 'general', 'checked' =>0, 'onchange' => 'thjmSwitchCbChangeListener(this)', 'save_require' => 'search_and_filt'),
				'search_location' 		=> array('type'=>'checkbox', 'name'=>'search_location', 'label' => __('Location', 'job-manager-career'), 'label_for' => false, 'form_field' => true, 'post_require' => 'general', 'checked' =>0, 'onchange' => 'thjmSwitchCbChangeListener(this)', 'save_require' => 'search_and_filt'),
				'enable_user_form' 		=> array('type'=>'seperator', 'title'=>__('USER APPLICATION OPTIONS', 'job-manager-career'), 'colspan' => '3'),
				'def_user_form_fields' 	=> array('type'=>'seperator', 'title'=>__('DEFAULT FORM FIELDS', 'job-manager-career'), 'colspan' => '3'),
			);
		}

		private function get_settings_field_labels(){
			return array(
				'job_detail' => array(
					'job_def_feature', 
					'job_expiration',
					'job_hide_expired', 
					'job_hide_filled', 
					'job_display_post_date'
				),
				'job_submission' => array(
					'enable_apply_form', 
					'apply_form_disabled_msg'
				),
				'search_and_filt' => array(
					'search_category', 
					'search_type', 
					'search_location'
				),
				'advanced' => array(
					'delete_data_uninstall'
				),
				
			);
		}
			
		public function render_page(){
			$this->render_content();
		}
		
		public function reset_to_default($tab) {
			THJMF_Utils::reset_advanced_settings($tab, true);
			
			return '<div class="updated"><p>'. __('Product fields successfully reset', 'job-manager-career') .'</p></div>';
		}
		
		public function render_content(){
			$c_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

			
			if(isset($_POST['save_settings'])){
				$this->save_settings_form();
			}

			if(isset($_POST['reset_settings'])){
				$this->reset_to_default($c_tab);
			}
			?>
			<form method="post" id="thjmf_settings_fields_form" action="">
            	<?php
            	$this->render_settings_forms($c_tab);
				?>
            </form>
	    	<?php
	    }
			
		private function save_settings_form(){
			$settings = THJMF_Utils::get_default_settings();
			$tab_detail = $this->get_current_tab_slug();
			$def_ft = array();
			
			foreach ($this->field_props as $key => $value) {
				if( isset( $value['post_require']) == $tab_detail ){
					$new_key = $this->is_posted_switch($key) ? $key.'_hidden': $key;
					$f_name = 'i_'.$new_key;
					if( $this->is_posted_checkbox( $key ) && isset( $settings[$value['save_require']][$key] ) ){ //For checkboxes (Switches not included) 
						$settings[$value['save_require']][$key] = isset( $_POST[$f_name] ) ? true : false;

					}else if( isset( $_POST[$f_name] ) ){
						if( $key == 'job_def_feature' && isset( $settings[$value['save_require']]['job_feature'][$key] ) ){
							foreach ($_POST[$f_name] as $ft_key => $ft_value) {
								if(empty( $ft_value ) ){
									continue;
								}
								$ft_value = apply_filters( 'thjmf_field_name_enable_stripslash', true ) ? sanitize_text_field( stripslashes( $ft_value ) ) : sanitize_text_field( $ft_value );
								if( empty( $ft_value ) ){ //Sanitized value empty - feature given as script
									continue;
								}
								$def_ft[THJMF_Utils::format_field_name( $ft_value )] = $ft_value; 
							}
							$settings[$value['save_require']]['job_feature'][$key] = $def_ft;
						}else if( isset( $settings[$value['save_require']][$key] ) ){
							$type = isset( $value['type'] ) ? $value['type'] : 'text';
							$settings[$value['save_require']][$key] = $this->thjmf_sanitize_post_fields($type, $_POST[$f_name] );
						}
					}
				}
			}
			$new = empty($settings) ? true : false;
			$result = THJMF_Utils::save_default_settings($settings, $new);
			$this->display_save_settings_notification($result);
		}

		public function display_save_settings_notification($result){
			$msg = $result ? __('Your changes were saved.', 'job-manager-career') : __('Your changes were not saved due to an error (or you made none!).', 'job-manager-career');
			$class = $result ? "thjmf-save-success" : "thjmf-save-error";
			?>
			<div id="thjmf_save_settings_notification" class="thjmf-show-save <?php echo esc_attr( $class ); ?>"><?php echo esc_html( $msg ); ?></div>
			<script type="text/javascript">
					jQuery(function($) {
					    setTimeout(function(){
							$("#thjmf_save_settings_notification").remove();
						}, 2500);
					});
				</script>
			
			<?php
		}

		public function render_settings_forms($form_tab){
			?>
			<div class="thjmf-tab-settings-wrapper" id="thjmf-settings-tabmenu-wrapper">
				<?php
				$settings = THJMF_Utils::format_settings();
				$tab_detail = $this->get_current_tab_slug();
				foreach ($this->field_props as $key => $value) {
					if( isset($value['post_require']) && $tab_detail && $value['post_require'] === $tab_detail){
						if( isset($settings[$tab_detail][$key]) ){
							$this->field_props[$key]['value'] = $settings[$tab_detail][$key];
							if( $this->is_posted_switch( $key ) || $this->is_posted_checkbox( $key)){
								$this->field_props[$key]['checked'] = $settings[$tab_detail][$key] == true ? 1 : 0;
								if( $key == 'enable_apply_form' && $this->field_props[$key]['checked'] ){
									$this->field_props['apply_form_disabled_msg']['disabled'] = 'readonly';
								}	
							}

						}else if( in_array($key, $this->def_jb_feature_fields) ){
							$this->field_props[$key]['value'] =  isset( $settings[$tab_detail]['job_feature'][$key] ) && !empty( $settings[$tab_detail]['job_feature'][$key] ) ? $settings[$tab_detail]['job_feature'][$key] : "";
						}
					}
				}
				call_user_func( array( $this, $this->tab_calls[$form_tab] ) ); ?>
			</div>
			<?php
		}

		public function prepare_plugin_settings(){
			$settings_advaced = THJMF_Utils::get_default_settings();
			$plugin_settings = array(
				'OPTION_KEY_ADVANCED_SETTINGS' => $settings_advaced,
			);
			return base64_encode(serialize($plugin_settings));
		}

		private function render_shortcode_info(){
			?>
			<div class="thjmf-shortcode-info">
				<i> Use the shortcode <br>
					<input type="text" name="thjmf-shortcode-text" class="thjmf-shortcode-text" value="[<?php echo esc_attr( THJMF_Utils::$shortcode ); ?>]">
					<br>to list jobs on a page
				</i>
				<button type="button" class="button" onclick="thjmfCopyShortcodeEvent(this)" style="margin-top: 20px;">Copy Shortcode</button>
				<input type="hidden">
			</div>
			<?php
		}

		public function general_tab_settings(){
			$this->render_shortcode_info();
			?>
			<table id="general_settings_tab" class="thjmf-settings-table thjmf-admin-form-table">
				<tr>
					<td>
						<?php 
						$this->render_tab_section_jb_features_default();
						$this->render_tab_section_jb_feature_settings();
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php 
						$this->render_tab_section_user_application_form_enable();
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php $this->render_tab_section_search_criteria(); ?>
					</td>
				</tr>
				<tr>
					<td>
						<p class="submit">
						<input type="submit" name="save_settings" class="button-primary" value="Save changes">
	                    <input type="submit" name="reset_settings" class="button" value="Reset to default" onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
	            	</p>
					</td>
				</tr>
			</table>
			<?php
		}

		public function data_management_settings(){
			$this->import_export_settings();
		}

		
		private function render_tab_section_jb_features_default(){
			?>
			<table class="thjmf-tab-jb-features-default thjmf-settings-tab-form-table thjmf-settings-tb">
				<?php 
				$this->render_form_section_separator($this->field_props['def_feature_title'], '405px');
				$feature = isset( $this->field_props['job_def_feature']['value'] ) && !empty($this->field_props['job_def_feature']['value'])? $this->field_props['job_def_feature']['value'] : false;
				$ft_count = 0;
				if($feature){
					foreach ($feature as $key => $value) {
						$features = isset( $this->field_props['job_def_feature'] ) && !empty($this->field_props['job_def_feature'])? $this->field_props['job_def_feature'] : false;
						
					?>
						<tr>
							<td class="thjmf-cell-nolabel">
								<?php
								$features['value'] = isset( $features['value'][$key] ) ? $features['value'][$key] : "";
								$this->render_form_field_element($features, $this->cell_props_NOCELL_L, false, false);
								if($ft_count != 0){
									?>
									<span class="dashicons dashicons-trash thjmf-dashicon-delete" onclick="thjmRemoveCurrentDataRow(this)"></span>
									<?php
								}
								$ft_count++;
								?>
							</td>
						</tr>
					<?php 
					}
				}else{
					?>
					<tr>
						<td class="thjmf-cell-nolabel">
							<?php
							$this->render_form_field_element($this->field_props['job_def_feature'], $this->cell_props_NOCELL_L, false, false);
							?>
						</td>
					</tr>
				<?php 
				}
				?>
				<tr>
					<td class="thjmf-cell-nolabel">
						<?php
						$this->render_form_field_element($this->field_props['job_feature_add'],false, false);
						?>
					</td>
				</tr>
			</table>
			<?php
		}

		private function render_tab_section_jb_feature_settings(){
			?>
			<table class="thjmf-tab-jb-feature-settings thjmf-settings-tab-form-table thjmf-settings-tb">
				<?php 
				$this->render_form_section_separator($this->field_props['job_detail_settings'], '496px');
				?>
				<tr>
					<?php $this->render_form_field_element($this->field_props['job_expiration'],$this->cell_props_L); ?>
				</tr>
				<tr>
					<?php $this->render_form_field_element($this->field_props['job_hide_expired'], $this->cell_props_L); ?>
				</tr>
				<tr>
					<?php $this->render_form_field_element($this->field_props['job_hide_filled'],$this->cell_props_L); ?>
				</tr>
				<tr>
					<?php $this->render_form_field_element($this->field_props['job_display_post_date'],$this->cell_props_L); ?>
				</tr>
			</table>
			<?php
		}

		public function render_tab_section_user_application_form_enable(){
			?>
			<table class="thjmf-tab-jb-apply-form-enable thjmf-settings-tab-form-table thjmf-settings-tb">
				<?php 
				$this->render_form_section_separator($this->field_props['enable_user_form'], '369px');
				?>
				<tr>
					<?php $this->render_form_field_element($this->field_props['enable_apply_form'],$this->cell_props_SP, true, false); ?>
				</tr>
				<tr>
					<td colspan="3" style="padding: 8px 0px 8px 15px;color:#828282;">
						<p><i> <?php echo esc_html__('Apply Now form contains the default fields - Name, Phone, Email, Resume upload & Description', 'job-manager-career'); ?></i></p>
					</td>
				</tr>
				<tr class="thjmf-toggle-row">
					<?php
					$msg_props = $this->cell_props_SP;
					$msg_props['input_width'] = "300px";
					$this->render_form_field_element($this->field_props['apply_form_disabled_msg'],$msg_props, true, true); ?>
				</tr>
			</table>
			<?php
		}

		public function render_tab_section_search_form(){
			?>
			<table class="thjmf-tab-search-filter-form thjmf-settings-tab-form-table thjmf-settings-tb">
				<?php 
				$this->render_form_section_separator($this->field_props['search_form'], '469px');
				?>
				<tr>
					<?php
					$this->render_form_field_element($this->field_props['enable_search_on_listing'], $this->cell_props_XL);
					?>
				</tr>
			</table>
			<?php
		}

		public function render_tab_section_search_criteria(){
			?>
			<table class="thjmf-tab-search-filter-criteria thjmf-settings-tab-form-table thjmf-settings-tb">
				<?php 
				$this->render_form_section_separator($this->field_props['search_criteria'], '446px');
				?>
				<tr>
					<?php
					$this->render_form_field_element($this->field_props['search_category'], $this->cell_props_CB);
					?>
				</tr>
				<tr>
					<?php
					$this->render_form_field_element($this->field_props['search_type'], $this->cell_props_CB);
					?>
				</tr>
				<tr>
					<?php
					$this->render_form_field_element($this->field_props['search_location'], $this->cell_props_CB);
					?>
				</tr>
			</table>
			<?php
		}

		public function import_export_settings(){
			$plugin_settings = $this->prepare_plugin_settings();
			if(isset($_POST['save_plugin_settings'])){ 
				$result = $this->save_plugin_settings(); 
			}	
			$imp_exp_fields = array(
				'section_import_export' => array('title'=>__('Backup and Import Settings', 'job-manager-career'), 'type'=>'separator', 'colspan'=>'3'),
				'thjmf_settings_data' => array(
					'name'=>'thjmf_settings_data', 'label'=> __('Plugin Settings Data', 'job-manager-career'), 'type'=>'textarea',
					'sub_label'=> __('You can transfer the saved settings data between different installs by copying the text inside the text box. To import data from another install, replace the data in the text box with the one from another install and click "Save Settings".', 'job-manager-career'),
				),
			);

			$cell_props_textarea['label_cell_props'] = 'class="titledesc" scope="row" style="width: 20%; vertical-align:top; padding-right: 25px;text-align:justify;"';
			$cell_props_textarea['rows'] = 10;
			
			?>
			<div style="padding-left: 15px;">               
			    <form id="import_export_settings_form" method="post" action="" class="clear">
	                <table id="import_export_settings_tab" class="thjmf-admin-form-table">
	    				<tbody>
	    					<?php 
							foreach( $imp_exp_fields as $name => $field ) { 
								if($field['type'] === 'separator'){
									$this->render_form_section_separator($field, '548px');
									$this->render_field_form_fragment_h_spacing(10);
								}else { ?>
		                        <tr valign="top">
		                            <?php 
										$field['value'] = $plugin_settings;
										$this->render_form_field_element_advanced($field, $cell_props_textarea);
									?>
		                        </tr>
		                        <?php 
								}
							} 
							?>
	    				</tbody>
	                </table> 
	                <p class="submit">
						<input type="submit" name="save_plugin_settings" class="button-primary" value=" <?php echo esc_html__('Save Settings', 'job-manager-career'); ?>">
	            	</p>
	            </form>
	    	</div> 
			<?php 
		}

		public function save_plugin_settings(){		
			if(isset($_POST['i_thjmf_settings_data']) && !empty($_POST['i_thjmf_settings_data'])) {
				$settings_data_encoded = THJMF_Utils::sanitize_post_fields( $_POST['i_thjmf_settings_data'], 'textarea');   
				$settings = unserialize(base64_decode($settings_data_encoded)); 
				$result = $result1 = $result2 = false;
				if($settings){	
					foreach($settings as $key => $value){	
						if($key === 'OPTION_KEY_ADVANCED_SETTINGS'){ 
							$result1 = THJMF_Utils::save_default_settings($value);
						}				  
					}					
				}					
				if($result || $result1 || $result2){
					echo '<div class="updated"><p>'. esc_html__('Your Settings Updated.', 'job-manager-career') .'</p></div>';
					return true; 
				}else{
					echo '<div class="error"><p>'. esc_html__('Your changes were not saved due to an error (or you made none!).', 'job-manager-career') .'</p></div>';
					return false;
				}	 			
			}
		}

		public function get_current_tab_slug(){
			return isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : "general";
		}

		public function render_form_field_element_advanced($field, $atts=array(), $render_cell=true){
			if($field && is_array($field)){
				$ftype = isset($field['type']) ? $field['type'] : 'text';	
				if($ftype == 'checkbox'){
					$this->render_form_field_element_checkbox($field, $atts, $render_cell);
					return true;
				}
				$args = shortcode_atts( array(
					'label_cell_props' => '',
					'input_cell_props' => '',
					'label_cell_th' => false,
					'input_width' => '',
					'rows' => '5',
					'cols' => '100',
					'input_name_prefix' => 'i_'
				), $atts );
				$fname  = $args['input_name_prefix'].$field['name'];
				$flabel = __( $field['label'], 'job-manager-career' );
				$fvalue = isset($field['value']) ? $field['value'] : '';
				$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
				$field_props  = 'name="'. esc_attr( $fname ) .'" value="'. esc_attr( $fvalue ) .'" style="'. esc_attr( $input_width ) .'"';
				$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.esc_attr( $field['placeholder'] ).'"' : '';
				
				$required_html = ( isset($field['required']) && $field['required'] ) ? '<abbr class="required" title="required">*</abbr>' : '';
				$field_html = '';
				
				if($ftype == 'textarea'){
					$field_props  = 'name="'. esc_attr( $fname ) .'" style=""';
					$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'. esc_attr( $field['placeholder'] ).'"' : '';
					$field_html = '<textarea '. $field_props .' rows="'.esc_attr( $args['rows'] ).'" cols="'.esc_attr( $args['cols'] ).'" >'. esc_html( $fvalue ).'</textarea>';	
				}
				$label_cell_props = !empty($args['label_cell_props']) ? ' '.$args['label_cell_props'] : '';
				$input_cell_props = !empty($args['input_cell_props']) ? ' '.$args['input_cell_props'] : '';
				?>
				<td <?php echo $label_cell_props; ?> > 
					<?php 
					echo esc_html( $flabel );
					echo $required_html; 
					if(isset($field['sub_label']) && !empty($field['sub_label'])){ ?>
	                    <br /><span class="thpladmin-subtitle"><?php echo esc_html__($field['sub_label'], 'job-manager-career' ); ?></span>
						<?php
					}
					?>
	            </td>
	            <td <?php echo $input_cell_props; ?> ><?php echo $field_html; ?></td>
	            <?php
			}
		}

		public function render_form_field_element($field, $atts = array(), $render_cell = true, $display_label=true){
			if($field && is_array($field)){
				$args = shortcode_atts( array(
					'label_cell_props' => '',
					'input_cell_props' => '',
					'label_cell_colspan' => '',
					'input_cell_colspan' => '',
				), $atts );		
				$ftype     = isset($field['type']) ? $field['type'] : 'text';
				$flabel    = isset($field['label']) && !empty($field['label']) ? __( $field['label'], 'job-manager-career' ) : '';
				$sub_label = isset($field['sub_label']) && !empty($field['sub_label']) ? __( $field['sub_label'], 'job-manager-career' ) : '';
				$tooltip   = isset($field['hint_text']) && !empty($field['hint_text']) ? __( $field['hint_text'], 'job-manager-career' ) : '';
				$field_html = '';
				if($ftype == 'text'){
					$field_html = $this->render_form_field_element_inputtext($field, $atts);
					
				}else if($ftype == 'textarea'){
					$field_html = $this->render_form_field_element_textarea($field, $atts);
					   
				}else if($ftype == 'select'){
					$field_html = $this->render_form_field_element_select($field, $atts);     
					
				}else if($ftype == 'checkbox'){
					$field_html = $this->render_form_field_element_checkbox($field, $atts, $render_cell);   
					 
				}else if($ftype == 'button'){
					$field_html = $this->render_form_field_element_button($field, $atts);
					   
				}else if($ftype == 'switch'){
					$field_html = $this->render_form_field_element_switch($field, $atts);
					   
				}else if($ftype == 'hidden'){
					$field_html = $this->render_form_field_element_hidden($field, $atts);

				}
				
				if( isset( $field['disabled'] ) && ( $field['disabled'] == 'readonly' || $field['type'] == 'switch') ){
					$field_html = '<div class="thjmf-disabled-input-wrapper">'.$field_html.'</div>';
				}
				if($render_cell){
					$required_html = isset($field['required']) && $field['required'] ? '<abbr class="required" title="required">*</abbr>' : '';
					$label_cell_props = !empty($args['label_cell_props']) ? $args['label_cell_props'] : '';
					$input_cell_props = !empty($args['input_cell_props']) ? $args['input_cell_props'] : '';
					?>
					<td <?php echo $label_cell_props; ?> >
						<?php echo esc_html( $flabel ); 
						echo esc_html( $required_html );
						if($sub_label){
							?>
							<br/><span class="thpladmin-subtitle"><?php echo esc_html( $sub_label ); ?></span>
							<?php
						}
						if($tooltip){
							$this->render_form_fragment_tooltip($tooltip); 
						}
						?>
					</td>
					<td style="width: 5%; padding:0px;"></td>
					<td <?php echo $input_cell_props; ?> >
						<?php echo $field_html; ?>
						</td>
					<?php
				}
				else{
					echo $field_html;
				}
			}
		}
		
		private function prepare_form_field_props($field, $atts = array()){
			$field_props = '';
			$input_width ='';
			$input_height ='';
			$input_margin ='';
			$border_radius ='';
			$args = shortcode_atts( array(
				'input_width' => '',
				'input_height' => '',
				'input_bod_rad' => '4px',
				'input_margin' => '',
				'input_name_prefix' => 'i_',
				'input_name_suffix' => '',
			), $atts );
			
			$ftype = isset($field['type']) ? $field['type'] : 'text';
			if( isset( $field['class'] ) ){
				$field['class'] = isset($field['disabled']) && $field['disabled'] && isset($field['class']) ? ($field['class'].' thjmf-disabled-input') :  $field['class'];
			}
			if($ftype == 'hidden'){
				if( isset( $field['checked'] ) ){
					$field['value'] = $field['checked'] == 1 ? "true" : "false";
					$field['class'] = isset($field['hidden-class']) ? $field['hidden-class'] : "";
				}
			}else{
				$input_width  = $args['input_width'] ? 'width:'.esc_attr( $args['input_width'] ).';' : '';
				$input_height  = $args['input_height'] ? 'height:'.esc_attr( $args['input_height'] ).';' : '';
				$input_margin = $args['input_margin'] ? 'margin:'.esc_attr( $args['input_margin'] ).';' : '';
				$border_radius = $args['input_bod_rad'] ? 'border-radius:'.esc_attr( $args['input_bod_rad'] ).';' : '';
			}
			$fname  = $args['input_name_prefix'].$field['name'].$args['input_name_suffix'];
			$fvalue = isset($field['value']) ? $field['value'] : '';
			$field_props  = 'name="'. esc_attr( $fname ) .'" value="'. esc_attr( $fvalue ) .'" style="'. esc_attr( $input_width ) . esc_attr( $input_height ) . esc_attr( $input_margin ) . esc_attr( $border_radius ).'"';
			$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.esc_attr( $field['placeholder'] ).'"' : '';
			$field_props .= ( isset($field['onchange']) && !empty($field['onchange']) ) ? ' onchange="'.esc_attr( $field['onchange'] ).'"' : '';
			$field_props .= ( isset($field['onclick']) && !empty($field['onclick']) ) ? ' onclick="'.esc_attr( $field['onclick'] ).'"' : '';
			$field_props .= ( isset($field['class']) && !empty($field['class']) ) ? ' class="'.esc_attr( $field['class'] ).'"' : '';
			if(isset($field['disabled']) && $field['disabled']){
				$field_props .= ( $field['disabled'] == 'readonly' ) ? ' readonly="readonly"' : 'disabled';
			}
			return $field_props;
		}
		
		private function render_form_field_element_inputtext($field, $atts = array()){
			$field_html = '';
			if($field && is_array($field)){
				$auto_type = isset($field['auto-type']) ? $field['auto-type'] : $field['type'];
				$autocomplete = apply_filters('thjmf_disable_form_field_autocomplete',true);
				$autocomplete = $autocomplete ? "off" : "on" ;
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<input type="text" '. $field_props .' autocomplete="'.esc_attr( $autocomplete ).'"/>';
			}
			return $field_html;
		}

		private function render_form_field_element_hidden($field, $atts = array()){
			$field_html = '';
			if($field && is_array($field)){
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<input type="hidden" '. $field_props .' />';
			}
			return $field_html;
		}
		
		private function render_form_field_element_textarea($field, $atts = array()){
			$field_html = '';
			if($field && is_array($field)){
				$args = shortcode_atts( array(
					'rows' => '5',
					'cols' => '100',
				), $atts );
				$fvalue = isset($field['value']) ? $field['value'] : '';
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<textarea '. $field_props .' rows="'.esc_attr( $args['rows'] ).'" cols="'.esc_attr( $args['cols'] ).'" >'.esc_html( $fvalue ).'</textarea>';
			}
			return $field_html;
		}
		
		private function render_form_field_element_select($field, $atts = array()){
			$field_html = '';
			if($field && is_array($field)){
				$fvalue = isset($field['value']) ? $field['value'] : '';
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<select '. $field_props .' >';
				foreach($field['options'] as $value => $label){
					$selected = $value === $fvalue ? 'selected' : '';
					$field_html .= '<option value="'. esc_attr( trim($value) ) .'" '.$selected.'>'. esc_html__($label, 'job-manager-career') .'</option>';
				}
				$field_html .= '</select>';
			}
			return $field_html;
		}
		
		private function render_form_field_element_checkbox($field, $atts = array(), $name='', $render_cell = true){
			$field_html = '';
			if($field && is_array($field)){
				$args = shortcode_atts( array(
					'label_props' => '',
					'cell_props'  => 3,
					'render_input_cell' => false,
				), $atts );
		
				$fid 	= 'a_f'. $field['name'].'_'.$name;
				$flabel = isset($field['label']) && !empty($field['label']) ? esc_html__( $field['label'], 'job-manager-career' ) : '';
				$field_props  = $this->prepare_form_field_props($field, $atts);
				$field_props .= isset($field['checked']) && $field['checked'] ? ' checked' : '';
				$hidden_prefix = isset($field['loop']) ? '_hidden[]' : '_hidden';
				$field_html  = '<input type="checkbox" '.$field_props.' />';
				if(isset($field['label_for']) && $field['label_for']){
					$field_html .= '<label for="'. esc_attr( $fid ) .'" '. $args['label_props'] .' style="'.esc_attr( $args['label_props'] ).'"> '. esc_html( $flabel ) .'</label>';
				}
			}
			if(!$render_cell && $args['render_input_cell']){
				return '<td '. $args['cell_props'] .' >'. $field_html .'</td>';
			}else{
				return $field_html;
			}
		}

		private function render_form_field_element_button($field, $atts = array()){
			$field_html = '';
			$preview_class = '';
			if($field && is_array($field)){
				$button_text = isset($field['text']) && $field['text'] != '' ? $field['text'] : 'Upload';
				$field_props = $this->prepare_form_field_props($field, $atts);
				$field_html = '<button type="button" '. $field_props .'>'. esc_html($button_text) .'</button>';
				if( isset($field['upload_button']) && $field['upload_button'] ){
					$field_html = '<div class="thjmf-upload-preview-wrapper"><div class="thjmf-upload-button-wrapper">'.$field_html;
					if( isset( $field['value'] ) && !empty( $field['value'] ) ){
						$preview_class = isset( $field['value'] ) && !empty( $field['value'] ) ? 'thjmf-upload-visible' : "";
						$field_html .= '<button type="button" name="i_gif_remove_upload" onclick="thjmRemoveUploadedImage(this)" class="button thjmf-button thjmf-remove-upload" style="margin-left:10px;">Remove</button>';
					}
					$field_html .='<input type="hidden" name="i_'.esc_attr( $field['name'] ).'_hidden" value="'.esc_url($field['value']).'">';
					$field_html .= '</div>';
					$field_html .= '<div class="thjmf-upload-preview '.esc_attr( $preview_class ).'"><img src="'.esc_url($field['value']).'"></div>';
					$field_html .= '<div class="thjmf-upload-notices"></div>';
					$field_html .= '</div>';
				}
			}
			return $field_html;
		}
		
		private function render_form_field_element_switch($field, $atts = array()){
			$hidden_prefix = isset($field['loop']) ? '_hidden[]' : '_hidden';
			$field_html = '';
			if($field && is_array($field)){
				$sublabel = isset($field['sub-label']) ? $field['sub-label'] : ""; 
				$checkbox = $this->render_form_field_element_checkbox($field, $atts = array());
				$hidden_field = $field;
				$hidden_field['name'] = str_replace("[]", "", $hidden_field['name']).$hidden_prefix;
				$hidden_field['type'] = 'hidden';
				$class = isset( $field['class'] ) ? $field['class'] : "";
				$hidden_field['hidden-class'] = 'thjmf-switch-hidden';
				$hidden = $this->render_form_field_element_hidden($hidden_field, $atts = array());
				$field_html .= '<div class="thjmf-switch-wrapper '.esc_attr( $class ).'">'.$hidden;
				$field_html .= '<label class="thjmf-switch">'.$checkbox.' <span class="thjmf-slider thjmf-round"></span>';
				$field_html .= '<span class="thjmf-switch-sublabel">'.esc_html( $sublabel ).'</span></label></div>';
			}
			return $field_html;
		}
		
		public function render_form_fragment_tooltip($tooltip = false){
			if($tooltip){
				?>
					<a href="javascript:void(0)" title="<?php echo esc_html($tooltip); ?>" class="thpladmin_tooltip thjmf-label-tooltip"><span class="dashicons dashicons-info thjmf-dashicons-info"></span></a>
				<?php 
			}
		}

		public function render_field_form_fragment_h_spacing($padding = 5, $colspan=''){
			$style = $padding ? 'padding-top:'.$padding.'px;' : '';
			?>
	        <tr><td colspan="<?php echo esc_attr( $colspan );?>" style="<?php echo esc_attr( $style );?>"></td></tr>
	        <?php
		}
		
		public function render_form_section_separator($props, $hr_width){
			$tooltip   = isset($props['hint_text']) && !empty($props['hint_text']) ? __( $props['hint_text'], 'job-manager-career' ) : '';
			?>
			<tr valign="top">
				<td colspan="<?php echo esc_attr( $props['colspan'] ); ?>" style="height:10px;"></td>
			</tr>
			<tr valign="top">
				<td colspan="<?php echo esc_attr( $props['colspan'] ); ?>" class="thpladmin-form-section-title" >
					<?php echo esc_html( $props['title'] ); 
					if($tooltip){
					?>
						<a href="javascript:void(0)" title="<?php echo esc_html($tooltip); ?>" class="thpladmin_tooltip"><span class="dashicons dashicons-info thjmf-dashicons-info"></span></a>
					<?php } ?>	
					<hr class="thjmf-seperator-hr" style="width:<?php echo esc_attr( $hr_width ); ?>">
				</td>
			</tr>
			<tr valign="top">
				<td colspan="<?php echo esc_attr( $props['colspan'] ); ?>" class="thpladmin-form-section-title-border"></td>
			</tr>
			<?php
		}

		public function thjmf_sanitize_post_fields($type, $value){
			$cleaned = '';
			$value = stripslashes( $value );
			if($type){
				switch ($type) {
					case 'text':
					case 'select':
						$cleaned = sanitize_text_field($value); 
						break;
					case 'colorpicker':
						$cleaned = sanitize_hex_color($value);
						break;
					case 'number':
						$cleaned = is_numeric( trim( $value ) );
						$cleaned = $cleaned ? absint( trim( $value ) ) : "";
						break;
					case 'switch':
					case 'checkbox':
						$cleaned = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
						break;
					case 'button':
						$cleaned = esc_url($value);
						break;
					case 'email':
						$cleaned = is_email($value) ? $value : '';
						break;
					default:
						$cleaned = sanitize_text_field($value); 
						break;
				}
			}
			return $cleaned;
		}
	}

endif;
