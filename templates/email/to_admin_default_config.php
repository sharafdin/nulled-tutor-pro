<?php
/**
 * E-mail template for test default email config.
 *
 * @since 2.5.0
 *
 * @package TutorPro\Addons
 * @subpackage Email\Views
 * @author Themeum
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
				<h2 class="tutor-email-heading"><?php esc_html_e( 'Default Email Heading', 'tutor-pro' ); ?></h2>
				<br>
				<p><?php esc_html_e( 'Hello John,', 'tutor-pro' ); ?></p>
				<p><?php esc_html_e( 'This email serves as a test message, allowing you to preview the default', 'tutor-pro' ); ?> <strong><?php esc_html_e( 'email configuration.', 'tutor-pro' ); ?></strong></p>
				<br/>
				<div class="tutor-email-buttons">
					<a href="#" class="tutor-email-button-bordered"><?php esc_html_e( 'Secondary', 'tutor-pro' ); ?></a>
					<a href="#" class="tutor-email-button"><?php esc_html_e( 'Primary', 'tutor-pro' ); ?></a>
				</div>
			</div>
			<?php require TUTOR_PRO()->path . 'templates/email/email_footer.php'; ?>
		</div>
	</div>
</body>
</html>
