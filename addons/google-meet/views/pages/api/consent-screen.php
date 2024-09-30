<?php
/**
 * Google meet consent screen
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TutorPro\GoogleMeet\GoogleMeet;

$plugin_data = GoogleMeet::meta_data();

?>
<div class="tutor-google-meet-consent-screen">
	<div class="tutor-card <?php echo is_admin() ? 'tutor-card-no-border' : ''; ?> tutor-text-center tutor-px-md-56 tutor-pt-md-72 tutor-pb-md-80 tutor-p-36">
		<div class="tutor-fs-4 tutor-color-black tutor-mb-24">
			<?php echo esc_html_e( 'The app is not permitted yet!', 'tutor-pro' ); ?>
		</div>
		<div class="tutor-fs-6 tutor-color-muted tutor-mb-56">
			<?php echo esc_html_e( 'Press the button to grant access to your google classroom. Please allow all required permission to make this app working perfectly.', 'tutor-pro' ); ?>
		</div>

		<div class="tutor-mb-52">
			<div class="tutor-round-box tutor-mb-16">
				<img class="tutor-img-responsive" src="<?php echo esc_url( trailingslashit( $plugin_data['assets'] . 'images' ) . 'google-calender-icon.svg' ); ?>" alt="Upload JSON file icon" />
			</div>
			<div class="tutor-fs-5 tutor-color-dark">
				<?php echo esc_html_e( 'Google Calender', 'tutor-pro' ); ?>
			</div>
		</div>
		<div>
			<a href="<?php echo esc_url( $data['consent_url'] ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-lg">
				<?php echo esc_html_e( 'Go To Google\'s Consent Screen', 'tutor-pro' ); ?>
			</a>
			<br>
			<a href="#" class="tutor-btn tutor-btn-outline-primary tutor-btn-lg tutor-mt-12" data-tutor-modal-target="tutor-google-meet-confirmation-modal">
				<?php esc_html_e( 'Reset Credential', 'tutor-pro' ); ?>
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
