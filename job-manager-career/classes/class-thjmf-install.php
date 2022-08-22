<?php
/**
 * The file that defines the settings of plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    job-manager-career
 * @subpackage job-manager-career/includes
 */

defined( 'ABSPATH' ) || exit;

if(!class_exists('THJMF_Install')) :

	class THJMF_Install{
		public function __construct() {
			
		}

		public static $notice_class = array(
			'update' => 'notice notice-error',
			'updated' => 'notice notice-success is-dismissible"'
		);
		
		public static function needs_db_update() {
			// Update to all versions less than 1.0.3, Don't have DB flag and have jobs
			$needs_update = $old_version = false;
			$current_db_version = get_option( THJMF_Utils::get_db_version_key(), null );
			$db_updated = THJMF_Utils::database_updated();
			$jobs = self::get_thjm_posts( 'jobs' );
			$applicants = self::get_thjm_posts( 'applicants' );
			$empty_posts = count($jobs) > 0 || count($applicants) > 0 ? false : true;

			if( ! is_null( $current_db_version ) ){ // version 1.0.2 or May be after plugin version key update
				$old_version = version_compare( $current_db_version, THJMF_VERSION, '<' );
				return $old_version;
			}

			// V1.0.0, V1.0.1 or Fresh install have no keys set during checking
			//First release of plugin have no db settings key from 1.0.3 unless saved by clicking save from general settings. And fresh install won't have key. Key updated only after this check

			if( !$db_updated && !$empty_posts ){//Older version or New Installation
				return true;
			}
			return $needs_update;
		}

		public static function update_db_version() {
			delete_option( THJMF_Utils::get_db_version_key() );
			add_option( THJMF_Utils::get_db_version_key(), THJMF_VERSION );
			THJMF_Utils::set_database_updated( true );
			error_log('Database successfully upgraded');
		}

		public static function render_update_notice( $notice = 'update' ){
			?>
			<div class="<?php echo isset( self::$notice_class[$notice] ) ? self::$notice_class[$notice] : ''; ?>">
				<?php echo self::manage_notices( $notice ); ?>
			</div>
			<?php
		}

		public static function perform_updation(){
			self::update();
			self::update_db_version();
		}

		public static function update(){
			self::run_updation_manager();
		}

		public static function run_updation_manager(){
		
			$job_posts 	= self::get_thjm_posts( 'jobs' );
			$appl_posts = self::get_thjm_posts( 'applicants' );

			self::update_jobs( $job_posts );
			self::update_applications( $appl_posts );
		}

		public static function get_thjm_posts( $type, $post_ids=false ){
			$args = array(
				'post_status' => array('publish', 'trash'),
				'numberposts' => -1
			);
			$args['post_type'] = $type == 'jobs' ? THJMF_Utils::get_job_cpt() : THJMF_Utils::get_applicant_cpt();
			if( is_array( $post_ids ) && !empty( $post_ids ) ){
				$args['post__in'] = $post_ids;
			}
			$posts = get_posts( $args );
			return $posts;
		}

		public static function update_jobs( $job_posts ){
			$count = !is_null( $job_posts ) ? count( $job_posts ) : false;
			if( $count ){
				foreach ($job_posts as $key => $jobs) {
					$post_metas = get_post_meta( $jobs->ID, '_thjm_post_custom_settings', true);
					
					$feature_details = isset( $post_metas['features']['job_feature_details'] ) ? $post_metas['features']['job_feature_details'] : '';
					if( is_array( $feature_details ) && !empty( $feature_details ) ){
						foreach ($feature_details as $ft_key => $ft_value) {
							THJMF_Utils::save_post_metas($jobs->ID, $ft_key, $ft_value);
						}
					}

					// Managing Featured Job
					$featured = THJMF_Utils::get_post_metas( $jobs->ID, THJMF_Utils::THJMF_PM_FEATURED, true);
					THJMF_Utils::save_post_metas( $jobs->ID, THJMF_Utils::THJMF_JPM_FEATURED, $featured );

					// // Managing Filled Job
					$filled = THJMF_Utils::get_post_metas( $jobs->ID, THJMF_Utils::THJMF_PM_FILLED, true);
					THJMF_Utils::save_post_metas( $jobs->ID, THJMF_Utils::THJMF_JPM_FILLED, $filled );

					// // Managing Expired Job
					$expired = THJMF_Utils::get_post_metas( $jobs->ID, THJMF_Utils::THJMF_PM_EXPIRED, true);
					THJMF_Utils::save_post_metas( $jobs->ID, THJMF_Utils::THJMF_JPM_EXPIRED, $expired );
				}
			}
		}

		public static function update_applications( $appl_posts ){
			$count = !is_null( $appl_posts ) ? count( $appl_posts ) : false;
			if( $count ){
				foreach ($appl_posts as $key => $appl) {
					$post_metas = get_post_meta( $appl->ID, THJMF_Utils::get_applicant_pm_key(), true);
					// Change status post meta key

					$status = THJMF_Utils::get_post_metas( $appl->ID, THJMF_Utils::get_applicant_status_key(), true);
					THJMF_Utils::save_post_metas( $appl->ID, THJMF_Utils::get_application_status_key(), $status );
					$job_relation = THJMF_Utils::get_post_metas( $appl->ID, THJMF_Utils::get_cpt_key(), true );
					THJMF_Utils::save_post_metas( $appl->ID, THJMF_Utils::get_cpt_map_job_key(), $job_relation );				
					$details = isset( $post_metas['details'] ) ? $post_metas['details'] : '';
					if( is_array( $details ) && !empty( $details ) ){
						if( isset( $details['meta'] ) ){
							$meta =  $details['meta'];
							unset( $details['meta'] );
							$details = $details+$meta;
						}

						foreach ($details as $dkey => $dvalue) {
							$dkey = $dkey == 'title' ? 'job_title' : $dkey;
							THJMF_Utils::save_post_metas( $appl->ID, $dkey, $dvalue );
						}
					}
					$additional_note = isset( $post_metas['additional_note'] ) ? $post_metas['additional_note'] : '';
					if( !empty( $additional_note ) ){
						THJMF_Utils::save_post_metas( $appl->ID, 'additional_note', $additional_note );
					}
				}
			}
		}

	}

endif;