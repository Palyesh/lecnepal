<?php
/**
 * Job listing in the loop.
 *
 * This template can be overridden by copying it to yourtheme/job-manager-career/content-job-listing.php
 *
 * @author      ThemeHigh
 * @package     job-manager-career
 * @category    Template
 * @since       1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="thjmf-job-listings-list thjmf-listing-loop-content list-wrapper">
	<table class="thjmf-listing-solo-table" border="0" cellspacing="0" cellpadding="0">
		<thead>
			<tr class="thjmf-listing-header">
				<td colspan="2">
					<div class="thjmf-listing-title" style="overflow: hidden;">
						<h3><?php get_thjmf_title( $featured ); ?></h3>
					</div>
					<div class="thjmf-job-list-single-tags">
						<?php if( $show_date ) : ?>
							<div class="thjmf-inline-tags">
								<span class="dashicons dashicons-clock thjmf-dashicons"></span>
								<?php 
								echo esc_html( $date );
								?>
							</div>
						<?php endif;
						if( isset( $location ) && !empty( $location ) ) : ?>
							<div class="thjmf-inline-tags">
								<span class="dashicons dashicons-location thjmf-dashicons"></span><?php echo esc_html( $location ); ?>
							</div>
						<?php endif;
						if( isset( $job_type ) && !empty( $job_type ) ) : ?>
							<div class="thjmf-inline-tags">
								<span class="dashicons dashicons-portfolio thjmf-dashicons"></span><?php echo esc_html( $job_type ); ?>
							</div>
						<?php endif; ?>
					</div>
				</td>
			</tr>
			<tr class="thjmf-listing-body">
				<td class="thjmf-job-single-excerpt">
					<div class="thjmf-listing-single-content">
						<?php the_excerpt();?>
					</div>
				</td>
				<td class="thjmf-job-single-more">
					<a class="button" href="<?php echo esc_url(the_permalink()); ?>">
						<?php echo esc_html( apply_filters('thjmf_job_details_button', __('Details', 'job-manager-career') ) ); ?></a>
				</td>
			</tr>
		</thead>
	</table>
</div>