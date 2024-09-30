<?php
/**
 * E-mail settings handler.
 *
 * @package TutorPro
 * @subpackage Addons\TutorEmail
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.5.0
 */

namespace TUTOR_EMAIL;

use TUTOR\Input;
use Tutor\Traits\JsonResponse;
use TUTOR\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EmailSettings
 *
 * @since 2.5.0
 */
class EmailSettings {
	use JsonResponse;

	/**
	 * Dedicated option key for email default config.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'tutor_email_default_config';

	/**
	 * E-mail template color option key.
	 *
	 * @var string
	 */
	const TEMPLATE_COLORS_KEY = 'email_template_colors';

	/**
	 * Register hooks.
	 *
	 * @since 2.5.0
	 *
	 * @param bool $reuse reuse the class or not.
	 *
	 * @return mixed
	 */
	public function __construct( $reuse = false ) {
		if ( $reuse ) {
			return;
		}

		add_filter( 'tutor_option_input', array( $this, 'merge_email_default_config_data' ) );
		add_action( 'wp_ajax_save_email_settings', array( $this, 'save_email_settings' ) );
	}

	/**
	 * Get email template colors data.
	 *
	 * @since 2.5.0
	 *
	 * @param bool $with_saved_values get saved values or not.
	 *
	 * @return array
	 */
	public static function get_colors_fields( $with_saved_values = true ) {
		$email_color_fields = array(
			'body_header'      => array(
				'header_background_color' => array(
					'id'      => 'header_background_color',
					'label'   => __( 'Header Background', 'tutor-pro' ),
					'default' => '#FFFFFF',
				),
				'header_divider_color'    => array(
					'id'      => 'header_divider_color',
					'label'   => __( 'Header Divider', 'tutor-pro' ),
					'default' => '#E0E2EA',
				),
			),
			'email_body'       => array(
				'body_background_color'  => array(
					'id'      => 'body_background_color',
					'label'   => __( 'Background', 'tutor-pro' ),
					'default' => '#FFFFFF',
				),
				'email_title_color'      => array(
					'id'      => 'email_title_color',
					'label'   => __( 'Email Title', 'tutor-pro' ),
					'default' => '#212327',
				),
				'email_text_color'       => array(
					'id'      => 'email_text_color',
					'label'   => __( 'Email Text', 'tutor-pro' ),
					'default' => '#5B616F',
				),
				'email_short_code_color' => array(
					'id'      => 'email_short_code_color',
					'label'   => __( 'Email Bold Text', 'tutor-pro' ),
					'default' => '#212327',
				),
				'footnote_color'         => array(
					'id'      => 'footnote_color',
					'label'   => __( 'Footnote Text', 'tutor-pro' ),
					'default' => '#A4A8B2',
				),
			),
			'primary_button'   => array(
				'primary_button_color'       => array(
					'id'      => 'primary_button_color',
					'label'   => __( 'Button Background', 'tutor-pro' ),
					'default' => '#3E64DE',
				),
				'primary_button_hover_color' => array(
					'id'      => 'primary_button_hover_color',
					'label'   => __( 'Background Hover', 'tutor-pro' ),
					'default' => '#395BCA',
				),
				'primary_button_text_color'  => array(
					'id'      => 'primary_button_text_color',
					'label'   => __( 'Text Color', 'tutor-pro' ),
					'default' => '#FFFFFF',
				),
			),
			'secondary_button' => array(
				'secondary_button_color'       => array(
					'id'      => 'secondary_button_color',
					'label'   => __( 'Button Background', 'tutor-pro' ),
					'default' => '#FFFFFF',
				),
				'secondary_button_hover_color' => array(
					'id'      => 'secondary_button_hover_color',
					'label'   => __( 'Background Hover', 'tutor-pro' ),
					'default' => '#395BCA',
				),
				'secondary_button_text_color'  => array(
					'id'      => 'secondary_button_text_color',
					'label'   => __( 'Text Color', 'tutor-pro' ),
					'default' => '#3E64DE',
				),
			),
		);

		if ( $with_saved_values ) {
			$template_color_options = tutor_utils()->get_option( self::TEMPLATE_COLORS_KEY, array() );
			foreach ( $email_color_fields as $key => &$field_group ) {
				foreach ( $field_group as $key => &$color ) {
					$color['value'] = $template_color_options[ $key ] ?? $color['default'];
				}
			}
		}

		return apply_filters( 'tutor_email_template_colors_fields', $email_color_fields );
	}

