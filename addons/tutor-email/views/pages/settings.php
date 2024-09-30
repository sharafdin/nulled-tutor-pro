<?php
/**
 * E-mail default configuration view
 *
 * @since 2.5.0
 *
 * @package TutorPro\Addons
 * @subpackage Email\Views
 * @author Themeum
 */

use TUTOR_EMAIL\EmailPlaceholder;
use TUTOR_EMAIL\EmailSettings;
use TUTOR_PRO\ContentSecurity;

$email_back_url = add_query_arg(
	array(
		'page'     => 'tutor_settings',
		'tab_page' => 'email_notification',
	),
	admin_url( 'admin.php' )
);
$arrow_left     = esc_url( TUTOR_EMAIL()->url . 'assets/images/arrow-left.svg' );
$email_template = 'to_admin_default_config';

$color_option_key   = EmailSettings::TEMPLATE_COLORS_KEY;
$email_color_fields = EmailSettings::get_colors_fields();
$default_colors     = EmailSettings::get_template_default_colors();

$user          = get_userdata( get_current_user_id() );
$media_id      = get_tutor_option( 'tutor_email_template_logo_id' );
$logo_url      = apply_filters( 'tutor_email_logo_src', tutor()->url . 'assets/images/tutor-logo.png' );
$logo_height   = $this->get( 'email_logo_height', 30 );
$logo_alt_text = $this->get( 'email_logo_alt_text', __( 'Tutor', 'tutor-pro' ) );
$logo_position = $this->get( 'email_logo_position', 'center' );

$email_from_name       = $this->get( 'email_from_name', get_bloginfo( 'name' ) );
$email_from_address    = $this->get( 'email_from_address', $user->user_email );
$email_footer_text     = json_decode( $this->get( 'email_footer_text' ) );
$email_button_position = $this->get( 'email_template_button_position', 'center' );

