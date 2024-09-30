<?php
/**
 * E-mail template for instructor when his application received.
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

				<table class="tutor-email-datatable">
					<tr>
						<td><?php esc_html_e( 'Instructor Name:', 'tutor-pro' ); ?></td>
						<td><strong>{instructor_username}</strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Email Address:', 'tutor-pro' ); ?></td>
						<td><strong>{instructor_email}</strong></td>
					</tr>
				</table>
			</div>

			<?php require TUTOR_PRO()->path . 'templates/email/email_footer.php'; ?>

		</div>
	</div>
</body>

</html>
