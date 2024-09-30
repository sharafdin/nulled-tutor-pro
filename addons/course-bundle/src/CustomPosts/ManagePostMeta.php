<?php
/**
 * Manage course bundle meta data
 *
 * @package TutorPro\CourseBundle\CustomPosts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\CustomPosts;

use TUTOR\Course;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use TUTOR_CERT\Certificate;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\MetaBoxes\BundlePrice;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage course bundle post meta
 */
class ManagePostMeta {

	/**
	 * Course bundle product meta key
	 *
	 * @var string
	 */
	const BUNDLE_RIBBON_META_KEY = 'tutor_bundle_ribbon_type';

	/**
	 * Register hooks
	 *
	 * @since 2.2.0
	 */
	public function __construct() {
		add_action( 'save_post_' . CourseBundle::POST_TYPE, array( $this, 'update_bundle_meta' ), 10, 2 );
	}

	/**
	 * Update bundle product
	 *
	 * @since 2.2.0
	 *
	 * @param int     $post_id post id.
	 * @param WP_Post $post post.
	 *
	 * @return void
	 */
	public function update_bundle_meta( int $post_id, WP_Post $post ) {
		$product_id = (int) get_post_meta( $post_id, CourseModel::WC_PRODUCT_META_KEY, true );

		$sale_price = Input::post( 'tutor-bundle-sale-price', 0, Input::TYPE_NUMERIC );
		$reg_price  = BundlePrice::get_bundle_regular_price( $post_id );

		$product_id = Course::create_wc_product( $post->post_title, $reg_price, $sale_price, $product_id );

		// Update product meta.
		self::update_bundle_product_meta( $post_id, $product_id );

		// Update ribbon type.
		$ribbon_type = Input::post( 'tutor-bundle-ribbon-type', '', Input::TYPE_STRING );
		self::update_bundle_ribbon_meta( $post_id, $ribbon_type );

		// Update additional data.
		$benefits = Input::post( 'course_benefits', '', Input::TYPE_TEXTAREA );
		self::update_bundle_benefits( $post_id, $benefits );

		// TODO Certificate will be used later on.
		// if ( class_exists( Certificate::class ) ) {
		// 	$certificate = new Certificate( true );
		// 	$certificate->save_certificate_template_meta( $post_id );
		// }
	}

	/**
	 * Update bundle product meta
	 *
	 * @since 2.2.0
	 *
	 * @param integer $post_id post id.
	 * @param integer $product_id WC product id.
	 *
	 * @return mixed int|bool
	 */
	public static function update_bundle_product_meta( int $post_id, int $product_id ) {
		return update_post_meta( $post_id, CourseModel::WC_PRODUCT_META_KEY, $product_id );
	}

	/**
	 * Update bundle ribbon meta
	 *
	 * @since 2.2.0
	 *
	 * @param integer $post_id post id.
	 * @param string  $ribbon_type ribbon type.
	 *
	 * @return int|bool
	 */
	public static function update_bundle_ribbon_meta( int $post_id, string $ribbon_type ) {
		return update_post_meta( $post_id, self::BUNDLE_RIBBON_META_KEY, $ribbon_type );
	}

	/**
	 * Update bundle benefits meta (what i will learn)
	 *
	 * @since 2.2.0
	 *
	 * @param integer $post_id post id.
	 * @param string  $content benefits content.
	 *
	 * @return int|bool
	 */
	public static function update_bundle_benefits( int $post_id, string $content = '' ) {
		return update_post_meta( $post_id, CourseModel::BENEFITS_META_KEY, $content );
	}

	/**
	 * Get ribbon type
	 *
	 * @since 2.2.0
	 *
	 * @param integer $post_id post id.
	 *
	 * @return string
	 */
	public static function get_ribbon_type( int $post_id ): string {
		$type = get_post_meta( $post_id, self::BUNDLE_RIBBON_META_KEY, true );
		return $type ? $type : '';
	}

}