$email_placeholders = array_values( EmailPlaceholder::only( array( 'site_name', 'site_url', 'current_year' ) ) );
$hotlink_protection = (bool) get_tutor_option( ContentSecurity::HOTLINKING_OPTION );
?>
<section class="tutor-backend-settings-page email-settings-page">
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
						<span id="email_template_title"><?php esc_html_e( 'Default Configuration', 'tutor-pro' ); ?></span>
					</h4>
					<span class="subtitle tutor-mt-8 tutor-fs-6 d-inline-flex tutor-pl-0">
						<?php esc_html_e( 'Setup and configure your default system email', 'tutor-pro' ); ?>
					</span>
				</div>
			</div>

			<div class="header-right tutor-d-inline-flex">
				<button class="tutor-btn tutor-btn-primary" action-tutor-email-template-save><?php esc_html_e( 'Save Changes', 'tutor-pro' ); ?></button>
			</div>
		</div>
	</header>

	<main class="email-page-container main-content-wrapper">
		<div class="tutor-row tutor-gx-0">

			<div class="tutor-col-md-6 tutor-border-right">
				<div class="content-form tutor-pr-md-16 tutor-pr-xl-32 tutor-pt-32">
					<form method="POST" id="tutor-email-settings-form">
						<input type="hidden" name="action" value="save_email_settings">

						<div class="tutor-card tutor-no-border tutor-mb-24">
							<div class="tutor-card-header tutor-no-border">
								<div class="tutor-card-title"><?php esc_html_e( 'Email Logo', 'tutor-pro' ); ?></div>
							</div>
							<div class="tutor-card-body">
								<div class="tutor-email-template-logo-wrapper">
									<div class="tutor-row">
										<div class="tutor-col-xl-8 tutor-border-right">
											<div class="tutor-option-field-input field-group tutor-mb-16 tutor-mb-xl-8">
												<div class="tutor-thumbnail-uploader">
													<div class="thumbnail-wrapper tutor-d-flex tutor-is-borderless">
														<div class="thumbnail-preview image-previewer tutor-mr-16 tutor-p-8" style="height: 50px;">
															<span class="preview-loading"></span>
															<?php if ( ! $hotlink_protection ) : ?>
															<input type="hidden" class="tutor-tumbnail-id-input" name="tutor_email_template_logo_id" value="<?php echo esc_attr( $media_id ); ?>">
															<?php endif; ?>
															<img src="<?php echo esc_url( $logo_url ); ?>" height="<?php echo esc_attr( $logo_height ); ?>"/>
														</div>
														<div class="thumbnail-input tutor-mt-2">
															<?php if ( ! $hotlink_protection ) { ?>
															<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-thumbnail-upload-button tutor-nowrap-ellipsis">
																<span class="tutor-icon-image-landscape tutor-mr-8" aria-hidden="true"></span>
																<span><?php esc_html_e( 'Upload Image', 'tutor' ); ?></span>
															</button>
															<?php } else { ?>
															<input	type="text" 
																	class="tutor-form-control email-logo-url" 
																	name="tutor_email_template_logo_id" 
																	placeholder="<?php esc_attr_e( 'Enter logo URL', 'tutor-pro' ); ?>" 
																	value="<?php echo esc_attr( $media_id ); ?>">
															<?php } ?>
															<div class="tutor-fs-8 tutor-color-secondary tutor-mt-4">
															<?php if ( ! $hotlink_protection ) { ?>
																<span><?php echo esc_html_e( 'Size: 100x36 pixels, Max height: 50px', 'tutor-pro' ); ?></span>
															<?php } else { ?>
																<span><?php echo esc_html_e( 'Please enter an image URL from another host.', 'tutor-pro' ); ?></span>
															<?php } ?>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="tutor-option-field-input field-group tutor-mb-16 tutor-mb-xl-0">
												<label class="tutor-form-label tutor-d-flex tutor-align-center">
													<span><?php esc_html_e( 'Alt Text', 'tutor-pro' ); ?></span>
												</label>
												<input type="text" name="email_logo_alt_text" class="tutor-form-control" value="<?php echo esc_attr( $logo_alt_text ); ?>">
											</div>
										</div>
										<div class="tutor-col-xl-4">
											<div class="tutor-option-field-input field-group tutor-mb-16">
												<label class="tutor-form-label tutor-d-flex tutor-align-center">
													<span><?php esc_html_e( 'Position', 'tutor-pro' ); ?></span>
												</label>
												<div class="tutor-form-alignment">
													<input type="hidden" name="email_logo_position" value="<?php echo esc_attr( $logo_position ); ?>" />
													<div class="tutor-btn-group tutor-d-flex" role="group">
														<button type="button" class="tutor-btn tutor-justify-center tutor-btn-secondary" data-position="left">
															<i class="tutor-icon-align-left" aria-hidden="true"></i>
														</button>
														<button type="button" class="tutor-btn tutor-justify-center tutor-btn-secondary" data-position="center">
															<i class="tutor-icon-align-center" aria-hidden="true"></i>
														</button>
														<button type="button" class="tutor-btn tutor-justify-center tutor-btn-secondary" data-position="right">
															<i class="tutor-icon-align-right" aria-hidden="true"></i>
														</button>
													</div>
												</div>
											</div>
											<div class="tutor-option-field-input field-group">
												<label class="tutor-form-label tutor-d-flex tutor-align-center">
													<span><?php esc_html_e( 'Height', 'tutor-pro' ); ?></span>
												</label>
												<input type="number" name="email_logo_height" class="tutor-form-control" value="<?php echo esc_attr( $logo_height ); ?>">
											</div>
										</div>
									</div>
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

						<div class="tutor-card tutor-no-border tutor-mb-24">
							<div class="tutor-card-body">
								<div class="tutor-d-flex tutor-align-center tutor-justify-between">
									<label class="tutor-form-label" style="margin-bottom: 0;">
										<span class="tutor-fs-6"><?php esc_html_e( 'Button Position', 'tutor-pro' ); ?></span>
									</label>
									<div class="tutor-form-alignment">
										<input type="hidden" name="email_template_button_position" value="<?php echo esc_attr( $email_button_position ); ?>" />
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

						<div class="tutor-card tutor-no-border">
							<div class="tutor-card-body">
								<div class="tutor-option-field-input field-group tutor-mb-16">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<span><?php esc_html_e( 'Sender Email Address', 'tutor-pro' ); ?></span>
										<div class="tooltip-wrap tooltip-icon">
											<span class="tooltip-txt tooltip-right"><?php esc_html_e( 'The E-Mail address from which all emails will be sent', 'tutor-pro' ); ?></span>
										</div>
									</label>
									<input type="email" name="email_from_address" class="tutor-form-control" value="<?php echo esc_attr( $email_from_address ); ?>" required>
								</div>
								<div class="tutor-option-field-input field-group tutor-mb-16">
									<label class="tutor-form-label tutor-d-flex tutor-align-center">
										<span><?php esc_html_e( 'Sender Name', 'tutor-pro' ); ?></span>
										<div class="tooltip-wrap tooltip-icon">
											<span class="tooltip-txt tooltip-right"><?php esc_html_e( 'The name under which all the emails will be sent', 'tutor-pro' ); ?></span>
										</div>
									</label>
									<input type="text" name="email_from_name" value="<?php echo esc_attr( $email_from_name ); ?>" class="tutor-form-control" required>
								</div>

								<div class="tutor-option-field-input tutor-email-placeholders tutor-has-tinymce-editor field-group" data-placeholders="<?php echo esc_attr( wp_json_encode( $email_placeholders ) ); ?>">
									<label class="tutor-form-label tutor-d-flex  tutor-align-start">
										<span><?php esc_html_e( 'Email Footer Text', 'tutor-pro' ); ?> </span>
										<div class="tooltip-wrap tooltip-icon">
											<span class="tooltip-txt tooltip-right"><?php esc_html_e( 'The text to appear in E-Mail template footer', 'tutor-pro' ); ?></span>
										</div>
									</label>

									<?php EmailSettings::get_email_editor( $email_footer_text, 'email_footer_text' ); ?>
								</div>
							</div>
						</div>
					</form>
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
