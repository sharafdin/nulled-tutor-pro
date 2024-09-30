<?php
/**
 * E-mail template for user's email verification.
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
		<div class="tutor-email-wrapper" style="background-color: #fff;">


			<?php require TUTOR_PRO()->path . 'templates/email/email_header.php'; ?>
			<div class="tutor-email-content">

				<?php require TUTOR_PRO()->path . 'templates/email/email_heading_content.php'; ?>

				<div class="tutor-email-buttons">
					<a target="_blank" class="tutor-email-button" href="{link}" data-source="email-btn-url"><?php esc_html_e( 'Verify Email Address', 'tutor-pro' ); ?></a>
				</div>

				<div style="margin-top: 25px;">
					<p>
						{additional_text}
					</p>
					<a href="{link}">
						{link}
					</a>
				</div>

			</div>

			<?php require TUTOR_PRO()->path . 'templates/email/email_footer.php'; ?>
		</div>
	</div>
</body>
</html>
