<?php

/**
 * Class WPML init
 *
 * @package TUTOR
 *
 * @since v.1.9.1
 */

namespace TUTOR_WPML;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Addon init
 */
class init {

	public $version = TUTOR_WPML_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	private $wpml_duplicator;


	function __construct() {
		if (!function_exists('tutor')) {
			return;
		}
		$addonConfig 	= tutor_utils()->get_addon_config(TUTOR_WPML()->basename);
		$isEnable 		= (bool) tutor_utils()->avalue_dot('is_enable', $addonConfig);
		if (!$isEnable) {
			return;
		}
		$this->path 	= plugin_dir_path(TUTOR_WPML_FILE);
		$this->url 		= plugin_dir_url(TUTOR_WPML_FILE);
		$this->basename = plugin_basename(TUTOR_WPML_FILE);

		$this->load_TUTOR_WPML();
	}


	/**
	 * Tutor LMS autoload
	 *
	 * @return null
	 */
	public function load_TUTOR_WPML() {
		// SPL Autoloader
		spl_autoload_register(array($this, 'loader'));
		$this->wpml_duplicator = new Wpml_Translation();
	}


	/**
	 * Auto Load class and the files
	 *
	 * @param $className
	 */
	private function loader($className) {
		if (class_exists($className)) {
			return;
		}

		$className = preg_replace(
			array('/([a-z])([A-Z])/', '/\\\/'),
			array('$1$2', DIRECTORY_SEPARATOR),
			$className
		);

		// Make file path
		$className = str_replace('TUTOR_WPML' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $className);
		$file_name = $this->path . $className . '.php';

		// Load class
		if (file_exists($file_name) && is_readable($file_name)) {
			require_once $file_name;
		}
	}


	/**
	 * Register tutor addon
	 *
	 * Run the TUTOR right now
	 *
	 * @return null
	 */
	public function run() {
		register_activation_hook(TUTOR_WPML_FILE, array($this, 'tutor_activate'));
	}


	/**
	 * Compare Tutor wpml version with current Tutor wpml version
	 *
	 * Update Tutor WPML version
	 *
	 * @return null
	 */
	public function tutor_activate() {
		$version = get_option('TUTOR_WPML_VERSION');
		if (version_compare($version, TUTOR_WPML_VERSION, '<')) {
			update_option('TUTOR_WPML_VERSION', TUTOR_WPML_VERSION);
		}
	}
}
