<?php
/**
 * Quiz Export Import Addon
 *
 * @package TutorPro/Addons
 * @subpackage QuizImportExport
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defined the tutor main file
 */
define( 'QUIZ_IMPORT_EXPORT_VERSION', '1.0.0' );
define( 'QUIZ_IMPORT_EXPORT_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_quiz_import_export_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_quiz_import_export_config( $config ) {
	$new_config   = array(
		'name'        => __( 'Quiz Export/Import', 'quiz-import-export' ),
		'description' => __( 'Save time by exporting/importing quiz data with easy options.', 'quiz-import-export' ),
	);
	$basic_config = (array) QUIZ_IMPORT_EXPORT();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( QUIZ_IMPORT_EXPORT_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'QUIZ_IMPORT_EXPORT' ) ) {
	/**
	 * Addon helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function QUIZ_IMPORT_EXPORT() {
		$info = array(
			'path'         => plugin_dir_path( QUIZ_IMPORT_EXPORT_FILE ),
			'url'          => plugin_dir_url( QUIZ_IMPORT_EXPORT_FILE ),
			'basename'     => plugin_basename( QUIZ_IMPORT_EXPORT_FILE ),
			'version'      => QUIZ_IMPORT_EXPORT_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return (object) $info;
	}
}

require 'classes/init.php';
new \QUIZ_IMPORT_EXPORT\Init();
