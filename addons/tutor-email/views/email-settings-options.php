<?php
/**
 * E-mail settings options
 *
 * @since 2.5.0
 * @author Themeum
 * @package TutorPro\Addons
 * @subpackage Email\Views
 */

$admin_url        = admin_url( 'admin.php' );
$setting_page_url = add_query_arg(
	array(
		'page'     => 'tutor_settings',
		'tab_page' => 'email_notification',
		'edit'     => 'settings',
	),
	$admin_url
);

$mailer_page_url = add_query_arg(
	array(
		'page'     => 'tutor_settings',
		'tab_page' => 'email_notification',
		'edit'     => 'mailer',
	),
	$admin_url
);
?>

<div class="tutor-card-list-item item-wrapper tutor-p-16 tutor-d-flex tutor-justify-between">
	<div class="tutor-d-flex">
		<div class="tutor-mr-20">
			<img style="width:148px;" 
				src="<?php echo esc_url( TUTOR_EMAIL()->url . 'assets/images/default-config-thumb.png' ); ?>"/>
		</div>

		<div>
			<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-8">
				<?php esc_html_e( 'Default Configuration', 'tutor-pro' ); ?>
			</div>
			<div class="tutor-fs-7 tutor-color-muted tutor-d-block">
				<?php esc_html_e( 'Configure logo, colors, sender email, and more for default system emails', 'tutor-pro' ); ?>
			</div>
		</div>
	</div>

	<div>
		<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" href="<?php echo esc_url( $setting_page_url ); ?>"><?php esc_html_e( 'Edit', 'tutor-pro' ); ?></a>
	</div>
</div>

<!-- End default config card -->

<div class="tutor-mt-16">
	<div class="tutor-card-list-item item-wrapper tutor-p-16 tutor-d-flex tutor-justify-between tutor-align-center">
		<div>
			<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-8">
				<?php esc_html_e( 'Manual Email', 'tutor-pro' ); ?>
			</div>
			<div class="tutor-fs-7 tutor-color-muted tutor-d-block">
				<?php esc_html_e( 'Create and send emails for custom events to specific recipient types', 'tutor-pro' ); ?>
			</div>
		</div>

		<div>
			<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" href="<?php echo esc_url( $mailer_page_url ); ?>"><?php esc_html_e( 'Compose Manual Email', 'tutor-pro' ); ?></a>
		</div>
	</div>
</div>
<!-- End manual email card -->
