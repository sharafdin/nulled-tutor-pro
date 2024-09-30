<?php
/**
 * Manage google meet admin sub-menu
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Admin
 */

namespace TutorPro\GoogleMeet\Admin;

use TutorPro\GoogleMeet\GoogleMeet;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage admin side features
 */
class SubMenu {

	/**
	 * Register hooks & manage dependency
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		add_action( 'tutor_admin_register', __CLASS__ . '::register_menu' );
	}

	/**
	 * Register sub-menu
	 *
	 * @since v2.1.0
	 *
	 * @return void
	 */
	public static function register_menu() {
		add_submenu_page(
			'tutor',
			__( 'Google Meet', 'tutor-pro' ),
			__( 'Google Meet', 'tutor-pro' ),
			'manage_tutor_instructor',
			'google-meet',
			array( __CLASS__, 'render_view' )
		);
	}

	/**
	 * Render menu view
	 *
	 * @return void
	 */
	public static function render_view() {
		$plugin_data = GoogleMeet::meta_data();
		$file        = $plugin_data['views'] . 'pages/main.php';
		if ( file_exists( $file ) ) {
			tutor_load_template_from_custom_path(
				$file
			);
		} else {
			echo esc_html( $file . ' is not exists' );
		}
	}
}
