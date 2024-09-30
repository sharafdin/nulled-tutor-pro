<?php
/*
Plugin Name: Tutor WPML
Plugin URI: https://www.themeum.com/product/tutor-pro
Description: Tutor LMS will make compatible with WPML 
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 5.7.2
Text Domain: tutor-pro
Domain Path: /languages/
*/

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Defined the tutor main file
 */
define('TUTOR_WPML_VERSION', '1.0.0');
define('TUTOR_WPML_FILE', __FILE__);


// Return when Tutor plugin not active
if(!is_plugin_active( 'tutor/tutor.php' )) return;


/**
 * Showing config for addons central lists
 */
add_filter('tutor_addons_lists_config', 'tutor_wpml_config');
function tutor_wpml_config($config){
	$newConfig = array(
		'name'          => __('WPML Multilingual CMS', 'tutor-pro'),
		'description'   => __('Create multilingual courses, lessons, dashboard and more for a global audience.', 'tutor-pro'),
		'depend_plugins'    => array('sitepress-multilingual-cms/sitepress.php' => 'WPML'),
	);
	$basicConfig        = (array) TUTOR_WPML();
	$newConfig          = array_merge($newConfig, $basicConfig);

    $baseName           = plugin_basename( TUTOR_WPML_FILE );
	$config[$baseName]  = $newConfig;

	return $config;
}

if ( ! function_exists('TUTOR_WPML')) {
	function TUTOR_WPML() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_WPML_FILE ),
			'url'               => plugin_dir_url( TUTOR_WPML_FILE ),
			'basename'          => plugin_basename( TUTOR_WPML_FILE ),
			'version'           => TUTOR_WPML_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);
		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new TUTOR_WPML\init();
$tutor->run(); //Boom