<?php
/**
 * Bundle restriction message
 *
 * @since 2.2.0
 *
 * @package TutorPro\CourseBundle\Views
 */

$total_enrolled = isset( $data['total_enrolled'] ) ? $data['total_enrolled'] : 0;
?>

<?php if ( $total_enrolled ) : ?>
<div class="tutor-row" style="margin: 0 2px;">
	<div class="tutor-alert tutor-warning" style="display:block;">
		<span>
			<i class="tutor-icon-warning tutor-color-warning"></i>
		</span>
		<?php esc_html_e( 'You cannot add/remove course(s) from a course bundle with enrolled students as it may disrupt the learning experience.', 'tutor-pro' ); ?>            
	</div>
</div>
<?php endif; ?>
