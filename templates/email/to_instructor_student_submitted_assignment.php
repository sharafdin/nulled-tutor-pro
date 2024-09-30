<?php
/**
 * E-mail template for instructor when a student submit an assignment.
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
						<td class="label"><?php esc_html_e( 'Student Name:', 'tutor-pro' ); ?></td>
						<td><strong>{student_name}</strong></td>
					</tr>
					<tr>
						<td class="label"><?php esc_html_e( 'Course Name:', 'tutor-pro' ); ?></td>
						<td><strong>{course_name}</strong></td>
					</tr>
					<tr>
						<td class="label"><?php esc_html_e( 'Assignment Name:', 'tutor-pro' ); ?></td>
						<td><strong>{assignment_name}</strong></td>
					</tr>
				</table>

				<hr style="margin:20px 0;">

				<div data-source="email-before-button" class="tutor-email-before-button tutor-h-center email-mb-30">{before_button}</div>

				<div class="tutor-email-buttons tutor-h-center">
					<a href="{review_link}" class="tutor-email-button"><?php esc_html_e( 'Review Assignment', 'tutor-pro' ); ?></a>
				</div>

			</div>
			<?php require TUTOR_PRO()->path . 'templates/email/email_footer.php'; ?>
		</div>
	</div>
</body>
</html>
