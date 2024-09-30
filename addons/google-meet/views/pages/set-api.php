<?php

/**
 * Google event API setup
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TUTOR\Input;
use TutorPro\GoogleMeet\GoogleEvent\GoogleEvent;
use TutorPro\GoogleMeet\GoogleMeet;

$plugin_data = GoogleMeet::meta_data();
$google_meet = new GoogleEvent();
?>

<div class="tutor-google-meet-setapi-content tutor-mt-76">
	<div class="<?php echo is_admin() ? esc_attr( 'tutor-admin-container tutor-admin-container-sm' ) : ''; ?>">
		<?php
		$view       = '';
		$params     = array();
		$credential = $google_meet->upload_dir . "{$google_meet->username}-credential.json";
		$error_msg  = __( 'Credential is not correct, refresh the page & upload again!', 'tutor-pro' );
		if ( ! $google_meet->is_credential_loaded() ) {
			$view = $plugin_data['views'] . 'pages/api/credential-form.php';
		} elseif ( ! $google_meet->is_app_permitted() ) {
		
			$code = Input::get( 'code', '' );
			if ( '' !== $code ) {

				$save_token = $google_meet->save_token( $code );
			
				if ( false !== $google_meet->is_app_permitted() ) {
					$view   = $plugin_data['views'] . 'pages/api/replace-account.php';
					$params = array(
						'consent_url' => $google_meet->get_consent_screen_url(),
					);

				} else {
					$view   = $plugin_data['views'] . 'pages/api/consent-screen.php';
					$params = array(
						'consent_url' => $google_meet->get_consent_screen_url(),
					);

				}
			} else {
				try {
					$view   = $plugin_data['views'] . 'pages/api/consent-screen.php';
					$params = array(
						'consent_url' => $google_meet->get_consent_screen_url(),
					);
				} catch ( \Throwable $th ) {
					if ( file_exists( $credential ) ) {
						unlink( $credential );
					}
					echo esc_html( $error_msg, 'tutor-pro' );
				}
			}
		} else {
			$view   = $plugin_data['views'] . 'pages/api/replace-account.php';
			$params = array(
				'consent_url' => $google_meet->get_consent_screen_url(),
			);
		}
		// Load view page.
		if ( file_exists( $view ) ) {
			tutor_load_template_from_custom_path(
				$view,
				$params,
				false
			);
		} else {
			echo esc_html( $view . ' not exists' );
		}
		?>
	</div>
</div>
