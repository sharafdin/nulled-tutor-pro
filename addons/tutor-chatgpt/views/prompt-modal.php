<?php
/**
 * ChatGPT prompt input modal.
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
<div id="tutor-chatgpt-modal"
		data-target=""
		class="tutor-modal tutor-modal-scrollable<?php echo is_admin() ? ' tutor-admin-design-init' : ''; ?>">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content">

			<div class="tutor-modal-header">
				<div class="tutor-modal-title">
					<?php esc_html_e( 'Ask ChatGPT', 'tutor-pro' ); ?>
				</div>

				<button class="tutor-iconic-btn tutor-modal-close">
					<span class="tutor-icon-times" area-hidden="true"></span>
				</button>
			</div>

			<div class="tutor-modal-body">
				<div>
					<div class="tutor-mb-16">
						<textarea class="tutor-form-control tutor-chatgpt-input" rows="5"></textarea>
					</div>
					<div class="tutor-mb-4">
						<label for="tutor-chatgpt-word-limit"><?php esc_html_e( 'Word Limit', 'tutor-pro' ); ?></label>
						<input type="number" style="width:130px" min="0"
								name="tutor-chatgpt-word-limit" class="tutor-form-control tutor-mt-8 tutor-ml-0">
					</div>
				</div>

			</div>
			<!-- # modal body -->

			<div class="tutor-modal-footer">
				<button class="tutor-btn tutor-btn-outline-primary tutor-modal-close">
					<?php esc_html_e( 'Cancel', 'tutor-pro' ); ?>
				</button>

				<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-submit">
					<?php esc_html_e( 'Generate', 'tutor-pro' ); ?>
				</button>
			</div>
			<!-- # modal footer -->
		</div>
	</div>
</div>
