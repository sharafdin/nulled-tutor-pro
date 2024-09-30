<?php
/**
 * E-mail template for student when course completed.
 *
 * @package TutorPro
 * @subpackage Templates\Email
 *
 * @since 2.0.0
 */

use TUTOR\Input;
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

				<div class="tutor-email-cardblock" style="margin-bottom:30px">
					<div class="tutor-cardblock-heading"><?php esc_html_e( 'Course Instructor', 'tutor-pro' ); ?></div>
					<div class="tutor-cardblock-wrapper">
						<img src="{instructor_avatar}" alt="author" width="50" height="50" class="user-avatar" style="border-radius: 50%;margin-right: 12px">
						<div class="tutor-cardblock-content">
							<p style="font-size:16px;"><strong>{instructor_username}</strong></p>
							<p style="font-size:15px;font-weight:400">{instructor_description}</p>
						</div>
					</div>
				</div>

				<div data-source="email-before-button" class="tutor-email-before-button tutor-h-center email-mb-30">{before_button}</div>

				<div class="tutor-email-buttons">
					<?php
					if ( isset( $course_id ) ) {
						$certificate_template = get_post_meta( $course_id, 'tutor_course_certificate_template', true );
						if ( 'none' !== $certificate_template && ! empty( $certificate_url ) ) {
							?>
							<a target="_blank" class="tutor-email-button-bordered" href="<?php echo esc_url( $certificate_url ); ?>" data-source="email-btn-url">
								<span><?php esc_html_e( 'Download Certificate', 'tutor-pro' ); ?></span>
							</a>
							<?php
						}
					}

					if ( get_tutor_option( 'enable_course_review' ) ) {
						?>
							<a target="_blank" class="tutor-email-button" href="{course_url}" data-source="email-btn-url">
								<img src="<?php echo esc_url( TUTOR_EMAIL()->url . 'assets/images/star.png' ); ?>" alt="star">
								<span><?php esc_html_e( 'Rate This Course', 'tutor-pro' ); ?></span>
							</a>
							<?php
					}
					?>
				</div>

			</div>
			<?php require TUTOR_PRO()->path . 'templates/email/email_footer.php'; ?>
		</div>
	</div>
</body>
</html>
