<?php
/**
 * Multiple instructor user card
 * 
 * @author themeum
 * @link https://themeum.com
 * @package TutorPro\MultiInstructors
 */

// Receive param from template.
$main_instructor_id = isset( $main_instructor_id ) ? $main_instructor_id : '';

?>
<div id="added-instructor-id-<?php echo esc_attr( $instructor->ID ); ?>" 
	class="added-instructor-item added-instructor-item-<?php echo esc_attr( $instructor->ID ); ?>" 
	data-instructor-id="<?php echo esc_attr( $instructor->ID ); ?>">

	<?php echo tutor_utils()->get_tutor_avatar( $instructor->ID, 'md' ); //phpcs:ignore ?>
	<span class="instructor-name tutor-ml-12"> 
		<div class="instructor-intro">
			<div class="tutor-text-btn-xlarge tutor-color-black"><?php echo esc_attr( $instructor->display_name ); ?></div>
			<?php echo isset( $authorTag ) ? $authorTag : ''; //phpcs:ignore ?>
		</div>
		<div class="instructor-email tutor-d-block tutor-fs-7 tutor-color-secondary">
			<?php echo esc_html( $instructor->user_email ); ?>
		</div>
	</span>
	<!-- remove delete option for the main instructor -->
	<?php if ( current_user_can( 'administrator' ) && $main_instructor_id !== $instructor->ID ) : ?>
		<span class="instructor-control">
			<a href="javascript:void(0)" class="<?php echo isset( $delete_class ) ? esc_attr( $delete_class ) : ''; ?> tutor-action-icon tutor-iconic-btn">
				<i class="tutor-icon-times"></i>
			</a>
		</span>
	<?php endif; ?>
	<?php echo isset( $inner_content ) ? $inner_content : ''; //phpcs:ignore ?>
</div>
