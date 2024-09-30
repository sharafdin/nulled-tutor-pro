<?php
/**
 * Restrict Content PRO integration Addon Init
 *
 * @package TutorPro/Addons
 * @subpackage RestrictContentPro
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.5.6
 */

namespace TUTOR_RC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 */
class Init {
	//phpcs:disable
	public $version = TUTOR_RC_VERSION;
	public $path;
	public $url;
	public $basename;
	public $restrict_content;
	//phpcs:enable

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		add_filter( 'tutor_monetization_options', array( $this, 'tutor_monetization_options' ) );

		$addon_config = tutor_utils()->get_addon_config( TUTOR_RC()->basename );
		$monetize_by  = tutor_utils()->get_option( 'monetize_by' );
		$is_enable    = (bool) tutor_utils()->array_get( 'is_enable', $addon_config );
		$has_rc       = $this->has_rc();
		if ( ! $is_enable || ! $has_rc || 'restrict-content-pro' !== $monetize_by ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_RC_FILE );
		$this->url      = plugin_dir_url( TUTOR_RC_FILE );
		$this->basename = plugin_basename( TUTOR_RC_FILE );

		$this->load_addon();
	}

	/**
	 * Load Addon
	 *
	 * @return void
	 */
	public function load_addon() {
		spl_autoload_register( array( $this, 'loader' ) );
		$this->restrict_content = new RestrictContent();
	}

	/**
	 * Class autoloader
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

			$class_name = str_replace( 'TUTOR_RC' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Check has RC plugin
	 *
	 * @return boolean
	 */
	public function has_rc() {
		$activated_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$depends           = array( 'restrict-content-pro/restrict-content-pro.php' );
		return count( array_intersect( $depends, $activated_plugins ) ) == count( $depends );
	}

	/**
	 * Add monetization option to tutor settings.
	 *
	 * @param array $arr attributes.
	 *
	 * @return array
	 */
	public function tutor_monetization_options( $arr ) {
		$addon_config = tutor_utils()->get_addon_config( TUTOR_RC()->basename );
		$has_rc       = $this->has_rc();
		$is_enabled   = (bool) tutor_utils()->array_get( 'is_enable', $addon_config );

		/**
		 * Add RCP monetization option only if RCP & add-on enabled
		 *
		 * @since 2.1.4
		 */
		if ( $has_rc && $is_enabled ) {
			$arr['restrict-content-pro'] = __( 'Restrict Content Pro', 'tutor-pro' );
		}
		return $arr;
	}


}
