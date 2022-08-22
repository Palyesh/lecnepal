<?php
/**
 * The file that defines the fields for post types of plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    job-manager-career
 * @subpackage job-manager-career/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THJMF_Post_Fields')):

	class THJMF_Post_Fields{
		private $cell_props_L = array();
		private $cell_props_R = array();
		private $cell_props_SL = array();
		private $cell_props_BTN = array();
		protected $post_data = array(); 
		private $posted_switch = array();
		public $tforms ='';

		public function __construct() {
		}
		
		public function initialize(){
			$this->init_constants();
		}

		public function init_constants(){
			$this->cell_props_L = array( 
				'label_cell_props' => 'style="width:13%;"', 
				'input_cell_props' => 'style="width:34%;"', 
				'input_width' => '150px', 
				'input_height' => '30px',
				'input_margin' => '0 20px 0 0',
				'input_bod_rad' => '4px',
			);

			$this->cell_props_SL = array( 
				'label_cell_props' => 'style="width:13%;"', 
				'input_cell_props' => 'style="width:34%;"', 
				'input_width' => '90px', 
				'input_height' => '30px',
				'input_margin' => '0 20px 0 0',
				'input_bod_rad' => '4px',
			);

			$this->cell_props_R = array( 
				'label_cell_props' => 'style="width:13%;"', 
				'input_cell_props' => 'style="width:34%;"', 
				// 'input_width' => '150px',
				'input_width' => '450px',
				'input_height' => '30px',
				'input_margin' => '0 20px 0 0', 
				'input_bod_rad' => '4px',
			);

			$this->cell_props_BTN = array( 
				'input_height' => '',
			);
			$this->tforms = THJMF_Settings_General::instance();
			$this->field_props = $this->get_field_form_props();
			$this->posted_switch = array('field_required');
		}

		public function load_post_meta_data(){
			global $post;
			$field_arr = [];
			$ft_details = [];
			$settings = false;
			$feature_atts = array('job_feature', 'job_feature_details');
			$this->post_data = THJMF_Utils::get_post_meta_datas($post->ID);
			$feature_keys = THJMF_Utils::get_job_feature_keys();
			if( is_array( $feature_keys ) && !empty( $feature_keys ) ){
				$field_arr['job_feature'] = $feature_keys;
				foreach ($feature_keys as $ft_key => $ft_value) {
					$ft_details[$ft_key] = THJMF_Utils::get_post_metas( $post->ID, $ft_key, true );
				}
				$field_arr['job_feature_details'] = $ft_details;
			}
			$date_expiry = THJMF_Utils::get_jpm_expired( $post->ID );
			$field_arr['job_expiry'] = !empty( $date_expiry ) ? THJMF_Utils::convert_date_wp( $date_expiry, true ) : $date_expiry;

			foreach ($field_arr as $key => $value) {
				$found_field = isset( $this->field_props[$key] ) ? $this->field_props[$key] : false;
				if($found_field){
					if( isset( $field_arr[$key] ) ){
						$this->field_props[$key]['value'] = $field_arr[$key];
					}
				}
			}
		}

		public function is_posted_switch($name){
			if(in_array($name, $this->posted_switch)){
				return true;
			}
			return false;
		}

		public function get_field_form_props(){
			$field_type = array('' => 'Select a type', 'text'=>'Text', 'select'=>'Select', 'radio' => 'Radio');
			return array(
				'job_features' 			=> array('content'=> __('Job Features', 'job-manager-career'), 'class'=>'thjmf-section-head'),
				'job_feature' 			=> array('type'=>'hidden', 'name'=>'job_feature[]', 'placeholder'=> 'Feature', 'value'=>'', 'post_require' => true, 'render' => false),
				'job_feature_details' 	=> array('type'=>'text', 'name'=>'job_feature_details[]', 'value'=>'', 'placeholder'=> __( 'Details', 'job-manager-career'), 'class'=>'job-data-inputs', 'post_require' => true),

				'apply_now_fields' 		=> array('content'=> __( 'Apply Now Form Fields', 'job-manager-career'), 'class'=>'thjmf-section-head'),
				'field_add_new' 		=> array('type'=>'button', 'text'=>__('Add New Field', 'job-manager-career'), 'name'=>'field_add_new', 'id'=>'add_new_field', 'class'=>'job-data-inputs button thjmf-button', 'onclick'=>'thjmAddNewField(this)'),
				'job_expiry_head' 		=> array('content'=> __('Job Expiry', 'job-manager-career'), 'class'=>'thjmf-section-head'),
				'job_expiry' 			=> array('type'=>'text', 'name'=>'job_expiry', 'placeholder'=> 'DD-MM-YYYY', 'value'=>'', 'class'=>'job-data-inputs thjmf-datepicker-field', 'auto-type' => 'datepicker', 'post_require' => true),
			);
			
		}

		public function render_job_feature_table(){
			$qualification_count = 1;
			?>
			<table id="job-feature" class="job-data-sections" style="width: 90%;">
				<thead>
					<?php
						$this->render_form_fragment_h_separator($this->field_props['job_features']);
					?>
				</thead>
				<tbody>
					<?php
					$feature = isset( $this->field_props['job_feature']['value'] ) && !empty($this->field_props['job_feature']['value']) ? $this->field_props['job_feature']['value'] : false;
					if($feature){
						foreach ($feature as $key => $value) {
							$job_feature = $this->field_props['job_feature'];
							$job_feature['value'] = isset( $job_feature['value'][$key] ) ? $job_feature['value'][$key] : "";
							$job_feature_details = $this->field_props['job_feature_details'];
							$job_feature_details['value'] = isset( $job_feature_details['value'][$key] ) ? $job_feature_details['value'][$key] : "";
							?>
							<tr class="job-feature-fields">
								<td style="max-width: 15%;">
									<label><?php echo esc_html( $value ); ?></label>
									<?php
									$this->tforms->render_form_field_element($job_feature, $this->cell_props_R, false);
									?>
								</td>
								<td style="max-width: 85%;">
								<?php
									$this->tforms->render_form_field_element($job_feature_details, $this->cell_props_R, false);
								?>
								</td>
							</tr>
						<?php
						}
					}else{ 
						echo '<tr><td>'.$this->get_settings_url().'</td></tr>';
					}
					?>
				</tbody>
			</table>
			<?php
		}

		private function get_settings_url(){
			$url = '<a href="'.esc_url( THJMF_Utils::get_admin_url('general') ).'">'.esc_html__('Create','job-manager-career').'</a>';
			$message = ' fields from plugin settings submenu to add job features';
			return '<i>'.$url.$message.'</i>';
		} 


		public function render_form_fragment_h_separator($atts = array()){
			$args = shortcode_atts( array(
				'colspan' 	   	=> 6,
				'padding-top'  	=> '10px',
				'border-style' 	=> 'dashed',
	    		'border-width' 	=> '1px',
				'border-color' 	=> '#e6e6e6',
				'content'	   	=> '',
				'class'			=> '',
			), $atts );
			
			$style  = isset( $args['padding-top'] ) ? 'padding-top:'.$args['padding-top'].';' : '';
			$style .= isset( $args['border-style'] ) ? ' border-bottom:'.$args['border-width'].' '.$args['border-style'].' '.$args['border-color'].';' : '';
			
			?>
	        <tr>
	        	<td colspan="<?php echo esc_attr( $args['colspan'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $args['content'] ); ?>
	        	</td>
	        </tr>
	        <?php
		}

		public function render_job_expiry(){
			?>
			<table id="job-expiry-table" class="job-data-sections">
				<thead>
					<tr>
						<?php
						$this->render_form_fragment_h_separator($this->field_props['job_expiry_head']);
					?>
					</tr>
				</thead>
				<tbody>
					<td>
						<?php
						$this->tforms->render_form_field_element($this->field_props['job_expiry'], $this->cell_props_L,false);
						?>
					</td>

				</tbody>

			</table>
			<?php
		}
	}

endif;

new THJMF_Post_Fields();