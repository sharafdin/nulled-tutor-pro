<?php
/**
 * Replace account view
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TutorPro\GoogleMeet\GoogleMeet;

require __DIR__ . '/banner.php';
?>
<div class="tutor-card <?php echo esc_attr( is_admin() ? 'tutor-card-no-border' : '' ); ?> tutor-px-32 tutor-py-28 tutor-mt-24">
	<div class="tutor-d-md-flex tutor-justify-between tutor-align-center">
		<div>
			<div class="tutor-fs-4 tutor-fw-medium tutor-mb-0 tutor-color-black">
				<?php esc_html_e( 'Meet Account Activated', 'tutor-pro' ); ?>
			</div>
			<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
				<?php
				esc_html_e( 'You are currently connected to Meet', 'tutor-pro' );
				?>
			</div>
		</div>
		<div class="tutor-mt-md-0 tutor-mt-16">
			<a href="#" class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-target="tutor-google-meet-confirmation-modal">
				<?php esc_html_e( 'Reset Credential', 'tutor-pro' ); ?>
			</a>
			<a class="tutor-btn tutor-btn-outline-primary" href="<?php echo esc_url( $data['consent_url'] ); ?>">
				<?php esc_html_e( 'Change Account', 'tutor-pro' ); ?>
			</a>
		</div>
	</div>
</div>
<?php
// Load confirmation modal for resetting credential.
$plugin_data = GoogleMeet::meta_data();
tutor_load_template_from_custom_path(
	$plugin_data['views'] . 'modal/confirmation-modal.php',
	array(
		'action' => 'tutor_google_meet_reset_cred',
	)
);
?>
