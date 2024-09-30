<?php
/**
 * Buddypress Integration Init
 *
 * @package TutorPro\Addons
 * @subpackage Buddypress
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_BP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//phpcs:ignore
class init {
	//phpcs:disable
	public $version = TUTOR_BP_VERSION;
	public $path;
	public $url;
	public $basename;

	// Module.
	private $buddypress_messages;
	private $buddypress_groups;
	private $buddypress_group_settings;
	//phpcs:enable

	/**
	 * Constructor
	 *
	 * @return void|null
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		$addon_config = tutor_utils()->get_addon_config( TUTOR_BP()->basename );
		$is_enable    = (bool) tutor_utils()->array_get( 'is_enable', $addon_config );
		$has_bp       = tutor_utils()->has_bp();
		if ( ! $is_enable || ! $has_bp ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_BP_FILE );
		$this->url      = plugin_dir_url( TUTOR_BP_FILE );
		$this->basename = plugin_basename( TUTOR_BP_FILE );

		add_action( 'bp_init', array( $this, 'load_group_extension' ) );
	}

	/**
	 * Auto loader
	 *
	 * @return void
	 */
	public function load_tutor_bp() {
		spl_autoload_register( array( $this, 'loader' ) );

		if ( bp_is_active( 'groups' ) ) {
			$this->buddypress_groups         = new BuddyPressGroups();
			$this->buddypress_group_settings = new BuddyPressGroupSettings();
		}
		if ( bp_is_active( 'messages' ) ) {
			$this->buddypress_messages = new BuddyPressMessages();
		}
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

			$class_name = str_replace( 'TUTOR_BP' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Load group extension
	 *
	 * @return void
	 */
	public function load_group_extension() {
		$this->load_tutor_bp();

		if ( bp_is_active( 'groups' ) && current_user_can( 'manage_tutor' ) ) {
			bp_register_group_extension( 'TUTOR_BP\BuddyPressGroupSettings' );
		}
	}

}
