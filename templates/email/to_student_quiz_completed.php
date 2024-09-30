<?php
/**
 * E-mail template for student when quiz completed.
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
						<td><?php esc_html_e( 'Your score:', 'tutor-pro' ); ?></td>
						<td><strong>{earned_marks}</strong> <?php esc_html_e( 'out of', 'tutor-pro' ); ?> <strong>{total_marks}</strong> {attempt_result} </td>
					</tr>
				</table>

				<div class="tutor-email-buttons">
					<a target="_blank" class="tutor-email-button" href="{attempt_url}"><?php esc_html_e( 'See Quiz Details', 'tutor-pro' ); ?></a>
				</div>

			</div>
		</div>
	</div>
</body>
</html>
