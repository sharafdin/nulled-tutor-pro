<?php
/**
 * ChatGPT integration for content generation
 *
 * @package TutorPro/Addons
 * @subpackage ChatGPT
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once tutor_pro()->path . '/vendor/autoload.php';

define( 'TUTOR_CHATGPT_FILE', __FILE__ );
define( 'TUTOR_CHATGPT_DIR', plugin_dir_path( __FILE__ ) );

new TutorPro\ChatGPT\Init();
