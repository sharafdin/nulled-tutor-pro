<?php
/**
 * Bundle price meta box
 *
 * @since 2.2.0
 *
 * @package TutorPro\CourseBundle\Views
 */

use TUTOR\Input;
use TutorPro\CourseBundle\CustomPosts\ManagePostMeta;
use TutorPro\CourseBundle\MetaBoxes\BundlePrice;
use TutorPro\CourseBundle\Models\BundleModel;

$ribbon_options = BundleModel::get_ribbon_display_options();
$bundle_id      = Input::get( 'bundle-id', null ) ?? get_the_ID();

$regular_price   = BundlePrice::get_bundle_regular_price( $bundle_id );
$sale_price      = BundlePrice::get_bundle_sale_price( $bundle_id );
$max_sale_price  = $regular_price - 1;
$currency_symbol = tutor_utils()->currency_symbol();

do_action( 'tutor_pro_course_bundle_before_price_meta_box' );
?>
<div class="tutor-pro-course-bundle-price-wrapper">
	<div class="tutor-row">
		<div class="tutor-col-12 tutor-col-lg-7">
			<div class="tutor-row">
				<!-- price  -->
				<div class="tutor-col-6 tutor-col-sm-6 tutor-col-lg-6 tutor-course-price-row-regular">
					<div class="tutor-form-label"><?php esc_html_e( 'Bundle Sale Price', 'tutor-pro' ); ?></div>
						<div class="tutor-form-check tutor-align-center tutor-d-flex">
							<label for="tutor-pro-bundle-price" class="tutor-amount-field">
								<div class="tutor-input-group">
									<span class="tutor-input-group-addon">
										<?php echo esc_html( $currency_symbol ); ?>
									</span>
									<input type="number" class="tutor-form-number-verify tutor-form-control" name="tutor-bundle-sale-price" id="tutor-pro-bundle-price" value="<?php echo esc_attr( $sale_price ? $sale_price : '' ); ?>" placeholder="<?php esc_html_e( 'Set Bundle price', 'tutor-pro' ); ?>" step="any" min="0" pattern="^\d*(\.\d{0,2})?$" data-regular-price="<?php echo esc_attr( $regular_price ); ?>"
									<?php echo esc_attr( $max_sale_price > 0 ? "max={$max_sale_price} " : '' ); ?>>
								</div>
							</label>
						</div>
					</div>
					<div class="tutor-col-6 tutor-col-sm-6 tutor-col-lg-6 tutor-course-price-paid">
						<div class="tutor-form-label"><?php esc_html_e( 'Subtotal Regular Price', 'tutor-pro' ); ?></div>
						<div class="tutor-form-check tutor-align-center tutor-d-flex">
							<label id="tutor-bundle-subtotal-price">
                                <?php echo tutor_utils()->tutor_price( $regular_price ); //phpcs:ignore ?>
							</label>
						</div>
					</div>            
				<!-- price end -->
			</div>
		</div>
		<div class="tutor-col-12 tutor-col-lg-5">
			<div class="tutor-wp-dashboard-filter-item">
				<label for="tutor-pro-bundle-ribbon" class="tutor-form-label">
					<?php esc_html_e( 'Select Ribbon to Display', 'tutor-pro' ); ?>
				</label>
				<select class="tutor-form-select" name="tutor-bundle-ribbon-type">
					<?php if ( is_array( $ribbon_options ) && count( $ribbon_options ) ) : ?>
						<?php foreach ( $ribbon_options as $key => $option ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, ManagePostMeta::get_ribbon_type( $bundle_id ) ); ?>>
								<?php echo esc_html( $option ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
		</div>
	</div>
</div>
<?php
do_action( 'tutor_pro_course_bundle_after_price_meta_box' );
