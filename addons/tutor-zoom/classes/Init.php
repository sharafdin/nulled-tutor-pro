<?php
/**
 * Zoom Addon Init
 *
 * @package TutorPro\Addons
 * @subpackage Zoom
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_ZOOM;

use TUTOR\Permalink;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Init Class
 */
class Init {
	// phpcs:disable
	public $version = TUTOR_ZOOM_VERSION;
	public $path;
	public $url;
	public $basename;
	public static $_instance = null;
	public $zoom;
	//phpcs:enable

	/**
	 * Register hooks and initial task.
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		add_action( 'tutor_addon_before_enable_tutor-pro/addons/tutor-zoom/tutor-zoom.php', array( $this, 'update_permalink' ) );

		$addon_config = tutor_utils()->get_addon_config( TUTOR_ZOOM()->basename );
		$is_enable    = (bool) tutor_utils()->avalue_dot( 'is_enable', $addon_config );
		if ( ! $is_enable ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_ZOOM_FILE );
		$this->url      = plugin_dir_url( TUTOR_ZOOM_FILE );
		$this->basename = plugin_basename( TUTOR_ZOOM_FILE );

		$this->load_tutor_zoom();
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
	 * Instance
	 *
	 * @return mixed
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Load zoom addon.
	 *
	 * @return void
	 */
	public function load_tutor_zoom() {
		spl_autoload_register( array( $this, 'loader' ) );
		$this->zoom = new Zoom();
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

			$class_name = str_replace( 'TUTOR_ZOOM' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}
}
