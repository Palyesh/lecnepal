<?php
/**
 * Utils functions for plugin
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    job-manager-career
 * @subpackage job-manager-career/classes
 */

defined( 'ABSPATH' ) || exit;
if(!defined('WPINC')){	die; }

if(!class_exists('THJMF_Utils')):

	class THJMF_Utils {
		const THJMF_CPT_JOB 	= 'thjm_jobs';
		const THJMF_CPT_APPLICANTS 	= 'thjm_applicants';
		const OPTION_KEY_THJMF_ADVANCED_SETTINGS 	= 'thjmf_advanced_settings';
		const OPTION_KEY_THJMF_VERSION = 'thjmf_db_version';
		const THJMF_JPM_FEATURED = 'featured_job';
		const THJMF_JPM_FILLED = 'filled_job';
		const THJMF_JPM_EXPIRED = 'expired_job';
		const THJMF_APPLICATION_STATUS 	= 'application_status';
		const THJMF_JOB_ID 	= 'job_id';
		const THJMF_PM_ADDITIONAL_NOTE = 'additional_note';
		const OPTION_KEY_THJM_JOB_UPDATION = 'thjm_jobs_updation';
		const OPTION_KEY_THJM_APPL_UPDATION = 'thjm_applicants_updation';
		const OPTION_KEY_THJMF_DB_UPDATED = 'thjmf_110_db_updated';

		//old & not used
		const THJMF_CPT_MAP 	= '_thjm_applicant_job_relation';
		const THJMF_APPLICANT_POST_SETTINGS 	= '_thjm_applicant_data';
		const THJMF_APPLICANT_STATUS 	= '_thjm_applicant_status';
		const THJMF_POST_CUSTOM_SETTINGS 	= '_thjm_post_custom_settings';
		const THJMF_PM_FEATURED = '_thjm_job_featured';
		const THJMF_PM_FILLED = '_thjm_job_filled';
		const THJMF_PM_EXPIRED = '_thjm_job_expired';

		public static $tax = array('location' => 'thjm_job_locations', 'job_type' => 'thjm_job_type', 'category' => 'thjm_job_category');
		public static $shortcode = 'THJM_JOBS';

		public static $appl_fields = array(
				'name' 			=> 'Name',
				'phone' 		=> 'Phone',
				'email'			=> 'Email',
				'cover_letter' 	=> 'Cover Letter',
			);

		public static $appl_meta = array(
			'location' => 'Location', 'category' => 'Category', 'job_type' => 'Job Type'
		);

		// OLD Keys - START

		public static function get_applicant_status_key(){
			return self::THJMF_APPLICANT_STATUS;
		}

		public static function get_cpt_key(){
			return self::THJMF_CPT_MAP;
		}

		public static function get_applicant_pm_key(){
			return self::THJMF_APPLICANT_POST_SETTINGS;
		}

		// OLD Keys - END

		public static function get_posttypes(){
			return array( self::THJMF_CPT_JOB, self::THJMF_CPT_APPLICANTS);
		}
		
		public static function get_all_taxonomies(){
			return self::$tax;
		}

		public static function get_job_cpt(){
			return self::THJMF_CPT_JOB;
		}

		public static function get_applicant_cpt(){
			return self::THJMF_CPT_APPLICANTS;
		}

		public static function get_filled_meta_key(){
			return self::THJMF_JPM_FILLED;
		}

		public static function get_expired_meta_key(){
			return self::THJMF_JPM_EXPIRED;
		}

		public static function get_db_version_key(){
			return self::OPTION_KEY_THJMF_VERSION;
		}

		public static function get_settings_key(){
			return self::OPTION_KEY_THJMF_ADVANCED_SETTINGS;
		}

		public static function get_cpt_map_job_key(){
			return self::THJMF_JOB_ID;
		}

		public static function get_application_status_key(){
			return self::THJMF_APPLICATION_STATUS;
		}

		public static function get_pm_expiry(){
			return self::THJMF_JPM_EXPIRED;
		}

		public static function get_apm_additional_note(){
			return self::THJMF_PM_ADDITIONAL_NOTE;
		}

		public static function get_jpm_featured($post_id, $text=false){
			$featured = self::get_post_metas($post_id, self::THJMF_JPM_FEATURED, true);
			if($text){
				$featured = filter_var( $featured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
				$featured =  $featured ? '<span class="dashicons dashicons-yes"></span>' : '--';
			}
			return $featured;
		}

		public static function get_jpm_filled($post_id, $text=false){
			$filled = self::get_post_metas($post_id, self::THJMF_JPM_FILLED, true);
			if($text){
				$filled = filter_var( $filled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
				$filled =  $filled ? '<span class="dashicons dashicons-yes"></span>' : '--';
			}
			return $filled;
		}

		public static function get_jpm_expired($post_id, $text=false){
			$expiry = self::get_post_metas($post_id, self::THJMF_JPM_EXPIRED, true);
			if( $text ){
				$expiry = !empty( $expiry ) ? self::is_post_expired($expiry) : false;
				$expiry = $expiry ? __('Expired', 'job-manager-career') : __('Active', 'job-manager-career');
				$expiry = '<span class="thjmf-listing-status status-'.esc_attr( strtolower ($expiry) ).'">'. $expiry.'</span>';
			}
			return $expiry;
		}

		public static function get_job_updation(){
			return get_option( self::OPTION_KEY_THJM_JOB_UPDATION );
		}

		public static function get_applicants_updation(){
			return get_option( self::OPTION_KEY_THJM_APPL_UPDATION );
		}

		public static function database_updated(){
			return get_option( self::OPTION_KEY_THJMF_DB_UPDATED );
		}

		public static function set_database_updated( $value ){
			return update_option( self::OPTION_KEY_THJMF_DB_UPDATED, $value );
		}

		public static function set_job_updation( $value ){
			return update_option( self::OPTION_KEY_THJM_JOB_UPDATION, $value );
		}

		public static function set_applicants_updation( $value ){
			return update_option( self::OPTION_KEY_THJM_APPL_UPDATION, $value );
		}

		public static function get_database_version(){
			return get_option( self::get_db_version_key());
		}

		public static function set_database_version( $value ){
			delete_option( self::get_db_version_key() );
			return add_option( self::get_db_version_key(), $value );
		}

		public static function is_applicant_post($type){
			if( self::THJMF_CPT_APPLICANTS == sanitize_key($type) ){
				return true;
			}
			return false;
		}

		public static function is_jobs_post($type){
			if( self::THJMF_CPT_JOB == sanitize_key($type) ){
				return true;
			}
			return false;
		}

		public static function format_field_name($name){
			return sanitize_text_field( strtolower( str_replace( ' ', '_', trim( $name ) ) ) );
		}

		public static function get_formated_label($name){
			$formatted = '';
			if($name){
				$formatted = ucfirst( str_replace('_', ' ', strip_tags( $name ) ) );
			}
			return $formatted;
		}

		public static function sanitize_post_fields($value, $type='text'){
			$cleaned = '';
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
						$cleaned = is_email($value);
						break;
					case 'textarea':
						$cleaned = sanitize_textarea_field($value);
					default:
						$cleaned = sanitize_text_field($value); 
						break;
				}
			}
			return $cleaned;
		}

		public static function get_default_settings($tab=false){
			$settings = get_option(self::OPTION_KEY_THJMF_ADVANCED_SETTINGS);
			if( empty( $settings ) ){
				$settings = self::default_settings();
			}
			if($tab){
				$settings = isset($settings[$tab]) ? $settings[$tab] : "";
			}
			
			return empty($settings) ? array() : $settings;
		}

		public static function plugin_db_settings($tab=false){
			$settings = get_option(self::OPTION_KEY_THJMF_ADVANCED_SETTINGS);	
			return empty($settings) ? array() : $settings;
		}

		public static function get_job_feature_keys(){
			$settings = self::get_default_settings('job_detail');
			if( $settings && !empty($settings) && is_array($settings) ){
				$settings = isset($settings['job_feature']['job_def_feature']) ? $settings['job_feature']['job_def_feature'] : "";
			}
			return empty($settings) ? array() : $settings;
		}

		public static function should_enqueue($post){
			if( ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, self::$shortcode) ) || get_post_type() == 'thjm_jobs') {
				return true;
			}
			return false;
		}
		
		public static function get_setting_value($settings, $key){
			if(is_array($settings) && isset($settings[$key])){
				return $settings[$key];
			}
			return '';
		}
		
		public static function get_settings($key){
			$settings = self::get_default_settings();
			if(is_array($settings) && isset($settings[$key])){
				return $settings[$key];
			}
			return '';
		}

		public static function get_taxonamy_term_string($post_id, $id){
			$string = '';
			$terms = get_the_terms($post_id, $id);
		
			if(is_array($terms)){
				$count = count($terms);

				foreach ($terms as $key => $value) {
					$suffix = $count == 1 ? '' : ', '; 
					$string .= $value->name.$suffix;
					$count--;
				}
			}else{
				$string = '-';
			}
			
			return $string;
		}

		public static function is_user_capable(){
			$capable = false;
			$user = wp_get_current_user();
			$allowed_roles = apply_filters('thjmf_override_user_capabilities', array('editor', 'administrator') );
			if( array_intersect($allowed_roles, $user->roles ) ) {
	   			$capable = true;
	   		}
	   		return $capable;
		}
		
		public static function load_user_roles(){
			$user_roles = array();
			
			global $wp_roles;
	    	$roles = $wp_roles->roles;
			foreach($roles as $key => $role){
				$user_roles[] = array("id" => $key, "title" => $role['name']);
			}		
			
			return $user_roles;
		}

		public static function save_default_settings($settings, $new=false){
			$result = false;
			if($new){
				$result = add_option(self::OPTION_KEY_THJMF_ADVANCED_SETTINGS, $settings);
			}else{
				$result = update_option(self::OPTION_KEY_THJMF_ADVANCED_SETTINGS, $settings);
			}
			return $result;
		}

		public static function save_post_meta_datas($post_id, $featured, $filled, $expired){
			$save1 = $save2 = $save3 = false;
			$save1 = self::save_post_metas($post_id, self::THJMF_JPM_FEATURED, $featured);
			$save2 = self::save_post_metas($post_id, self::THJMF_JPM_FILLED, $filled);
			$save3 = self::save_post_metas($post_id, self::THJMF_JPM_EXPIRED, $expired);
			return $save1 & $save2 & $save3;
		}

		public static function save_post_metas( $id, $m_key, $m_val){
			if( !metadata_exists('post', $id, $m_key) ){
				$save = add_post_meta($id, $m_key, $m_val);
			}else{
				$save = update_post_meta( $id, $m_key, $m_val );
			}
			return $save;
		}

		public static function get_post_meta_datas($post_id){
			$settings = [];
			$settings['job_featured'] = self::get_post_metas( $post_id, self::THJMF_JPM_FEATURED, true);
			$settings['job_filled'] = self::get_post_metas( $post_id, self::THJMF_JPM_FILLED, true);
			$settings['job_expiry'] = self::get_post_metas( $post_id, self::THJMF_JPM_EXPIRED, true);
			return $settings;
		}

		public static function get_post_metas($post_id, $key, $single=false){
			$settings = false;
			if( get_post_meta( $post_id, $key) ){
				$settings = get_post_meta( $post_id, $key, $single);
			}
			return $settings;
		}

		public static function get_applicant_post_meta_status($post_id){
			$settings = '';
			if( get_post_meta( $post_id, self::THJMF_APPLICATION_STATUS) ){
				$settings = get_post_meta( $post_id, self::THJMF_APPLICATION_STATUS, true);
			}
			return $settings;
		}

		public static function delete_post_meta_datas($post_id){
			$settings = '';
			if( get_post_meta( $post_id, self::THJMF_POST_META_SETTINGS) ){
				$settings = delete_post_meta( $post_id, self::THJMF_POST_META_SETTINGS);
			}
			return $settings;
		}

		public static function reset_advanced_settings( $key, $all=false){
			$settings = false;
			$all = apply_filters('thjmf_clear_plugin_settings', $all);
			if($all){
				$settings = delete_option(self::OPTION_KEY_THJMF_ADVANCED_SETTINGS);
			}else{
				$settings = self::get_default_settings();
				if( isset( $settings[$key] ) ){
					$new_settings = self::default_settings();
					$settings[$key] = isset( $new_settings[$key] ) ? $new_settings[$key] : ""; 
				}
				$settings = self::save_default_settings($settings);
			}
			return $settings;
		}

		public static function get_comma_seperated_taxonamy_terms($id, $name){
			$tags = '';
			$name = isset( self::$tax[$name] ) ? self::$tax[$name] : "";
			$terms = wp_get_post_terms( $id, $name );
			if(is_array($terms) && !empty($terms) && !is_wp_error($terms)){
				$tags = implode(', ', wp_list_pluck($terms, 'name') );
			}
			return $tags;
		}

		public static function get_all_post_terms($tag){
			$terms = '';
			switch($tag){
				case "location":
					$terms = self::get_specific_taxonamy_terms('thjm_job_locations');
					break;
				case "job_type":
					$terms = self::get_specific_taxonamy_terms('thjm_job_type');
					break;
				case "category":
					$terms = self::get_specific_taxonamy_terms('thjm_job_category');
					break;
				default: 
					$terms = "";
			}
			return $terms;
		}

		public static function get_specific_taxonamy_terms($tax){
			$terms = '';
			$args = array(
			    'taxonomy' => sanitize_key( $tax ),
			    'hide_empty' => false,
			);
			if($tax){
				$terms = get_terms( $args );
			}
			return $terms;
		}

		public static function get_logged_user_email(){
			$logged_email = '';
		   	$current_user = wp_get_current_user();
			if( $current_user !== 0 ){
				$logged_email =  $current_user->user_email;
			}
			$admin_email = get_option('admin_email');
			return $admin_email;
		}

		public static function get_template_directory(){
		    $upload_dir = wp_upload_dir();
		    $dir = $upload_dir['basedir'].'/thjmf_uploads';
	      	$dir = trailingslashit($dir);
	      	return $dir;
		}

		public static function default_settings(){
			$settings = array(
				'job_detail'	=>	array(
					'job_feature'	=> array(
						'job_def_feature' => array(),
					),
	      			'job_expiration' => true,
	      			'job_hide_expired' => false,
	      			'job_hide_filled' => false,
	      			'job_display_post_date' => true,
				),
				'job_submission'	=>	array(
					'enable_apply_form'	=> true,
					'apply_form_disabled_msg' => 'Mail your resume to '.self::get_logged_user_email(),
				),
				'search_and_filt'		=> array(
					'search_category' => true,
					'search_type' => true,
					'search_location' => true,
				),
				'advanced'	=> array(
					'delete_data_uninstall' => false,
				),
			);
			return $settings;
		}

		public static function format_settings(){
			$settings = self::get_default_settings();
			$new_settings = [];
			$jb_det = isset( $settings['job_detail'] ) ? $settings['job_detail'] : false;
			$features = $jb_det['job_feature'];
			unset($jb_det['job_feature']);
			$jb_det = array_merge($jb_det, $features);
			$jb_sub = isset( $settings['job_submission'] ) ? $settings['job_submission'] : false;
			$jb_filt = isset( $settings['search_and_filt'] ) ? $settings['search_and_filt'] : false;
			$general = '';
			if( $jb_det && $jb_sub && $jb_filt){
				$general  = array_merge( $settings['job_detail'], $settings['job_submission'], $settings['search_and_filt'] );
			}
			$new_settings = array('general' => $general, 'data_management' => isset( $settings['advanced'] ) ? $settings['advanced'] : "");
			
			return $new_settings;
		}

		public static function get_job_post_titles(){
			$articles = get_posts(
				array(
				  'numberposts' => -1,
				  'post_status' => 'publish',
				  'post_type' => 'thjm_jobs',
				)
			);
			return wp_list_pluck( $articles, 'post_title', 'ID' );
		}

		public static function is_post_expired($date){
			if( strtotime( date('Y-m-d') ) <= strtotime($date) ){
	    		return false; 
			}
			return true;
		}

		public static function show_job_expiration(){
			$settings = self::get_default_settings( 'job_detail' );
			$expiration = isset( $settings['job_expiration'] ) ? $settings['job_expiration'] : false;
			return $expiration;
		} 

		public static function get_post_meta_requirements_column( $id ){
			$meta = self::get_post_meta_datas( $id );
			$data = array('featured' => '--', 'filled' => '--', 'status' => false);
			if( isset( $meta['job_featured'] ) ){
				$featured = isset( $meta['job_featured']) ? $meta['job_featured'] : 0;
				$featured = filter_var( $featured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
				$data['featured'] =  $featured ? '<span class="dashicons dashicons-yes"></span>' : '--';
			}
			if( isset( $meta['job_filled'] ) ){
				$filled = isset( $meta['job_filled'] ) ? $meta['job_filled'] : 0;
				$filled = filter_var( $filled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
				$data['filled'] =  $filled ? '<span class="dashicons dashicons-yes"></span>' : '--';
			}
			if( isset( $meta['job_expiry'] ) ){
				$expiry = isset( $meta['job_expiry'] ) ? $meta['job_expiry'] : false;
				if($expiry){
					$data['status'] =  self::is_post_expired($meta['job_expiry']) ? __('Expired', 'job-manager-career') : __('Active', 'job-manager-career');
				}else{
					$data['status'] = __('Active', 'job-manager-career');
				}
				$data['status'] = '<span class="thjmf-listing-status status-'.esc_attr( strtolower ($data['status']) ).'">'. $data['status'].'</span>';
			}
			return $data;
		}

		public static function get_post_meta_requirements( $id ){
			$meta = self::get_post_meta_datas( $id );
			$data = array('featured' => '--', 'filled' => '--', 'status' => false);
			$featured =  isset( $meta['job_featured'] ) ? $meta['job_featured'] : false;
			$data['featured'] = filter_var( $featured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
			$filled =  isset( $meta['job_filled'] ) ? $meta['job_filled'] : false;
			$data['filled'] = filter_var( $filled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
			$expiry = isset( $meta['job_expiry'] ) ? $meta['job_expiry'] : false;
			if($expiry){
				$data['status'] =  self::is_post_expired($meta['job_expiry']) ? __('Active', 'job-manager-career') : __( 'Expired', 'job-manager-career');
			}else{
				$data['status'] = false;
			}
			return $data;
		}

		public static function get_post_meta_expiration($id){
			$expired = false;
			$job_expiry = self::get_post_metas( $id, self::THJMF_JPM_EXPIRED, true);
			$expiry = isset( $job_expiry ) ? $job_expiry : false;
			if($expiry){
				$expired =  self::is_post_expired($expiry);
			}
			return $expired;
		}

		public static function get_admin_url( $tab = false ){
			$url = 'edit.php?post_type=thjm_jobs&page=thjmf_settings';
			if($tab && !empty($tab)){
				$url .= '&tab='. $tab;
			}
			return admin_url($url);
		}

		public static function get_posted_date($id = false){
			if($id){
				$date_format = apply_filters( 'thjmf_change_job_column_date_format', get_option('date_format') );
				$date = get_the_time( $date_format , $id );
			}else{
				$date = get_the_Date();
			}
			return $date;
		}

		public static function convert_date_wp($date, $reverse=false){
			$format = $reverse ? 'd-m-Y' : 'Y-m-d';
			$new_date = date( $format, strtotime( $date ) );
			return $new_date;
		}

		public static function sanitize_uploads( $type, $value){
			$cleaned = '';
			$value = $type != 'tmp_name' ? stripslashes( $value ) : $value;
			if( $type ){
				switch ($type) {
					case 'name':
						$cleaned = sanitize_file_name( $value );
						break;
					case 'type':
						$cleaned = sanitize_mime_type( $value );
						break;
					case 'error':
					case 'size':
						$cleaned = is_numeric( trim( $value ) );
						$cleaned = $cleaned ? absint( trim( $value ) ) : "";
						break;
					
					default:
						$cleaned = $value;
						break;
				}
			}
			return $cleaned;
		}

		public static function applicant_post_type_url(){
		// Url used in html content. So use single qoutes.
			$url = "<a href='".admin_url("edit.php?post_type=".self::THJMF_CPT_APPLICANTS."'")."'>All Applicants</a>";
			return $url;
		}

		public static function dump( $str, $left ){
		?>
			<pre style="<?php echo $left ? "margin-left:".$left."px;" : "";?>">
				<?php echo var_dump($str); ?>
			</pre>
		<?php
		}
	}

endif;