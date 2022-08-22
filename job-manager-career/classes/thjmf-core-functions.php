<?php

/**
 * Gets template part (for templates in loops).
 *
 * @since 1.2.0
 * @param string      $slug
 * @param string      $name (default: '').
 * @param string      $template_path (default: 'job-manager-career').
 * @param string|bool $default_path (default: '') False to not load a default.
 */
 function get_thjmf_template_part( $slug, $name = '', $template_path = 'job-manager-career', $default_path = '' ) {
	$template = '';

	if ( $name ) {
		$template = locate_thjmf_template( "{$slug}-{$name}.php", $template_path, $default_path );
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/job_manager/slug.php.
	if ( ! $template ) {
		$template = locate_thjmf_template( "{$slug}.php", $template_path, $default_path );
	}
	
	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get other templates (e.g. layout attributes) passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function get_thjmf_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); // @codingStandardsIgnoreLine
	}

	$located = locate_thjmf_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		/* translators: %s template */
		rp_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.4.0' );
		return;
	}

	include $located;
}

/**
 * Like rp_get_template, but returns the HTML instead of outputting.
 *
 * @see   rp_get_template
 * @since 1.2.0
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function thjmf_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	rp_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * @param  string $template_name Template name.
 * @param  string $template_path Template path. (default: '').
 * @param  string $default_path  Default path. (default: '').
 * @return string
 */
function locate_thjmf_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = 'job-manager-career/';
	}

	if ( ! $default_path ) {
		$default_path = THJMF_PATH . 'templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'thjmf_locate_template', $template, $template_name, $template_path );
}

/**
 * Get job listings based on arguments
 *
 * @param  array $args Arguments to query jobs
 * @return jobs
 */
function get_thjmf_job_listings( $args ){
	return new WP_Query( $args );
}

/**
 * Get job title
 *
 * @param  boolean $featured show feature icon in title
 */
function get_thjmf_title( $featured = false ){
	$link = apply_filters( 'thjmf_shortcode_post_title_link', true );
	if( $link ){
		echo '<a href="'.esc_url(get_permalink()).'">'.get_the_title().'</a>';
	}else{
		echo wp_kses_post( the_title() );
	}

	if( !$featured ){
		return;
	}
	?>
	<div class="thjmf-featured-post">
		<img class="thjm-featured-icon" src="<?php echo esc_url( THJMF_ASSETS_URL.'images/bookmark.svg'); ?>" title="Featured Job">
	</div>
	<?php
}