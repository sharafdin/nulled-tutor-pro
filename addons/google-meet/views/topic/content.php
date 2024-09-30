<?php
/**
 * Tutor google meet live lesson content
 *
 * Contains sortable event view with modal to update
 *
 * @since v.2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Models\EventsModel;

$plugin_data   = GoogleMeet::meta_data();
$event         = $data['event'];
$counter_index = isset( $data['counter_index'] ) ? $data['counter_index'] : '';
$event_details = get_post_meta( $event->ID, EventsModel::POST_META_KEYS[2], true );
$event_details = json_decode( $event_details );
?>
<div id="tutor-google-meet-lesson-<?php echo esc_attr( $event->ID ); ?>">
	<div id="tutor-lesson-<?php echo esc_attr( $event->ID ); ?>" class="course-content-item ui-sortable-handle" data-course_content_id="<?php echo esc_attr( $event->ID ); ?>">
		<div class="tutor-course-content-top tutor-d-flex tutor-align-center">
			<span class="tutor-icon-hamburger-menu tutor-cursor-move tutor-px-12"></span>
			<a href="javascript:;" class="" data-tutor-modal-target="tutor-google-meet-lesson-update-modal-ID">
				<?php echo sprintf( __( 'Google Meet %1$s: %2$s', 'tutor-pro' ), $counter_index, $event->post_title ); ?>
			</a>

			<div class="tutor-course-content-top-right-action">
				<a href="javascript:;" class="tutor-iconic-btn" data-tutor-modal-target="tutor-google-meet-lesson-update-modal-<?php echo esc_attr( $event->ID ); ?>">
					<span class="tutor-icon-edit" area-hidden="true"></span>
				</a>
				<a href="javascript:;" class="tutor-iconic-btn tutor-google-meet-list-delete" data-event-id="<?php echo esc_attr( $event_details->id ); ?>" data-meeting-post-id="<?php echo esc_attr( $event->ID ); ?>" data-item-reference="<?php echo esc_attr( 'tutor-google-meet-lesson-' . $event->ID ); ?>" data-tutor-modal-target="tutor-common-confirmation-modal">
					<span class="tutor-icon-trash-can-line" area-hidden="true"></span>
				</a>
			</div>
		</div>
	</div>
	<?php
        // Load modal.
		tutor_load_template_from_custom_path(
			$plugin_data['views'] . 'modal/dynamic-modal-content.php',
			array(
				'post-id'  => $event->ID,
				'modal_id' => 'tutor-google-meet-lesson-update-modal-' . $event->ID,
			),
			false
		);
		?>
</div>
