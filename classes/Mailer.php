<?php
/**
 * A helper class to send e-mail.
 *
 * @package TutorPro
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailer Class
 *
 * @since 2.1.9
 */
class Mailer {

	/**
	 * Send e-mail
	 *
	 * @since 2.1.9
	 *
	 * @param string|string[] $to            single/multiple e-mail address.
	 * @param string          $subject       e-mail subject.
	 * @param string          $message       e-mail body message.
	 * @param string|string[] $header        set single/multiple additional header.
	 * @param string|string[] $attachments   single/multiple attachments where each are absolute file path.
	 *
	 * @return bool|array     if single to address it returns bool
	 *                        for multiple address it returns assoc array.
	 *                        ex: array( 'jhon@ex.com' => true, 'adam@ex.com' => false )
	 */
	public static function send_mail( $to, $subject, $message, $header = '', $attachments = array() ) {
		$obj        = new self();
		$is_enabled = tutils()->is_addon_enabled( TUTOR_EMAIL()->basename );

		add_filter( 'wp_mail_content_type', array( $obj, 'get_content_type' ) );

		if ( $is_enabled ) {
			add_filter( 'wp_mail_from', array( $obj, 'get_from_address' ) );
			add_filter( 'wp_mail_from_name', array( $obj, 'get_from_name' ) );
		}

		$to_array = is_array( $to ) ? $to : array( $to );
		$return   = array();

		foreach ( $to_array as $email ) {
			$return[ $email ] = wp_mail( $email, $subject, $message, $header, $attachments );
		}

		remove_filter( 'wp_mail_content_type', array( $obj, 'get_content_type' ) );

		if ( $is_enabled ) {
			remove_filter( 'wp_mail_from', array( $obj, 'get_from_address' ) );
			remove_filter( 'wp_mail_from_name', array( $obj, 'get_from_name' ) );
		}

		return is_array( $to ) ? $return : $return[ $to ];
	}

	/**
	 * Prepare HTML e-mail template with replaceable data.
	 *
	 * @since 2.1.9
	 *
	 * @param string $template_path    e-mail html template path.
	 * @param array  $data             an assoc array contains replaceable key and value.
	 *
	 * @return string prepared message.
	 */
	public static function prepare_template( string $template_path, array $data = array() ) {
		if ( ! file_exists( $template_path ) || ! tutils()->is_assoc( $data ) ) {
			return '';
		}

		ob_start();
		include $template_path;
		$string = ob_get_clean();

		return str_replace( array_keys( $data ), array_values( $data ), $string );
	}

	/**
	 * Get the from name for outgoing emails from tutor.
	 *
	 * @since 2.1.9
	 *
	 * @param string $from_email email from e-mail address.
	 *
	 * @return string
	 */
	public function get_from_address( $from_email ) {
		$email = tutor_utils()->get_option( 'email_from_address' );
		if ( empty( $email ) ) {
			$email = $from_email;
		}
		return sanitize_email( $email );
	}

	/**
	 * Get the from name for outgoing emails from tutor
	 *
	 * @since 2.1.9
	 *
	 * @param string $from_name from name of email.
	 *
	 * @return string
	 */
	public function get_from_name( $from_name ) {
		$name = tutor_utils()->get_option( 'email_from_name' );
		if ( empty( $name ) ) {
			$name = $from_name;
		}
		return wp_specialchars_decode( esc_html( $name ), ENT_QUOTES );
	}

	/**
	 * Filter callback to set email content type.
	 *
	 * @since 2.1.9
	 *
	 * @return string
	 */
	public function get_content_type() {
		return 'text/html';
	}
}
