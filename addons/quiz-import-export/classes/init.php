<?php
/**
 * Quiz Export Import Addon Init
 *
 * @package TutorPro/Addons
 * @subpackage QuizImportExport
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.5.6
 */

namespace QUIZ_IMPORT_EXPORT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 */
class Init {
	//phpcs:disable
	public $version = QUIZ_IMPORT_EXPORT_VERSION;
	public $path;
	public $url;
	public $basename;
	private $quiz_import_export;
	//phpcs:enable

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		$addon_config = tutor_utils()->get_addon_config( QUIZ_IMPORT_EXPORT()->basename );
		$is_enable    = (bool) tutor_utils()->array_get( 'is_enable', $addon_config );
		if ( ! $is_enable ) {
			return;
		}

		$this->path     = plugin_dir_path( QUIZ_IMPORT_EXPORT_FILE );
		$this->url      = plugin_dir_url( QUIZ_IMPORT_EXPORT_FILE );
		$this->basename = plugin_basename( QUIZ_IMPORT_EXPORT_FILE );

		$this->load_quiz_import_export();
	}

	/**
	 * Load Addon
	 *
	 * @return void
	 */
	public function load_quiz_import_export() {
		spl_autoload_register( array( $this, 'loader' ) );
		$this->quiz_import_export = new QuizImportExport();
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

			$class_name = str_replace( 'QUIZ_IMPORT_EXPORT' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

}
