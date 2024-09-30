<?php
/**
 * Enrollment Addon Init
 *
 * @package TutorPro\Addons
 * @subpackage Enrollment
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_ENROLLMENTS;

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//phpcs:ignore
class init {
	//phpcs:disable
	public $version = TUTOR_ENROLLMENTS_VERSION;
	public $path;
	public $url;
	public $basename;

	// Module.
	private $enrollments;
	public $enrollment_list;
	//phpcs:enable

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		$addon_config = tutor_utils()->get_addon_config( TUTOR_ENROLLMENTS()->basename );
		$is_enable    = (bool) tutor_utils()->array_get( 'is_enable', $addon_config );
		if ( ! $is_enable ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_ENROLLMENTS_FILE );
		$this->url      = plugin_dir_url( TUTOR_ENROLLMENTS_FILE );
		$this->basename = plugin_basename( TUTOR_ENROLLMENTS_FILE );

		add_action( 'admin_enqueue_scripts', array( $this, 'register_scritps' ) );

		$this->load_enrollment();
	}

	/**
	 * Register scripts
	 *
	 * @return void
	 */
	public function register_scritps() {
		if ( is_admin() && 'enrollments' === Input::get( 'page' ) ) {
			wp_enqueue_script( 'enrollment-js-script', TUTOR_ENROLLMENTS()->url . 'assets/js/enroll.js', array( 'jquery', 'wp-i18n' ), TUTOR_PRO_VERSION, true );
			wp_enqueue_style( 'enrollment-css-script', TUTOR_ENROLLMENTS()->url . 'assets/css/enroll.css', array(), TUTOR_PRO_VERSION );
		}
	}

	/**
	 * Auto loader.
	 *
	 * @return void
	 */
	public function load_enrollment() {
		spl_autoload_register( array( $this, 'loader' ) );
		$this->enrollments     = new Enrollments();
		$this->enrollment_list = new Enrollments_List();
	}

	/**
	 * Auto Load class and the files
	 *
	 * @param string $class_name class name.
	 *
	 * @return void
	 */
	private function loader( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			$class_name = preg_replace(
				array( '/([a-z])([A-Z])/', '/\\\/' ),
				array( '$1$2', DIRECTORY_SEPARATOR ),
				$class_name
			);

			$class_name = str_replace( 'TUTOR_ENROLLMENTS' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

}
