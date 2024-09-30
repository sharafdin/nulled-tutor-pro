<?php
/**
 * Create placeholder for social authentication buttons
 *
 * @package TutorPro\SocialLogin\Authentication
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\SocialLogin\Authentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook up & create placeholder
 *
 * @since 2.1.9
 */
class Placeholder {

	/**
	 * Register hooks
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_after_login_form_wrapper', __CLASS__ . '::create_placeholder' );
		add_action( 'tutor_after_registration_form_wrap', __CLASS__ . '::create_placeholder' );
		add_action( 'tutor_before_instructor_reg_form', __CLASS__ . '::set_instructor' );
		add_action( 'tutor_before_student_reg_form', __CLASS__ . '::unset_instructor' );
		add_action( 'tutor_before_login_form', __CLASS__ . '::unset_instructor' );
	}

	/**
	 * Function to set twitter_login_is_instructor for Twitter Login
	 *
	 * @since 2.1.10
	 *
	 * @return void
	 */
	public static function set_instructor() {
		set_transient( 'twitter_login_is_instructor', true );
	}

	/**
	 * Function to unset twitter_login_is_instructor for Twitter Login
	 *
	 * @since 2.1.10
	 *
	 * @return void
	 */
	public static function unset_instructor() {
		delete_transient( 'twitter_login_is_instructor' );
	}

	/**
	 * Create placeholder wrapper so that
	 * social authentication button can be placed here
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public static function create_placeholder(): void {
		// Return if user already logged-in.
		if ( get_current_user_id() ) {
			return;
		}
		$authentications = self::authentication_info();
		$twitter_btn_url = rtrim( tutor_utils()->tutor_dashboard_url(), '/' ) . '?twitter_oauth_verify=true';
		?>
		<style>
			#tutor-pro-twitter-login {
				width: 400px; background-color: #00acee; border-color: #00acee; font-weight: bold;
			}
		</style>
		<div id="tutor-pro-social-authentication" class="tutor-pt-24 tutor-d-flex tutor-flex-column tutor-align-center tutor-border-top-light" style="gap: 10px;">
			<?php
			foreach ( $authentications as $authentication ) {
				$is_enabled = tutor_utils()->get_option( $authentication['key'] );
				// If auth enabled then create placeholder.
				if ( $is_enabled ) {
					?>
					<div class="tutor-d-flex tutor-justify-center" id="<?php echo esc_html( $authentication['placeholder_id'] ); ?>">
						<?php if ( 'facebook' === $authentication['auth'] ) : ?>
							<div class="fb-login-button" data-width="400px" data-size="large" data-button-type="" data-layout="" data-auto-logout-link="false" data-use-continue-as="true" scope="public_profile,email" show-faces="false" onlogin="checkLoginState();"></div>
						<?php endif; ?>

						<?php if ( 'twitter' === $authentication['auth'] ) : ?>
							<a href="<?php echo $twitter_btn_url; ?>" class="tutor-btn tutor-btn-primary tutor-justify-center" id="tutor-pro-twitter-login">
								<span class="tutor-icon-brand-twitter"></span>
								<span>&nbsp;Sign In with Twitter</span>
							</a>
						<?php endif; ?>
					</div>
					<?php
				}
			}
			?>
		</div>
		<?php
	}

	/**
	 * Get available authentication info
	 *
	 * @since 2.1.9
	 *
	 * @return array
	 */
	public static function authentication_info() {
		return array(
			array(
				'auth'           => 'google',
				'placeholder_id' => 'tutor-pro-google-authentication',
				'key'            => 'enable_google_login',
			),
			array(
				'auth'           => 'facebook',
				'placeholder_id' => 'tutor-pro-facebook-authentication',
				'key'            => 'enable_facebook_login',
			),
			array(
				'auth'           => 'twitter',
				'placeholder_id' => 'tutor-pro-twitter-authentication',
				'key'            => 'enable_twitter_login',
			),
		);
	}
}
