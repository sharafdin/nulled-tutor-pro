<?php
/**
 * Dynamic modal content
 *
 * This template is for using as edit meeting-template
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Modal
 */

use TutorPro\GoogleMeet\Models\EventsModel;
use TutorPro\GoogleMeet\Utilities\Utilities;

$event = get_post( $data['post-id'] );
if ( ! is_a( $event, 'WP_Post' ) ) {
	esc_html_e( 'Invalid post', 'tutor-pro' );
	return;
}

$details   = json_decode( get_post_meta( $event->ID, EventsModel::POST_META_KEYS[2], true ) );
$event_id  = $details->id;
$datetime  = Utilities::get_formatted_start_end_datetime( $details->start_datetime, $details->end_datetime );
$attendees = $details->attendees;
$course_id = $event->post_parent;

?>

<div class="tutor-modal tutor-modal-scrollable" id="<?php echo esc_attr( $data['modal_id'] ); ?>">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content">
				
				<div class="tutor-modal-header">
					<div class="tutor-modal-title">
						<?php esc_html_e( 'Google Meet', 'tutor-pro' ); ?>       
					</div>
					<button class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close="">
						<span class="tutor-icon-times" area-hidden="true"></span>
					</button>
				</div>
				<div class="tutor-modal-body tutor-modal-container">
					<div class="tutor-google-meet-form-controls">
                        <?php tutor_nonce_field(); ?>
						<input type="hidden" name="post-id" value="<?php echo esc_attr( $event->ID ); ?>">
						<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>">
						<input type="hidden" name="event-id" value="<?php echo esc_attr( $event_id ); ?>">
						<input type="hidden" name="attendees" value="<?php echo 'Yes' === $details->attendees ? 'Yes' : 'No'; ?>">
						<div class="tutor-mb-16">
							<label class="tutor-form-label">
								<?php echo esc_html_e( 'Meeting Name', 'tutor-pro' ); ?>
							</label>
							<input class="tutor-form-control" type="text" name="meeting_title" value="<?php echo esc_attr( $event->post_title ); ?>" placeholder="<?php echo esc_attr( 'Enter Meeting Name', 'tutor-pro' ); ?>">
						</div>

						<div class="tutor-mb-16">
							<label class="tutor-form-label">
								<?php echo esc_html_e( 'Meeting Summary', 'tutor-pro' ); ?>
							</label>
							<textarea class="tutor-form-control" type="text" name="meeting_summary" rows="4" placeholder="<?php echo esc_attr( 'Summary...', 'tutor-pro' ); ?>"><?php echo esc_textarea( $event->post_content ); ?></textarea>
						</div>

						<div class="tutor-mb-16">
							<label class="tutor-form-label">
								<?php echo esc_html_e( 'Meeting Time', 'tutor-pro' ); ?>
							</label>

							<div class="tutor-gmi-meeting-time">
								<div>
									<div class="tutor-v2-date-picker tutor-v2-date-picker-fd tutor-google-meet-start-date" style="width: 100%;" data-prevent_redirect="1" data-input_name="meeting_start_date" data-input_value="<?php echo esc_attr( $details->start_datetime ? tutor_get_formated_date( 'd-m-Y', $details->start_datetime ) : '' ); ?>" tutor-disable-past-date></div>
									<div class="tutor-form-wrap">
										<span class="tutor-icon-clock-line tutor-form-icon tutor-form-icon-reverse tutor-google-meet-start-time"></span>
										<input type="text" name="meeting_start_time" class="tutor-form-control tutor-google-meet-timepicker" data-name="meeting_time" autocomplete="off" placeholder="HH:MM PM" value="<?php echo esc_attr( $details->start_datetime ? tutor_get_formated_date( 'h:i A', $details->start_datetime ) : '' ); ?>" >
									</div>
								</div>
								<span class="tutor-icon-minus-o tutor-icon-minus-o tutor-fs-6"></span>
								<div>
									<div class="tutor-v2-date-picker tutor-v2-date-picker-fd tutor-google-meet-end-date" style="width: 100%;" data-prevent_redirect="1" data-input_name="meeting_end_date" data-input_value="<?php echo esc_attr( $details->end_datetime ? tutor_get_formated_date( 'd-m-Y', $details->end_datetime ) : '' ); ?>" tutor-disable-past-date></div>
									<div class="tutor-form-wrap">
										<span class="tutor-icon-clock-line tutor-form-icon tutor-form-icon-reverse tutor-google-meet-end-time"></span>
										<input type="text" name="meeting_end_time" data-name="meeting_time" class="tutor-form-control tutor-google-meet-timepicker" autocomplete="off" placeholder="HH:MM PM" value="<?php echo esc_attr( $details->end_datetime ? tutor_get_formated_date( 'h:i A', $details->end_datetime ) : '' ); ?>">
									</div>
								</div>
							</div>
						</div>

						<div class="tutor-mb-16 tutor-row">
							<div class="tutor-col-md-8 tutor-mb-md-0 tutor-mb-16">
								<label class="tutor-form-label">
									<?php esc_html_e( 'Timezone', 'tutor-pro' ); ?>
								</label>
								<select name="meeting_timezone" class="tutor-form-select">
									<?php foreach ( tutor_global_timezone_lists() as $key => $value ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $details->timezone, $key ); ?>>
											<?php echo esc_html( $value ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="tutor-mb-16">
							<div class="tutor-form-check">
								<input type="checkbox" id="meeting-attendees-enroll-students-<?php echo esc_attr( $event->ID ); ?>" name="meeting_attendees_enroll_students" value="Yes" class="tutor-form-check-input" <?php echo 'Yes' === $details->attendees ? 'checked' : ''; ?>>
								<label for="meeting-attendees-enroll-students-<?php echo esc_attr( $event->ID ); ?>">
									<?php echo esc_html_e( 'Add Enrolled Students as Attendees', 'tutor-pro' ); ?>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="tutor-modal-footer">
					<button type="button" class="tutor-btn tutor-btn-outline-primary" id="" data-tutor-modal-close="">
						<?php esc_html_e( 'Cancel', 'tutor-pro' ); ?>						
					</button>								
					<button type="submit" class="tutor-btn tutor-btn-primary tutor-gm-update-meeting">
						<?php echo esc_html_e( 'Update Meeting', 'tutor-pro' ); ?>						
					</button>
				</div>
		</div>
	</div>
</div>
