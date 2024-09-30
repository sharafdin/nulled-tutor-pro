<?php
/**
 * Initialize Calendar addon
 *
 * @package TutorPro\Addons
 * @subpackage Calendar
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.9.10
 */

namespace TUTOR_PRO_C;

use TUTOR\Permalink;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 */
class Init {
	//phpcs:disable
	public $version = TUTOR_C_VERSION;
	public $path;
	public $url;
	public $basename;
	public $calendar;
	//phpcs:enable

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		add_action( 'tutor_addon_before_enable_tutor-pro/addons/calendar/calendar.php', array( $this, 'update_permalink' ) );

		$addon_config = tutor_utils()->get_addon_config( tutor_pro_calendar()->basename );
		$is_enable    = (bool) tutor_utils()->avalue_dot( 'is_enable', $addon_config );
		if ( ! $is_enable ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_C_FILE );
		$this->url      = plugin_dir_url( TUTOR_C_FILE );
		$this->basename = plugin_basename( TUTOR_C_FILE );

		$this->load_tutor_calendar();
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
	 * Load addon classes.
	 *
	 * @return void
	 */
	public function load_tutor_calendar() {
		spl_autoload_register( array( $this, 'loader' ) );

		$this->calendar = new Tutor_Calendar();
	}

	/**
	 * Auto Load class and the files
	 *
	 * @param string $class_name class name.
	 */
	private function loader( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			$class_name = preg_replace(
				array( '/([a-z])([A-Z])/', '/\\\/' ),
				array( '$1$2', DIRECTORY_SEPARATOR ),
				$class_name
			);

			$class_name = str_replace( 'TUTOR_PRO_C' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}
}
