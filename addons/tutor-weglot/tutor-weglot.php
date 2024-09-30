<?php
/**
 * Plugin Name: Tutor Weglot
 * Description: Translate & manage multilingual courses for global reach with full edit control.
 * Author: Themeum
 * Version: 1.0.0
 * Author URI: http://themeum.com
 * Requires at least: 5.3
 * Tested up to: 6.1
 *
 * @package TutorPro\GoogleMeet
 */

namespace TutorPro\Weglot;

if ( ! class_exists( 'Weglot' ) ) {

	/**
	 * PluginStarter main class that trigger the plugin
	 */
	final class Weglot {

		/**
		 * Plugin meta data
		 *
		 * @since 1.0.0
		 *
		 * @var $plugin_data
		 */
		private static $meta_data = array();

		/**
		 * Plugin instance
		 *
		 * @since 1.0.0
		 *
		 * @var $instance
		 */
		public static $instance = null;

		/**
		 * Register hooks and load dependent files
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			add_filter( 'tutor_addons_lists_config', __CLASS__ . '::register_addon' );
		}

		/**
		 * Plugin meta data
		 *
		 * @since 1.0.0
		 *
		 * @return array  contains plugin meta data
		 */
		public static function meta_data(): array {
			self::$meta_data['url']       = plugin_dir_url( __FILE__ );
			self::$meta_data['path']      = plugin_dir_path( __FILE__ );
			self::$meta_data['basename']  = plugin_basename( __FILE__ );
			self::$meta_data['templates'] = trailingslashit( plugin_dir_path( __FILE__ ) . 'templates' );
			self::$meta_data['views']     = trailingslashit( plugin_dir_path( __FILE__ ) . 'views' );
			self::$meta_data['assets']    = trailingslashit( plugin_dir_url( __FILE__ ) . 'assets' );

			// set ENV DEV | PROD.
			self::$meta_data['env'] = 'DEV';
			return self::$meta_data;
		}

		/**
		 * Create and return instance of this plugin
		 *
		 * @return self  instance of plugin
		 */
		public static function instance() {
			// If tutor is not active then return.
			if ( ! function_exists( 'tutor' ) ) {
				return;
			}

			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Register on the Addon list
		 *
		 * @since 1.0.0
		 *
		 * @param array $addons  available addons.
		 *
		 * @return array  addons list
		 */
		public static function register_addon( array $addons ): array {
			$new_addon = array(
				'name'           => __( 'Weglot', 'tutor-pro' ),
				'description'    => __( 'Translate & manage multilingual courses for global reach with full edit control.', 'tutor-pro' ),
				'depend_plugins' => array( 'weglot/weglot.php' => 'Weglot' ),
				'disable_on_off' => true,
			);

			$meta_data = self::meta_data();
			$meta_data = array_merge( $new_addon, $meta_data );

			$addons[ $meta_data['basename'] ] = $meta_data;
			return $addons;
		}
	}
	// trigger.
	Weglot::instance();
}
