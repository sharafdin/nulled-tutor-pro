<?php
/**
 * Handle Ajax Request.
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\Auth;

use Tutor\Helpers\SessionHelper;

/**
 * Ajax Class.
 *
 * @since 2.1.9
 */
class Ajax {
	/**
	 * Register hooks.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_nopriv_tutor_resent_login_otp', array( $this, 'resent_login_otp' ) );
	}

	/**
	 * Resent login OTP
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function resent_login_otp() {
		tutor_utils()->checking_nonce();

		$otp_data     = SessionHelper::get( 'tutor_login_otp' );
		$otp_time     = SessionHelper::get( 'resent_otp_at' );
		$current_time = time();

		if ( ! isset( $otp_time ) || ! isset( $otp_data->user ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid request', 'tutor-pro' ) )
			);
		}

		/**
		 * To get OTP again, user must wait 60 sec.
		 */
		if ( $otp_time > $current_time ) {
			wp_send_json_error(
				array( 'message' => __( 'Sorry! please try sometimes later', 'tutor-pro' ) )
			);
		}

		$new_otp        = Utils::generate_otp();
		$otp_data       = SessionHelper::get( 'tutor_login_otp' );
		
		$main_sent = Utils::sent_login_otp( $otp_data->user->user_email, $new_otp );
		if ( ! $main_sent ) {
			wp_send_json_error(
				array( 'message' => __( 'Mail sent failed. Try again later.', 'tutor-pro' ) )
			);
		}
		
		// Update session OTP.
		$otp_data->code = $new_otp;
		SessionHelper::set( 'tutor_login_otp', $otp_data );

		// Set new time limit for OTP resent.
		SessionHelper::set( 'resent_otp_at', time() + _2FA::MINUTE_IN_SECONDS );

		wp_send_json_success(
			array( 'message' => __( 'OTP sent successfull.', 'tutor-pro' ) )
		);
	}
}
