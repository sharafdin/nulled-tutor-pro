<?php
/**
 * Template for editing email template
 *
 * @since 2.0.0
 * @since 2.5.0 Markup update and code reactor
 *
 * @package TutorPro\Addons
 * @subpackage Email\Views
 * @author Themeum
 * @link https://themeum.com
 */

use TUTOR_EMAIL\EmailSettings;

/**
 * Data comes from Init class options declaration.
 * It contains: { mail, to, key, edit, to_readable, back_url }
 */
$data     = $active_tab_data['edit_email_data'];
$to       = $data['to'];
$edit     = $data['edit'];
$back_url = $data['back_url'];

/**
 * Default data for selected mail trigger.
 * Data source: EmailData class.
 */
$default_data = $data['mail'];

$email_template = $default_data['template'];
$field_name     = "tutor_option[{$to}][{$edit}]";


/**
 * Fetch data from tutor settings,
 * To check trigger is enabled or disabled.
 */
$field_key     = $to . '.' . $edit;
$settings_data = $this->get( $field_key, 'off' );


$template_data = get_option( 'email_template_data' );
$saved_data    = null;
if ( false !== $template_data ) {
	if ( isset( $template_data[ $to ][ $edit ] ) ) {
		$saved_data = $template_data[ $to ][ $edit ];
	} else {
		// Set trigger default data.
		$saved_data = $default_data;
	}
}


