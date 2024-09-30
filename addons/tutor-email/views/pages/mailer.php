<?php
/**
 * Manual email page.
 *
 * @since 2.5.0
 *
 * @package TutorPro\Addons
 * @subpackage Email\Views
 * @author Themeum
 */

use Tutor\Models\CourseModel;
use TUTOR_EMAIL\EmailPlaceholder;
use TUTOR_EMAIL\EmailSettings;
use TUTOR_EMAIL\ManualEmail;

$email_back_url = add_query_arg(
	array(
		'page'     => 'tutor_settings',
		'tab_page' => 'email_notification',
	),
	admin_url( 'admin.php' )
);

$arrow_left     = esc_url( TUTOR_EMAIL()->url . 'assets/images/arrow-left.svg' );
$email_template = 'mailer';

$user           = get_userdata( get_current_user_id() );
$courses        = CourseModel::get_courses();
$receiver_types = ManualEmail::get_receiver_types();

$mailer_data         = get_option( ManualEmail::OPTION_KEY );
$selected_receiver   = $mailer_data['receiver_type'] ?? '';
$selected_course_ids = $mailer_data['course_ids'] ?? array();

$subject         = $mailer_data['email_subject'] ?? __( 'Greetings from {site_name}', 'tutor-pro' );
$heading         = $mailer_data['email_heading'] ?? __( 'Hi there', 'tutor-pro' );
$email_body      = $mailer_data['email_body'] ?? __( "<p>We hope this message finds you well. We're excited to share some updates and important information regarding your experience with {site_name}", 'tutor-pro' );
$footer_text     = $mailer_data['email_footer'] ?? __( 'Thank you for being a valued member of our community!', 'tutor-pro' );
$action_button   = $mailer_data['email_action_button'] ?? 'on';
$button_text     = $mailer_data['email_action_label'] ?? __( 'Get Started', 'tutor-pro' );
$button_link     = $mailer_data['email_action_link'] ?? '#';
$button_position = $mailer_data['email_action_position'] ?? 'left';

$color_option_key   = EmailSettings::TEMPLATE_COLORS_KEY;
$email_color_fields = EmailSettings::get_colors_fields();
$default_colors     = EmailSettings::get_template_default_colors();

$email_placeholders = array_values( EmailPlaceholder::only( array( 'site_name', 'site_url', 'current_year' ) ) );

