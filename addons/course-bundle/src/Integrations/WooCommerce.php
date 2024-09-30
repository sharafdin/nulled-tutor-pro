<?php
/**
 * Handle woocommerce hooks for course bundle.
 *
 * @package TutorPro\CourseBundle
 * @subpackage Integrations
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Integrations;

use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\Models\BundleModel;

/**
 * WooCommerce Class
 *
 * @since 1.0.0
 */
class WooCommerce {

	/**
	 * Register hooks
	 *
	 * @since 2.2.0
	 *
	 * @return void|null
	 */
	public function __construct() {
		$monetize_by = tutor_utils()->get_option( 'monetize_by' );
		if ( 'wc' !== $monetize_by ) {
			return;
		}

		add_action( 'woocommerce_order_status_changed', array( $this, 'bundle_course_enrol' ), 10, 3 );
	}

	/**
	 * Auto enrol to bundle courses when order is completed.
	 *
	 * @since 2.2.0
	 *
	 * @param int    $order_id      order id.
	 * @param string $status_from   status from.
	 * @param string $status_to     status to.
	 *
	 * @return void
	 */
	public function bundle_course_enrol( $order_id, $status_from, $status_to ) {
		if ( 'completed' !== $status_to || ! tutor_utils()->is_tutor_order( $order_id ) ) {
			return;
		}

		$order         = wc_get_order( $order_id );
		$order_user_id = $order->get_user_id();
		$order_items   = $order->get_items();

		tutor_utils()->change_earning_status( $order_id, $status_to );

		/**
		 * WC order items.
		 *
		 * @var $item \WC_Order_Item_Product
		 */
		foreach ( $order_items as $item ) {
			$product_id  = $item->get_product_id();
			$bundle_data = tutor_utils()->product_belongs_with_course( $product_id );
			$bundle_id   = isset( $bundle_data->post_id ) ? $bundle_data->post_id : 0;
			$post_type   = get_post_type( $bundle_id );

			if ( CourseBundle::POST_TYPE === $post_type ) {
				BundleModel::enroll_to_bundle_courses( $bundle_id, $order_user_id );
			}
		}
	}
}
