<?php
/**
 * Zoom meeting active list at frontend dashboard
 *
 * @since 1.9.4
 */
if ( ! defined( 'ABSPATH' ) )
exit;

$_filter = 'active';
require dirname( __DIR__ ) . '/template/meeting-list-loader.php';

do_action('tutor_zoom/after/meetings');
?>