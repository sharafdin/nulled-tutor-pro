<?php
/**
 * Manage box
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\MetaBox
 */

namespace TutorPro\GoogleMeet\MetaBox;

use TutorPro\GoogleMeet\GoogleMeet;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage meta-box for google meet
 */
class MetaBox {

	/**
	 * Register hooks
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'tutor/frontend_course_edit/after/course_builder', array( $this, 'add_meta_box_frontend' ) );
	}

	/**
	 * Register meta box for course admin side
	 *
	 * @since v2.1.0
	 *
	 * @return void
	 */
	public function register_meta_box() {
		tutor_meta_box_wrapper(
			'tutor-google-meet',
			__( 'Google Meet', 'tutor-pro' ),
			array( $this, 'render_box_content' ),
			tutor()->course_post_type,
			'advanced',
			'default',
			'tutor-admin-post-meta'
		);
	}

	/**
	 * Render meta-box
	 *
	 * @since v2.1.0
	 *
	 * @return mixed  if echo false then return content.
	 */
	public function render_box_content() {

		$plugin_data   = GoogleMeet::meta_data();
		$meta_box_view = $plugin_data['views'] . 'metabox/index.php';
		if ( file_exists( $meta_box_view ) ) {
			tutor_load_template_from_custom_path( $meta_box_view, array(), false );
		} else {
			echo esc_html( $meta_box_view ) . esc_html__( ' file not exists', 'tutor-pro' );
		}
	}

	/**
	 * Add meta box on the front end course build
	 *
	 * @since v2.1.0
	 */
	public function add_meta_box_frontend() {
		course_builder_section_wrap( self::render_frontend_box_content(), __( 'Google Meet', 'tutor-pro' ) );
	}

	/**
	 * Frontend metabox content
	 *
	 * @since v2.1.0
	 *
	 * @return string
	 */
	public static function render_frontend_box_content() {

		$plugin_data   = GoogleMeet::meta_data();
		$meta_box_view = $plugin_data['views'] . 'metabox/index.php';

		ob_start();
		if ( file_exists( $meta_box_view ) ) {
			tutor_load_template_from_custom_path( $meta_box_view, array(), false );
		} else {
			echo esc_html( $meta_box_view ) . esc_html__( ' file not exists', 'tutor-pro' );
		}
		return ob_get_clean();
	}
}
