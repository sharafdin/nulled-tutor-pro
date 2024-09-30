<?php
/**
 * Frontend course bundle builder
 *
 * @package TutorPro\CourseBundle
 * @subpackage Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Cache\FlashMessage;
use TUTOR_CERT\Certificate;
use TutorPro\CourseBundle\MetaBoxes\BundleAdditionalData;
use TutorPro\CourseBundle\MetaBoxes\BundlePrice;
use TutorPro\CourseBundle\Models\BundleModel;
use TutorPro\CourseBundle\Utils;

get_tutor_header( true );

$bundle_id   = Utils::get_bundle_id();
$post        = get_post( $bundle_id ); //phpcs:ignore
$post_slug   = $post->post_name;
$description = $post->post_content;

setup_postdata( $post );

$user_id               = get_current_user_id();
$can_edit_current_post = Utils::is_bundle_author( $bundle_id );
$is_admin              = current_user_can( 'administrator' );
$total_enrolled        = BundleModel::get_total_bundle_sold( $bundle_id );


if ( $is_admin || $can_edit_current_post ) {
	get_tutor_header( true );

	$flash_msg = new FlashMessage();
	?>
	<form method="post" id="tutor-frontend-course-builder">
		<input type="hidden" name="tutor_action" value="update_course_bundle">
		<?php tutor_nonce_field(); ?>
		<?php
		// Load sticky header.
		$header = tutor_pro()->templates . 'components/sticky-header.php';
		include $header;
		do_action( 'tutor_pro_course_bundle_before_frontend_builder' );
		?>
		<div class="tutor-container">
			<?php if ( $flash_msg->has_cache() ) : ?>
				<div class="tutor-row">
					<?php $flash_msg->show(); ?>
				</div>
			<?php endif; ?>

			<?php
			// Show bundle edit restriction message.
			$restriction_template = Utils::view_path( 'backend/components/bundle-restriction.php' );
			tutor_load_template_from_custom_path(
				$restriction_template,
				array( 'total_enrolled' => $total_enrolled )
			);
			?>

			<div class="tutor-row tutor-pro-course-bundle-builder-wrapper">
				<div class="tutor-col-12 tutor-col-lg-7 tutor-mb-32 tutor-pr-32">
					<!-- bundle overview  -->
					<div class="tutor-course-builder-section">
						<div class="tutor-course-builder-section-title">
							<span class="tutor-fs-5 tutor-fw-bold tutor-color-secondary">
								<i class="tutor-icon-angle-up" area-hidden="true"></i>
								<span><?php esc_html_e( 'Bundle Overview', 'tutor-pro' ); ?></span>
							</span>
						</div>
						<div class="tutor-course-builder-section-content">
							<div class="tutor-mb-32">
								<label class="tutor-course-field-label tutor-fs-6 tutor-color-black" id="tutor-course-bundle-title"><?php esc_html_e( 'Bundle Title', 'tutor-pro' ); ?></label>
								<div id="tutor-course-create-title-tooltip-wrapper" class="tooltip-wrap tutor-d-block tutor-mt-12">
									<span class="tooltip-txt tooltip-right tutor-mt-12">
										<?php esc_html_e( '255', 'tutor-pro' ); ?>
									</span>
									<input id="tutor-course-bundle-title" type="text" name="title" class="tutor-form-control" value="<?php echo esc_html( get_the_title( $bundle_id ) ); ?>" placeholder="<?php esc_html_e( 'ex. Learn Photoshop CS6 from scratch', 'tutor-pro' ); ?>" maxlength="255">
								</div>
							</div>
							<div class="tutor-mb-32">
								<label class="tutor-course-field-label tutor-fs-6 tutor-color-black"><?php esc_html_e( 'Bundle Slug', 'tutor-pro' ); ?></label>
								<div id="tutor-course-create-slug-tooltip-wrapper" class="tooltip-wrap tutor-d-block">
									<span class="tooltip-txt tooltip-right tutor-mt-12">
										<?php esc_html_e( '255', 'tutor-pro' ); ?>
									</span>
									<input id="tutor-course-slug" type="text" name="post_name" class="tutor-form-control" placeholder="<?php esc_html_e( 'Please enter the bundle slug here', 'tutor-pro' ); ?>" value="<?php echo esc_html( $post_slug ); ?>" maxlength="255">
									<div class="tutor-fs-7 tutor-has-icon tutor-color-muted tutor-mt-12">
										<?php esc_html_e( 'Permalink: ', 'tutor-pro' ); ?>
										<a href="<?php echo esc_url( get_the_permalink( $bundle_id ) ); ?>" target="_blank">
											<?php echo esc_url( get_the_permalink( $bundle_id ) ); ?>
										</a>
									</div>
								</div>
							</div>
							<div class="tutor-mb-32">
								<label class="tutor-course-field-label tutor-fs-6 tutor-color-black"><?php esc_html_e( 'Description', 'tutor-pro' ); ?></label>
								<div class="tutor-mb-16  tutor-mt-12">
									<?php
									$editor_settings = array(
										'media_buttons' => false,
										'quicktags'     => false,
										'editor_height' => 150,
										'textarea_name' => 'content',
										'statusbar'     => false,
									);
									wp_editor( $description, 'course_description', $editor_settings );
									?>
								</div>
							</div>
							<div class="tutor-mb-32">
								<label class="tutor-course-field-label tutor-fs-6"><?php esc_html_e( 'Bundle Thumbnail', 'tutor-pro' ); ?></label>
								<div class="tutor-mb-16">
									<?php
									tutor_load_template_from_custom_path(
										tutor()->path . '/views/fragments/thumbnail-uploader.php',
										array(
											'media_id'    => get_post_thumbnail_id( $bundle_id ),
											'input_name'  => 'tutor_course_thumbnail_id',
											'placeholder' => tutor()->url . '/assets/images/thumbnail-placeholder.svg',
											'borderless'  => true,
											'background'  => '#E3E6EB',
											'border'      => '#E3E6EB',
										),
										false
									);
									?>
								</div>
							</div>
							<div class="tutor-mb-32">
								<?php ( new BundlePrice() )->callback(); ?>
							</div>
						</div>
					</div>
					<!-- bundle overview end -->

					<!-- additional data & certificate section -->
					<div class="tutor-mb-32">
						<?php
							ob_start();
							// Show additional data section.
							( new BundleAdditionalData() )->callback();
							$additional_data = ob_get_clean();

							course_builder_section_wrap(
								$additional_data,
								__( 'Additional Data', 'tutor-pro' )
							);

							// TODO Certificate will be used later on.
							// ob_start();
							// ( new Certificate( true ) )->render_template_selection_ui( get_post( $bundle_id ), true );
							// $certificate_template = ob_get_clean();

							// course_builder_section_wrap(
							// $certificate_template,
							// __( 'Certificate Template', 'tutor-pro' )
							// );
						?>
					</div>
					<!-- additional data & certificate section end -->
				</div>
				<!-- sidebar  -->
				<div class="tutor-col-12 tutor-col-lg-5 tutor-mb-32 tutor-pl-40 tutor-course-bundle-builder-components">
					<div class="tutor-mb-32">
					<?php
						// Course selection component.
						$course_selection_template = Utils::view_path( 'backend/components/bundle-course-selection.php' );
						tutor_load_template_from_custom_path( $course_selection_template );
					?>
					</div>
					<!-- placeholders to show dynamic data  -->
					<div id="tutor-bundle-course-list-wrapper" class="tutor-mb-32"></div>
					<div id="tutor-course-bundle-overview-wrapper"  class="tutor-mb-32"></div>
					<div id="tutor-course-bundle-authors-wrapper"></div>
				</div>
				<input type="hidden" id="tutor-course-bundle-id" name="bundle-id" value="<?php echo esc_attr( $bundle_id ); ?>">
			</div>
		</div>
		<?php do_action( 'tutor_pro_course_bundle_after_frontend_builder' ); ?>
	</form>

	<?php
	get_tutor_footer( true );
} else {
	tutor_permission_denied_template();
}