$label         = isset( $saved_data['label'] ) ? $saved_data['label'] : null;
$subject       = isset( $saved_data['subject'] ) ? $saved_data['subject'] : null;
$heading       = isset( $saved_data['heading'] ) ? $saved_data['heading'] : null;
$message       = isset( $saved_data['message'] ) ? $saved_data['message'] : null;
$before_button = isset( $saved_data['before_button'] ) ? $saved_data['before_button'] : null;
$footer        = isset( $saved_data['footer_text'] ) ? $saved_data['footer_text'] : null;
$block_heading = isset( $saved_data['block_heading'] ) ? $saved_data['block_heading'] : null;
$block_content = isset( $saved_data['block_content'] ) ? $saved_data['block_content'] : null;
$inactive_days = isset( $saved_data['inactive_days'] ) ? $saved_data['inactive_days'] : null;
?>
<section class="tutor-backend-settings-page email-manage-page">
	<header class="header-wrapper tutor-px-0 tutor-px-xl-32 tutor-py-24 tutor-bg-white tutor-border-bottom">
		<div class="email-page-container header-main tutor-d-flex flex-wrap  tutor-align-center tutor-justify-between">
			<div class="header-left tutor-d-flex tutor-gap-2">
				<div>
					<a href="<?php echo esc_url( $back_url ); ?>" class="prev-page">
						<img src="<?php echo esc_url( TUTOR_EMAIL()->url . 'assets/images/arrow-left.svg' ); ?>" alt="arrow-left">
					</a>
				</div>
				<div>
					<h4 class="tutor-color-black tutor-d-flex tutor-align-center tutor-fs-4 tutor-fw-medium tutor-mt-4">
						<span id="email_template_title"><?php echo esc_attr( $default_data['label'] ); ?></span>
						<label class="tutor-form-toggle tutor-ml-20">
							<input type="hidden" class="tutor-form-toggle-input" id="email_option_data" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $settings_data ); ?>">
							<input type="checkbox" class="tutor-form-toggle-input" <?php checked( $settings_data, 'on' ); ?>>
							<span class="tutor-form-toggle-control"></span>
						</label>
					</h4>
					<span class="subtitle tutor-mt-8 tutor-fs-6 d-inline-flex tutor-pl-0">
						<?php esc_html_e( $data['to_readable'], 'tutor-pro' ); //phpcs:ignore ?>
					</span>
				</div>
			</div>

			<div class="header-right tutor-d-inline-flex">
				<button class="tutor-btn tutor-btn-primary" id="tutor-btn-save" action-tutor-email-template-save><?php esc_html_e( 'Save Changes', 'tutor-pro' ); ?></button>
			</div>
		</div>
	</header>

	<main class="email-page-container main-content-wrapper">
		<div class="tutor-row tutor-gx-0">

			<div class="tutor-col-md-6 tutor-border-right">
				<div class="content-form tutor-pr-md-16 tutor-pr-xl-32 tutor-pt-32">
					<div class="tutor-text-right tutor-mb-8">
						<button 
							type="button"
							class="tutor-btn tutor-btn-sm tutor-color-secondary tutor-trigger-restore-button"
							data-defaults="<?php echo esc_attr( htmlspecialchars( wp_json_encode( $default_data ), ENT_QUOTES, 'UTF-8' ) ); ?>">
								<i class="tutor-icon-refresh tutor-color-muted tutor-mr-4" aria-hidden="true"></i>
								<?php esc_html_e( 'Restore Default', 'tutor-pro' ); ?>
						</button>
					</div>

					<div class="tutor-card tutor-no-border">
						<div class="tutor-card-body">
							<form method="POST" id="tutor-email-template-form" class="tutor-email-edit-form">
								<input type="hidden" name="to" value="<?php echo esc_attr( $data['to'] ); ?>">
								<input type="hidden" name="key" value="<?php echo esc_attr( $data['key'] ); ?>">
								<input type="hidden" name="action" value="save_email_template">

								<?php if ( isset( $default_data['inactive_days'] ) && null !== $default_data['inactive_days'] ) : ?>
									<div class="tutor-option-field-input tutor-email-placeholders field-group tutor-mb-16">
										<label class="tutor-form-label tutor-d-flex tutor-align-center">
											<span><?php esc_html_e( 'Days', 'tutor-pro' ); ?></span>
											<div class="tooltip-wrap tooltip-icon">
												<span class="tooltip-txt tooltip-right"><?php esc_html_e( 'Send reminder if inactive for', 'tutor-pro' ); ?></span>
											</div>
										</label>
										<input type="number" name="inactive-days" value="<?php echo esc_html( $inactive_days ); ?>" class="tutor-form-control" placeholder="<?php esc_html_e( 'Add the days', 'tutor-pro' ); ?>" required min="1">
									</div>
								<?php endif; ?>

								<div class="tutor-option-field-input tutor-email-placeholders field-group tutor-mb-16">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<span><?php esc_html_e( 'Subject', 'tutor-pro' ); ?></span>
										<div class="tooltip-wrap tooltip-icon">
											<span class="tooltip-txt tooltip-right"><?php esc_html_e( 'Edit the subject of your email', 'tutor-pro' ); ?></span>
										</div>
									</label>
									<input type="text" name="email-subject" value="<?php echo esc_html( $subject ); ?>" class="tutor-form-control" placeholder="<?php esc_html_e( 'Add the Email Subject', 'tutor-pro' ); ?>" required>
								</div>

								<div class="tutor-option-field-input tutor-email-placeholders field-group tutor-mb-16">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<span><?php esc_html_e( 'Email Heading', 'tutor-pro' ); ?> </span>
										<div class="tooltip-wrap tooltip-icon">
											<span class="tooltip-txt tooltip-right"><?php esc_html_e( 'Edit the Email Heading of your email', 'tutor-pro' ); ?></span>
										</div>
									</label>
									<input type="text" name="email-heading" class="tutor-form-control" placeholder="<?php esc_html_e( 'Add an Email Heading', 'tutor-pro' ); ?>" value="<?php echo esc_html( $heading ); ?>" required>
								</div>

								<div class="tutor-option-field-input tutor-email-placeholders tutor-has-tinymce-editor field-group">
									<label class="tutor-form-label tutor-d-flex  tutor-align-start">
										<span><?php esc_html_e( 'Additional Content', 'tutor-pro' ); ?> </span>
										<div class="tooltip-wrap tooltip-icon">
											<span class="tooltip-txt tooltip-right"><?php esc_html_e( 'Edit additional content of your email', 'tutor-pro' ); ?></span>
										</div>
									</label>

									<?php
									$content   = json_decode( $message );
									$editor_id = 'email-additional-message';
									EmailSettings::get_email_editor( $content, $editor_id );
									?>
								</div>

								<?php if ( isset( $default_data['before_button'] ) && null !== $default_data['before_button'] ) : ?>
									<div class="tutor-option-field-input tutor-email-placeholders field-group tutor-mt-16">
										<label class="tutor-form-label tutor-d-flex tutor-align-center">
											<span><?php esc_html_e( 'Email Before Button', 'tutor-pro' ); ?></span>
											<div class="tooltip-wrap tooltip-icon">
												<span class="tooltip-txt tooltip-right"><?php esc_html_e( 'Add a CTA text to appear on top of your Action Button', 'tutor-pro' ); ?></span>
											</div>
										</label>
										<textarea style="height: 100px; resize:none;" class="tutor-form-control" name="email-before-button" placeholder="Before button text."><?php echo esc_html( $before_button ); ?></textarea>
									</div>
								<?php endif; ?>

								<?php if ( isset( $default_data['footer_text'] ) && null !== $default_data['footer_text'] ) : ?>
									<div class="tutor-option-field-input field-group tutor-mt-16">
										<label class="tutor-form-label tutor-d-flex tutor-align-center">
											<span><?php esc_html_e( 'Footnote', 'tutor-pro' ); ?></span>
											<div class="tooltip-wrap tooltip-icon">
												<span class="tooltip-txt tooltip-right"><?php esc_html_e( 'Text to appear below the main email content.', 'tutor-pro' ); ?></span>
											</div>
										</label>
										<input type="text" name="email-footer-text" class="tutor-form-control" placeholder="footer text of email" value="<?php echo esc_html( $footer ); ?>">
									</div>
								<?php endif; ?>
							</form>
						</div>
					</div>
				</div>
			</div>

			<div class="tutor-col-md-6">
				<div class="tutor-fs-5 tutor-color-black tutor-pt-32 tutor-px-md-16 tutor-px-xl-32 tutor-pb-12 tutor-border-bottom">
					<?php esc_html_e( 'Email Preview', 'tutor-pro' ); ?> 
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
								<div class="tutor-dropdown" style="width: 350px;" data-tutor-copy-target="">
									<?php
										$user = get_userdata( get_current_user_id() );
									?>
									<div class="tutor-px-16 tutor-pt-8">
										<label class="tutor-form-label"><?php esc_html_e( 'Email address', 'tutor-pro' ); ?></label>
										<input type="text" value="<?php echo esc_attr( $user->user_email ); ?>" name="testing_email" class="tutor-form-control">
									</div>
									<div class="tutor-d-flex tutor-justify-end tutor-p-16 tutor-pb-8 tutor-border-top tutor-mt-24">
										<button class="tutor-btn tutor-btn-ghost tutor-mr-24" action-tutor-dropdown="toggle">
											<?php esc_html_e( 'Cancel', 'tutor-pro' ); ?>
										</button>
										<button class="tutor-btn tutor-btn-outline-primary" id="tutor-btn-test-mail" data-mailto="<?php echo esc_attr( $to ); ?>">
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
