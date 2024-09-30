<?php
/**
 * Report sales list
 *
 * @package Report
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR_REPORT\PageController;

$page_ctrl = new PageController();
$page_ctrl->handle_sales_page();
?>