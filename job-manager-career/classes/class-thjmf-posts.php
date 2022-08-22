<?php
/**
 * The file that defines the post types of plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    job-manager-career
 * @subpackage job-manager-career/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THJMF_Posts')):

	class THJMF_Posts extends THJMF_Post_Fields{
		protected static $_instance = null;
		private $meta_data = null;
		private $appl_fields = null;
		private $appl_meta = null;
		public function __construct() {
			$this->init();
		}

		public static function instance() {
			if(is_null(self::$_instance)){
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function init(){
			$this->initialize();
			if( post_type_exists('thjm_jobs') ){
				$this->register_thjmf_taxonomies();
			}

			$this->appl_meta = THJMF_Utils::$appl_meta;

			add_action( 'add_meta_boxes_thjm_jobs', array($this, 'thjmf_jobs_metaboxes') );
			add_action( 'add_meta_boxes_thjm_applicants', array($this, 'thjmf_applicants_metaboxes') );
			add_action( 'save_post_thjm_jobs', array ($this, 'thjmf_metabox_save_data') ,10, 3);
			add_action( 'save_post_thjm_applicants', array ($this, 'thjmf_applicants_metabox_save_data') ,10, 3);
			add_filter( 'manage_thjm_jobs_posts_columns' , array($this, 'jobs_event_modify_columns') );
			add_filter( 'manage_thjm_applicants_posts_columns' , array($this, 'applicants_event_modify_columns') );
			add_action( 'manage_thjm_jobs_posts_custom_column', array($this, 'populate_jobs_post_columns'),10,2);
			add_action( 'manage_thjm_applicants_posts_custom_column', array($this, 'populate_applicant_columns'),10,2);
			add_action( 'post_submitbox_minor_actions', array($this, 'render_applicant_status'));
			add_filter( 'parse_query', array($this, 'add_applicant_filter') );
			add_action( 'restrict_manage_posts', array( $this, 'add_job_taxonomy_filters') );
			add_action( 'admin_head-post-new.php', array($this, 'applicant_admin_css') );
			add_action( 'admin_head-post.php', array($this, 'applicant_admin_css') );
			add_action( 'admin_head-edit.php', array($this, 'applicant_admin_listing') );
			add_filter( 'thjmf_change_job_column_date_format', array( $this, 'change_jobs_cpt_column_date_format' ) );
		}

		public function add_applicant_filter( $query ){
			global $pagenow;

		    if ( isset($_GET['post_type']) && is_admin() && $pagenow=='edit.php' && isset($_GET['thjmf_applied_jobs']) && $_GET['thjmf_applied_jobs'] != '' && $query->is_main_query() ) {
		        $query->query_vars['meta_key'] = THJMF_Utils::get_cpt_key();
		        $query->query_vars['meta_value'] = absint( intval( $_GET['thjmf_applied_jobs'] ) );
		    }
		    return $query;
		}

		public function add_job_taxonomy_filters() {
			global $typenow;
		    global $wp_query;
		    		
		    if ( THJMF_Utils::is_jobs_post( $typenow ) ) {
		    	foreach (THJMF_Utils::$tax as $taxonomy) {
		    		$cat_slug      = isset($_GET[$taxonomy]) ? sanitize_title( $_GET[$taxonomy] ) : '';
		    		wp_dropdown_categories(array(
			            'show_option_all' =>  get_taxonomy($taxonomy)->labels->all_items,
			            'taxonomy'        =>  $taxonomy,
			            'name'            =>  $taxonomy,
			            'orderby'         =>  'name',
			            'selected'        =>  $cat_slug,
			            'hierarchical'    =>  true,
			            'depth'           =>  3,
			            'show_count'      =>  true,
			            'hide_empty'      =>  true,
			            'value_field'	  => 'slug',
			            'hide_if_empty'   =>  true,
		        	));
		    	}
		    }else if( THJMF_Utils::is_applicant_post( $typenow ) ){
		    	?>
		    	<select name="thjmf_applied_jobs">
		        <option value=""><?php echo esc_html__('All Jobs','job-manager-career'); ?></option>
		        <?php
		            $current_job = isset($_GET['thjmf_applied_jobs'])? sanitize_title( $_GET['thjmf_applied_jobs'] ) : '' ;
		            foreach (THJMF_Utils::get_job_post_titles() as $key => $value) {
		                printf
		                    (
		                        '<option value="%s"%s>%s</option>',
		                        $key,
		                        $key == $current_job? ' selected="selected"':'',
		                        $value
		                    );
		                }
		        ?>
		        </select>
		    	<?php
		    }
		}

		public function remove_quick_edit( $actions, $post ) {
	     	unset($actions['inline hide-if-no-js']);
	    	return $actions;
		}

		private function register_thjmf_taxonomies(){
			$cat_labels = array(
			    'name' => _x( 'Categories', 'job-manager-career' ),
			    'singular_name' => _x( 'Category', 'job-manager-career' ),
			    'search_items' =>  __( 'Search Categories', 'job-manager-career' ),
			    'all_items' => __( 'All Categories', 'job-manager-career' ),
			    'parent_item' => __( 'Parent Categories', 'job-manager-career' ),
			    'parent_item_colon' => __( 'Parent Category:', 'job-manager-career' ),
			    'edit_item' => __( 'Edit Category', 'job-manager-career' ), 
			    'update_item' => __( 'Update Category', 'job-manager-career' ),
			    'add_new_item' => __( 'Add New Category', 'job-manager-career' ),
			    'new_item_name' => __( 'New Category Name', 'job-manager-career' ),
			    'menu_name' => __( 'Categories', 'job-manager-career' ),
	  		); 	
	 
			register_taxonomy('thjm_job_category',array('thjm_jobs'), array(
				'hierarchical' => true,
			    'labels' => $cat_labels,
			    'show_ui' => true,
			    'show_admin_column' => true,
			    'query_var' => true,
			    'description' => false,
			 ));
		
			$loc_labels = array(
			    'name' => _x( 'Locations', 'job-manager-career' ),
			    'singular_name' => _x( 'Location', 'job-manager-career' ),
			    'search_items' =>  __( 'Search Location', 'job-manager-career' ),
			    'all_items' => __( 'All Location', 'job-manager-career' ),
			    'parent_item' => __( 'Parent Location', 'job-manager-career' ),
			    'parent_item_colon' => __( 'Parent Location:', 'job-manager-career' ),
			    'edit_item' => __( 'Edit Location', 'job-manager-career' ), 
			    'update_item' => __( 'Update Location', 'job-manager-career' ),
			    'add_new_item' => __( 'Add New Location', 'job-manager-career' ),
			    'new_item_name' => __( 'New Location Name', 'job-manager-career' ),
			    'menu_name' => __( 'Locations', 'job-manager-career' ),
			    'not_found' => __( 'No locations found.', 'job-manager-career' ),
			    'back_to_items' => __( '← Back to locations', 'job-manager-career' ),
		  	); 	
	 
		  	register_taxonomy('thjm_job_locations',array('thjm_jobs'), array(
			    'hierarchical' => true, // Box design changes
			    'labels' => $loc_labels,
			    'show_ui' => true,
			    'show_admin_column' => true,
			    'query_var' => true,
			    'description' => false,
		  	));

	  		$type_labels = array(
			    'name' => _x( 'Job Types', 'job-manager-career' ),
			    'singular_name' => _x( 'Job Type', 'job-manager-career' ),
			    'search_items' =>  __( 'Search Job Type', 'job-manager-career' ),
			    'all_items' => __( 'All Job Type', 'job-manager-career' ),
			    'parent_item' => __( 'Parent Job Type', 'job-manager-career' ),
			    'parent_item_colon' => __( 'Parent Job Type:', 'job-manager-career' ),
			    'edit_item' => __( 'Edit Job Type', 'job-manager-career' ), 
			    'update_item' => __( 'Update Job Type', 'job-manager-career' ),
			    'add_new_item' => __( 'Add New Job Type', 'job-manager-career' ),
			    'new_item_name' => __( 'New Job Type Name', 'job-manager-career' ),
			    'menu_name' => __( 'Job Types', 'job-manager-career' ),
			    'not_found' => __( 'No job types found.', 'job-manager-career' ),
			    'back_to_items' => __( '← Back to job types', 'job-manager-career' ),
	  		); 	
	 
			register_taxonomy('thjm_job_type',array('thjm_jobs'), array(
				'hierarchical' => true,
			    'labels' => $type_labels,
			    'show_ui' => true,
			    'show_admin_column' => true,
			    'query_var' => true,
			    'description' => false,
			 ));
		}

		public function jobs_event_modify_columns( $columns ) {
		  	// New columns to add to table
		  	$columns[ 'title' ] = 'Position';  
			$new_columns = array(
				'cb'	=> $columns['cb'],
				'title'	=> $columns['title'],
				'category' => __( 'Category', 'job-manager-career' ),
				'type' => __( 'Type', 'job-manager-career' ),
				'location' => __( 'Location', 'job-manager-career' ),
				'posted' => __( 'Posted', 'job-manager-career' ),
				'featured' => __( 'Featured', 'job-manager-career' ),
				'filled' => __( 'Filled', 'job-manager-career' ),
				'status' => __( 'Status', 'job-manager-career' ),
		  	);
		  	return $new_columns;
		}

		public function applicants_event_modify_columns( $columns ){ 
			unset($columns['date']);
			$columns['title'] 				= __( 'Name', 'themehigh-job-manager' );
			$columns['thjmf_position'] 		= __( 'Position', 'job-manager-career' );		
			$columns['thjmf_location'] 		= __( 'Location', 'job-manager-career' );
			$columns['thjmf_applied_date'] 	= __( 'Applied Date', 'job-manager-career' );
			$columns['thjmf_status'] 		= __( 'Status', 'job-manager-career' );
		  	return $columns;
		}

		public function thjmf_jobs_metaboxes() {
			
		    add_meta_box( 'job-data-meta', __( 'Job Data', 'job-manager-career' ), array($this, 'render_job_data_meta_box'), 'thjm_jobs', 'normal', 'default');
		    add_meta_box("featured-listing-meta", __("Featured Listing", 'job-manager-career'), array($this, "render_job_featured_meta_box" ), "thjm_jobs", "side", "default", null);
		    add_meta_box("position-filled-meta", __("Job Status", 'job-manager-career'), array($this, "render_job_filled_meta_box"), "thjm_jobs", "side", "default", null);
		    remove_meta_box( 'slugdiv', 'thjm_jobs', 'normal' );
		}

		public function thjmf_applicants_metaboxes(){
			add_meta_box( 'thjmf_applicant_data', __( 'Applicant Data', 'job-manager-career' ), array($this, 'render_applicant_data_meta_box'), 'thjm_applicants', 'normal', 'default');
			add_meta_box( 'thjmf_admin_note', __( 'Additional Notes - For Admin', 'job-manager-career' ), array($this, 'render_additional_admin_note'), 'thjm_applicants', 'normal', 'default');
			remove_meta_box( 'slugdiv', 'thjm_applicants', 'normal' );
		}

		public function thjmf_metabox_save_data( $pid, $post, $boolean){
			if ( 'trash' === $post->post_status ) {
	           return;
	       	}

	       	if(empty($_POST)){
	        	return;
	       	}

	       	if ( empty( $_POST['thjmf_job_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['thjmf_job_meta_nonce'] ), 'thjmf_save_jobs' ) ) {
				return;
			}

			if( !THJMF_Utils::is_user_capable() ){
				return;
			}

			$feature_fields = array('job_feature', 'job_feature_details');
			$result = '';
			if($post->post_status == 'publish' ){
				$spec_arr = array();
				$features = array();
				$feature_arr = array();
				
				if( isset( $_POST['i_job_feature'] ) && is_array( $_POST['i_job_feature'] ) ){
					foreach ($_POST['i_job_feature'] as $ft_key => $ft_value) {
						$formated_name = THJMF_Utils::format_field_name( $ft_value );
						array_push($feature_arr, $formated_name );
						$features['job_features'][ $formated_name ] = THJMF_Utils::sanitize_post_fields( $ft_value );
					}
				}
				if( isset( $_POST['i_job_feature_details'] ) && is_array( $_POST['i_job_feature_details'] ) ){
					foreach ($_POST['i_job_feature_details'] as $det_key => $det_value) {
						$feature_key = isset( $feature_arr[$det_key] ) ? $feature_arr[$det_key] : THJMF_Utils::sanitize_post_fields( $det_key, "number" );
						THJMF_Utils::save_post_metas( $pid, $feature_key, THJMF_Utils::sanitize_post_fields( $det_value ) );
					}

				}
				if( apply_filters('thjmf_remove_postmeta_invalids', false) ){
					$feature_keys = THJMF_Utils::get_job_feature_keys();
					$diff = array_diff($features['job_feature'], $feature_keys);
					if( is_array( $diff ) && !empty( $diff) ){
						foreach ($diff as $d_key => $d_value) {
							if( isset( $features['job_feature'][$d_key] ) && isset( $features['job_feature_details'][$d_key] ) ){
								unset($features['job_feature'][$d_key]);
								unset($features['job_feature_details'][$d_key]);
							}
						}
					}
					
				}
				$job_featured = isset( $_POST['thjm_featured_meta_switch'] ) ? THJMF_Utils::sanitize_post_fields( $_POST['thjm_featured_meta_switch'], 'checkbox' ) : false;
				$job_filled = isset( $_POST['thjm_filled_meta_switch'] ) ? THJMF_Utils::sanitize_post_fields( $_POST['thjm_filled_meta_switch'], 'checkbox' ) : false;
				$job_expired = isset( $_POST['i_job_expiry'] ) && !empty( $_POST['i_job_expiry'] ) ? sanitize_text_field( THJMF_Utils::convert_date_wp( $_POST['i_job_expiry'] ) ) : "";
				$result = THJMF_Utils::save_post_meta_datas($pid, $job_featured, $job_filled, $job_expired);
			}
			return $result;
		}

		public function thjmf_applicants_metabox_save_data( $pid, $post, $boolean ){

			if ( 'trash' === $post->post_status ) {
	           return;
	       	}

	       	if( isset($_POST['thjmf_save_popup']) ){
	       		return;
	       	}

	       	if(empty($_POST) || !isset($_POST) ){
	        	return;
	       	}

	       	if ( empty( $_POST['thjmf_applicant_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['thjmf_applicant_meta_nonce'] ), 'thjmf_save_application' ) ) {
				return;
			}

	       	if( !THJMF_Utils::is_user_capable() ){
				return;
			}

	       	$pm1 = $pm2 = true;

	       	if( $post->post_status == 'publish' ){
				$additional_note = isset( $_POST['appl_additional_note'] ) ? THJMF_Utils::sanitize_post_fields( $_POST['appl_additional_note'], 'textarea' ) :"";
				$status = isset( $_POST['appl_status'] ) ? THJMF_Utils::sanitize_post_fields( $_POST['appl_status'], 'text') :"";

				$pm1 = THJMF_Utils::save_post_metas( $pid, THJMF_Utils::THJMF_PM_ADDITIONAL_NOTE, $additional_note );
				$pm2 = THJMF_Utils::save_post_metas( $pid, THJMF_Utils::get_application_status_key(), $status );
			}
			return $pm1 && $pm2;

		}

		public function render_job_featured_meta_box(){
			global $post;
			$featured_job = THJMF_Utils::get_jpm_featured($post->ID);
			$featured_job = filter_var( $featured_job, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
			?>
			<div class="jb-featured-meta" style="padding: 10px;">
				<input type="checkbox" id="thjm_featured_meta_switch" name="thjm_featured_meta_switch" <?php echo $featured_job ? 'checked' : ""; ?>/>
				<label for="thjm_featured_meta_switch" style="vertical-align: top;">Featured Job</label>
			</div>
			<?php
		}

		public function render_job_filled_meta_box(){
			global $post;
			$filled_job = THJMF_Utils::get_jpm_filled($post->ID);
			$filled_job = filter_var( $filled_job, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
			?>
			<div class="jb-filled-meta" style="padding: 10px;">
				<input type="checkbox" id="thjm_filled_meta_switch" name="thjm_filled_meta_switch" <?php echo $filled_job ? 'checked' : ""; ?>/>
				<label for="thjm_filled_meta_switch" style="vertical-align: top;">Job Filled</label>
			</div>
			<?php
		}

	    public function render_applicant_data_meta_box($post){
	    	$resume = false;
	    	$job_meta = false;
	    	$job_title = THJMF_Utils::get_post_metas( $post->ID, 'job_title', true);
	    	$tax = '';
	    	$resume = THJMF_Utils::get_post_metas($post->ID, 'resume', true);
	    	$applicant_name = get_the_title();
			if ( function_exists('wp_nonce_field') ){
				wp_nonce_field( 'thjmf_save_application', 'thjmf_applicant_meta_nonce' ); 
    		}
	    	?>
			<div id="thjmf_applicant_meta" class="thjmf-metabox-wrapper">
				<div class="applicant-data-wrapper">
					<div class="thjmf-applicant-data-head">
						<div class="thjmf-applicant-title">
							<h3><?php echo esc_html( $applicant_name); ?></h3>
							<?php
							if($resume){
								$this->render_resume_actions($resume);
							}
							?>
							<div class="thjmf-job-meta">
								<?php 
								foreach ($this->appl_meta as $amkey => $amvalue ) {
									reset($this->appl_meta);
									end($this->appl_meta);
									$value = THJMF_Utils::get_post_metas( $post->ID, $amkey, true);
									$value = $this->prepare_applicant_job_meta( $amkey, $amvalue, $value );

									$suffix = $amkey === key($this->appl_meta) ? '' : ' | ';
									$tax.= $value.$suffix;
								}
								?>
								<h4> <?php echo esc_html( $job_title ) .' - '. wp_kses_post( $tax ); ?></h4>
							</div>
						</div>
					</div>
					<div class="thjmf-applicant-data-body">
						<table class="thjmf-applicant-details-table">
							<?php
							foreach (THJMF_Utils::$appl_fields as $apkey => $apvalue) {
	    						if($apkey == 'first_name' || $apkey == 'last_name'){
	    							continue;
	    						}
	    						$mvalue = THJMF_Utils::get_post_metas( $post->ID, $apkey, true);
								$mvalue = empty($mvalue) ? ' - ' : $mvalue;
	    						?>
	    						<tr>
	    							<td class="left">
	    								<label><?php echo $apvalue; ?></label>
	    							</td>
	    							<td class="right" style="text-align: justify;">
	    								<span>
	    									<?php echo esc_html($mvalue); ?>
	    								</span>
	    							</td>
	    						</tr>
	    						<?php
	    					} ?>
						</table>
					</div>
				</div>
			</div>
			<?php
	    }

	    private function prepare_applicant_job_meta( $mkey, $label, $mvalue){
	    	$placeholder = '<span class="thjmf-applicant-job-meta">['.$label.']</span>';
	    	$mvalue = $mvalue == '' ? 'n/a '.$placeholder : $mvalue;
	    	return $mvalue;
	    }

	    public function render_resume_actions( $url ){
	    	?>
	    	<div class="thjmf-admin-resume">
	    		<a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html__("Resume", 'job-manager-career'); ?></a>
	    	</div>
	    	<?php
	    }

	    public function render_additional_admin_note( $post ){
	    	$content = THJMF_Utils::get_post_metas($post->ID, THJMF_Utils::get_apm_additional_note(), true);
	    	?>
	    	<div id="thjmf_applicant_meta_additional" class="thjmf-metabox-wrapper">
	    		<textarea rows="9" name="appl_additional_note"><?php echo esc_html($content); ?></textarea>
	    	</div>
	    	<?php
	    }

	    public function render_applicant_status( $post ){
	    	$status = THJMF_Utils::get_applicant_post_meta_status($post->ID);
	    	if( THJMF_Utils::is_applicant_post( get_post_type() ) ){
		    	$options = array(
		    		'pending' => __('Pending', 'job-manager-career'), 
		    		'rejected' => __('Rejected', 'job-manager-career'), 
		    		'selected' => __('Selected', 'job-manager-career'),
		    	);
		    	?>
		    	<div id="thjmf_applicant_status_meta">
		    		<p class="thjmf-meta-title"><?php echo esc_html__('Status', 'job-manager-career'); ?></p>
		    		<select name="appl_status" id="thjmf_status" class="thjmf-status-select">
		    			<?php
		    			foreach ($options as $key => $value) {
		    				?>
		    				<option value="<?php echo esc_attr($key); ?>" <?php echo $key == $status ? 'selected' : ""?> ><?php echo esc_html($value); ?></option>
		    			<?php } ?>
		    		</select>
		    	</div>
	    	<?php
	    	}
	    }

		public function render_job_data_meta_box(){
			if ( function_exists('wp_nonce_field') ){
				wp_nonce_field( 'thjmf_save_jobs', 'thjmf_job_meta_nonce' ); 
    		}
			?>
			<div id="job-data-meta" class="thjmf-metabox-wrapper">
				<div class="job-data-wrapper">
					<?php
					$this->load_post_meta_data();
					$this->render_job_feature_table();
					if( THJMF_Utils::show_job_expiration() ){
						$this->render_job_expiry();
					}?>	
				</div>
			</div>
			<?php
		}

		public function populate_jobs_post_columns($column, $post_id){
			if($column == 'category'){
				$category = THJMF_Utils::get_taxonamy_term_string( $post_id, 'thjm_job_category' );
				echo esc_html( $category );

			}else if($column == 'type'){
				$type = THJMF_Utils::get_taxonamy_term_string( $post_id, 'thjm_job_type' );
				echo esc_html( $type );

			}else if($column == 'location'){
				$location = THJMF_Utils::get_taxonamy_term_string( $post_id, 'thjm_job_locations' );
				echo esc_html( $location );

			}else if($column == 'posted'){
				$posted = THJMF_Utils::get_posted_date( $post_id );
				echo esc_html( $posted );

			}else if($column == 'featured'){
				$featured = THJMF_Utils::get_jpm_featured( $post_id, true );
				echo wp_kses( trim( stripslashes( $featured ) ),wp_kses_allowed_html('post') );

			}else if($column == 'filled'){
				$filled = THJMF_Utils::get_jpm_filled( $post_id, true );
				echo wp_kses( trim( stripslashes( $filled ) ),wp_kses_allowed_html('post') );

			}else if($column == 'status'){
				$status = THJMF_Utils::get_jpm_expired( $post_id, true );
				echo wp_kses( trim( stripslashes( $status ) ),wp_kses_allowed_html('post') );
			}
		}

		public function populate_applicant_columns( $column, $post_id ){
			if( $column == 'thjmf_applied_date' ){
				$date = human_time_diff( get_the_time('U'), current_time( 'timestamp' ) ) . ' ago';
				echo esc_html( $date );
			}else if($column == 'thjmf_position'){
				echo esc_html( THJMF_Utils::get_post_metas( $post_id, 'job_title', true) );
			}else if( $column == 'thjmf_location'){
				echo esc_html( THJMF_Utils::get_post_metas( $post_id, 'location', true) );
			}
			else if( $column == 'thjmf_status'){
				$status = THJMF_Utils::get_post_metas( $post_id, 'application_status', true);
				$status = !empty( $status ) ? $status : "pending";
				echo '<span class="thjmf-listing-status status-'.esc_attr( $status ).'">'. esc_html( THJMF_Utils::get_formated_label( $status ) ).'</span>';
			}
		}

		public function applicant_admin_css() {
		    global $post_type;
		   	if( THJMF_Utils::is_applicant_post( $post_type ) ){
			    echo  '<style type="text/css">
			    		#post-body-content,
	                    #misc-publishing-actions,
	                    #minor-publishing-actions .misc-pub-section,
			    		#thjmf_applicant_data .postbox-header,
			    		#thjmf_admin_note .handle-actions,
			    		button.handle-order-lower,
			    		button.handle-order-higher{
	                        display:none;
	                    }
	                    .postbox-header{
	                    	border-bottom: 0;
	                    }
	                </style>';
			}
		}

		public function applicant_admin_listing(){
			global $post_type;
		   	if( THJMF_Utils::is_applicant_post( $post_type ) ){
		   		echo  '<style type="text/css">
						.post-type-'.$post_type.' .wp-list-table .thjmf-listing-status{
							font-weight: bold;
						}
						.post-type-'.$post_type.' .wp-list-table .thjmf-listing-status.status-rejected{
							color: #e60000;
						}
						.post-type-'.$post_type.' .wp-list-table .thjmf-listing-status.status-selected{
							color:  #4f964f;
						}
						.post-type-'.$post_type.' .wp-list-table .thjmf-listing-status.status-pending{
							color: #e67300;
						}
	                    .post-type-'.$post_type.' .wp-list-table .column-title{
							width: 15ch;
						}
						.post-type-'.$post_type.' .wp-list-table .column-thjmf_position{
							width: 12ch;
						}
						.post-type-'.$post_type.' .wp-list-table .column-thjmf_location{
							width: 12ch;
						}
						.post-type-'.$post_type.' .wp-list-table .column-thjmf_applied_date{
							width: 8ch;
						}
						.post-type-'.$post_type.' .wp-list-table .column-thjmf_status{
							width: 8ch;
						}
	                </style>';
	        }else if( THJMF_Utils::is_jobs_post( $post_type ) ){
		   		echo  '<style type="text/css">
		   				.post-type-'.$post_type.' .wp-list-table td{
							vertical-align: middle;
						}
						.post-type-'.$post_type.' .wp-list-table .thjmf-listing-status{
							display: inline-block;
							width: 75px;
							text-align: center;
							padding: 8px 0px;
							border-radius: 4px;
							font-weight: bold;
						}
						.post-type-'.$post_type.' .wp-list-table .thjmf-listing-status.status-expired{
							color: #d9534f;
						}
						.post-type-'.$post_type.' .wp-list-table .thjmf-listing-status.status-active{
							color:  #5cb85c;
						}
						
	                    .post-type-'.$post_type.' .wp-list-table .column-title{
							width: 18ch;
						}
						.post-type-'.$post_type.' .wp-list-table .column-category{
							width: 12ch;
						}
						.post-type-'.$post_type.' .wp-list-table .column-type{
							width: 12ch;
						}
						.post-type-'.$post_type.' .wp-list-table .column-location{
							width: 18ch;
						}
						.post-type-'.$post_type.' .wp-list-table .column-posted{
							width: 6ch;
						}
						.post-type-'.$post_type.' .wp-list-table .column-featured,
						.post-type-'.$post_type.' .wp-list-table .column-filled{
							width: 5ch;
							text-align:center;
						}
						.post-type-'.$post_type.' .wp-list-table .column-status{
							width: 6ch;
							text-align:center;
						}
	                </style>';
	        }
			
		}

		public function change_jobs_cpt_column_date_format( $format ){
			return 'Y/m/d';
		}

	}

endif;