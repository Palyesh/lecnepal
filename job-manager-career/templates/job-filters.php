<?php
/**
 * Filters in job manager career plugin shortcode.
 *
 * This template can be overridden by copying it to yourtheme/job-manager-career/job-filters.php
 *
 * @author      ThemeHigh
 * @package     job-manager-career
 * @category    Template
 * @version     1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'thjmf_job_filters_before', $atts );

?>
<div class="thjmf-job-listing-filter-wrapper">
	<form id="thjmf_job_filter_form" name="thjmf_job_filter_form" method="POST">
		<div class="thjmf-search-filters">
			<?php if( $enable_location ) : ?>
				<div class="thjmf-job-filters">
					<label>Select Location</label>
					<select name="thjmf_filter_location">
						<option value=""><?php echo esc_html__('All Locations', 'job-manager-career'); ?></option>
						<?php
						if( is_array( $locations ) ){
							foreach ( $locations as $location_slug => $location) {
								echo '<option value="'.esc_attr( $location_slug ).'" '.( $location_slug == $job_location  ? "selected" : "").'>'.esc_html( $location ).'</option>';
							}
						}
						?>
					</select>
				</div>
			<?php endif;
			if( $enable_category ) : ?>
				<div class="thjmf-job-filters">
					<label>Select Category</label>
					<select name="thjmf_filter_category">
						<option value=""> <?php echo esc_html__('All Categories', 'job-manager-career'); ?></option>
						<?php
						if( is_array( $categories ) ){
							foreach ( $categories as $category_slug => $category) {
								echo '<option value="'.esc_attr( $category_slug ).'"'.( $category_slug == $job_category ? "selected" : "").'>'.esc_html( $category ).'</option>';
							}
						}
						?>
					</select>
				</div>
			<?php endif;
			if( $enable_type ) : ?>
				<div class="thjmf-job-filters">
					<label>Select Job Type</label>
					<select name="thjmf_filter_type">
						<option value=""> <?php echo esc_html__('All Job Types', 'job-manager-career'); ?></option>
						<?php
						if( is_array( $types ) ){
							foreach ( $types as $type_slug => $type ) {
								echo '<option value="'.esc_attr( $type_slug ).'"'.($type_slug == $job_type ? "selected" : "").'>'.esc_html( $type ).'</option>';
							}
						}
						?>
					</select>
				</div>
			<?php endif; ?>
		</div>
		<?php if( $enable_location || $enable_category || $enable_type ): ?>
			<div class="thjmf-search-button">
				<button type="submit" class="thjmf-job-filters-button" id="thjmf_job_filter_reset" name="thjmf_job_filter_reset"><?php echo esc_html__('Reset', 'job-manager-career'); ?></button>
				<button type="submit" class="thjmf-job-filters-button" id="thjmf_job_filter" name="thjmf_job_filter" onclick="thjmfFilterJobsEvent(this)"><?php echo esc_html__('Filter', 'job-manager-career'); ?></button>
			</div>
		<?php endif; ?>
	</form>
</div>
<?php do_action( 'thjmf_job_filters_after', $atts ); ?>