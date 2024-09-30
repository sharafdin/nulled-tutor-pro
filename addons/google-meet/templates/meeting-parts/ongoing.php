<?php
/**
 * Contains expired meeting content that will be visible
 * on the course content (spotlight) section
 *
 * @since v.2.1.0
 *
 * @package TutorPro\GoogleMeet\Templates
 */

$meeting       = $data['meeting'];
$event_details = $data['event_details'];
?>
<div class="tutor-google-meet-countdown-wrap">
	<p class="tutor-mb-24">
		<?php esc_html_e( 'Meeting Starts in', 'tutor-pro' ); ?>
	</p>
	<div class="tutor-time-countdown tutor-countdown-lg" data-datetime="<?php echo esc_attr( $event_details->start_datetime ); ?>" data-timezone="<?php echo esc_attr( $event_details->timezone ); ?>"></div>
	<div class="tutor-zoom-join-button-wrap">
		<a href="<?php echo esc_url( $event_details->meet_link ); ?>" target="_blank" class="tutor-btn tutor-btn-primary tutor-mb-40">
			<?php echo esc_html_e( 'Join Meeting', 'tutor-pro' ); ?>
		</a>
	</div>
</div>
<div class="tutor-google-meet-summary">
    <h2 class="tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mb-12">
		<?php echo esc_html( $meeting->post_title ); ?>
	</h2>
    <p>
        <?php echo esc_textarea( $meeting->post_content ); ?>
    </p>
	<div class="tutor-d-flex tutor-mt-32" style="column-gap: 50px">
		<div class="tutor-d-flex tutor-flex-column">
			<span class="tutor-mb-12"><?php esc_html_e( 'Meeting Start Date', 'tutor-pro' ); ?></span>
			<span class="tutor-mb-12"><?php esc_html_e( 'Meeting End Date', 'tutor-pro' ); ?></span>
			<span><?php esc_html_e( 'Host Email', 'tutor-pro' ); ?></span>
		</div>
		<div>
			<p class="tutor-fw-medium tutor-color-black tutor-mb-12"><?php echo esc_html( tutor_i18n_get_formated_date( $event_details->start_datetime ) ); ?></p>
			<p class="tutor-fw-medium tutor-color-black tutor-mb-12"><?php echo esc_html( tutor_i18n_get_formated_date( $event_details->end_datetime ) ); ?></p>
			<p class="tutor-fw-medium tutor-color-black"><?php echo esc_html( $event_details->organizer->email ); ?></p>
		</div>
	</div>
</div>
