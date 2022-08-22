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

if(!class_exists('THJMF_Settings')) :

	class THJMF_Settings{
		public function __construct() {
			
		}
		
		public function enqueue_styles_and_scripts($hook) {
	    	if( !in_array($hook, array('post.php', 'post-new.php', 'thjm_jobs_page_thjmf_settings') ) ){
	    		return;
	    	}

	    	$screen = get_current_screen();
	    	if( is_object( $screen ) && !in_array( $screen->post_type, array( 'thjm_jobs', 'thjm_applicants') ) ){
	        	return;
	    	}

			wp_enqueue_style('jquery-ui-style', THJMF_ASSETS_URL . 'css/jquery-ui.min.css', THJMF_VERSION, true);
			wp_enqueue_style('thjmf-admin-style', THJMF_ASSETS_URL . 'css/thjmf-admin.css', THJMF_VERSION, true);
			$deps = array( 'jquery', 'jquery-ui-datepicker' );
			wp_enqueue_script( 'thjmf-admin-script', THJMF_ASSETS_URL . 'js/thjmf-admin.js', $deps, THJMF_VERSION, true );

			$script_var = array(
	            'admin_url' 	=> admin_url(),
	            'ajaxurl'   	=> admin_url( 'admin-ajax.php' ),
	            'autocomplete'	=> apply_filters('thjmf_disable_form_field_autocomplete',true) ? "off" : "on",
	            'date_format'	=> get_option('date_format'),
	            'shortcode'		=> THJMF_Utils::$shortcode,
	        );
			wp_localize_script('thjmf-admin-script', 'thjmf_var', $script_var);
		}
		
		public function admin_menu() {
			$this->screen_id = add_submenu_page('edit.php?post_type=thjm_jobs', __('Settings', 'job-manager-career'), 
			__('Settings', 'job-manager-career'), 'manage_options', 'thjmf_settings', array($this, 'render_plugin_settings'));
		}

		public function render_plugin_settings(){
			echo '<div class="wrap"><h1 style="disply:none;"></h2>';
			$tabs = array( 'general' => 'General', 'data_management'=>'Data');
			$current_tab = $this->get_current_tab();

			echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
			foreach( $tabs as $id => $label ){
				$active = ( $current_tab == $id ) ? 'nav-tab-active' : '';
				$label  =__($label, 'job-manager-career');
				echo '<a class="nav-tab '.esc_attr( $active ).'" href="'. esc_url( THJMF_Utils::get_admin_url($id) ) .'">'.esc_html( $label ).'</a>';
			}
			echo '</h2>';
			$this->output_settings($current_tab, $tabs);
			echo '</div>';
		}

		public function plugin_action_links($links) {
			$settings_link = '<a href="'.admin_url('edit.php?post_type=thjm_jobs&page=thjmf_settings').'">'. esc_html__('Settings', 'job-manager-career') .'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
		
		public function output_settings($current_tab, $tabs){
			if(array_key_exists($current_tab, $tabs)){			
				$general_settings = THJMF_Settings_General::instance();	
				$general_settings->render_page();
			}
		}

		public function get_current_tab(){
			return isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		}

	}

endif;