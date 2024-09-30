<?php
/**
 * Spam protection using HonyPot method.
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\Auth;

use TUTOR\Input;

/**
 * HoneyPot Class.
 *
 * @since 2.1.9
 */
class HoneyPot {

	/**
	 * Add form input for check spam request.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public static function form_content( $field_name ) {
		?>
		<input type="text" style="display:none" autocomplete="off" value="" name="<?php echo esc_attr( $field_name ); ?>">
		<?php
	}

	/**
	 * Verify request using HonyPot technique.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public static function verify( $field_name ) {
		if ( ! empty( Input::post( $field_name ) ) ) {
			return new \WP_Error( 'tutor_sp_error', __( 'Spam request catched!', 'tutor-pro' ) );
		}
	}
}
