<?php
/**
 * Manage Frontend My Bundle List.
 *
 * @package TutorPro\CourseBundle
 * @subpackage Frontend
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Frontend;

use TUTOR\Input;
use Tutor\Models\CourseModel;
use TutorPro\CourseBundle\Backend\BundleList;
use TutorPro\CourseBundle\Models\BundleModel;
use TutorPro\CourseBundle\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MyBundleList Class
 *
 * @since 2.2.0
 */
class MyBundleList {
	/**
	 * Register hooks.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'handle_status' ) );
		add_action( 'load_dashboard_template_part_from_other_location', array( $this, 'load_my_bundles_template' ), 100 );
		add_action( 'wp_ajax_tutor_delete_dashboard_bundle', array( $this, 'delete_bundle' ) );
	}

	/**
	 * Load dashboard my bundles templates.
	 *
	 * @since 2.2.0
	 *
	 * @param string $template template path.
	 *
	 * @return string
	 */
	public function load_my_bundles_template( $template ) {
		global $wp_query;
		$query_vars   = $wp_query->query_vars;
		$is_dashboard = isset( $query_vars['tutor_dashboard_page'] );

		if ( $is_dashboard && isset( $query_vars['tutor_dashboard_sub_page'] ) && 'pending-bundles' === $query_vars['tutor_dashboard_sub_page'] ) {
			$template = Utils::template_path( 'dashboard/my-bundles/pending-bundles.php' );
			if ( file_exists( $template ) ) {
				return $template;
			}
		}

		if ( $is_dashboard && isset( $query_vars['tutor_dashboard_sub_page'] ) && 'draft-bundles' === $query_vars['tutor_dashboard_sub_page'] ) {
			$template = Utils::template_path( 'dashboard/my-bundles/draft-bundles.php' );
			if ( file_exists( $template ) ) {
				return $template;
			}
		}

		if ( $is_dashboard && 'my-bundles' === $query_vars['tutor_dashboard_page'] ) {
			$template = Utils::template_path( 'dashboard/my-bundles/my-bundles.php' );
			if ( file_exists( $template ) ) {
				return $template;
			}
		}

		return $template;
	}

	/**
	 * Handler for change bundle status
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function handle_status() {
		$action = Input::get( 'tutor_action' );
		$id     = Input::get( 'bundle_id', 0, Input::TYPE_INT );
		$status = Input::get( 'status' );

		if ( 'update_bundle_status' !== $action
			|| 0 === $id
			|| ! in_array( $status, array( CourseModel::STATUS_DRAFT, CourseModel::STATUS_PENDING ), true )
			|| ! get_post( $id )
			) {
			// Invalid request for status update.
			return;
		}

		tutor_utils()->checking_nonce( 'GET' );

		if ( ! tutor_utils()->can_user_manage( 'course', $id ) ) {
			return;
		}

		$can_publish_course = current_user_can( 'administrator' ) || (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );

		/**
		 * If user is admin or has course publish permission, then
		 * Status pending will be changed to publish.
		 */
		if ( true === $can_publish_course && CourseModel::STATUS_PENDING === $status ) {
			$status = CourseModel::STATUS_PUBLISH;
		}

		$args = array(
			'ID'          => $id,
			'post_status' => $status,
		);

		wp_update_post( $args );

		$link          = wp_get_referer();
		$flash_message = null;

		if ( CourseModel::STATUS_PUBLISH === $status ) {
			$flash_message = __( 'Bundle successfully published', 'tutor-pro' );
			$link          = tutor_utils()->tutor_dashboard_url( 'my-bundles' );
		}

		if ( CourseModel::STATUS_PENDING === $status ) {
			$flash_message = __( 'Bundle submitted for review', 'tutor-pro' );
			$link          = tutor_utils()->tutor_dashboard_url( 'my-bundles/pending-bundles' );
		}

		if ( CourseModel::STATUS_DRAFT === $status ) {
			$flash_message = __( 'Bundle moved to draft', 'tutor-pro' );
			$link          = tutor_utils()->tutor_dashboard_url( 'my-bundles/draft-bundles' );
		}

		tutor_utils()->redirect_to( $link, $flash_message );
	}

	/**
	 * Delete bundle from frontend dashboard.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function delete_bundle() {
		tutor_utils()->checking_nonce();

		$bundle_id = Input::post( 'bundle_id', 0, Input::TYPE_INT );
		if ( ! tutor_utils()->can_user_manage( 'course', $bundle_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor-pro' ) ) );
		}

		if ( ! current_user_can( 'administrator' ) ) {
			$can_delete_bundle = tutor_utils()->get_option( 'instructor_can_delete_course' );
			if ( $can_delete_bundle ) {
				$trash_bundle = wp_update_post(
					array(
						'ID'          => $bundle_id,
						'post_status' => 'trash',
					)
				);
				if ( $trash_bundle ) {
					wp_send_json_success( __( 'Bundle successfully trashed', 'tutor-pro' ) );
				}
			}
			wp_send_json_error( tutor_utils()->error_message() );
		}

		if ( BundleModel::get_total_bundle_sold( $bundle_id ) > 0 ) {
			wp_send_json_error( array( 'message' => BundleList::get_delete_restriction_message() ) );
		}

		BundleModel::delete_bundle( $bundle_id );
		wp_send_json_success();
	}
}
