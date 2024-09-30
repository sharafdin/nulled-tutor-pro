<?php
/**
 * ChatGPT API key setup modal.
 *
 * @package TutorPro\ChatGPT
 * @subpackage Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tutor-chatgpt-api-key-modal"
		data-target=""
		class="tutor-modal tutor-modal-scrollable<?php echo is_admin() ? ' tutor-admin-design-init' : ''; ?>">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content">

			<div class="tutor-modal-header">
				<div class="tutor-modal-title">
					<?php esc_html_e( 'Set ChatGPT API key', 'tutor-pro' ); ?>
				</div>

				<button class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close>
					<span class="tutor-icon-times" area-hidden="true"></span>
				</button>
			</div>

			<div class="tutor-modal-body">
				<div>
					<div class="tutor-mb-16">
						<?php esc_html_e( 'Find your Secret API key in your', 'tutor-pro' ); ?><a href="https://platform.openai.com/account/api-keys" target="_blank"> <?php esc_html_e( 'ChatGPT User settings', 'tutor-pro' ); ?></a>
						<?php esc_html_e( 'and paste it here to connect ChatGPT with your Tutor LMS website.', 'tutor-pro' ); ?>
					</div>
					<div class="tutor-mb-28">
						<input  type="text" 
								class="tutor-form-control tutor-chatgpt-api-key" 
								placeholder="<?php _e( 'API key', 'tutor-pro' ); ?>">
					</div>
					<div>
						<label class="tutor-form-toggle">
							<span class="tutor-mr-8"><?php esc_html_e( 'Enable ChatGPT', 'tutor-pro' ) ?></span>
							<span class="label-before"></span>
							<input type="hidden" value="on">
							<input type="checkbox" name="tutor_pro_chatgpt_enable" checked="checked" class="tutor-form-toggle-input">
							<span class="tutor-form-toggle-control"></span>
						</label>
					</div>
				</div>

			</div>
			<!-- # modal body -->

			<div class="tutor-modal-footer">
				<button class="tutor-btn tutor-btn-outline-primary tutor-modal-close" data-tutor-modal-close>
					<?php esc_html_e( 'Cancel', 'tutor-pro' ); ?>
				</button>

				<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-submit">
					<?php esc_html_e( 'Save', 'tutor-pro' ); ?>
				</button>
			</div>
			<!-- # modal footer -->
		</div>
	</div>
</div>
