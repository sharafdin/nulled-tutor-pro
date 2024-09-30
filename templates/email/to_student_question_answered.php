<?php
/**
 * E-mail template for student when question answered.
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
					<p style="margin-bottom: 10px">
						<?php
							// Placeholder might have markup.
							_e( 'Here is the answer- {answer}', 'tutor-pro' ); //phpcs:ignore
						?>
					</p>
					<div class="tutor-user-info-wrap">
						<?php if ( isset( $_GET['edit'] ) && 'after_question_answered' === $_GET['edit'] ) : ?>
							<img class="tutor-email-avatar" src="<?php echo esc_url( get_avatar_url( wp_get_current_user()->ID ) ); ?>" alt="author" width="50" height="50">
						<?php else : ?>
							{instructor_avatar}
						<?php endif; ?>
						<div class="answer-block">
							<div class="answer-heading">
								<span>{answer_by}</span>
								<span>{answer_date}</span>
							</div>
							<p class="answer-content">{question}</p>
						</div>
					</div>
				</div>

				<div data-source="email-before-button" class="tutor-email-before-button tutor-h-center email-mb-30">{before_button}</div>

				<div class="tutor-email-buttons tutor-h-center">
					<a target="_blank" class="tutor-email-button" href="{answer_url}"><?php esc_html_e( 'Reply Q&amp;A', 'tutor-pro' ); ?></a>
				</div>

			</div>
		</div>
	</div>
</body>
</html>
