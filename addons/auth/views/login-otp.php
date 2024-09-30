<?php
/**
 * OTP verify page.
 *
 * @package TutorPro\Auth
 * @subpackage Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

use Tutor\Helpers\SessionHelper;
use TutorPro\Auth\_2FA;
use TutorPro\Auth\Utils;

tutor_utils()->tutor_custom_header();

// Clear any login errors from tutor login page.
delete_transient( \TUTOR\Ajax::LOGIN_ERRORS_TRANSIENT_KEY );

$opt_data   = SessionHelper::get( 'tutor_login_otp' );
$email_hint = Utils::get_email_hint( $opt_data->user->user_email );

?>
<div <?php tutor_post_class( 'tutor-page-wrap' ); ?>>
	<div style="max-width:450px;margin: 0 auto;margin-top:80px;padding: 0 24px;">
		<form method="post" id="tutor-otp-form">

			<p class="tutor-mt-16 tutor-mb-20">
				<?php
					/* translators: %s: email address */
					$str = sprintf( __( 'We have sent an e-mail to your registered e-mail address (%s) with an OTP code.', 'tutor-pro' ), $email_hint );
					echo esc_html( $str );
				?>
				<?php
					esc_html_e( 'Please collect OTP and enter here to complete login process.', 'tutor-pro' );
				?>
			</p>
			<div class="tutor-d-flex">
				<input required="required" 
						name="tutor-login-otp" class="tutor-form-control" 
						type="number"
						autofocus
						placeholder="<?php esc_html_e( 'Enter OTP', 'tutor-pro' ); ?>">

				<button type="submit" class="tutor-btn tutor-btn-primary tutor-ml-8"><?php echo esc_html__( 'Submit', 'tutor-pro' ); ?></button>
			</div>
			<div class="tutor-mt-8">
				<a class="tutor-btn-rent-otp" href="#"><?php echo esc_html__( 'Resend e-mail', 'tutor-pro' ); ?></a>
				<div class="tutor-couter-div tutor-color-muted"><?php echo esc_html__( 'Resend e-mail after', 'tutor-pro' ); ?> <span class="tutor-resent-counter"></span> sec.</div>
			</div>
			<input type="hidden" id="ajax_url" value="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
		</form>
	</div>
</div>

<style>
	.tutor-disable-link{
		pointer-events: none;
		color:#999;
	}
</style>
<script>
	document.addEventListener('DOMContentLoaded', async function() {
		const { __ } = wp.i18n;

		const timeLimit = parseInt('<?php echo esc_html( _2FA::MINUTE_IN_SECONDS ); ?>');

		let otpForm = jQuery('#tutor-otp-form');
		let counterSpan = jQuery('.tutor-resent-counter');
		let counterDiv = jQuery('.tutor-couter-div');
		let btnOtp = jQuery('.tutor-btn-rent-otp');
		let interval = null;

		/**
		 * Run time left to resend OTP email again.
		 *
		 * @since 2.1.9
		 * 
		 * @return void
		 */
		function runCounter() {
			let counter = timeLimit;

			btnOtp.hide(0);
			counterDiv.show(0)
			counterSpan.text(counter)

			interval = setInterval(function(){
				counter--
				counterSpan.text(counter)
			},1000)

			setTimeout(()=>{
				clearInterval(interval)
				counterSpan.text('')
				counterDiv.hide()
				btnOtp.show(0)
			},timeLimit*1000)
		}

		/**
		 * Handle ajax request to resend email.
		 * 
		 * @since 2.1.9
		 *
		 * @return void
		 */
		function handleResentOtpAjax() {
			let ajaxUrl = jQuery('#ajax_url').val()
			let link = jQuery(this)
			let oldText = link.text()

			link.addClass('tutor-disable-link');
			link.text('Sending...')

			jQuery.ajax({
				url: ajaxUrl,
				postType:'JSON',
				data: { action: 'tutor_resent_login_otp' },
				method:'POST',
				success:function(res){
					if (res.success) {
						tutor_toast(__('Success!', 'tutor-pro'), res.data.message, 'success')
						runCounter()
					} else {
						tutor_toast(__('Sorry!', 'tutor-pro'), res.data.message, 'error')
					}
				},
				complete:function(){
					link.removeClass('tutor-disable-link');
					link.text(oldText)
				}
			})
		}

		/** 
		 * Handle OTP form submit
		 * 
		 * @since 2.1.9
		 * 
		 * @param {Event} e form submit event.
		 * 
		 * @return void
		 */
		function handleOTPform(e) {
			e.preventDefault();
			let ajaxUrl = jQuery('#ajax_url').val()
			let form = jQuery(this)
			let btn = form.find('button[type="submit"]')
			let otp = jQuery('input[name="tutor-login-otp"]').val()

			btn.attr('disabled','disabled').addClass('is-loading')

			jQuery.ajax({
				url: ajaxUrl,
				postType:'JSON',
				data: { action: 'tutor_verify_login_otp', otp:otp },
				method:'POST',
				success:function(res){
					if (res.success) {
						window.location = res.data.redirect_url + "?nocache=" + (new Date()).getTime()
						tutor_toast(__('Success!', 'tutor-pro'), res.data.message, 'success')
					} else {
						tutor_toast(__('Sorry!', 'tutor-pro'), res.data.message, 'error')
					}
				},
				complete:function(){
					btn.removeAttr('disabled').removeClass('is-loading')
				}
			})
		}

		// Init the counter.
		runCounter()
		btnOtp.click(handleResentOtpAjax)
		otpForm.submit(handleOTPform)
	})
</script>
<?php
SessionHelper::unset( 'tutor_otp_error' );
tutor_utils()->tutor_custom_footer();
