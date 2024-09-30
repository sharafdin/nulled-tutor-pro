<?php
/**
 * E-mail template for student when remove from course.
 *
 * @package TutorPro
 * @subpackage Templates\Email
 *
 * @since 2.0.0
 */

?>
<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
	<?php require TUTOR_EMAIL()->path . 'views/email_styles.php'; ?>
</head>

<body>
	<div class="tutor-email-body">
		<div class="tutor-email-wrapper">
			<?php require TUTOR_PRO()->path . 'templates/email/email_header.php'; ?>
			<div class="tutor-email-content">

				<?php require TUTOR_PRO()->path . 'templates/email/email_heading_content.php'; ?>
				<div class="tutor-email-from">
					<p><?php esc_html_e( 'Regards', 'tutor-pro' ); ?>, </p>
					<p><strong>{site_name}</strong></p>
					<p>{site_url} </p>
				</div>
			</div><!-- .tutor-email-content -->
		</div>
	</div>
</body>
</html>
