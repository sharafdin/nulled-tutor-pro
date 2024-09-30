<?php
/**
 * Zoom meeting both of active and expired list at backend dashboard
 *
 * @since 1.9.4
 */

if ( ! defined( 'ABSPATH' ) )
exit;

$current_page   = sanitize_text_field( tutor_utils()->array_get('sub_page', $_GET, '') );
$_filter    = $current_page == 'expired' ? 'expired' : 'active';

require dirname( __DIR__ ) . '/template/meeting-list-loader.php';

?>