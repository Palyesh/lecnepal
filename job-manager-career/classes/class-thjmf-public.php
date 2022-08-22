<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    job-manager-career
 * @subpackage job-manager-career/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THJMF_Public')):
 
	class THJMF_Public {
		private $post_id = null;
		private $post_set = false;
		private $post_type = null;
		private $ft_meta = null;
		private $ft_settings = null;
		private $db_gen_settings = null;
		private $loop_data = null;
		private $hide_filled = null;
		private $show_p_date = null;
		private $email_placeholders = array();
		private $submit_msg = array();
		private $is_admin = null;

		public function __construct() {
		}

		public function enqueue_styles_and_scripts() {
			global $post;
			// if( THJMF_Utils::should_enqueue($post) ){
				$in_footer = apply_filters( 'thjmf_enqueue_script_in_footer', true );
				$deps = array('jquery');
				wp_enqueue_style( 'thjmf-public-style', THJMF_ASSETS_URL . 'css/thjmf-public.css', THJMF_VERSION );
				if( !wp_style_is('dashicons')){
					wp_enqueue_style('dashicons');
				}
				wp_register_script('thjmf-public-script', THJMF_ASSETS_URL.'js/thjmf-public.js', $deps, THJMF_VERSION, $in_footer);
				wp_enqueue_script('thjmf-public-script');	
				$public_var = array(
					'ajax_url'				=> admin_url( 'admin-ajax.php' ),
					'ajax_nonce' 			=> wp_create_nonce('thjmf_ajax_filter_job'),
					'popup_style'			=> $this->popup_style(),
				);
				wp_localize_script('thjmf-public-script', 'thjmf_public_var', $public_var);
			// }
		}

		public function define_public_hooks(){
			add_action('init', array( $this, 'setup_settings_data' ) );
			add_action('wp', array( $this, 'prepare_post_datas' ) );
			add_action( 'wp', array($this, 'prepare_form_submission') );
			add_filter( 'excerpt_more', array($this, 'new_excerpt_more'), 20 );
			add_filter('the_content', array($this, 'thjmf_single_post_content'));
		}

		protected function max_num_pages( $per_page, $count=false){
			$published_posts = $count ? $count : wp_count_posts('thjm_jobs')->publish;
			$max = ceil($published_posts / $per_page);
			return $max;
		}

		public function prepare_form_submission(){
			global $post;
			if(is_single() && get_post_type( $post ) == THJMF_Utils::get_job_cpt()  && isset( $_POST['thjmf_save_popup'] ) ){
				
				if ( empty( $_POST['thjmf_application_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['thjmf_application_meta_nonce'] ), 'thjmf_new_job_application' ) ) {
					wp_die("You don't have enough permission");
					return;
				}

				$submit = $this->process_apply_now_form_submit();
				$submit = filter_var( $submit, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
				$submit = $submit ? 'success' : 'error';
				$permalink = esc_url( add_query_arg( 'submit', $submit , get_permalink( $post ) ) );
				set_transient( 'thjmf_apply_now_submit', $submit, 5 * MINUTE_IN_SECONDS );
				wp_safe_redirect( $permalink );
				exit;
				
			}
			else if( is_single() && get_post_type( $post ) == THJMF_Utils::get_job_cpt() && isset( $_GET['submit'] ) ){
				if( get_transient('thjmf_apply_now_submit') ){
					delete_transient('thjmf_apply_now_submit');
					
				}else{
					wp_safe_redirect( get_permalink( $post ) );
					exit;
				}
			}
		}

		public function process_apply_now_form_submit(){
			$applicant_meta = [];
			$pm1 = $pm2 = $mail = false;
			$field_list = [];
			$error = [];
			$post_id = is_int( absint( $_POST['thjmf_post_id'] ) ) ? THJMF_Utils::sanitize_post_fields( $_POST['thjmf_post_id'], 'number' ) : false;
			if($post_id && get_post_type($post_id) == 'thjm_jobs'){
				if( isset( $_FILES['thjmf_resume'] ) && is_array( $_FILES['thjmf_resume'] ) ){
					$validate = $this->validate_file( $_FILES['thjmf_resume'] );
					if( !isset($validate['error']) ){
						add_filter('upload_dir', array( $this, 'reset_thjmf_upload_dir'));
						$uploadedfile = $this->sanitize_upload_data( $_FILES['thjmf_resume'] );
						$upload_overrides = array( 'test_form' => false );
						require_once(ABSPATH. 'wp-admin/includes/file.php');
						require_once(ABSPATH. 'wp-admin/includes/media.php');
						$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
						if ( $movefile && ! isset( $movefile['error'] ) ) {
							$applicant_meta['resume'] = isset( $movefile['url'] ) ? esc_url( $movefile['url'] ) : ''; 
							foreach (THJMF_Utils::$appl_fields as $fkey => $fname) {
								$applicant_meta[$fkey] = isset( $_POST['thjmf_'.$fkey] ) ? THJMF_Utils::sanitize_post_fields( $_POST['thjmf_'.$fkey], $this->apply_field_sanitize[$fkey] ) : '--'; 
							}
							$applicant_meta = $this->add_apply_form_post_meta( $post_id, $applicant_meta );
							
							$post_info = array(
									'post_title'	=> $applicant_meta['name'],
									'post_content'  => '',
									'post_status'   => 'publish',          
									'post_type'     => THJMF_Utils::get_applicant_cpt(), 
							);
							
							$ins_post_id = wp_insert_post( $post_info, true );
							foreach ($applicant_meta as $mkey => $mvalue) {
								add_post_meta($ins_post_id, $mkey, $mvalue, true);
							}
							
							$this->email_placeholders = array(
								'name' => $applicant_meta['name'], 
								'job'  => get_the_title( $post_id ),
								'email'=> $applicant_meta['email'],
							);

							$pm1 = add_post_meta($ins_post_id, THJMF_Utils::get_cpt_map_job_key(), $post_id, true);
							$pm2 = add_post_meta($ins_post_id, THJMF_Utils::get_application_status_key(), 'pending', true);
							$mail = $this->confirm_application();
						} else {
						   $error = $movefile['error'];
						}
					}else{
						$error = $validate['error'];
					}
				}
			}
			return $pm1 && $pm2 && $mail;
		}

		public function sanitize_upload_data( $files ){
			$uploads = [];
			if( is_array( $files ) && $files){
				foreach ($files as $fkey => $fvalue) {
					$uploads[ sanitize_key( $fkey) ] = THJMF_Utils::sanitize_uploads( $fkey, $fvalue);
				}
			}
			return $uploads;
		}

		public function setup_settings_data(){
			$this->apply_field_sanitize = array('name' => 'text', 'phone' => 'number', 'email' => 'email', 'cover_letter' => 'textarea');
			$this->db_gen_settings = THJMF_Utils::get_default_settings();
			$this->submit_msg = array(
	   			'success' => __('Application Submitted Successfully', 'job-manager-career'),
	   			'error'	  => __('An error occured while submitting the application. Try again', 'job-manager-career'),
	   		);
		}

		private function confirm_application(){
			$admin_mail = $this->send_admin_notification();
			$applicant_mail = $this->send_applicant_notification();
			return $admin_mail && $applicant_mail;
		}

		private function send_admin_notification(){
			$this->is_admin = true;
			$to = get_option('admin_email');
			/* translators: site name. */
			$subject = sprintf( __( '[%s] New Job Application Received', 'job-manager-career'), get_bloginfo('name') );
			$message = $this->get_template_content();
			return $this->send_notification( $to, $subject, $message );
		}

		private function send_applicant_notification(){
			$this->is_admin = false;
			$email = isset( $this->email_placeholders['email'] ) ? $this->email_placeholders['email'] : THJMF_Utils::get_logged_user_email();
			$to = sanitize_email( $email );
			/* translators: site name. */
			$subject = sprintf( __( '[%s] Job Application Received', 'job-manager-career'), get_bloginfo('name') );
			$message = $this->get_template_content();
			return $this->send_notification( $to, $subject, $message );
		}

		private function send_notification( $to, $subject, $message ){
			add_filter( 'wp_mail_from', array( $this, 'get_mail_from_address' ) );
			add_filter( 'wp_mail_from_name', array( $this, 'get_mail_from_name' ) );

			$headers = "Content-Type: text/html\r\n";
			if( apply_filters('thjmf_enable_reply_to_email', false) ){
				$headers .= 'Reply-to: ' . $this->get_mail_from_name() . ' <' . $this->get_mail_from_address() . ">\r\n";
			}

			$send = wp_mail($to, $subject, $message, $headers);
			
			remove_filter( 'wp_mail_from', array( $this, 'get_mail_from_address' ) );
			remove_filter( 'wp_mail_from_name', array( $this, 'get_mail_from_name' ) );
			return $send;
		}

		private function add_apply_form_post_meta_old( $id ){
			$meta = [];
			$meta['title'] = get_the_title( $id );
			$meta['location'] = THJMF_Utils::get_comma_seperated_taxonamy_terms($id, 'location');
			$meta['category'] = THJMF_Utils::get_comma_seperated_taxonamy_terms($id, 'category');
			$meta['job_type'] = THJMF_Utils::get_comma_seperated_taxonamy_terms($id, 'job_type');
			return $meta;
		}

		private function add_apply_form_post_meta( $id, $meta ){
			$meta['job_title'] = get_the_title( $id );
			$meta['location'] = THJMF_Utils::get_comma_seperated_taxonamy_terms($id, 'location');
			$meta['category'] = THJMF_Utils::get_comma_seperated_taxonamy_terms($id, 'category');
			$meta['job_type'] = THJMF_Utils::get_comma_seperated_taxonamy_terms($id, 'job_type');
			return $meta;
		}

		public function validate_file($file){
			$ftypes = array('doc', 'docx', 'pdf');
			$errors = array();
			$errors['status'] = 'SUCCESS';
		
			if($file){
				$ftype = isset( $file['type'] ) ? $file['type'] : false;
				$fsize = isset( $file['size'] ) ? $file['size'] : false;			
				if($ftype && $fsize){
					$name  = isset($file['name']) ? $file['name'] : '';
					$title = isset($file['title']) ? $file['title'] : '';
					$file_type = strtolower($ftype);

					$maxsize = apply_filters('thjmf_file_upload_maxsize', 2);
					$maxsize_bytes = is_numeric($maxsize) ? $maxsize*1048576 : false;
					
					$accept = apply_filters('thjmf_file_upload_accepted_file_types', $ftypes);
					$allowed = $accept && !is_array($accept) ? array_map('trim', explode(",", $accept)) : $accept;
					$file_type = pathinfo($file['name'], PATHINFO_EXTENSION);
					if(is_array($allowed) && !empty($allowed) && !in_array($file_type, $allowed)){
						/* translators: Allowed file types $accept. */
						$err_msg = '<strong>'. $title .':</strong> '. __( 'Invalid file type.', 'job-manager-career' );
						/* translators: Allowed file types. */
						$err_msg = sprintf(__('Invalid file type, allowed types are %s.', 'job-manager-career'), implode($accept) );
						$errors['error'] = $err_msg;
						$errors['status'] = 'ERROR';							
						
					}else if($maxsize_bytes && is_numeric($maxsize_bytes) && $fsize >= $maxsize_bytes){
						/* translators: Allowed maximum size of file to upload. */
						$err_msg = sprintf(__('Uploaded file should not exceed %sMB.', 'job-manager-career'), $maxsize);
						$errors['error'] = $err_msg;
						$errors['status'] = 'ERROR';
					}
				}
			}
			return $errors;
		}

		public function reset_thjmf_upload_dir($upload){
			$upload['subdir'] = '/thjmf_uploads';
	        $upload['path'] = $upload['basedir'] . $upload['subdir'];
	        $upload['url']  = $upload['baseurl'] . $upload['subdir'];
	        return $upload;
		}

		public function prepare_post_datas(){
			global $post;
			if( THJMF_Utils::should_enqueue( $post ) && is_single() ) {
				$this->post_id = $post->ID;
				$post_type = get_post_type($this->post_id);
				$this->post_set = $post_type == 'thjm_jobs' ? true : false;
				if($this->post_set){
					$this->post_type = $post_type;
					$meta_job_ft = THJMF_Utils::get_post_meta_datas($this->post_id);
					$this->ft_meta = isset( $meta_job_ft[0]['features'] ) ? $meta_job_ft[0]['features'] : "";
				}
			}
			
		}

		public function new_excerpt_more($more) {
		    return '...';
		}

		public function new_excerpt_more_link($more) {
	   		global $post;
	   		return '...<a href="'.get_permalink($post->ID).'">'.'Read More &raquo;'.'</a>';
	   	}

	   	public function thjmf_single_post_content($content){
	
	   		if(is_single() && $this->post_type == 'thjm_jobs'){
		   		if(	isset( $_GET['submit'] ) ){
		   			$submit = sanitize_key( $_GET['submit'] );
					?>
					<div class="thjmf-form-submission-msg">
						<?php 
						$msg = isset( $this->submit_msg[$submit] ) ? $this->submit_msg[$submit] : "";
						echo '<p class="thjmf-'.esc_attr( $submit ).'">'.esc_html( $msg ).'</p>';
						?>
					</div>
				<?php 
				}
				$ft_content = '';
				$attach_features = apply_filters( 'thjmf_attach_features_with_default_content', true );
				if( $attach_features ){
					if( apply_filters( 'thjmf_show_job_features', true ) ){
						$ft_content = $this->render_pre_content_data();
		   			}
		   			$apply_form = $this->render_additional_single_post_data();
		   			if( apply_filters('thjmf_features_before_description', true) ){
		   				$content = $ft_content.$content.$apply_form;
		   			}else{
		   				$content = $content.$ft_content.$apply_form;
		   			}
				}
	   		}
	   		return $content;
	   	}

	   	private function render_pre_content_data_old(){
	   		$mod_content = '';
	   		$features = '';
	   		$details = '';
	   		
	   		$mod_content = $this->render_post_tags(); 
   			$features = isset( $this->ft_meta['job_features'] ) ? $this->ft_meta['job_features'] : false;
   			$details = isset( $this->ft_meta['job_feature_details'] ) ? $this->ft_meta['job_feature_details'] : false;
	   		if( !empty( $features ) && !empty( $details ) ){
		   		$mod_content = $this->thjmf_display_job_features($features, $details);
	   		}
	   		return $mod_content;
	   	}

	   	/* Creating Job Feature - Details display on single job page */
	   	private function thjmf_display_job_features_old( $ft, $dt, $args = array() ) {
			$parts = array();
			$html    = '';
			$disp_class = apply_filters('thjmf_job_feature_plain_list_view', true) ? "thjmf-plain-list" : "thjmf-bullet-list";
			$args    = wp_parse_args(
				$args,
				array(
					'before'       => '<ul class="thjmf-job-features-list '.$disp_class.'"><li>',
					'after'        => '</li></ul>',
					'separator'    => '</li><li>',
					'autop'        => false,
					'label_before' => '<strong class="thjmf-job-feature-label">',
					'label_after'  => ': </strong> ',
				)
			);
			$args = apply_filters( 'thjmf_job_features_args', $args );
			foreach ( $ft as $ft_key => $feature ) {
				$value = isset( $dt[$ft_key] ) ? $dt[$ft_key] : "";
				if( empty( $value ) ){
					continue;
				}
				$parts[] = $args['label_before'] . wp_kses_post( $feature ) . $args['label_after'].$value;
			}
			if ( $parts ) {
				$html = $args['before'] . implode( $args['separator'], $parts ) . $args['after'];
			}
			$html = apply_filters( 'thjmf_display_job_features', $html, $ft, $dt, $args );
			return $html;
		}

		private function render_pre_content_data(){
	   		$mod_content = '';
	   		$features = '';
	   		$details = '';
	   		
	   		$mod_content = $this->render_post_tags(); 
   			$features = isset( $this->db_gen_settings['job_detail']['job_feature']['job_def_feature'] ) ? $this->db_gen_settings['job_detail']['job_feature']['job_def_feature'] : false;

	   		if( !empty( $features ) ){
		   		$mod_content = $this->thjmf_display_job_features($features);
	   		}
	   		return $mod_content;
	   	}

	   	/* Creating Job Feature - Details display on single job page */
	   	private function thjmf_display_job_features( $ft, $args = array() ) {
			$parts = array();
			$html    = '';
			$disp_class = apply_filters('thjmf_job_feature_plain_list_view', true) ? "thjmf-plain-list" : "thjmf-bullet-list";
			$args    = wp_parse_args(
				$args,
				array(
					'before'       => '<ul class="thjmf-job-features-list '.$disp_class.'"><li>',
					'after'        => '</li></ul>',
					'separator'    => '</li><li>',
					'autop'        => false,
					'label_before' => '<strong class="thjmf-job-feature-label">',
					'label_after'  => ': </strong> ',
				)
			);
			$args = apply_filters( 'thjmf_job_features_args', $args );
			foreach ( $ft as $ft_key => $feature ) {
				$value = THJMF_Utils::get_post_metas( $this->post_id, $ft_key, true );
				if( empty( $value ) ){
					continue;
				}
				$parts[] = $args['label_before'] . wp_kses_post( $feature ) . $args['label_after'].$value;
			}
			if ( $parts ) {
				$html = $args['before'] . implode( $args['separator'], $parts ) . $args['after'];
			}
			$html = apply_filters( 'thjmf_display_job_features', $html, $ft, $args );
			return $html;
		}

		private function thjmf_format_email_url( $occurances){
			$html = '';
			if ( $occurances[1] == '[' && $occurances[6] == ']' ) {
				return substr($occurances[0], 1, -1);
			}
			$email = isset( $occurances[1] ) && is_email( $occurances[1] ) ? $occurances[1] : "";
			$html = '<a href="mailto:'.esc_attr( $email ).'">'.esc_html( $email ).'</a>';
			return $html;

		}

		public function render_apply_now_button($expired, $apply_form){
			$inactive = false;
			$filled = get_post_meta( get_the_ID(), THJMF_Utils::get_filled_meta_key(), true);
			$filled = filter_var( $filled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

			if( $filled || $expired){
				$inactive = true;
				echo $this->render_disabled_apply_now_msg( $expired, $filled);
			}

			if( isset( $apply_form['enable_apply_form'] ) && !$apply_form['enable_apply_form'] ){
				if( !in_array( true , array( $filled, $expired ) ) ){
					$msg = isset( $apply_form['apply_form_disabled_msg'] ) ? $apply_form['apply_form_disabled_msg'] : "";
					$msg = preg_replace_callback("/{(.*?)}/", array($this, "thjmf_format_email_url"),$msg);
					echo '<div class="thjmf-apply-now-msg">'.esc_html( $msg ).'</div>';
				}
				return;
			}

			?>
				<button id="thjmf_apply_now" <?php echo ( !$inactive ) ? 'onclick="thjmEventApplyJob(this)"' : 'disabled'; ?> data-post="<?php echo esc_attr( $this->post_id ); ?>" class="thjmf-btn-apply-now" name="thjmf_apply_now" value="Apply Now">
					<?php echo esc_html( apply_filters( 'thjmf_job_apply_now_button', __('Apply Now', 'job-manager-career') ) ); ?>
				</button>
			<?php
		}

		private function render_disabled_apply_now_msg($expired, $filled){
			$msg = '';
			$msgs = array(
				'expired' => __( 'This job is Expired', 'job-manager-career'),
				'filled' => __( 'This Job is Filled', 'job-manager-career'),
			);
			if( $expired ){
				$msg = $msgs['expired'];
			}else if( $filled ){
				$msg = $msgs['filled'];
			}
			return '<div class="thjmf-apply-now-disabled-msg">'.esc_html( $msg ).'</div>';	
		}

	   	public function render_additional_single_post_data(){
	   		ob_start();
	   		$expired = THJMF_Utils::get_post_meta_expiration( get_the_ID() );
	   		$apply_form = isset( $this->db_gen_settings['job_submission'] ) ? $this->db_gen_settings['job_submission'] : ""; 
	   		if( !$expired ) : ?>
		   		<div id="thjmf_apply_now_popup">
		   			<div class="thjmf-popup-wrapper">
		   				<div class="thjmf-popup-header">
		   					<p class="thjmf-popup-title"><?php echo __('Application Form', 'job-manager-career'); ?></p>
		   					<span class="thjmf-popup-close" onclick="thjmEventClosePopup(this)">
		   						<span class="dashicons dashicons-no-alt"></span>
		   					</span>
		   				</div>
		   				<form class="thjmf-apply-form" name="thjmf_apply_now_form" method="post" enctype="multipart/form-data">
		   					<?php 
				    		if ( function_exists('wp_nonce_field') ){
								wp_nonce_field( 'thjmf_new_job_application', 'thjmf_application_meta_nonce' ); 
    						}
		   					?>
		   					<input type="hidden" name="thjmf_post_id" value=<?php echo esc_attr($this->post_id ); ?>>
		   					<div class="thjmf-popup-outer-wrapper">
			   					<div class="thjmf-popup-content-wrapper">
			   						<div class="thjmf-validation-notice" tabindex="0"></div>
			   						<div class="thjmf-popup-content">
			   							<?php $this->render_apn_form(); ?>
			   						</div>
			   					</div>
			   				</div>
		   					<div class="thjmf-popup-footer-actions">
		   						<button type="submit" name="thjmf_save_popup" id="thjmf_popup_save" onclick="thjmEventSavePopupForm(event, this)"><?php echo esc_html__('Apply', 'job-manager-career'); ?></button>
		   					</div>
		   				</form>
		   			</div>
		   		</div>
		   		<?php echo $this->render_apply_now_button($expired, $apply_form);
	   		else :
	   			$this->render_apply_now_button($expired, $apply_form);
	   		endif;
	   		return ob_get_clean();
	   	}

	   	private function render_apn_form(){
	   		?>
	   		<p class="thjmf-form-field thjmf-form-row thjmf-form-row-wide thjmf-validation-required" id="thjmf_first_name_field" data-priority="">
	   			<label for="thjmf_first_name" class=""><?php echo esc_html__('Name', 'job-manager-career'); ?>&nbsp;<span><abbr class="thjmf-required" title="<?php echo esc_attr__( 'required', 'job-manager-career' )?>">*</abbr></span></label>
	   			<span class="thjmf-field-input-wrapper ">
	   				<input type="text" class="input-text" name="thjmf_name" id="thjmf_name" placeholder="" value="">
	   			</span>
	   		</p>
	   		<p class="thjmf-form-field thjmf-form-row thjmf-form-row-first" id="thjmf_phone_field" data-priority="">
	   			<label for="thjmf_phone" class=""><?php echo esc_html__('Phone', 'job-manager-career'); ?>&nbsp;</label>
	   			<span class="thjmf-field-input-wrapper ">
	   				<input type="text" class="input-text" name="thjmf_phone" id="thjmf_phone" placeholder="" value="">
	   			</span>
	   		</p>
	   		<p class="thjmf-form-field thjmf-form-row thjmf-form-row-last thjmf-validation-required" id="thjmf_email_field" data-priority="">
	   			<label for="thjmf_email" class=""><?php echo esc_html__('Email', 'job-manager-career'); ?>&nbsp;<span><abbr class="thjmf-required" title="<?php echo esc_attr__( 'required', 'job-manager-career' )?>">*</abbr></span></label>
	   			<span class="thjmf-field-input-wrapper ">
	   				<input type="text" class="input-text" name="thjmf_email" id="thjmf_email" placeholder="" value="">
	   			</span>
	   		</p>
	   		<p class="thjmf-form-field thjmf-form-row thjmf-form-row-wide thjmf-validation-required" id="thjmf_resume_field" data-priority="">
	   			<label for="thjmf_resume" class=""><?php echo esc_html__('Resume', 'job-manager-career'); ?>&nbsp;<span><abbr class="thjmf-required" title="<?php echo esc_attr__( 'required', 'job-manager-career' )?>">*</abbr></span></label>
	   			<span class="thjmf-field-input-wrapper ">
	   				<input type="file" class="input-text" name="thjmf_resume" id="thjmf_resume" placeholder="" value="">
	   			</span>
	   		</p>
	   		<p class="thjmf-form-field thjmf-form-row thjmf-form-row-wide" id="thjmf_cover_letter_field" data-priority="">
	   			<label for="thjmf_cover_letter" class=""><?php echo esc_html__('Cover Letter', 'job-manager-career'); ?>&nbsp;</label>
	   			<span class="thjmf-field-input-wrapper ">
	   				<textarea class="input-text" name="thjmf_cover_letter" id="thjmf_cover_letter" placeholder="" value=""></textarea>
	   			</span>
	   		</p>
	   		<?php
	   	}

	   	public function render_post_tags(){
	   		$settings = THJMF_Utils::get_default_settings('job_detail');
	   		$p_date_visible = isset( $settings['job_display_post_date'] ) ? $settings['job_display_post_date'] : false;
			$args = $this->get_tag_details();

			$p_date = apply_filters('thjmf_toggle_posted_timestap', true) ? human_time_diff( get_the_time('U'), current_time( 'timestamp' ) ) . ' ago' : THJMF_Utils::get_posted_date();
			?>
			<div class="thjmf-job-list-single-tags">
				<?php if($p_date_visible) : ?>
					<div class="thjmf-inline-tags">
						<span class="dashicons dashicons-clock thjmf-dashicons"></span>
						<?php 
						echo esc_html( $p_date );
						?>
					</div>
				<?php endif; ?>
				<?php if( isset( $args['location'] ) && !empty( $args['location'] )){ ?>
					<div class="thjmf-inline-tags">
						<span class="dashicons dashicons-location thjmf-dashicons"></span><?php echo esc_html( $args['location'] ); ?>
					</div>
				<?php } if( isset( $args['job_type'] ) && !empty( $args['job_type'] ) ){ ?>
					<div class="thjmf-inline-tags">
						<span class="dashicons dashicons-portfolio thjmf-dashicons"></span><?php echo esc_html($args['job_type']); ?>
					</div>
				<?php } ?>
			</div>
			<?php
		}

		public function get_job_meta_tags( $settings ){
			$meta = THJMF_Utils::get_post_meta_requirements( get_the_ID() );
			$tax = $this->get_tag_details();

			if( !isset( $meta['show_date'] ) ){
				$meta['show_date'] = isset( $settings['job_detail']['job_display_post_date'] ) ? $settings['job_detail']['job_display_post_date'] : false;
				$meta['date'] = apply_filters('thjmf_toggle_posted_timestap', true) ? human_time_diff( get_the_time('U'), current_time( 'timestamp' ) ) . ' ago' : THJMF_Utils::get_posted_date();
			}

			return is_array( $tax ) ? array_merge( $meta, $tax ) : $meta;
		}

	   	private function get_tag_details(){
			$args = [];
			$args['category'] = THJMF_Utils::get_comma_seperated_taxonamy_terms( get_the_ID(), 'category' );
			$args['location'] = THJMF_Utils::get_comma_seperated_taxonamy_terms( get_the_ID(), 'location' );
			$args['job_type'] = THJMF_Utils::get_comma_seperated_taxonamy_terms( get_the_ID(), 'job_type' );
			return apply_filters('thjmf_manage_public_job_taxonomies', $args, is_single());
		}

		public function get_template_content(){
			ob_start();
			$this->render_email_content();
			$message = ob_get_clean();
			return $message;
		}

		private function render_email_content(){
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
				<title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
			</head>
			<?php $this->render_body();?>
			</html>
			<?php
		} 

		private function render_body(){
			?>
			<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
				<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" style="background-color: #f7f7f7;padding: 70px 0;margin: 0;width: 100%;">
					<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
						<tr>
							<td align="center" valign="top">
								<?php 
								$this->render_template_main_content();
								?>
							</td>
						</tr>
					</table>
				</div>
			</body>
			<?php
		}
		private function render_template_main_content(){
			?>
				<table cellpadding="0" cellspacing="0" width="600" border="0" style="border: 1px solid #dedede;border-radius: 4px;border-collapse: collapse;">
					<tr>
						<td style='background-color: #fff; color: #636363; font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;font-size: 14px;line-height: 150%;text-align: left;'>
							<?php 
							$this->get_default_header();
							if( $this->is_admin ){
								$this->get_addressing_block_admin();
							}else{
								$this->get_addressing_block_applicant();
							}
							$this->get_default_footer();
							?>
						</td>
					</tr>
				</table>
			<?php
		}

		private function get_default_header(){
			?>
			 <table cellspacing="0" cellpadding="0" border="0" style="width: 100%;background-color: #51519d;color: #ffffff;border-bottom: 0;font-weight: bold;line-height: 100%;vertical-align: middle;border-radius: 3px 3px 0 0; border-collapse: collapse;">
				<tr>
					<td style='text-align: center; font-size: 12px;font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;padding:36px 48px;'>
						<h1 style="font-size: 30px;font-weight: 300;line-height: 150%;margin: 0;text-align: left;color: #ffffff;"><?php echo esc_html__('Job Application', 'job-manager-career'); ?></h1>
					</td>
				</tr>
			</table>
			<?php
		}

		public function get_default_footer(){
			?>
			<table cellspacing="0" cellpadding="0" border="0" style="width: 100%;border-collapse: collapse;">
				<tr>
					<td style='text-align: center; font-size: 12px;font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;padding: 20px 40px 40px 40px;'>
						<p><?php echo get_bloginfo(); ?></p>
					</td>
				</tr>
			</table>
			<?php
		}

		public function get_addressing_block_applicant(){
			$name = isset( $this->email_placeholders['name'] ) ? esc_html( $this->email_placeholders['name'] ) : "";
			$job_title = isset( $this->email_placeholders['job'] ) ? '<b>'.esc_html( $this->email_placeholders['job'] ).'</b>' : "";
			?>
			<table cellspacing="0" cellpadding="0" width="100%" border="0" style="border-collapse: collapse;">
				<tr>
					<td style="padding: 40px 40px 20px 40px;">
						<?php /* translators: Name of the applicant. */ ?>
						<p> <?php printf( 'Hi %s,', $name ); ?></p>
						<p><?php echo esc_html__('Thank you for your interest!', 'job-manager-career'); ?></p>
				
						<p>
							<?php printf( wp_kses( 
								/* translators: Job title for which the application was received. */
								__('We have received your application for %s.', 'job-manager-career'), 
								array( 'b' => array() ) 
								), $job_title );
							?>
						</p>
						<p><?php echo esc_html__('Our teams will organize next steps post reviewing your application.', 'job-manager-career'); ?></p>
						<div style="margin-top: 20px;">
							<p><?php echo esc_html__('Regards,', 'job-manager-career'); ?><br> <i>HR Team</i></p>
						</div>
					</td>
				</tr>
			</table>
			<?php
		}

		public function get_addressing_block_admin(){
			$name = isset( $this->email_placeholders['name'] ) ? '<b>'.esc_html( $this->email_placeholders['name'] ).'</b>' : "";
			$job_title = isset( $this->email_placeholders['job'] ) ? '<b>'.esc_html( $this->email_placeholders['job'] ).'</b>' : "";
			?>
			<table cellspacing="0" cellpadding="0" width="100%" border="0" style="border-collapse: collapse;">
				<tr>
					<td style="padding: 40px 40px 20px 40px;">
						<p> <?php echo 'Hi,'; ?></p>
						<?php /* translators: Job applicant name, Job title. */ ?>
						<p><?php echo sprintf( __('You have received an application from %1$s for the job %2$s.', 'job-manager-career'), $name, $job_title ); ?></p>
						<?php /* translators: Url of the applicant admin dashboard */ ?>
						<p><?php printf( __(' View %s for more detials.', 'job-manager-career'), THJMF_Utils::applicant_post_type_url() ); ?></p>
					</td>
				</tr>
			</table>
			<?php
		}

		public function get_mail_from_address(){
			$address = apply_filters( 'thjmf_email_from_address', get_bloginfo('admin_email') );
			return sanitize_email( $address );
		}

		public function get_mail_from_name(){
			$name = apply_filters( 'thjmf_email_from_name', get_bloginfo('name') );
			return wp_specialchars_decode( esc_html( $name ), ENT_QUOTES );
		}

		public function popup_style(){
			$width = array( 'width1' => '40%','width2' => '50%','width3' => '62%','width4' => '78%' );
			$styles = apply_filters( 'thjmf_apply_now_popup_width', $width);
			if( is_array( $styles ) ){
				return array_map( 'sanitize_text_field', $styles );
			}
			return $width;
		}
	}

endif;