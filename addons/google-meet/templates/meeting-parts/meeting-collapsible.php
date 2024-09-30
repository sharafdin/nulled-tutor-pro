<?php
/**
 * Show ongoing meeting on the course details page's
 * course info tab
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Templates
 */

use TutorPro\GoogleMeet\Models\EventsModel;

$course_id    = get_the_ID();
$sorting_args = array(
	'course_id'   => get_the_ID(),
	'search_term' => '',
	'author_id'   => '',
	'date'        => '',
);
$paging_args  = array(
	'limit'  => tutor_utils()->get_option( 'pagination_per_page' ),
	'offset' => 0,
);
$meetings     = EventsModel::get( 'active', $sorting_args, $paging_args, true );
?>
<?php if ( tutor_utils()->is_enrolled( $course_id ) || tutor_utils()->has_user_course_content_access( get_current_user_id(), $course_id ) ) : ?>
	<?php if ( is_array( $meetings['meetings'] ) && count( $meetings['meetings'] ) ) : ?>
		<div class="tutor-single-course-segment tutor-course-topics-wrap">
			<div class="tutor-course-topics-header">
				<div class="tutor-course-topics-header-left">
					<h4 class="tutor-segment-title">
						<?php esc_html_e( 'Live Google Meets', 'tutor-pro' ); ?>
					</h4>
				</div>
			</div>
		</div>
		<?php foreach ( $meetings['meetings'] as $key => $meeting ) : ?>
			<?php
				$event_details = json_decode( $meeting->event_details );
			?>
		<div class="tutor-course-topics-contents">
			<div class="tutor-course-topic tutor-google-meet-meeting <?php echo esc_attr( 0 === $key ? 'tutor-active' : '' ); ?>">
				<div class="tutor-course-title tutor-d-flex tutor-justify-between tutor-align-center">
					<div class="tutor-google-meet-meeting-detail">
						<h3>
							<?php echo esc_html( $meeting->post_title ); ?>
						</h3>
						<div class="tutor-d-flex">
							<p class="tutor-mr-32">
								<?php esc_html_e( 'Date:', 'tutor-pro' ); ?>
								<span>
									<?php echo esc_html( tutor_i18n_get_formated_date( $event_details->start_datetime ) ); ?>
								</span>
							</p>
							<p>
								<?php esc_html_e( 'Copy Link:', 'tutor-pro' ); ?>
								<i class="tutor-icon-copy tutor-copy-text" data-text="<?php echo esc_attr( $event_details->meet_link ); ?>"></i>
							</p>
						</div>
					</div>
					<div>
						<i class="tutor-icon-angle-right"></i>
					</div>
				</div>
				<div class="tutor-course-lessons" style="display: none;">
					<div class="tutor-time-countdown tutor-countdown-lg tutor-mt-32 tutor-px-32" data-datetime="<?php echo esc_attr( $event_details->end_datetime ); ?>" data-timezone="<?php echo esc_attr( $event_details->timezone ); ?>">
	
					</div>						
					<div class="tutor-d-flex tutor-justify-between tutor-align-center tutor-p-32">
						<p>
							<?php esc_html_e( 'Host Email:', 'tutor-pro' ); ?>
							<?php echo esc_html( $event_details->organizer->email ); ?>
						</p>
						<a href="<?php echo esc_url( $event_details->meet_link ); ?>" class="tutor-btn tutor-btn-outline-primary">
							<?php esc_html_e( 'Continue to Meeting', 'tutor-pro' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	<?php endif; ?>
<?php endif; ?>
