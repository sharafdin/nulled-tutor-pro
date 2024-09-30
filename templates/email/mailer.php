<?php
/**
 * Manual email template.
 *
 * @since 2.5.0
 *
 * @package TutorPro\Addons
 * @subpackage Email\Views
 * @author Themeum
 */

use TUTOR_EMAIL\ManualEmail;

?>
<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
	<?php
	$mailer_data           = get_option( ManualEmail::OPTION_KEY );
	$email_template_colors = $mailer_data['email_template_colors'] ?? array();
	$action_button         = $mailer_data['email_action_button'] ?? 'on';
	$button_label          = $mailer_data['email_action_label'] ?? __( 'Get Started', 'tutor-pro' );
	$button_link           = $mailer_data['email_action_link'] ?? '#';
	$button_position       = $mailer_data['email_action_position'] ?? 'left';
	$email_footer          = $mailer_data['email_footer'] ?? '';

	add_filter(
		'tutor_email_template_colors',
		function( $colors ) use ( $email_template_colors ) {
			foreach ( $colors as $key => &$color ) {
				if ( isset( $email_template_colors[ $key ] ) ) {
					$color['value'] = $email_template_colors[ $key ];
				}
			}
			return $colors;
		}
	);

	require TUTOR_EMAIL()->path . 'views/email_styles.php';
	?>
</head>

<body>
	<div class="tutor-email-body">
		<div class="tutor-email-wrapper" style="background-color: #fff;">

			<?php require TUTOR_PRO()->path . 'templates/email/email_header.php'; ?>

			<div class="tutor-email-content">
				<table>
					<tr>
						<td>
							<h2 class="tutor-email-heading" data-source="email_heading">{email_heading}</h2>
							<br>
							<div data-source="email_body">{email_body}</div>
							<br>
							<div 
								class="tutor-email-buttons"
								style="<?php echo 'off' === $action_button ? 'display:none' : 'text-align:' . esc_attr( $button_position ); ?>"
								>
								<a href="<?php echo esc_url( $button_link ); ?>" class="tutor-email-button" data-source="email_action_label"><?php echo esc_html( $button_label ); ?></a>
							</div>
						</td>
					</tr>
				</table>
			</div>

			<div class="tutor-email-footer" style="<?php echo empty( $email_footer ) ? 'display:none;' : ''; ?>">
				<table>
					<tr>
						<td>
							<div class="tutor-email-footer-text" data-source="email_footer">
								{footer_text}
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</body>
</html>
