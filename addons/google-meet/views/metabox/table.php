<?php
/**
 * Meta box table
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TutorPro\GoogleMeet\GoogleMeet;

$plugin_data = GoogleMeet::meta_data();
?>
<div id="tutor-google-meet-list-item-<?php echo esc_attr( $data['post_id'] ); ?>">
	<div class="tutor-google-meet-list-item tutor-py-12" style="border-bottom: 1px solid #cdcfd5;">
		<div class="tutor-row">
			<div class="tutor-col-3 tutor-col-lg-3 tutor-mb-16 tutor-mb-lg-0">
				<div class="tutor-fs-7 tutor-color-secondary tutor-mb-8">
					<?php esc_html_e( 'Start Time ', 'tutor-pro' ); ?>                   
				</div>
				<div class="tutor-fs-6 tutor-color-black">
					<?php echo esc_html( tutor_i18n_get_formated_date( $data['start_datetime'] ) ); ?>                   
				</div>
			</div>

			<div class="tutor-col-4 tutor-col-lg-4 tutor-mb-16 tutor-mb-lg-0">
				<div class="tutor-fs-7 tutor-color-secondary tutor-mb-8">
					<?php echo esc_html_e( 'Meeting Title', 'tutor-pro' ); ?>
				</div>
				<div class="tutor-fs-6 tutor-color-black">
					<?php echo esc_html( $data['meeting_title'] ); ?>  
				</div>
			</div>
			<div class="tutor-col-5 tutor-col-lg-5 tutor-mb-16 tutor-mb-lg-0">
				<div class="tutor-d-flex tutor-align-center tutor-justify-end tutor-mt-16">
					<a href="<?php echo esc_url( $data['html_link'] ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mr-16">
						<span class="tutor-icon-brand-google-meet tutor-mr-8" area-hidden="true"></span>
						<span>
							<?php echo esc_html_e( 'Start Meeting', 'tutor-pro' ); ?>
						</span>
					</a>
					<?php
					// Unset unwanted data.

					?>
					<button type="button" class="tutor-iconic-btn tutor-mr-8 tutor-google-meet-list-edit" data-tutor-modal-target="tutor-google-meet-modal-<?php echo esc_attr( $data['post_id'] ); ?>">
						<span class="tutor-icon-pencil" area-hidden="true"></span>
					</button>
					<button type="button" class="tutor-iconic-btn tutor-mr-8 tutor-google-meet-list-delete" data-event-id="<?php echo esc_attr( $data['event_id'] ); ?>" data-meeting-post-id="<?php echo esc_attr( $data['post_id'] ); ?>" data-item-reference="tutor-google-meet-list-item-<?php echo esc_attr( $data['post_id'] ); ?>" data-tutor-modal-target="tutor-common-confirmation-modal">
						<span class="tutor-icon-trash-can" area-hidden="true"></span>
					</button>
				</div>
			</div>

		</div>
	</div>
</div>
<?php
tutor_load_template_from_custom_path(
	$plugin_data['views'] . 'modal/dynamic-modal-content.php',
	array(
		'post-id'  => $data['post_id'],
		'modal_id' => 'tutor-google-meet-modal-' . $data['post_id'],
	),
	false
);
?>
