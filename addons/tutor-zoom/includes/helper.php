<?php
/**
 * Zoom helper
 *
 * @package TutorPro\Zoom
 */

use TUTOR_ZOOM\Zoom;

/**
 * Check API connection
 *
 * @return mixed
 */
function tutor_zoom_check_api_connection() {
	$user_id    = get_current_user_id();
	$settings   = json_decode( get_user_meta( $user_id, 'tutor_zoom_api', true ), true );
	$api_key    = ( ! empty( $settings['api_key'] ) ) ? $settings['api_key'] : '';
	$api_secret = ( ! empty( $settings['api_secret'] ) ) ? $settings['api_secret'] : '';

	return ( $api_key && $api_secret );
}

/**
 * Get zoom meeting data by meeting post id
 *
 * @param int $meeting_id meeting post id.
 *
 * @return object
 */
function tutor_zoom_meeting_data( $meeting_id ) {
	$meeting_data   = get_post_meta( $meeting_id, '_tutor_zm_data', true );
	$meeting_data   = json_decode( stripslashes( $meeting_data ), true ); // json_decode( $meeting_data, true );
	$meeting_date   = isset( $meeting_data['start_time'] ) ? new DateTime( $meeting_data['start_time'], new DateTimeZone( 'UTC' ) ) : new DateTime();
	$countdown_date = $meeting_date->format( 'Y/m/d H:i:s' );
	$timezone       = isset( $meeting_data['timezone'] ) ? $meeting_data['timezone'] : 'UTC';
	$meeting_date->setTimezone( new DateTimeZone( $timezone ) );
	$start_date   = $meeting_date->format( 'j M, Y - h:i A' );
	$meeting_unix = $meeting_date->format( 'U' );
	$is_started   = ( $meeting_unix > time() ) ? false : true;
	$is_expired   = true;
	if ( isset( $meeting_data['duration'] ) ) {
		$is_expired = ( $meeting_unix + ( $meeting_data['duration'] * 60 ) > time() ) ? false : true;
	}

	return (object) array(
		'data'           => $meeting_data,
		'timezone'       => $timezone,
		'start_date'     => $start_date,
		'countdown_date' => $countdown_date,
		'is_started'     => $is_started,
		'is_expired'     => $is_expired,
	);
}
