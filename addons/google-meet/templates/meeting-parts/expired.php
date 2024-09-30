<?php
/**
 * Contains expired meeting content that will be visible
 * on the course content (spotlight) section
 *
 * @since v.2.1.0
 *
 * @package TutorPro\GoogleMeet\Templates
 */

use TutorPro\GoogleMeet\GoogleMeet;

$meeting       = $data['meeting'];
$event_details = $data['event_details'];
$plugin_data   = GoogleMeet::meta_data();
?>
<div class="tutor-google-expired-meeting">
	<h2 class="tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mb-12">
		<?php echo esc_html( $meeting->post_title ); ?>
	</h2>
	<div class="tutor-card">
		<div class="tutor-d-flex tutor-justify-between tutor-p-32">
			<img src="<?php echo esc_url( $plugin_data['assets'] . 'images/google-meet-expired.png' ); ?>" alt="img">
			<div>
				<h3 class="tutor-fs-4 tutor-fw-medium tutor-color-black">
					<?php esc_html_e( 'The Meeting has expired', 'tutor-pro' ); ?>
				</h3>
				<p>
					<?php esc_html_e( 'Please contact your instructor for further information', 'tutor-pro' ); ?>
				</p>
			</div>
		</div>
	</div>
	<div class="tutor-card tutor-mt-32">
		<div class="tutor-d-flex tutor-justify-between tutor-p-32">
			<div>
				<p>
					<?php echo esc_textarea( $meeting->post_content ); ?>
				</p>
				<span>
					<?php esc_html_e( 'Meeting Date:', 'tutor-pro' ); ?>
				</span>
				<p class="tutor-fw-medium tutor-color-black">
					<?php echo esc_html( tutor_i18n_get_formated_date( $event_details->start_datetime ) ); ?>
				</p>
			</div>
			<div>
				<span>
					<?php echo esc_html_e( 'Host Email:', 'tutor-pro' ); ?>
				</span>
				<p class="tutor-fw-medium tutor-color-black">
					<?php echo esc_html( $event_details->organizer->email ); ?>
				</p>
			</div>
		</div>
	</div>
</div>
