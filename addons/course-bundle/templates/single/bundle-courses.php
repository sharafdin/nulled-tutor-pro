<?php
/**
 * Template for bundle courses tab.
 *
 * @package TutorPro\CourseBundle
 * @subpackage Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

use TutorPro\CourseBundle\Models\BundleModel;

// Here $course_id is bundle_id passed from single-course-bundle.php.
$courses      = BundleModel::get_bundle_courses( $course_id );
$total_course = BundleModel::get_total_courses_in_bundle( $course_id );
?>

<h2 class="tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-12">
	<?php
		/* translators: %s: count total courses */
		echo esc_html( sprintf( __( 'Courses in the Bundle (%s)', 'tutor-pro' ), $total_course ) );
	?>
</h2>

<ul class="tutor-bundle-courses-wrapper">
<?php
foreach ( $courses as $course ) :
	$thumb_url     = get_tutor_course_thumbnail_src( 'post-thumbnail', $course->ID );
	$profile_url   = tutor_utils()->profile_url( $course->post_author, true );
	$course_link   = get_permalink( $course->ID );
	$course_title  = get_the_title( $course->ID );
	$course_author = get_the_author_meta( 'display_name', $course->post_author );
	?>
	<li class="tutor-bundle-course-list-wrapper">
		<div class="tutor-bundle-course-list-counter tutor-flex-center">
			<a href="<?php echo esc_url( $course_link ); ?>" class="tutor-bundle-feature-image">
				<img class="tutor-radius-4" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $course_title ); ?>" loading="lazy">
			</a>
		</div>
		<div class="tutor-bundle-course-list-desc">
			<a href="<?php echo esc_url( $course_link ); ?>">
				<h2 class="tutor-fs-6 tutor-fw-bold tutor-color-black tutor-line-clamp-2 tutor-bundle-course-title">
					<?php echo esc_html( $course_title ); ?>
				</h2>
			</a>
			<p>
				<span class="tutor-color-muted"><?php esc_html_e( 'By', 'tutor-pro' ); ?></span>
				<a href="<?php echo esc_url( $profile_url ); ?>" target="_parent"><?php echo esc_html( $course_author ); ?></a>
			</p>
		</div>
	</li>
	<?php
endforeach;
?>
</ul>

