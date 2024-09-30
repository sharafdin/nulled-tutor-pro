<?php
namespace TUTOR_WCS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_WCS_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	private $paid_memberships_pro;

	function __construct() {
		if ( ! function_exists('tutor')){
			return;
		}

		$addonConfig = tutor_utils()->get_addon_config(TUTOR_WCS()->basename);

		$monetize_by = tutor_utils()->get_option('monetize_by');
		$isEnable = (bool) tutor_utils()->array_get('is_enable', $addonConfig);
		$has_wcs = tutor_utils()->has_wcs();

		if ( ! $isEnable || ! $has_wcs || $monetize_by !== 'wc' ){
			return;
		}

		$this->path = plugin_dir_path(TUTOR_WCS_FILE);
		$this->url = plugin_dir_url(TUTOR_WCS_FILE);
		$this->basename = plugin_basename(TUTOR_WCS_FILE);

		$this->load_TUTOR_WCS();
	}

	public function load_TUTOR_WCS(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->paid_memberships_pro = new WCSubscriptions();
	}

	/**
	 * @param $className
	 *
	 * Auto Load class and the files
	 */
	private function loader($className) {
		if ( ! class_exists($className)){
			$className = preg_replace(
				array('/([a-z])([A-Z])/', '/\\\/'),
				array('$1$2', DIRECTORY_SEPARATOR),
				$className
			);

			$className = str_replace('TUTOR_WCS'.DIRECTORY_SEPARATOR, 'classes'.DIRECTORY_SEPARATOR, $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name)  ) {
				require_once $file_name;
			}
		}
	}

	//Run the TUTOR right now
	public function run(){
		//
	}

}