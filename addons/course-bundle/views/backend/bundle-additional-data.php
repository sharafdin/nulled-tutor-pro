<?php
/**
 * Course bundle builder meta box
 *
 * @since 2.2.0
 * @package TutorPro\CourseBundle\Views
 */

use TUTOR\Input;
use Tutor\Models\CourseModel;

$bundle_id = Input::get( 'bundle-id', null, Input::TYPE_INT ) ?? get_the_ID();

$course_benefits = get_post_meta( $bundle_id, CourseModel::BENEFITS_META_KEY, true );

do_action( 'tutor_pro_course_bundle_before_additional_data_meta_box' );
?>
<div class="tutor-pro-course-bundle-builder-wrapper">
	<div class="tutor-mb-32">
		<label class="tutor-fs-6 tutor-fw-medium tutor-color-black">
			<?php esc_html_e( 'What Will I Learn?', 'tutor' ); ?>
		</label>
		<textarea class="tutor-form-control tutor-form-control-auto-height tutor-mt-12" name="course_benefits" rows="2" placeholder="<?php esc_attr_e( 'Write here the course benefits (One per line)', 'tutor' ); ?>"><?php echo esc_textarea( $course_benefits ); ?></textarea>
	</div>
</div>
<?php
do_action( 'tutor_pro_course_bundle_after_additional_data_meta_box' );
