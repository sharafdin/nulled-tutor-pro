<?php
/**
 * Content Drip Addon Init
 *
 * @package TutorPro\Addons
 * @subpackage ContentDrip
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_CONTENT_DRIP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//phpcs:ignore
class init {
	//phpcs:disable
	public $version = TUTOR_CONTENT_DRIP_VERSION;
	public $path;
	public $url;
	public $basename;

	// Module.
	private $content_drip;
	//phpcs:enable

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		$addon_config = tutor_utils()->get_addon_config( TUTOR_CONTENT_DRIP()->basename );
		$is_enable    = (bool) tutor_utils()->array_get( 'is_enable', $addon_config );
		if ( ! $is_enable ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_CONTENT_DRIP_FILE );
		$this->url      = plugin_dir_url( TUTOR_CONTENT_DRIP_FILE );
		$this->basename = plugin_basename( TUTOR_CONTENT_DRIP_FILE );

		$this->load_content_drip();
	}

	/**
	 * Auto loader.
	 *
	 * @return void
	 */
	public function load_content_drip() {
		spl_autoload_register( array( $this, 'loader' ) );
		$this->content_drip = new ContentDrip();
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

			$class_name = str_replace( 'TUTOR_CONTENT_DRIP' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

}
