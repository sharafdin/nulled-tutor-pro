<?php
/**
 * Instructor Meta box
 *
 * @author themeum
 * @link https://themeum.com
 * @package TutorPro\MultiInstructors
 */
?>
<div class="tutor-course-instructors-metabox-wrap <?php echo is_admin() ? 'tutor-p-16' : ''; ?>">
	<?php
	$current_course_id = ( ! is_admin() && isset( $_GET['course_ID'] ) ) ? $_GET['course_ID'] : get_the_ID();
	$instructors       = tutor_utils()->get_instructors_by_course( $current_course_id );
	?>

	<div class="tutor-course-available-instructors">
		<?php
		$post                 = get_post( $current_course_id );
		$instructor_crown_src = TUTOR_MT()->url . 'assets/images/crown.svg';
		$delete_class         = 'tutor-instructor-delete-btn';
		$main_instructor_id   = is_a( $post, 'WP_Post' ) ? $post->post_author : 0;
		if ( is_array( $instructors ) && count( $instructors ) ) {
			foreach ( $instructors as $instructor ) {
				$authorTag = '';
				if ( $post->post_author == $instructor->ID ) {
					$authorTag = '<img src="' . $instructor_crown_src . '"/>';
				}

				include TUTOR_MT()->path . '/views/user-card.php';
			}
		}
		?>
	</div>

	<button 
		data-tutor-modal-target="tutor_course_instructor_modal"
		type="button" 
		class="tutor-mt-32 tutor-btn tutor-btn-outline-primary tutor-add-instructor-btn"> 
		<i class="tutor-icon-add-group tutor-mr-12"></i>
		<?php _e( 'Add Instructor', 'tutor' ); ?> 
	</button>
</div>

<div class="tutor-modal" id="tutor_course_instructor_modal" data-course_id="<?php echo $current_course_id; ?>">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content">
			<div class="tutor-modal-header">
				<div class="tutor-modal-title">
					<?php _e( 'Add Instructor', 'tutor' ); ?>
				</div>

				<button class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close>
					<span class="tutor-icon-times" area-hidden="true"></span>
				</button>
			</div>

			<div class="tutor-modal-body" style="min-height: 120px;">
				<div class="tutor-form-wrap">
					<span class="tutor-icon-search tutor-form-icon" area-hidden="true"></span>
					<input type="text" class="tutor-form-control" placeholder="<?php _e( 'Search instructors...', 'tutor' ); ?>">
				</div>
				<div class="tutor-search-result tutor-mt-12"></div>
				<div class="tutor-selected-result tutor-mt-16"></div>
			</div>

			<div class="tutor-modal-footer">
				<button type="button" data-action="back" class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
					<?php _e( 'Cancel', 'tutor' ); ?>
				</button>
				<button type="submit" data-action="next" class="tutor-btn tutor-btn-primary add_instructor_to_course_btn" disabled="disabled">
					<?php _e( 'Save Changes', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
