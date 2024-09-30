<?php
/*
Plugin Name: Tutor Course Preview
Plugin URI: https://www.themeum.com/product/tutor-course-preview
Description: Open some lesson to check course overview for guest
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-course-preview
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_CP_VERSION', '1.0.0');
define('TUTOR_CP_FILE', __FILE__);

/**
 * Showing config for addons central lists
 */
add_filter('tutor_addons_lists_config', 'tutor_course_preview_config');
function tutor_course_preview_config($config){
	$newConfig = array(
		'name'          => __('Course Preview', 'tutor-pro'),
		'description'   => __('Unlock some lessons for students before enrollment.', 'tutor-pro'),
	);
	$basicConfig = (array) TUTOR_CP();
	$newConfig = array_merge($newConfig, $basicConfig);

	$config[plugin_basename( TUTOR_CP_FILE )] = $newConfig;
	return $config;
}

if ( ! function_exists('TUTOR_CP')) {
	function TUTOR_CP() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_CP_FILE ),
			'url'               => plugin_dir_url( TUTOR_CP_FILE ),
			'basename'          => plugin_basename( TUTOR_CP_FILE ),
			'version'           => TUTOR_CP_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new TUTOR_CP\init();
$tutor->run(); //Boom