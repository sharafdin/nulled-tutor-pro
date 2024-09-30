<?php
/**
 * Paid Membership Pro Integration Init
 *
 * @package TutorPro\Addons
 * @subpackage PmPro
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.3.5
 */

namespace TUTOR_PMPRO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 */
class Init {
	//phpcs:disable
	public $version = TUTOR_PMPRO_VERSION;
	public $path;
	public $url;
	public $basename;
	private $paid_memberships_pro;
	//phpcs:enable

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		// Adding monetization options to core.
		add_filter( 'tutor_monetization_options', array( $this, 'tutor_monetization_options' ) );

		$addon_config = tutor_utils()->get_addon_config( TUTOR_PMPRO()->basename );
		$monetize_by  = tutor_utils()->get_option( 'monetize_by' );
		$is_enable    = (bool) tutor_utils()->array_get( 'is_enable', $addon_config );
		$has_pmpro    = tutor_utils()->has_pmpro();
		if ( ! $is_enable || ! $has_pmpro || 'pmpro' !== $monetize_by ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_PMPRO_FILE );
		$this->url      = plugin_dir_url( TUTOR_PMPRO_FILE );
		$this->basename = plugin_basename( TUTOR_PMPRO_FILE );

		$this->load_tutor_pmpro();
	}

	/**
	 * Load tutor pmpro
	 *
	 * @return void
	 */
	public function load_tutor_pmpro() {
		spl_autoload_register( array( $this, 'loader' ) );
		$this->paid_memberships_pro = new PaidMembershipsPro();
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

			$class_name = str_replace( 'TUTOR_PMPRO' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Paid membership pro label
	 *
	 * Check if main pmpro and Tutor's pmpro addons is activated or not
	 *
	 * @since 1.3.6
	 *
	 * @param array $arr attributes.
	 *
	 * @return mixed
	 */
	public function tutor_monetization_options( $arr ) {
		$is_addon_enabled = tutor_utils()->is_addon_enabled( TUTOR_PMPRO()->basename );
		$has_pmpro        = tutor_utils()->has_pmpro();
		if ( $has_pmpro && $is_addon_enabled ) {
			$arr['pmpro'] = __( 'Paid Memberships Pro', 'tutor-pro' );
		}
		return $arr;
	}

}
