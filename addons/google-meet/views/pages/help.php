<?php
/**
 * Google meet FAQ page
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

?>
<div class="tutor-google-meet-help-content">
	<div class="tutor-admin-container tutor-admin-container-sm">
		<div class="">
			<?php if ( is_admin() ) : ?>
				<div class="tutor-zoom-page-title tutor-mb-16">
					<div class="tutor-fs-4 tutor-fw-medium tutor-color-black"><?php _e( 'FAQ', 'tutor-pro' ); ?></div>
				</div>
			<?php endif; ?>

			<div class="tutor-accordion tutor-accordion-google-meet-help tutor-mt-24">
				<div class="tutor-accordion-item">
					<div class="tutor-accordion-item-header tutor-card tutor-mb-16">
						<span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
						<span class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24">
							<?php echo esc_html_e( 'How do I connect Google Meet with my LMS Website?', 'tutor-pro' ); ?>
						</span>
					</div>

					<div class="tutor-accordion-item-body" style="display: none;">
						<div class="tutor-accordion-item-body-content">
							<div class="tutor-fs-7 tutor-color-secondary">
								<?php
								$content  = _x( 'To integrate with Google Meet, go to this', 'google meet instruction', 'tutor-pro' );
								$content .= '<a href="https://console.cloud.google.com/apis/dashboard" target="_blank"> ' . _x( 'link', 'google meet instruction', 'tutor-pro' ) . ' </a>';
								$content .= _x( 'o create your OAuth Access Credentials. During this process, copy the link from the Set API Tab and paste it as your Redirect URI. For a more detailed guide, please refer to our ', 'google meet instruction', 'tutor-pro' );
								$content .= '<a href="https://docs.themeum.com/tutor-lms/addons/google-meet-integration/" target="_blank"> ' . _x( 'documentation', 'google meet instruction', 'tutor-pro' ) . ' </a>';
								echo html_entity_decode( $content );//phpcs:ignore
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="tutor-accordion-item">
					<div class="tutor-accordion-item-header tutor-card tutor-p-16 tutor-mb-16">
						<span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
						<span class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24">
							<?php esc_html_e( 'How do I create a Live Lesson on Tutor LMS?', 'tutor-pro' ); ?>
						</span>
					</div>

					<div class="tutor-accordion-item-body" style="display: none;">
						<div class="tutor-accordion-item-body-content">
							<div class="tutor-fs-7 tutor-color-secondary">
								<?php
								$live_lesson_content  = _x( 'You can create a live lesson by going into the course editor for any Tutor LMS course. There, you will see a section for Google Meet where you can schedule a Google Meet meeting. You can also add lesson-specific meetings by navigating into any topic and selecting the ', 'google meet live lesson FAQ', 'tutor-pro' );
								$live_lesson_content .= '<strong> ' . _x( 'Google Meet Live Lesson', 'google meet live lesson FAQ', 'tutor-pro' ) . ' </strong>';
								$live_lesson_content .= __( 'option', 'tutor-pro' );
								echo html_entity_decode( $live_lesson_content );

								?>
							</div>
						</div>
					</div>
				</div>
				<div class="tutor-accordion-item">
					<div class="tutor-accordion-item-header tutor-card tutor-p-16 tutor-mb-16">
						<span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
						<span class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24">
							<?php esc_html_e( 'How do I notify students about live lessons?', 'tutor-pro' ); ?>
						</span>
					</div>

					<div class="tutor-accordion-item-body" style="display: none;">
						<div class="tutor-accordion-item-body-content">
							<div class="tutor-fs-7 tutor-color-secondary">
								<?php
								esc_html_e( 'You can notify students about live lessons using Email Notifications of Tutor LMS and from the Google Meet settings on Tutor LMS frontend and backend.', 'tutor-pro' );
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="tutor-accordion-item">
					<div class="tutor-accordion-item-header tutor-card tutor-p-16 tutor-mb-16">
						<span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
						<span class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24">
							<?php esc_html_e( 'Do I need a Google account to integrate Google Meet with Tutor LMS?', 'tutor-pro' ); ?>
						</span>
					</div>

					<div class="tutor-accordion-item-body" style="display: none;">
						<div class="tutor-accordion-item-body-content">
							<div class="tutor-fs-7 tutor-color-secondary">
								<?php
								esc_html_e( 'Yes, you would need a Google Account to go through the entire process of setting up Google Meet with Tutor LMS. You will also need a Google account to host meetings with Google Meet.', 'tutor-pro' );
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="tutor-accordion-item">
					<div class="tutor-accordion-item-header tutor-card tutor-p-16 tutor-mb-16">
						<span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
						<span class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24">
							<?php esc_html_e( 'What Equipment Do I Need To Hold a Live Class?', 'tutor-pro' ); ?>
						</span>
					</div>

					<div class="tutor-accordion-item-body" style="display: none;">
						<div class="tutor-accordion-item-body-content">
							<div class="tutor-fs-7 tutor-color-secondary">
								<?php
								esc_html_e( 'You will need a Microphone, a PC running Windows or Mac OS, and preferably a Webcam to effectively hold a live class.', 'tutor-pro' );
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
