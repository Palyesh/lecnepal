<?php
/**
 * Content to be displayed when there is no job
 *
 * This template can be overridden by copying it to yourtheme/job-manager-career/content-no-jobs.php
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
		          	<div class="thjmf-no-jobs">
		               	<p><?php echo __('No jobs found . . .'); ?></p>
		            </div>
				</td>
			</tr>	
		</thead>
	</table>
</div>
