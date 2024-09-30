<?php
/**
 * E-mail template for student when announcement updated.
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

				<div class="tutor-email-announcement">
					<div class="announcement-heading">
						<div class="announcement-title">{announcement_title}</div>
						<div class="announcement-meta">
							<img class="author-image" src="<?php echo esc_url( get_avatar_url( wp_get_current_user()->ID ) ); ?>
		" alt="author" width="26" height="26" style="border-radius: 50%;">
							<span class="author-name">by <apan>{author_fullname}</apan></span>
							<span class="announcement-time">{announcement_date}</span>
						</div>
					</div>
					<div class="announcement-content">{announcement_content}</div>
				</div>

			</div>
			<?php require TUTOR_PRO()->path . 'templates/email/email_footer.php'; ?>
		</div>
	</div>
</body>
</html>
