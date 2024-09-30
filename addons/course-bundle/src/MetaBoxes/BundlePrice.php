<?php
/**
 * Course Bundle Builder meta boxes
 *
 * @package TutorPro\CourseBundle\MetaBoxes
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\MetaBoxes;

use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\Models\BundleModel;
use TutorPro\CourseBundle\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register meta boxes
 */
class BundlePrice implements MetaBoxInterface {

	/**
	 * Meta box id
	 *
	 * @var string
	 */
	const META_BOX_ID = 'tutor-course-bundle-price';

	/**
	 * Get meta box id
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_id(): string {
		return self::META_BOX_ID;
	}

	/**
	 * Get title
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Price', 'tutor-pro' );
	}

	/**
	 * Get screen
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_screen() {
		return CourseBundle::POST_TYPE;
	}

	/**
	 * Get context
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_context(): string {
		return 'advanced';
	}

	/**
	 * Get priority
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_priority(): string {
		return 'default';
	}

	/**
	 * Get args
	 *
	 * Args to pass to the callback func
	 *
	 * @since 2.2.0
	 *
	 * @return mixed
	 */
	public function get_args() {

	}

	/**
	 * Meta box callback
	 *
	 * Render meta box view
	 *
	 * @return void
	 */
	public function callback() {
		$view_file = Utils::view_path( 'backend/bundle-price.php' );
		tutor_load_template_from_custom_path( $view_file, array(), false );
	}

	/**
	 * Get bundle price
	 *
	 * It will calculate all the course price of a bundle
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 *
	 * @return int bundle regular price
	 */
	public static function get_bundle_regular_price( int $bundle_id ) {
		$course_ids = BundleModel::get_bundle_course_ids( $bundle_id );
		$price      = 0;

		foreach ( $course_ids as $course_id ) {
			$product_id = tutor_utils()->get_course_product_id( $course_id );
			$product    = wc_get_product( $product_id );
			if ( $product ) {
				$product_price = (float) $product->get_regular_price();
				$price        += $product_price;
			}
		}

		return $price;
	}

	/**
	 * Wrapper method to get raw course price
	 *
	 * @since 2.2.0
	 *
	 * @param integer $bundle_id int bundle id.
	 *
	 * @return int|float
	 */
	public static function get_bundle_sale_price( int $bundle_id ) {
		$price = tutor_utils()->get_raw_course_price( $bundle_id );
		return is_numeric( $price->sale_price ) ? $price->sale_price : 0;
	}

	/**
	 * Get bundle discount by ribbon settings of bundle.
	 *
	 * @since 2.2.0
	 *
	 * @param int    $bundle_id bundle id.
	 * @param string $ribbon_type ribbon type.
	 * @param bool   $symbol symbol.
	 *
	 * @return int|string
	 */
	public static function get_bundle_discount_by_ribbon( $bundle_id, $ribbon_type, $symbol = true ) {
		if ( BundleModel::RIBBON_NONE === $ribbon_type ) {
			return '';
		}

		$regular_price = self::get_bundle_regular_price( $bundle_id );
		$sale_price    = self::get_bundle_sale_price( $bundle_id );

		if ( BundleModel::RIBBON_PERCENTAGE === $ribbon_type ) {
			$discount = 0;
			try {
				$discount = ( $regular_price - $sale_price ) / $regular_price * 100;
			} catch ( \Throwable $th ) {
				$discount = 0;
			}

			$discount = round( $discount, 2 );
			return $symbol ? $discount . '%' : $discount;
		}

		if ( BundleModel::RIBBON_AMOUNT === $ribbon_type ) {
			$discount = $regular_price - $sale_price;
			$discount = round( $discount, 2 );

			$currency_sign = get_woocommerce_currency_symbol();
			return $symbol ? $currency_sign . $discount : $discount;
		}

	}

}
