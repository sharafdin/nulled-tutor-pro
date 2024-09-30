<?php
/**
 * E-mail template for instructor when a student ask a question.
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

				<div class="tutor-user-info">
					<div class="tutor-user-info-wrap">
						<?php if ( isset( $_GET['edit'] ) && 'a_student_placed_question' === $_GET['edit'] ) : ?>
							<img class="tutor-email-avatar" src="<?php echo esc_url( get_avatar_url( wp_get_current_user()->ID ) ); ?>" alt="author" width="50" height="50">
						<?php else : ?>
							{student_avatar}
						<?php endif; ?>

						<div class="answer-block">
							<div class="answer-heading">
								<span>{student_name}</span>
								<span>{question_date}</span>
							</div>
							<p class="answer-content">{question_title}</p>
						</div>
					</div>
				</div>

				<div class="tutor-email-buttons tutor-h-center">
					<a href="{question_url}" data-source="email-btn-url" target="_blank" class="tutor-email-button"><?php esc_html_e( 'Reply Q&amp;A', 'tutor-pro' ); ?></a>
				</div>

			</div>
			<?php require TUTOR_PRO()->path . 'templates/email/email_footer.php'; ?>
		</div>
	</div>
</body>
</html>
