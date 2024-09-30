<?php
/**
 * Tutor google meet create form
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Modal
 */

use TutorPro\GoogleMeet\Settings\Settings;

$modal_id         = $data['modal_id'];
$default_timezone = Settings::get_settings( 'meeting_timezone' );
?>
<div class="tutor-google-meet-form-controls">
	<div class="tutor-mb-16">
		<label class="tutor-form-label">
			<?php echo esc_html_e( 'Meeting Name', 'tutor-pro' ); ?>
		</label>
		<input class="tutor-form-control" type="text" name="meeting_title" value="" placeholder="<?php echo esc_attr( 'Enter Meeting Name', 'tutor-pro' ); ?>">
	</div>

	<div class="tutor-mb-16">
		<label class="tutor-form-label">
			<?php echo esc_html_e( 'Meeting Summary', 'tutor-pro' ); ?>
		</label>
		<textarea class="tutor-form-control" type="text" name="meeting_summary" rows="4" placeholder="<?php echo esc_attr( 'Summary...', 'tutor-pro' ); ?>"></textarea>
	</div>

	<div class="tutor-mb-16">
		<label class="tutor-form-label">
			<?php echo esc_html_e( 'Meeting Time', 'tutor-pro' ); ?>
		</label>
		
		<div class="tutor-gmi-meeting-time">
			<div>
				<div class="tutor-v2-date-picker tutor-v2-date-picker-fd tutor-google-meet-start-date" style="width: 100%;" data-prevent_redirect="1" data-input_name="meeting_start_date" data-input_value="" tutor-disable-past-date></div>
				<div class="tutor-form-wrap">
					<span class="tutor-icon-clock-line tutor-form-icon tutor-form-icon-reverse tutor-google-meet-start-time"></span>
					<input type="text" name="meeting_start_time" class="tutor-form-control tutor-google-meet-timepicker" data-name="meeting_start_time" autocomplete="off" placeholder="HH:MM PM" value="" >
				</div>
			</div>
			<span class="tutor-icon-minus-o tutor-icon-minus-o tutor-fs-6"></span>
			<div>
				<div class="tutor-v2-date-picker tutor-v2-date-picker-fd tutor-google-meet-end-date" style="width: 100%;" data-prevent_redirect="1" data-input_name="meeting_end_date" data-input_value="" tutor-disable-past-date></div>
				<div class="tutor-form-wrap">
					<span class="tutor-icon-clock-line tutor-form-icon tutor-form-icon-reverse tutor-google-meet-end-time"></span>
					<input type="text" name="meeting_end_time" data-name="meeting_end_time" class="tutor-form-control tutor-google-meet-timepicker" value="" autocomplete="off" placeholder="HH:MM PM">
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-mb-16 tutor-row">
		<div class="tutor-col-md-8 tutor-mb-md-0 tutor-mb-16">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Timezone', 'tutor-pro' ); ?>
			</label>
			<select name="meeting_timezone" id="<?php echo esc_attr( $modal_id . '-timezone' ); ?>" class="tutor-form-select">
				<?php foreach ( tutor_global_timezone_lists() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $default_timezone, $key ); ?>>
						<?php echo esc_html( $value ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<?php
		$random = rand( 0, 10 );
	?>
	<div class="tutor-mb-16">
		<div class="tutor-form-check">
			<input type="checkbox" id="tutor-google-meet-attendees-<?php echo esc_attr( $random ); ?>" name="meeting_attendees_enroll_students" value="Yes" checked="checked" class="tutor-form-check-input">
			<label for="tutor-google-meet-attendees-<?php echo esc_attr( $random ); ?>">
				<?php echo esc_html_e( 'Add Enrolled Students as Attendees', 'tutor-pro' ); ?>
			</label>
		</div>
	</div>
</div>
