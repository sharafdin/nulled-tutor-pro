<?php
/**
 * Google meet backend main page
 *
 * Loads other pages as per active tab
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TutorPro\GoogleMeet\GoogleEvent\GoogleEvent;
use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Utilities\Utilities;

$google_client = new GoogleEvent();
$active_tab    = Utilities::active_tab();
$page_tab      = 'active-meeting' === $active_tab || 'expired' === $active_tab ? 'meetings' : $active_tab;

if ( ! $google_client->is_app_permitted() ) {
	// Filter sub pages.
	Utilities::not_permitted_sub_pages();

	// Only set-api page access enable if app not permitted.
	if ( 'meetings' === $page_tab ) {
		$active_tab = 'set-api';
		$page_tab   = 'set-api';
	}
}
?>
<div class="tutor-admin-wrap">
	<!-- navbar  -->
	<?php
		// Load navbar template.

		Utilities::tabs_key_value();
		$navbar_template = tutor()->path . 'views/elements/navbar.php';
		$navbar_data     = array(
			'page_title' => __( 'Google Meet', 'tutor-pro' ),
			'tabs'       => Utilities::tabs_key_value(),
			'active'     => $active_tab,
		);
		tutor_load_template_from_custom_path(
			$navbar_template,
			$navbar_data
		);
		?>
	<!-- navbar end -->

	<!-- sub-page  -->
	<div class="tutor-admin-body">
	<?php
	// Load page template.
	$plugin_data = GoogleMeet::meta_data();

	$template = trailingslashit( $plugin_data['views'] . 'pages' ) . $page_tab . '.php';

	if ( file_exists( $template ) ) {
		tutor_load_template_from_custom_path(
			$template
		);
	} else {
		tutor_utils()->tutor_empty_state(
			__( 'You are trying to access invalid page tab', 'tutor-pro' )
		);
	}
	?>
	</div>
	<!-- sub-page end -->
</div>

