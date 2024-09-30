<?php
/**
 * Course Bundle Addon
 *
 * @package TutorPro\CourseBundle
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

require_once tutor_pro()->path . '/vendor/autoload.php';

define( 'TUTOR_COURSE_BUNDLE_FILE', __FILE__ );
define( 'TUTOR_COURSE_BUNDLE_DIR', plugin_dir_path( __FILE__ ) );

new TutorPro\CourseBundle\Init();