$cron_enabled     = (bool) tutor_utils()->get_option( 'tutor_email_disable_wpcron', false );
$cron_frequency   = (int) tutor_utils()->get_option( 'tutor_email_cron_frequency' );
$bulk_email_limit = (int) tutor_utils()->get_option( 'tutor_bulk_email_limit', 10 );
?>
<section class="tutor-backend-settings-page mailer-page">
	<header class="header-wrapper tutor-px-0 tutor-px-xl-32 tutor-py-24 tutor-bg-white tutor-border-bottom">
		<div class="email-page-container header-main tutor-d-flex flex-wrap  tutor-align-center tutor-justify-between">
			<div class="header-left tutor-d-flex tutor-gap-2">
				<div>
					<a href="<?php echo esc_url( $email_back_url ); ?>" class="prev-page">
						<img src="<?php echo esc_url( $arrow_left ); ?>" alt="arrow-left">
					</a>
				</div>
				<div>
					<h4 class="tutor-color-black tutor-d-flex tutor-align-center tutor-fs-4 tutor-fw-medium tutor-mt-4">
						<span id="email_template_title"><?php esc_html_e( 'Manual Email', 'tutor-pro' ); ?></span>
					</h4>
					<span class="subtitle tutor-mt-8 tutor-fs-6 d-inline-flex tutor-pl-0">
						<?php esc_html_e( 'Compose & send email for custom event', 'tutor-pro' ); ?>
					</span>
				</div>
			</div>

			<div class="header-right tutor-d-inline-flex">
				<button class="tutor-btn tutor-btn-secondary tutor-mr-16" id="tutor-btn-save-draft">
					<?php esc_html_e( 'Save Changes', 'tutor-pro' ); ?>
				</button>
				<button class="tutor-btn tutor-btn-primary" id="tutor-btn-confirm-manual-mail" 
					<?php
					if ( ! $cron_enabled ) {
						echo 'disabled';
					}
					?>
					>
					<span class="tutor-icon-paper-plane tutor-mr-8" aria-hidden="true"></span>
					<?php esc_html_e( 'Send Mail', 'tutor-pro' ); ?>
				</button>
			</div>
		</div>
	</header>

	<main class="email-page-container main-content-wrapper">
		<div class="tutor-row tutor-gx-0">

			<div class="tutor-col-md-6 tutor-border-right">
				<div class="content-form tutor-pr-md-16 tutor-pr-xl-32 tutor-pt-32">
					<form method="POST" id="tutor-mailer-form" class="tutor-email-edit-form">
						<input type="hidden" name="action" value="tutor_manual_email_save_draft">

						<?php
						if ( ! $cron_enabled ) {
							$email_settings_link = admin_url( 'admin.php?page=tutor_settings&tab_page=email_notification&highlight=field_tutor_email_disable_wpcron' );
							$warning_msg         = __( 'To use manual email functionality, you need to activate the tutor email cron schedule settings. Please ', 'tutor-pro' );
							$warning_msg        .= '<a target="_blank" href="' . $email_settings_link . '">' . __( 'Enable Cron Scheduling', 'tutor-pro' ) . '</a>'
							?>
							<div class="tutor-alert tutor-warning">
								<div class="tutor-alert-text">
									<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
									<span>
									<?php
									echo wp_kses(
										$warning_msg,
										array(
											'a' => array(
												'href'   => 1,
												'target' => 1,
											),
										)
									);
									?>
									</span>
								</div>
							</div>
							<?php
						}
						?>

						<div class="tutor-card tutor-no-border tutor-mb-24">
							<div class="tutor-card-body">
								<div class="tutor-option-field-input field-group">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<?php esc_html_e( 'Receiver Type', 'tutor-pro' ); ?>
									</label>

									<select name="receiver_type" class="tutor-form-select" require>
										<?php if ( count( $receiver_types ) ) : ?>
											<option value="">
												<?php esc_html_e( 'Select Receiver Type', 'tutor-pro' ); ?>
											</option>
											<?php foreach ( $receiver_types as $key => $value ) : ?>
												<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_receiver, $key ); ?>>
													<?php echo esc_html( $value ); ?>
												</option>
											<?php endforeach; ?>
										<?php else : ?>
											<option value=""><?php esc_html_e( 'No receiver type found', 'tutor-pro' ); ?></option>
										<?php endif; ?>
									</select>
								</div>

								<div class="tutor-option-field-input field-group tutor-mt-16">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<?php esc_html_e( 'Select Courses', 'tutor-pro' ); ?>
									</label>

									<select name="course_ids[]" multiple id="tutor-mailer-course-ids" data-selected="<?php echo esc_attr( wp_json_encode( $selected_course_ids ) ); ?>">
										<?php if ( count( $courses ) ) : ?>
											<?php foreach ( $courses as $course ) : ?>
											<option value="<?php echo esc_attr( $course->ID ); ?>"><?php echo esc_html( $course->post_title ); ?></option>
											<?php endforeach; ?>
										<?php else : ?>
											<option value=""><?php esc_html_e( 'No course found', 'tutor-pro' ); ?></option>
										<?php endif; ?>
									</select>
								</div>

								<div class="tutor-receiver-count-message tutor-color-success tutor-mt-16 tutor-d-flex tutor-align-center tutor-d-none">
									<i class="tutor-icon-circle-info tutor-mr-4" aria-hidden="true"></i>
									<div></div>
								</div>
							</div>
						</div>

						<div class="tutor-card tutor-no-border tutor-mb-24">
							<div class="tutor-card-body">
								<div class="tutor-option-field-input field-group tutor-mb-16 tutor-email-placeholders" data-placeholders="<?php echo esc_attr( wp_json_encode( $email_placeholders ) ); ?>">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<?php esc_html_e( 'Email Subject', 'tutor-pro' ); ?>
									</label>
									<input type="text" name="email_subject" value="<?php echo esc_attr( $subject ); ?>" class="tutor-form-control" required>
								</div>

								<div class="tutor-option-field-input field-group tutor-mb-16 tutor-email-placeholders" data-placeholders="<?php echo esc_attr( wp_json_encode( $email_placeholders ) ); ?>">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<?php esc_html_e( 'Email Heading', 'tutor-pro' ); ?>
									</label>
									<input type="text" name="email_heading" value="<?php echo esc_attr( $heading ); ?>" class="tutor-form-control" required>
								</div>

								<div class="tutor-option-field-input tutor-email-placeholders tutor-has-tinymce-editor field-group tutor-mb-16" data-placeholders="<?php echo esc_attr( wp_json_encode( $email_placeholders ) ); ?>">
									<label class="tutor-form-label tutor-d-flex  tutor-align-start">
										<?php esc_html_e( 'Email Body', 'tutor-pro' ); ?>
									</label>

									<?php EmailSettings::get_email_editor( $email_body, 'email_body' ); ?>
								</div>

								<div class="tutor-option-field-input tutor-email-placeholders field-group" data-placeholders="<?php echo esc_attr( wp_json_encode( $email_placeholders ) ); ?>">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<?php esc_html_e( 'Footnote', 'tutor-pro' ); ?>
									</label>
									<input type="text" name="email_footer" value="<?php echo esc_attr( $footer_text ); ?>" class="tutor-form-control" required>
								</div>
							</div>
						</div>

						<div class="tutor-card tutor-no-border tutor-mb-24">
							<div class="tutor-card-header tutor-no-border">
								<div class="tutor-card-title"><?php esc_html_e( 'Color Options', 'tutor-pro' ); ?></div>
								<button type="button" class="tutor-btn tutor-btn-sm tutor-color-secondary tutor-card-collapse-button">
									<i class="tutor-icon-angle-up" aria-hidden="true"></i>
								</button>
							</div>

							<div class="tutor-card-body" style="padding-top: 0px;">
								<div class="color-picker-wrapper tutor-email-colors">
									<?php
									foreach ( $email_color_fields as $key => $field_group ) {
										if ( 'secondary_button' === $key ) {
											continue;
										}
										$color_group_label = implode( ' ', array_map( 'ucwords', explode( '_', $key ) ) );
										?>
										<div class="tutor-card tutor-card-sm tutor-mb-20">
											<div class="tutor-card-header">
												<div class="tutor-fs-6"><?php echo esc_html( $color_group_label ); ?></div>
											</div>
											<div class="tutor-card-body">
												<div class="tutor-row tutor-gy-2">
													<?php
													foreach ( $field_group as $key => $field ) {
														$input_name = $color_option_key . '[' . $field['id'] . ']';
														?>
														<div class="tutor-col-6 tutor-col-sm-4 tutor-col-md-6 tutor-col-lg-6 tutor-col-xl-4">
															<div class="tutor-fs-7 tutor-mb-4">
															<?php echo esc_html( $field['label'] ); ?>
															</div>
															<div class="tutor-option-field-input">
																<label for="<?php echo esc_attr( $field['id'] ); ?>" class="color-picker-input" data-key="<?php echo esc_attr( $field['id'] ); ?>">
																	<input type="color" data-picker="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $input_name ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $field['value'] ?? $field['default'] ); ?>">
																	<input type="text" value="<?php echo esc_attr( $field['value'] ?? $field['default'] ); ?>" />
																</label>
															</div>
														</div>
														<?php
													}
													?>
												</div>
											</div>
										</div>
										<?php
									}
									?>
								</div>
								<div class="tutor-text-right">
									<button 
										type="button"
										class="tutor-btn tutor-btn-sm tutor-color-secondary tutor-color-restore-button"
										data-defaults="<?php echo esc_attr( htmlspecialchars( wp_json_encode( $default_colors ), ENT_QUOTES, 'UTF-8' ) ); ?>">
											<i class="tutor-icon-refresh tutor-color-muted tutor-mr-4" aria-hidden="true"></i>
											<?php esc_html_e( 'Restore Default', 'tutor-pro' ); ?>
									</button>
								</div>
							</div>
						</div>

						<div class="tutor-card tutor-no-border">
							<div class="tutor-card-header tutor-no-border">
								<div class="tutor-card-title"><?php esc_html_e( 'Button', 'tutor-pro' ); ?></div>
								<label class="tutor-form-toggle">
									<input type="hidden" name="email_action_button" value="<?php echo esc_attr( $action_button ); ?>">
									<input 	type="checkbox"
											class="tutor-form-toggle-input"
											value="<?php echo esc_attr( $action_button ); ?>" 
											<?php echo 'on' === $action_button ? 'checked' : ''; ?>>
									<span class="tutor-form-toggle-control"></span>
								</label>
							</div>
							<div class="tutor-card-body tutor-border-top">
								<div class="tutor-option-field-input field-group tutor-mb-16">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<?php esc_html_e( 'Label', 'tutor-pro' ); ?>
									</label>
									<input type="text" name="email_action_label" value="<?php echo esc_attr( $button_text ); ?>" class="tutor-form-control">
								</div>
								<div class="tutor-option-field-input field-group tutor-mb-16">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<?php esc_html_e( 'Link', 'tutor-pro' ); ?>
									</label>
									<input type="text" name="email_action_link" value="<?php echo esc_url( $button_link ); ?>" class="tutor-form-control">
								</div>
								<div class="tutor-option-field-input field-group">
									<div class="tutor-row tutor-align-center">
										<div class="tutor-col-md-6">
											<label class="tutor-form-label" style="margin-bottom: 0;">
												<span><?php esc_html_e( 'Position', 'tutor-pro' ); ?></span>
											</label>
										</div>
										<div class="tutor-col-md-6">
											<div class="tutor-form-alignment tutor-text-right">
												<input type="hidden" name="email_action_position" value="<?php echo esc_attr( $button_position ); ?>" />
												<div class="tutor-btn-group" role="group">
													<button type="button" class="tutor-btn tutor-btn-secondary" data-position="left">
														<i class="tutor-icon-align-left" aria-hidden="true"></i>
													</button>
													<button type="button" class="tutor-btn tutor-btn-secondary" data-position="center">
														<i class="tutor-icon-align-center" aria-hidden="true"></i>
													</button>
													<button type="button" class="tutor-btn tutor-btn-secondary" data-position="right">
														<i class="tutor-icon-align-right" aria-hidden="true"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

					</form>
				</div>
			</div>

			<div class="tutor-col-md-6">
				<div class="tutor-fs-5 tutor-color-black tutor-pt-32 tutor-px-md-16 tutor-px-xl-32 tutor-pb-12 tutor-border-bottom">
					<?php esc_html_e( 'Template Preview', 'tutor-pro' ); ?>
				</div>
				<div class="email-template-preview-wrapper">
					<div class="tutor-pl-md-16 tutor-pl-xl-32" data-email_template="<?php echo esc_attr( $email_template ); ?>">
						<div class="tutor-d-flex tutor-justify-between tutor-align-center tutor-my-24">
							<div class="tutor-email-preview-responsive-buttons">
								<button class="tutor-btn tutor-btn-sm active" data-preview-mode="desktop">
									<i class="tutor-icon-desktop" aria-hidden="true"></i>
								</button>
								<button class="tutor-btn tutor-btn-sm" data-preview-mode="mobile">
									<i class="tutor-icon-mobile" aria-hidden="true"></i>
								</button>
							</div>
							<div class="tutor-dropdown-parent">
								<button class="tutor-btn tutor-color-secondary" action-tutor-dropdown="toggle">
									<span class="tutor-icon-paper-plane tutor-mr-8" aria-hidden="true"></span>
									<span><?php esc_html_e( 'Send a test mail', 'tutor-pro' ); ?></span>
								</button>
								<div class="tutor-dropdown" style="width: 350px;" data-tutor-copy-target>
									<div class="tutor-px-16 tutor-pt-8">
										<label class="tutor-form-label"><?php esc_html_e( 'Email address', 'tutor-pro' ); ?></label>
										<input type="text" value="<?php echo esc_attr( $user->user_email ); ?>" name="testing_email" class="tutor-form-control">
									</div>
									<div class="tutor-d-flex tutor-justify-end tutor-p-16 tutor-pb-8 tutor-border-top tutor-mt-24">
										<button class="tutor-btn tutor-btn-ghost tutor-mr-24" action-tutor-dropdown="toggle">
											<?php esc_html_e( 'Cancel', 'tutor-pro' ); ?>
										</button>
										<button class="tutor-btn tutor-btn-outline-primary" id="tutor-btn-test-mail" data-mailto="">
											<span class="tutor-icon-paper-plane tutor-mr-8" aria-hidden="true"></span> <?php esc_html_e( 'Send', 'tutor-pro' ); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="loading-spinner" aria-hidden="true"></div>
					<div class="template-preview tutor-pl-md-16 tutor-pl-xl-32" data-email_template="<?php echo esc_attr( $email_template ); ?>"></div>
				</div>
			</div>
		</div>
	</main>
