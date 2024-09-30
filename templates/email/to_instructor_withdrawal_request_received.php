<?php
/**
 * E-mail template for instructor when a withdrawal request received.
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
						<td class="label"><?php esc_html_e( 'Withdraw Amount:', 'tutor-pro' ); ?></td>
						<td><strong><?php _e( '{withdraw_amount}', 'tutor-pro' );//phpcs:ignore ?></strong></td>
					</tr>
					<tr>
						<td class="label"><?php esc_html_e( 'Current Balance:', 'tutor-pro' ); ?></td>
						<td><strong><?php _e( '{total_amount}', 'tutor-pro' );//phpcs:ignore ?></strong></td>
					</tr>
				</table>
			</div>
			<?php require TUTOR_PRO()->path . 'templates/email/email_footer.php'; ?>
		</div>
	</div>
</body>
</html>
