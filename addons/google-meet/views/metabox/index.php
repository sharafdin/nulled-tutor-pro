<?php
/**
 * Meta box view for google meet
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TUTOR\Input;
use TutorPro\GoogleMeet\GoogleEvent\Events;
use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Models\EventsModel;
use TutorPro\GoogleMeet\Utilities\Utilities;

$course_id   = Input::get( 'course_ID', 0, Input::TYPE_INT );
if ( ! $course_id ) {
	$course_id = get_the_ID();
}

$new_post = get_post( $course_id );
if ( 'publish' !== $new_post->post_status && ! current_user_can( 'administrator' ) ) {
	return;
}

$lists       = Events::get_meetings( array( 'post_parent' => $course_id ) );
$plugin_data = GoogleMeet::meta_data();

?>

<div class="tutor-google-meet-meta-box-wrapper" id="tutor-google-meet-meta-box-wrapper">
	<div class="tutor-course-builder-google-meet-list">
		<?php if ( is_array( $lists ) && count( $lists ) ) : ?>
			<?php foreach ( $lists as $list ) : ?>
				<?php
					$details        = json_decode( get_post_meta( $list->ID, EventsModel::POST_META_KEYS[2], true ) );
					$start_datetime = get_post_meta( $list->ID, EventsModel::POST_META_KEYS[0], true );
					$end_datetime   = get_post_meta( $list->ID, EventsModel::POST_META_KEYS[1], true );

					$editable_info  = array(
						'meeting_title'   => $list->post_title,
						'meeting_summary' => $list->post_content,
						'start_date'      => $start_datetime ? tutor_get_formated_date( 'd-m-Y', $start_datetime ) : '',
						'start_time'      => $start_datetime ? tutor_get_formated_date( get_option( 'time_format' ), $start_datetime ) : '',
						'end_date'        => $end_datetime ? tutor_get_formated_date( get_option( 'date_format' ), $end_datetime ) : '',
						'end_time'        => $end_datetime ? tutor_get_formated_date( get_option( 'time_format' ), $end_datetime ) : '',
						'post_id'         => $list->ID,
						'event_id'        => $details->id,
						'attendees'       => $details->attendees,
						'timezone'        => $details->timezone,
						'start_datetime'  => $start_datetime,
						'end_datetime'    => $end_datetime,
						'html_link'       => $details->html_link,
					);
					$meta_box_table = $plugin_data['views'] . 'metabox/table.php';
					if ( file_exists( $meta_box_table ) ) {
						tutor_load_template_from_custom_path(
							$meta_box_table,
							$editable_info,
							false
						);
					}
					?>
								
			<?php endforeach; ?>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
	<div class="tutor-d-lg-flex tutor-align-center tutor-justify-sm-between tutor-google-meet-create-wrap tutor-my-12">
		<div class="tutor-d-flex tutor-align-center tutor-mb-lg-0 tutor-mb-16">
			<span class="tutor-icon-brand-google-meet tutor-fs-3 tutor-mr-8" style="color: #2e8cff" area-hidden="true"></span>
			<div class="tutor-fs-5 tutor-fw-medium tutor-color-secondary tutor-ml-8">
				<?php
					esc_html_e( 'Connect with your students using Google Meet', 'tutor-pro' );
				?>
							
			</div>
		</div>
		<div class="tutor-ml-lg-12 tutor-ml-0">
			<button class="tutor-btn tutor-btn-primary tutor-google-meet-new-meeting" data-tutor-modal-target="tutor-google-meet-create-modal">
				<span class="tutor-icon-brand-google-meet tutor-mr-8 tutor-google-meet-new-meeting" area-hidden="true"></span>
				<span class="tutor-google-meet-new-meeting">
					<?php esc_html_e( 'Create a Google Meet', 'tutor-pro' ); ?>
				</span>
			</button>
		</div>
	</div>
	<?php
	$plugin_data = GoogleMeet::meta_data();
	Utilities::load_template_as_modal(
		'tutor-google-meet-create-modal',
		$plugin_data['views'] . 'modal/meeting-create-update.php',
		__( 'Google Meet', 'tutor-pro' ),
		array(
			array(
				'label' => __( 'Cancel', 'tutor-pro' ),
				'class' => 'tutor-btn tutor-btn-outline-primary',
				'id'    => '',
				'type'  => 'button',
				'attr'  => 'data-tutor-modal-close',
			),
			array(
				'label' => __( 'Create Meeting', 'tutor-pro' ),
				'class' => 'tutor-btn tutor-btn-primary tutor-gm-create-new-meeting',
				'id'    => '',
				'type'  => 'submit',
			),
		),
		array(
			'course_id' => $course_id,
		),
	);

	// Delete confirmation modal.
	tutor_load_template_from_custom_path(
		tutor()->path . 'views/elements/common-confirm-popup.php',
		array(
			'message'           => __(
				'Do you want to delete? Google event will be deleted permanently.',
				'tutor-pro'
			),
			'additional_fields' => array(
				'event-id',
				'item-reference',
			),
			'disable_action_field' => true,
		),
		false
	);
	?>

</div>

