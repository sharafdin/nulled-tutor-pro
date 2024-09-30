<?php
/**
 * Banner part for google meet set API
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TutorPro\GoogleMeet\GoogleMeet;

$plugin_data = GoogleMeet::meta_data();
?>
<div class="tutor-google-meet-api-banner tutor-card <?php echo is_admin() ? 'tutor-card-no-border':''?>">
	<div class="tutor-row tutor-align-center tutor-gx-xl-5">
		<div class="tutor-col-md-7 tutor-mb-32 tutor-mb-lg-0">
			<div class="tutor-p-lg-48 tutor-p-28">
				<div class="tutor-fs-3 tutor-mb-0 tutor-color-black">
					<?php
					echo sprintf(
						'%s <strong class="tutor-fw-medium">%s</strong> <br /> %s',
						__( 'Setup your', 'tutor-pro' ),
						__( 'Google Meet', 'tutor-pro' ),
						__( 'Integration', 'tutor-pro' )
					);
					?>
				</div>
				<div class="tutor-mt-12 tutor-fs-7 tutor-color-muted">
					<?php
					$content = _x( 'To integrate with Google Meet, go to this', 'google meet instruction', 'tutor-pro' );
					$content .= '<a href="https://console.cloud.google.com/apis/dashboard" target="_blank"> ' . _x( 'link', 'google meet instruction', 'tutor-pro' ) . ' </a>';
					$content .= _x( 'o create your OAuth Access Credentials. During this process, copy the link below and paste it as your Redirect URI. For a more detailed guide, please refer to our ', 'google meet instruction', 'tutor-pro' );
					$content .= '<a href="https://docs.themeum.com/tutor-lms/addons/google-meet-integration/" target="_blank"> ' . _x( 'documentation', 'google meet instruction', 'tutor-pro' ) . ' </a>';
					echo html_entity_decode( $content );//phpcs:ignore
					?>
				</div>
				<div class="tutor-clipboard-input-field tutor-mt-28">
					<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-copy" data-tutor-clipboard-copy-target="tutor-google-meet-redirect-url">
						<?php esc_html_e( 'Copy', 'tutor-pro' ); ?>
					</button>
					<input type="text" class="tutor-form-control" placeholder="" value="<?php echo esc_url( admin_url() . 'admin.php?page=google-meet&tab=set-api' ); ?>" id="tutor-google-meet-redirect-url" />
				</div>
			</div>
		</div>

		<div class="tutor-col-md-5 tutor-text-right">
			<img class="tutor-img-responsive" src="<?php echo esc_url( trailingslashit( $plugin_data['assets'] . 'images' ) . 'setup-google-meet-illustration.svg' ); ?>" alt="google-meet config">
		</div>

	</div>
</div>
