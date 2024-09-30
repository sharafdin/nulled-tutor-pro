<?php
/**
 * Template for bundle filter
 *
 * @package TutorPro\CourseBundle
 * @subpackage Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

$filter_types = array(
	'course' => __( 'Course', 'tutor-pro' ),
	'bundle' => __( 'Bundle', 'tutor-pro' ),
);
?>

<div class="tutor-widget tutor-widget-course-price tutor-mt-48">
	<h3 class="tutor-widget-title">
	<?php esc_html_e( 'Type', 'tutor-pro' ); ?>
	</h3>

	<div class="tutor-widget-content">
		<ul class="tutor-list">
		<?php foreach ( $filter_types as $value => $label ) : ?>
			<div class="tutor-list-item">
				<label>
					<input type="checkbox" class="tutor-form-check-input" 
							id="<?php echo esc_html( $value ); ?>" 
							name="tutor-course-filter-type" 
							value="<?php echo esc_html( $value ); ?>"/> <?php echo esc_html( $label ); ?>
				</label>
			</div>
		<?php endforeach; ?>
		</ul>
	</div>
</div>
