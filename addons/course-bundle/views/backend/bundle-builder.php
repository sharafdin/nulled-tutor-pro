<?php
/**
 * Course bundle builder meta box
 *
 * @since 2.2.0
 *
 * @package TutorPro\CourseBundle\Views
 */

use TUTOR\Input;
use TutorPro\CourseBundle\Models\BundleModel;
use TutorPro\CourseBundle\Utils;

$bundle_id      = Input::get( 'bundle-id', null, Input::TYPE_INT ) ?? get_the_ID();
$total_enrolled = BundleModel::get_total_bundle_sold( $bundle_id );

do_action( 'tutor_pro_course_bundle_before_builder_meta_box' );
?>
<div class="tutor-pro-course-bundle-builder-wrapper">
	<?php
	// Show bundle edit restriction message.
	$restriction_template = Utils::view_path( 'backend/components/bundle-restriction.php' );
	tutor_load_template_from_custom_path(
		$restriction_template,
		array( 'total_enrolled' => $total_enrolled )
	);
	?>
	<div class="tutor-row">
		<!-- sidebar placeholder  -->
		<div class="tutor-col-4 tutor-course-bundle-builder-sidebar tutor-course-bundle-builder-components"> 
			<?php
				$course_selection_template = Utils::view_path( 'backend/components/bundle-course-selection.php' );
				tutor_load_template_from_custom_path( $course_selection_template );
			?>

			<div id="tutor-course-bundle-overview-wrapper"></div>

			<div id="tutor-course-bundle-authors-wrapper"></div>
		</div>

		<!-- course list placeholder -->
		<div class="tutor-col-8 tutor-course-bundle-builder-content">
			<div id="tutor-bundle-course-list-wrapper"></div>
		</div>
		<input type="hidden" id="tutor-course-bundle-id" value="<?php echo esc_attr( $bundle_id ); ?>">
	</div>
</div>
<?php
do_action( 'tutor_pro_course_bundle_after_builder_meta_box' );
