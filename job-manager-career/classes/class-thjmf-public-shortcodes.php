<?php
/**
 * The file that defines the plugin public functionalities
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    job-manager-career
 * @subpackage job-manager-career/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THJMF_Public_Shortcodes')):

	class THJMF_Public_Shortcodes extends THJMF_Public{
		public function __construct() {
			add_action( 'wp', array( $this, 'prepare_reset_actions') );
		}

		private function is_filter_enabled( $args ){
			if( $args['enable_location'] || $args['enable_category'] || $args['enable_type'] ){
				return true;
			}
			return false;
		}

		public function shortcode_thjmf_job_listing($atts){
			if( isset( $_POST['thjmf_job_filter'] ) || isset( $_POST['thjmf_filter_load_more'] ) ){
				$this->thjmf_jobs_filter_event(true);
				return;
			}
			ob_start();
			$this->output_jobs( $atts );
			return ob_get_clean();
		}

		public function output_jobs( $atts ){
			global $wp_query,$post;
			$content_args = [];
			$settings = THJMF_Utils::get_default_settings();
			$query_args = $this->get_query_arguments( $settings );
			$filter_args = $this->get_filter_arguments( $settings );
			
			get_thjmf_template( 'job-listing-header.php' );
			if ( $this->is_filter_enabled( $filter_args) ) {
				get_thjmf_template( 'job-filters.php', $filter_args );
			}

			$jobs = get_thjmf_job_listings( $query_args );
			if( $jobs->found_posts ){
				while( $jobs->have_posts() ) {
					$jobs->the_post();
					get_thjmf_template( 'content-job-listing.php', $this->get_job_meta_tags( $settings ) );
				}
			}else{
				get_thjmf_template_part( 'content', 'no-jobs' );
			}
			
			get_thjmf_template( 'job-listing-footer.php' );
		}

		private function get_filter_arguments( $settings ){
			$args = [];
			if( isset( $settings['search_and_filt'] ) && $settings['search_and_filt'] ){
				$filters = $settings['search_and_filt'];
				$args['enable_location'] 	= isset( $filters['search_location'] ) ? $filters['search_location'] : false;
				$args['enable_type'] 		= isset( $filters['search_type'] ) ? $filters['search_type'] : false;
				$args['enable_category'] 	= isset( $filters['search_category'] ) ? $filters['search_category'] : false;
			}

			$args['job_category'] = isset( $_POST['thjmf_filter_category'] ) && !empty( $_POST['thjmf_filter_category'] ) ? sanitize_key($_POST['thjmf_filter_category']) : false;

			$args['job_location'] = isset( $_POST['thjmf_filter_location'] ) && !empty( $_POST['thjmf_filter_location'] ) ? sanitize_key($_POST['thjmf_filter_location']) : false;

			$args['job_type'] = isset( $_POST['thjmf_filter_type'] ) && !empty( $_POST['thjmf_filter_type'] ) ? sanitize_key($_POST['thjmf_filter_type']) : false;

			$args['categories'] = $this->get_taxonomy_terms('category');
			$args['locations'] 	= $this->get_taxonomy_terms('location');
			$args['types'] 		= $this->get_taxonomy_terms('job_type');
			$args['atts'] = $args;

			return $args;
		}

		public function thjmf_jobs_filter_event( $filter_load_more = false){
			$per_page = get_option( 'posts_per_page' );
			$settings = THJMF_Utils::get_default_settings();
			$settings = THJMF_Utils::get_default_settings();
			$q_args = [];

			$q_args['hide_expired'] = isset( $settings['job_detail']['job_hide_expired'] ) ? $settings['job_detail']['job_hide_expired'] : false;
			$q_args['hide_filled'] = isset( $settings['job_detail']['job_hide_filled'] ) ? $settings['job_detail']['job_hide_filled'] : false;
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			
			$q_args['posts_per_page'] = (int) $per_page*$paged;
			
			$category = isset( $_POST['thjmf_filter_category'] ) && !empty( $_POST['thjmf_filter_category'] ) ? sanitize_key($_POST['thjmf_filter_category']) : false;
			$location = isset( $_POST['thjmf_filter_location'] ) && !empty( $_POST['thjmf_filter_location'] ) ? sanitize_key($_POST['thjmf_filter_location']) : false;
			$type = isset( $_POST['thjmf_filter_type'] ) && !empty( $_POST['thjmf_filter_type'] ) ? sanitize_key($_POST['thjmf_filter_type']) : false;

			if($category){
				$q_args['category'] = $category;
			}
			if($location){
				$q_args['location'] = $location;
			}
			if($type){
				$q_args['type'] = $type;
			}
			$filter_args = $this->get_query_args( $q_args, true );
			
			$filter_query = new WP_Query( $filter_args );
			if( ! $filter_query->have_posts() ) {
				?>
				<div id="thjmf-job-listings-box">
					<?php 
					$settings = THJMF_Utils::get_default_settings();
		            $query_args = $this->get_query_arguments( $settings );
		            $filter_args = $this->get_filter_arguments( $settings );
					get_thjmf_template( 'job-filters.php', $filter_args );
					get_thjmf_template( 'content-no-jobs.php' );
					?>
			  	</div>
			  	<?php
			   return false;
			}
			
			$q_args['thjmf_max_page'] = $this->max_num_pages($per_page, $filter_query->found_posts);
			$q_args['pagenum_link_url'] = html_entity_decode( get_pagenum_link() );
			$q_args['pagenum_link'] = $q_args['pagenum_link_url'].'page/'.($paged+1).'/';
            $this->render_page_listing_content($filter_query, $q_args, $paged, true, $filter_load_more);
			return;
		}

		private function render_page_listing_content($loop, $content_args, $paged, $filter=false, $filter_load_more = false){
			$pagenum_link = isset( $content_args['pagenum_link'] ) ? $content_args['pagenum_link'] : "";
			?>
			<div id="thjmf-job-listings-box">
				<?php 
				$settings = THJMF_Utils::get_default_settings();
	            $query_args = $this->get_query_arguments( $settings );
	            $filter_args = $this->get_filter_arguments( $settings );
				get_thjmf_template( 'job-filters.php', $filter_args ); ?>
				<form name="thjmf_load_more_post" method="POST" action="<?php echo esc_attr( $pagenum_link );?>">	
					<div class="thjmf-job-listings">
						<?php
						while( $loop->have_posts() ) {
						    $loop->the_post();
						    get_thjmf_template( 'content-job-listing.php', $this->get_job_meta_tags( $settings ) );
						} ?>
					</div>
				</form>
			</div>
			<?php
		}

		public function get_taxonomy_terms( $tax ){
			$terms = THJMF_Utils::get_all_post_terms( $tax );
			return wp_list_pluck( $terms, "name", "slug");
		}

		public function get_query_arguments( $settings ){
			$args = [];

			$per_page = get_option( 'posts_per_page' );
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

			$args['hide_expired'] = isset( $settings['job_detail']['job_hide_expired'] ) ? $settings['job_detail']['job_hide_expired'] : false;
			
			$args['hide_filled'] = isset( $settings['job_detail']['job_hide_filled'] ) ? $settings['job_detail']['job_hide_filled'] : false;

			$args['posts_per_page'] = (int) $per_page*$paged;

			return $this->get_query_args( $args );
		}


		private function get_query_args( $q_args, $filter=false){
			$posts_per_page = isset( $q_args['posts_per_page'] ) ? $q_args['posts_per_page'] : false;
			$args = array (
				'posts_per_page'    => $posts_per_page,
				'post_date'			=> 'DESC',
				'post_type'         => THJMF_Utils::get_job_cpt(),
			);

			if( $filter ){
				$category = isset( $q_args['category'] ) ? $q_args['category'] : false;
				$location = isset( $q_args['location'] ) ? $q_args['location'] : false;
				$type = isset( $q_args['type'] ) ? $q_args['type'] : false;
				if( $category && $location || $category && $type || $location && $type){
					$args['tax_query'] = array( 'relation'=>'AND' );
				}
			
				if($category){
					$args['tax_query'][] = array(
						'taxonomy' => 'thjm_job_category',
						'field' => 'slug',
						'terms' => $category
					);
				}
				if($location){
					$args['tax_query'][] = array(
						'taxonomy' => 'thjm_job_locations',
						'field' => 'slug',
						'terms' => $location
					);
				}
				if($type){
					$args['tax_query'][] = array(
						'taxonomy' => 'thjm_job_type',
						'field' => 'slug',
						'terms' => $type
					);
				}
			}
			$hide_filled = isset( $q_args['hide_filled'] ) ? $q_args['hide_filled'] : false;
			$hide_expired = isset( $q_args['hide_expired'] ) ? $q_args['hide_expired'] : false;

			if($hide_filled && $hide_expired){
				$args['meta_query'] = array( 'relation'=>'AND' );
			}

			if( $hide_filled == '1'){
	    		$args['meta_query'][] = array(
					'key'       => THJMF_Utils::get_filled_meta_key(),
				    'value'   	=> '',
				    'compare' 	=> '=',
				);
			}

			if( $hide_expired == '1'){
	    		$args['meta_query'][] = array(
	    			'relation'	=> 'OR',
	    			array(
						'key'       => THJMF_Utils::get_expired_meta_key(),
					    'value'   	=> '',
					    'compare' 	=> '=',
					),
					array(
					    'key' => THJMF_Utils::get_expired_meta_key(), // Check the start date field
		                'value' => date('Y-m-d'), // Set today's date (note the similar format)
		                'compare' => '>=', // Return the ones greater than today's date
		                'type' => 'DATE' // Let WordPress know we're working with date
					),
	    		);
			}
			return $args;
		}

		public function prepare_reset_actions(){
            global $post;
            if( !isset( $_POST['thjmf_job_filter'] ) && isset( $_POST['thjmf_job_filter_reset'] ) && has_shortcode( $post->post_content, THJMF_Utils::$shortcode) ){
                global $wp;
                wp_safe_redirect( home_url( $wp->request ) );
            }
        }

	}
	
endif;