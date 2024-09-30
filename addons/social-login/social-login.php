<?php
/**
 * Social Login Addon
 *
 * @package TutorPro/Addons
 * @subpackage SocialLogin
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 2.1.9
 */

namespace TutorPro;

use TutorPro\SocialLogin\Init;

if ( ! class_exists( 'SocialLogin' ) ) {

	/**
	 * PluginStarter main class that trigger the plugin
	 */
	final class SocialLogin {

		/**
		 * Plugin meta data
		 *
		 * @since 2.1.9
		 *
		 * @var array
		 */
		private static $meta_data = array();

		/**
		 * Plugin instance
		 *
		 * @since 2.1.9
		 *
		 * @var $instance
		 */
		public static $instance = null;

		/**
		 * Register hooks and load dependent files
		 *
		 * @since 2.1.9
		 *
		 * @return void
		 */
		public function __construct() {
			require_once tutor_pro()->path . '/vendor/autoload.php';

			$this->initialize_addon();
		}

		/**
		 * Plugin meta data
		 *
		 * @since 2.1.9
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
		 * Load packages
		 *
		 * @return void
		 */
		public function initialize_addon() {
			// Initialize addon.
			new Init();
		}
	}
	// trigger.
	SocialLogin::instance();
}
