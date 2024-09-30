<?php
/**
 * Google meet dashboard main page
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Templates
 */

use TutorPro\GoogleMeet\GoogleEvent\GoogleEvent;
use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Utilities\Utilities;

global $wp_query;

$google_client = new GoogleEvent();

$query_vars    = $wp_query->query_vars;
$dashboard_url = tutor_utils()->tutor_dashboard_url();
$active_tab    = isset( $query_vars['tutor_dashboard_sub_page'] ) ? $query_vars['tutor_dashboard_sub_page'] : 'active-meeting';

$page_tab = 'active-meeting' === $active_tab || 'expired' === $active_tab ? 'meetings' : $active_tab;

if ( ! $google_client->is_app_permitted() ) {
	// Filter sub pages.
	Utilities::not_permitted_sub_pages();

	// Only set-api page access enable if app not permitted.
	if ( 'meetings' === $page_tab ) {
		$active_tab = 'set-api';
		$page_tab   = 'set-api';
	}
}
$sub_pages = Utilities::sub_pages();
?>
<div class="">
	<div class="tutor-google-meet-main-wrapper">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
			<?php echo esc_html_e( 'Google Meet', 'tutor-pro' ); ?>
		</div>
		<!-- navbar  -->
		<div class="tutor-mb-32">
			<ul class="tutor-nav" tutor-priority-nav="">
				<?php foreach ( $sub_pages as $key => $value ) : ?>
					<?php $active_class = $key === $active_tab ? 'is-active' : ''; ?>
					<li class="tutor-nav-item">
						<a href="<?php echo esc_url( "{$dashboard_url}google-meet/{$key}" ); ?>" class="tutor-nav-link <?php echo esc_attr( $active_class ); ?>">
							<?php echo esc_html( $value ); ?>                           
						</a>
					</li>
				<?php endforeach; ?>

				<li class="tutor-nav-item tutor-nav-more tutor-d-none">
					<a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4"><?php esc_html_e( 'More', 'tutor-pro'); ?></span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
					<ul class="tutor-nav-more-list tutor-dropdown"></ul>
				</li>

			</ul>
		</div>
		<!-- navbar end -->

		<!-- sub-page  -->
		<div class="tutor-google-meet-frontend-content">
		<?php
		// Load page template.
		$plugin_data = GoogleMeet::meta_data();
		$template    = trailingslashit( $plugin_data['views'] . 'pages' ) . $page_tab . '.php';
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
</div>
