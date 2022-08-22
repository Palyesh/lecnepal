<?php
/**
 * The file that defines the core plugin class.
 *
 * @link       https://themehigh.com
 *
 * @package    job-manager-career
 * @subpackage job-manager-career/classes
 */

defined( 'ABSPATH' ) || exit;

if(!class_exists('THJMF')):

	class THJMF {
		const TEXT_DOMAIN = 'job-manager-career';

		public function __construct() {
			$this->init();
			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}

		private function load_dependencies() {
			if(!function_exists('is_plugin_active')){
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			require_once THJMF_PATH . 'classes/thjmf-core-functions.php';
			require_once THJMF_PATH . 'classes/class-thjmf-utils.php';
			require_once THJMF_PATH . 'classes/class-thjmf-settings.php';
			require_once THJMF_PATH . 'classes/class-thjmf-install.php';
			require_once THJMF_PATH . 'classes/class-thjmf-settings-general.php';
			require_once THJMF_PATH . 'classes/class-thjmf-post-fields.php';
			require_once THJMF_PATH . 'classes/class-thjmf-posts.php';
			require_once THJMF_PATH . 'classes/class-thjmf-public.php';
			require_once THJMF_PATH . 'classes/class-thjmf-public-shortcodes.php';
			
		}

		private function set_locale() {
			add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
		}

		public function load_plugin_textdomain(){
			$locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);

			load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR.'/'.self::TEXT_DOMAIN.'/'.self::TEXT_DOMAIN.'-'.$locale.'.mo');
			load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(THJMF_BASE_NAME) . '/languages/');
		}

		public function init(){
			add_action('init', array($this, 'initialize_events') );
		}

		public function initialize_events(){
			self::register_thjmf_post_types();
			$this->define_custom_post_types();
			$this->create_upload_directory();
		}

		private function define_custom_post_types(){
			$plugin_post_types = new THJMF_Posts();
		}

		public static function register_thjmf_post_types(){
			$labels_job = array(
				'name'               => __( 'Jobs', 'job-manager-career'),
				'singular_name'      => __( 'Jobs', 'job-manager-career'),
				'menu_name'          => __( 'Job Listings', 'job-manager-career'),
				'name_admin_bar'     => __( 'Job Listings', 'job-manager-career'),
				'add_new'            => __( 'Add New', 'job-manager-career'),
				'add_new_item'       => __( 'Add New Job', 'job-manager-career'),
				'new_item'           => __( 'New Job', 'job-manager-career'),
				'edit_item'          => __( 'Edit Job', 'job-manager-career'),
				'view_item'          => __( 'View Job', 'job-manager-career'),
				'all_items'          => __( 'All Jobs', 'job-manager-career'),
				'search_items'       => __( 'Search Jobs', 'job-manager-career'),
				'parent_item_colon'  => __( 'Parent Jobs:', 'job-manager-career'),
				'not_found'          => __( 'No jobs found.', 'job-manager-career'),
				'not_found_in_trash' => __( 'No jobs found in Trash.', 'job-manager-career'),
			);

			$rewrite = array(
				'slug'                  => 'jobs',
				'with_front'            => false,
				'pages'                 => true,
				'feeds'                 => true,
			);

			$args_job = array( 
				'labels'		=> $labels_job,
				'public'		=> true,
				'rewrite'		=> array( 'slug' => 'thjm_jobs' ),
				'has_archive'   => false,
				'menu_position' => 20,
				'rewrite'       => $rewrite,
				'show_in_rest'	=> apply_filters('thjmf_enable_gutenberg_editor', false),
				'menu_icon'     => 'dashicons-megaphone',
				'supports'      => array( 'title', 'editor'),
			);
			register_post_type( 'thjm_jobs', $args_job );
		

			$labels_applicants = array(
				'name'               => __( 'Applicants', 'job-manager-career'),
				'singular_name'      => __( 'Applicant', 'job-manager-career'),
				'menu_name'          => __( 'Applicant', 'job-manager-career'),
				'name_admin_bar'     => __( 'Applicant', 'job-manager-career'),
				'add_new'            => __( 'Add New', 'job-manager-career'),
				'add_new_item'       => __( 'Add New Applicant', 'job-manager-career'),
				'new_item'           => __( 'New Applicant', 'job-manager-career'),
				'edit_item'          => __( 'Edit Applicant', 'job-manager-career'),
				'view_item'          => __( 'View Applicant', 'job-manager-career'),
				'all_items'          => __( 'All Applicants', 'job-manager-career'),
				'search_items'       => __( 'Search Applicants', 'job-manager-career'),
				'parent_item_colon'  => __( 'Parent Applicant:', 'job-manager-career'),
				'not_found'          => __( 'No Applicants found.', 'job-manager-career'),
				'not_found_in_trash' => __( 'No Applicants found in Trash.', 'job-manager-career'),
			);

			$args_applicants = array( 
				'labels'		=> $labels_applicants,
				'public'		=> false,
				'show_ui' => true,
				'has_archive'   => false,
				'show_in_menu' => 'edit.php?post_type=thjm_jobs',
				'capabilities' => array(
				    'create_posts' => false,
				    'publish_posts' => 'do_not_allow',
				),
				'map_meta_cap' => true,
				'supports'      => false,
			);
			register_post_type( 'thjm_applicants', $args_applicants );
		}

		private function create_upload_directory(){
			wp_mkdir_p(THJMF_Utils::get_template_directory());
		}
		
		private function define_admin_hooks() {
			$plugin_admin = new THJMF_Settings();
			add_action('plugins_loaded', array( $this, 'thjmf_misc_actions' ) );
			add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles_and_scripts'));
			add_action('admin_menu', array($plugin_admin, 'admin_menu'));
			add_filter('plugin_action_links_'.THJMF_BASE_NAME, array($plugin_admin, 'plugin_action_links'));
		}

		private function define_public_hooks() {
			global $pagenow;
			if( !is_admin() && !in_array( $pagenow, array('post-new.php') ) ){
				$plugin_public = new THJMF_Public();
				add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles_and_scripts'));
				add_action('after_setup_theme', array($plugin_public, 'define_public_hooks'));
				$plugin_shortcode = new THJMF_Public_Shortcodes();
				add_shortcode( THJMF_Utils::$shortcode, array( $plugin_shortcode, 'shortcode_thjmf_job_listing' ) );
			}
		}

		public static function initialize_settings(){
			self::check_for_default_settings();
			self::register_post_types();
		}

		public static function check_for_default_settings(){
			$free_settings = THJMF_Utils::plugin_db_settings();
			if($free_settings && is_array($free_settings) && !empty($free_settings)){
				return;
			}else{
				$new_settings = THJMF_Utils::default_settings();
				THJMF_Utils::save_default_settings( $new_settings, true );
			}
		}

		public function thjmf_misc_actions(){
			if ( apply_filters('thjmf_manage_db_update_notice', THJMF_Utils::is_user_capable() ) ){
				add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
			}
		}

		public function add_notices(){
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			$scree_post = $screen ? $screen->post_type : '';
			$screens = array( 'dashboard', 'plugins' );
			if( !in_array( $screen->post_type, THJMF_Utils::get_posttypes()) ){
				if( !in_array( $screen_id,  $screens ) ){
					return;
				}
			}

			if( THJMF_Utils::get_database_version() != THJMF_VERSION ){
           		THJMF_Utils::set_database_version( THJMF_VERSION );
    		}

			if( apply_filters('thjmf_force_db_update_notice', false ) ){
				THJMF_Install::perform_updation();
			}
		}

		private static function register_post_types(){
			self::register_thjmf_post_types();
			flush_rewrite_rules();

		}

		public static function prepare_deactivation(){
			unregister_post_type('thjm_jobs');
			unregister_post_type('thjm_applicants');
			flush_rewrite_rules();
		}

	}

endif;