<?php
/**
 * Google single meeting for the course content (spotlight)
 * section
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Template
 */

use TutorPro\GoogleMeet\GoogleEvent\Events;
use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Models\EventsModel;
use TutorPro\GoogleMeet\Utilities\Utilities;

global $post;

$enable_spotlight_mode = tutor_utils()->get_option( 'enable_spotlight_mode' );
$plugin_data           = GoogleMeet::meta_data();

// Get the ID of this content and the corresponding course.
$course_content_id = get_the_ID();
$course_id         = tutor_utils()->get_course_id_by( 'lesson', $course_content_id );

$args            = array(
	'ID' => $course_content_id,
);
$current_meeting = get_post( $course_content_id );
$event_details   = json_decode( get_post_meta( $course_content_id, EventsModel::POST_META_KEYS[2], true ) );
$start_datetime  = $event_details->start_datetime;
$end_datetime    = $event_details->end_datetime;
$timezone        = $event_details->timezone;

$meeting_end_time_utc = strtotime( Utilities::get_gmt_date_from_timezone_date( $end_datetime, $timezone ) );
$current_time         = time();

?>

<?php ob_start(); ?>
	<?php
	tutor_load_template(
		'single.common.header',
		array(
			'course_id'        => $course_id,
			'mark_as_complete' => $current_time > $meeting_end_time_utc,
		)
	);
	$is_req_prev_content_completion = apply_filters( 'tutor_google_meet/single/content', null );
	if ( $is_req_prev_content_completion ) {
		echo $is_req_prev_content_completion;
	} else {
		?>
	<div class="tutor-google-meeting-content tutor-p-80">
		<?php
		if ( $current_time > $meeting_end_time_utc ) {
			$template = $plugin_data['templates'] . 'meeting-parts/expired.php';
		} else {
			$template = $plugin_data['templates'] . 'meeting-parts/ongoing.php';
		}
		if ( file_exists( $template ) ) {
			tutor_load_template_from_custom_path(
				$template,
				array(
					'meeting'       => $current_meeting,
					'event_details' => $event_details,
				)
			);
		} else {
			echo esc_html( $template . ' not found' );
		}
		?>
	</div>
	<?php } ?>

<?php
$html_content = ob_get_clean();
tutor_load_template_from_custom_path(
	tutor()->path . '/templates/single-content-loader.php',
	array(
		'context'      => 'tutor-google-meet',
		'html_content' => $html_content,
	),
	false
);
