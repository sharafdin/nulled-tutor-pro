<?php
/**
 * Course Bundle Addon Init
 *
 * @package TutorPro\CourseBundle
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle;

use TutorPro\CourseBundle\Backend\BundleList;
use TutorPro\CourseBundle\Backend\Menu;
use TutorPro\CourseBundle\CustomPosts\ManagePostMeta;
use TutorPro\CourseBundle\CustomPosts\RegisterPosts;
use TutorPro\CourseBundle\Frontend\BundleArchive;
use TutorPro\CourseBundle\Frontend\BundleBuilder;
use TutorPro\CourseBundle\Frontend\BundleDetails;
use TutorPro\CourseBundle\Frontend\Dashboard;
use TutorPro\CourseBundle\Frontend\DashboardMenu;
use TutorPro\CourseBundle\Frontend\MyBundleList;
use TutorPro\CourseBundle\Integrations\WooCommerce;
use TutorPro\CourseBundle\MetaBoxes\RegisterMetaBoxes;

/**
 * Init Class
 *
 * @since 2.2.0
 */
class Init {

	/**
	 * Register hooks and dependencies.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		add_filter( 'tutor_addons_lists_config', __CLASS__ . '::register_addon' );

		// Return if addon not enabled.
		if ( ! self::is_addon_enabled() ) {
			return;
		}

		// Return if has monetization requirement.
		$has_requirement = self::has_required_monetization();
		if ( $has_requirement['has'] ) {
			return;
		}

		$this->include_files();

		// Class instances.
		new Assets();
		new Menu();
		new Dashboard();
		new DashboardMenu();
		new RegisterPosts();
		new RegisterMetaBoxes();
		new BundleList();
		new MyBundleList();
		new Ajax();
		new ManagePostMeta();
		new BundleArchive();
		new BundleDetails();
		new BundleBuilder();

		// Integrations.
		new WooCommerce();
	}

	/**
	 * Register course bundle addon
	 *
	 * @since 2.2.0
	 *
	 * @param array $addons array of addons.
	 *
	 * @return array
	 */
	public static function register_addon( $addons ) {
		$required_settings = self::has_required_monetization();

		$new_addon = array(
			'name'              => __( 'Course Bundle', 'tutor-pro' ),
			'description'       => __( 'Group multiple courses to sell together.', 'tutor-pro' ),
			'path'              => TUTOR_COURSE_BUNDLE_DIR,
			'basename'          => plugin_basename( TUTOR_COURSE_BUNDLE_FILE ),
			'url'               => plugin_dir_url( TUTOR_COURSE_BUNDLE_FILE ),
			'required_settings' => $required_settings['has'],
			'required_title'    => $required_settings['title'],
			'required_message'  => $required_settings['message'],
		);

		$addons[ plugin_basename( $new_addon['basename'] ) ] = $new_addon;

		return $addons;
	}

	/**
	 * Check whether addon is enabled or not.
	 *
	 * @return boolean
	 */
	public static function is_addon_enabled() {
		$basename   = plugin_basename( TUTOR_COURSE_BUNDLE_FILE );
		$is_enabled = tutor_utils()->is_addon_enabled( $basename );
		return $is_enabled;
	}

	/**
	 * Check whether required monetization has enabled
	 *
	 * @since 2.2.0
	 *
	 * @return array of required settings & additional info
	 */
	private static function has_required_monetization(): array {
		$monetization = tutor_utils()->get_option( 'monetize_by', false );

		return array(
			'has'     => 'wc' !== $monetization,
			'title'   => __( 'WooCommerce Monetization is Required', 'tutor-pro' ),
			'message' => __( 'Please enable WooCommerce Monetization from Settings', 'tutor-pro' ),
		);
	}

	/**
	 * Include files.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	private function include_files() {

	}

}
