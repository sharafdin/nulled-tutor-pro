<?php
/**
 * E-mail template for student when comment replied.
 *
 * @package TutorPro
 * @subpackage Templates\Email
 *
 * @since 2.5.0
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

				<div class="tutor-user-info">
					<div class="tutor-user-info-wrap">
						<div class="answer-block">
							<p class="answer-content">
							<?php
							// Placeholder might have markup.
							esc_html_e( 'Here is the comment- {comment}', 'tutor-pro' );
							?>
							</p>
							<div class="answer-heading">
								<span>{comment_by}</span>
							</div>
						</div>
					</div>
				</div>
				<div data-source="email-before-button" class="tutor-email-before-button tutor-h-center email-mb-30">{before_button}</div>
				<div class="tutor-email-buttons tutor-h-center">
					<a target="_blank" class="tutor-email-button" href="{course_url}"><?php esc_html_e( 'Reply Comment', 'tutor-pro' ); ?></a>
				</div>

			</div>
		</div>
	</div>
</body>
</html>
