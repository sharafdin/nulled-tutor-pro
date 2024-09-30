<?php
/**
 * Courses
 *
 * @package Course List
 */

use TUTOR\Input;
use TUTOR_REPORT\PageController;

$page_ctrl = new PageController();

if ( Input::has( 'course_id' ) ) {
	$page_ctrl->handle_single_course_page();
} else {
	$page_ctrl->handle_course_table_page();
}