</section>

<div class="tutor-modal" id="tutor-email-confirmation-modal">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
				<span class="tutor-icon-times" area-hidden="true"></span>
			</button>

			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-mt-48">
					<img class="tutor-d-inline-block" src="<?php echo esc_url( TUTOR_EMAIL()->url . 'assets/images/sendmail.svg' ); ?>" />
				</div>

				<div class="tutor-email-modal-title tutor-fs-3 tutor-color-black tutor-mb-28">
					<?php esc_html_e( 'Do you want to send this email to', 'tutor-pro' ); ?> <br/> <strong>0</strong> <?php esc_html_e( 'receivers?', 'tutor-pro' ); ?>
				</div>

				<div class="tutor-px-28">
					<ul class="tutor-list tutor-text-left">
						<li class="tutor-list-item tutor-d-flex tutor-align-center tutor-gap-1">
							<i class="tutor-icon-circle-info tutor-color-primary"></i> <?php esc_html_e( 'Email will be scheduled according to Tutor LMS Email Cron Settings.', 'tutor-pro' ); ?>
						</li>
						<li class="tutor-list-item tutor-d-flex tutor-align-center tutor-gap-1">
							<i class="tutor-icon-circle-info tutor-color-primary"></i> <strong><?php echo esc_html( $bulk_email_limit ); ?></strong> <?php esc_html_e( 'emails will be sent per batch.', 'tutor-pro' ); ?>
						</li>
						<li class="tutor-list-item tutor-d-flex tutor-align-center tutor-gap-1">
							<i class="tutor-icon-circle-info tutor-color-primary"></i> <?php esc_html_e( 'Each batch execution interval time is', 'tutor-pro' ); ?> <strong><?php echo esc_html( $cron_frequency ); ?></strong> <?php esc_html_e( 'seconds.', 'tutor-pro' ); ?>
						</li>
					</ul>
				</div>

				<div class="tutor-d-flex tutor-justify-center tutor-my-48">
					<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor-pro' ); ?>
					</button>
					<button class="tutor-btn tutor-btn-primary tutor-ml-16" id="tutor-btn-send-manual-mail">
						<?php esc_html_e( 'Yes, Send Mail', 'tutor-pro' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
