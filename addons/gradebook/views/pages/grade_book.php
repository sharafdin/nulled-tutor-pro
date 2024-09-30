<?php
$page = "overview.php";
$sub_page = sanitize_text_field(tutor_utils()->array_get('sub_page', $_GET));

if( $sub_page ) {
	$page = $sub_page . ".php";
}

include TUTOR_GB()->path."views/pages/{$page}";
?>
