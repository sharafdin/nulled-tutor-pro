<?php
/**
 * Authentication
 *
 * Author URI: http://themeum.com
 * Requires at least: 5.3
 * Tested up to: 6.1
 *
 * @package TutorPro\Auth
 */

require_once tutor_pro()->path . '/vendor/autoload.php';

define( 'TUTOR_AUTH_FILE', __FILE__ );
define( 'TUTOR_AUTH_DIR', plugin_dir_path( __FILE__ ) );

new TutorPro\Auth\Init();
