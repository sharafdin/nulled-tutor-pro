<?php
/*
Plugin Name: Tutor Email Notification
Plugin URI: https://www.themeum.com/product/tutor-pmpro
Description: Allow Membership to your LMS website
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-pmpro
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_WCS_VERSION', '1.0.0');
define('TUTOR_WCS_FILE', __FILE__);

/**
 * Showing config for addons central lists
 */
add_filter('tutor_addons_lists_config', 'tutor_wcs_config');
function tutor_wcs_config($config){
	$newConfig = array(
		'name'          => __('WooCommerce Subscriptions', 'tutor-pro'),
		'description'   => __('Capture Residual Revenue with Recurring Payments.', 'tutor-pro'),
		'depend_plugins'   => array(
			'woocommerce/woocommerce.php' => 'WooCommerce',
			'woocommerce-subscriptions/woocommerce-subscriptions.php' => 'WooCommerce Subscriptions'
		),
	);
	$basicConfig = (array) TUTOR_WCS();
	$newConfig = array_merge($newConfig, $basicConfig);

	$config[plugin_basename( TUTOR_WCS_FILE )] = $newConfig;
	return $config;
}

if ( ! function_exists('TUTOR_WCS')) {
	function TUTOR_WCS() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_WCS_FILE ),
			'url'               => plugin_dir_url( TUTOR_WCS_FILE ),
			'basename'          => plugin_basename( TUTOR_WCS_FILE ),
			'version'           => TUTOR_WCS_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new \TUTOR_WCS\init();
$tutor->run(); //Boom