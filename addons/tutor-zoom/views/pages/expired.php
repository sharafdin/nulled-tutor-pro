<?php
/**
 * Zoom meeting expired list at frontend dashboard
 * 
 * This file is not actually for only expired meetings. It's reused in multiple place.
 * As a process of code unification we're now using single file for meeting list.
 * File name not changed in favor of frontend dashboard URL structure.
 * 
 * @since 1.9.4
 */
if ( ! defined( 'ABSPATH' ) )
exit;

$_filter = 'expired';

require dirname( __DIR__ ) . '/template/meeting-list-loader.php';
?>