	/**
	 * Get all email template color list with key value.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public static function get_email_template_colors() {
		$fields     = self::get_colors_fields();
		$all_colors = array();
		foreach ( $fields as $field_group ) {
			foreach ( $field_group as $key => $color ) {
				$all_colors[ $key ] = $color;
			}
		}

		return apply_filters( 'tutor_email_template_colors', $all_colors );
	}

	/**
	 * Get template color option's value
	 *
	 * @since 2.5.0
	 *
	 * @param string $key color option key.
	 * @param array  $colors all available colors.
	 *
	 * @return string color value
	 */
	public static function get_color( $key, $colors ) {
		$selected = $colors[ $key ] ?? array();
		return $selected['value'] ?? $selected['default'];
	}

	/**
	 * E-mail default config data merge to tutor options before tutor settings saved.
	 *
	 * @since 2.5.0
	 *
	 * @param array $inputs inputs.
	 *
	 * @return array
	 */
	public function merge_email_default_config_data( $inputs ) {
		$email_default_config = get_option( self::OPTION_KEY );
		if ( false !== $email_default_config ) {
			$inputs = array_merge( $inputs, $email_default_config );
		}

		return $inputs;
	}

	/**
	 * Save email default settings.
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function save_email_settings() {
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			$this->response_fail( tutor_utils()->error_message() );
		}

		$tutor_email_template_logo_id = Input::post( 'tutor_email_template_logo_id' );
		$email_logo_alt_text          = Input::post( 'email_logo_alt_text' );
		$email_logo_position          = Input::post( 'email_logo_position' );
		$email_logo_height            = Input::post( 'email_logo_height' );
		$email_template_colors        = Input::post( 'email_template_colors', array(), Input::TYPE_ARRAY );

		$email_from_name                = Input::post( 'email_from_name' );
		$email_from_address             = Input::post( 'email_from_address' );
		$email_footer_text              = Input::post( 'email_footer_text', null, Input::TYPE_KSES_POST );
		$email_template_button_position = Input::post( 'email_template_button_position' );

		if ( empty( sanitize_email( $email_from_address ) ) ) {
			$this->response_fail( __( 'Invalid e-mail address', 'tutor-pro' ) );
		}

		$config = array(
			'tutor_email_template_logo_id'   => $tutor_email_template_logo_id,
			'email_logo_alt_text'            => $email_logo_alt_text,
			'email_logo_position'            => $email_logo_position,
			'email_logo_height'              => $email_logo_height,
			'email_template_colors'          => $email_template_colors,
			'email_from_name'                => $email_from_name,
			'email_from_address'             => $email_from_address,
			'email_footer_text'              => wp_json_encode( $email_footer_text ),
			'email_template_button_position' => $email_template_button_position,
		);

		update_option( self::OPTION_KEY, $config );

		// Merge the default config with tutor options.
		$email_default_config = get_option( self::OPTION_KEY );
		$tutor_option         = array_merge( get_option( 'tutor_option' ), $email_default_config );
		update_option( 'tutor_option', $tutor_option );

		$this->response_success( __( 'Saved successfully.', 'tutor-pro' ) );
	}

	/**
	 * Get email tinymce editor.
	 *
	 * @since 2.5.0
	 *
	 * @param string $content content.
	 * @param string $editor_id editor id.
	 *
	 * @return void
	 */
	public static function get_email_editor( $content, $editor_id ) {
		$args = array(
			'tinymce'       => array(
				'toolbar1' => 'bold, alignleft, aligncenter, alignright, separator, link, unlink, undo, redo, wp_adv, wp_help',
				'toolbar2' => 'italic, underline, separator, pastetext, removeformat, charmap, outdent, indent',
				'toolbar3' => '',
			),
			'media_buttons' => false,
			'quicktags'     => true,
			'elementpath'   => false,
			'wpautop'       => false,
			'statusbar'     => false,
			'editor_height' => 130,
		);

		wp_editor( $content, $editor_id, $args );
	}

	/**
	 * Get template default colors
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public static function get_template_default_colors() {
		$fields   = self::get_colors_fields( false );
		$defaults = array();

		foreach ( $fields as $field_group ) {
			foreach ( $field_group as $key => $color ) {
				$defaults[ $key ] = $color['default'];
			}
		}

		return $defaults;
	}
}
