<?php
/**
 * Handle google meet integration
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet
 */

namespace TutorPro\GoogleMeet;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains initialization data member & methods
 */
class Init {

	/**
	 * Init props, hooks
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		add_filter( 'tutor_addons_lists_config', __CLASS__ . '::register_addon' );
	}

	/**
	 * Register on the Addon list
	 *
	 * @since v2.1.0
	 *
	 * @param array $addons  available addons.
	 *
	 * @return array  addons list
	 */
	public static function register_addon( array $addons ): array {
		$new_addon = array(
			'name'        => __( 'Google Meet', 'tutor-pro' ),
			'description' => __( 'Connect Tutor LMS with Google Meet to host live online classes. Students can attend live classes right from the lesson page.', 'tutor-pro' ),
		);

		$meta_data = GoogleMeet::meta_data();
		$meta_data = array_merge( $new_addon, $meta_data );

		$addons[ $meta_data['basename'] ] = $meta_data;
		return $addons;
	}
}
