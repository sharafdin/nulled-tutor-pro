<?php
/**
 * Report Addon Init
 *
 * @package TutorPro\Addons
 * @subpackage Report
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_REPORT;

use TUTOR\Permalink;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 */
//phpcs:ignore
class init {
	//phpcs:disable
	public $version = TUTOR_REPORT_VERSION;
	public $path;
	public $url;
	public $basename;

	public $report;
	public $analytics;
	public $course_analytics;
	public $export_analytics;

	public static $_instance = null;
	//phpcs:enable

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		add_action( 'tutor_addon_before_enable_tutor-pro/addons/tutor-report/tutor-report.php', array( $this, 'update_permalink' ) );

		$addon_config = tutor_utils()->get_addon_config( TUTOR_REPORT()->basename );
		$is_enable    = (bool) tutor_utils()->avalue_dot( 'is_enable', $addon_config );
		if ( ! $is_enable ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_REPORT_FILE );
		$this->url      = plugin_dir_url( TUTOR_REPORT_FILE );
		$this->basename = plugin_basename( TUTOR_REPORT_FILE );

		$this->load_tutor_report();
	}

	/**
	 * Update permalink during addon enable.
	 *
	 * @since 2.6.0
	 *
	 * @return void
	 */
	public function update_permalink() {
		Permalink::set_permalink_flag();
	}

	/**
	 * Load report addon
	 *
	 * @return void
	 */
	public function load_tutor_report() {
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register( array( $this, 'loader' ) );

		$this->report = new Report();
		/**
		 * Analytics class
		 *
		 * @since 1.9.8
		 */
		$this->analytics        = new Analytics();
		$this->course_analytics = new CourseAnalytics();
		$this->export_analytics = new ExportAnalytics();
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

			$class_name = str_replace( 'TUTOR_REPORT' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Single instance of tutor report
	 *
	 * @since 1.9.8
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}
