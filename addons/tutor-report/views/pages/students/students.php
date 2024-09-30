<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;
use TUTOR_REPORT\PageController;
?>


<?php
$_search  = Input::get( 'search','' );
$_student = Input::get( 'student_id','' );

$page_ctrl = new PageController();

if ( ! $_student ) {
	$page_ctrl->handle_student_table_page();
} else {
	$page_ctrl->handle_student_profile_page();
}
