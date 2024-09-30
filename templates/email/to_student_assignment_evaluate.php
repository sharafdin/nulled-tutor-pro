<?php
/**
 * E-mail template for student when assignment is evaluated.
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

				<table class="tutor-email-datatable" width="100%">
					<tr>
						<td><?php esc_html_e( 'Your score:', 'tutor-pro' ); ?></td>
						<td><strong>{assignment_score}  <?php esc_html_e( 'out of', 'tutor-pro' ); ?> {assignment_max_mark} </strong></td>
					</tr>
				</table>

				<div class="tutor-panel-block">
					<p data-source="email-block-heading"><?php esc_html_e( 'Instructor Note', 'tutor-pro' ); ?></p>
					<p style="margin-bottom: 0;" data-source="email-block-content">{assignment_comment}</p>
				</div>

				<div class="tutor-email-buttons">
					<a target="_blank" class="tutor-email-button" href="{assignment_url}"><?php esc_html_e( 'Go to Assignment Review', 'tutor-pro' ); ?></a>
				</div>

			</div>
		</div>
	</div>
</body>
</html>
