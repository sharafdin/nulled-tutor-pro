<?php
/**
 * Google meet API credential page
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TutorPro\GoogleMeet\GoogleMeet;

require __DIR__ . '/banner.php';
$plugin_data = GoogleMeet::meta_data();
?>
<form method="post" enctype="multipart/form-data">
	<div class="tutor-google-meet-credential-form tutor-option-single-item item-variation-dragndrop tutor-mt-24 <?php echo ! is_admin() ? 'tutor-border' : ''; ?>">
		<div class="item-wrapper">
			<div class="tutor-option-field-row tutor-d-block">
				<div class="tutor-option-field-label">
					<div class="drag-drop-zone">
						<div class="tutor-round-box tutor-mb-20">
							<img class="tutor-img-responsive" src="<?php echo esc_url( trailingslashit( $plugin_data['assets'] . 'images' ) . 'upload-json.svg' ); ?>" alt="Upload JSON file icon" />
						</div>
						<div class="tutor-fs-5 tutor-mb-32"><?php esc_html_e( 'Drag &amp; Drop your JSON File here, or', 'tutor-pro' ); ?> </div>
						<label class="tutor-d-inline-flex tutor-btn tutor-btn-primary tutor-btn-lg tutor-form-label" id="tutor-google-meet-choose-label" for="drag-drop-input">
							<input type="file" name="drag-drop-input" id="tutor-google-meet-credential-upload" class="tutor-d-none">
							<?php esc_html_e( 'Choose a file', 'tutor-pro' ); ?>
						</label>
						<div class="file-info tutor-fs-7"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
