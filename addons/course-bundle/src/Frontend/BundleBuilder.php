<?php
/**
 * Manage Course Bundle Frontend Builder
 *
 * @package TutorPro\CourseBundle
 * @subpackage Frontend
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Frontend;

use Tutor\Cache\FlashMessage;
use TUTOR\Input;
use TUTOR_PRO\General;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\Utils;
use WP_Admin_Bar;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Manage frontend builder functionalities
 */
class BundleBuilder {

	/**
	 * Query param to attach with the URL
	 *
	 * @var string
	 */
	const QUERY_PARAM = 'create-bundle';

	/**
	 * Register hooks
	 *
	 * @since 2.2.0
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', __CLASS__ . '::add_admin_toolbar', 100 );
		add_action( 'template_include', __CLASS__ . '::include_frontend_builder', 100 );
		add_filter( 'tutor_builder_screen', __CLASS__ . '::filter_builder_screen', 100 );
		add_action( 'tutor_action_update_course_bundle', __CLASS__ . '::update_course_bundle', 100 );
	}

	/**
	 * Get bundle edit link.
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 *
	 * @return string
	 */
	public static function get_edit_link( $bundle_id ) {
		return tutor_utils()->tutor_dashboard_url( self::QUERY_PARAM . '?bundle-id=' . $bundle_id );
	}

	/**
	 * Add bundle builder toolbar
	 *
	 * @since 2.2.0
	 *
	 * @param WP_Admin_Bar $admin_bar instance of WP_Admin_Bar.
	 *
	 * @return void
	 */
	public static function add_admin_toolbar( WP_Admin_Bar $admin_bar ) {
		// Return if not admin side.
		if ( ! is_admin() ) {
			return;
		}

		$id               = Utils::get_bundle_id();
		$post_type        = get_post_type( $id );
		$bundle_post_type = CourseBundle::POST_TYPE;

		$editor_link  = apply_filters(
			'tutor_frontend_builder_link',
			self::get_edit_link( $id )
		);
		$editor_title = apply_filters( 'tutor_frontend_builder_title', __( 'Edit with Frontend Builder', 'tutor-pro' ) );

		if ( $bundle_post_type === $post_type ) {
			$admin_bar->add_menu(
				array(
					'id'    => 'tutor-frontend-bundle-builder',
					'title' => $editor_title,
					'href'  => $editor_link,
					'meta'  => array(
						'title'  => $editor_title,
						'target' => '_blank',
					),
				)
			);
		}
	}

	/**
	 * Include frontend builder
	 *
	 * @since 2.2.0
	 *
	 * @param string $template template path.
	 *
	 * @return string
	 */
	public static function include_frontend_builder( $template ) {
		$bundle_id      = Utils::get_bundle_id();
		$dashboard_page = get_query_var( 'tutor_dashboard_page' );

		$is_bundle_create_page = 'create-bundle' === $dashboard_page;

		if ( CourseBundle::POST_TYPE === get_post_type( $bundle_id ) && $is_bundle_create_page ) {
			$template = Utils::template_path( 'dashboard/frontend-bundle-builder.php' );
		}
		return $template;
	}

	/**
	 * Filter builder screen to add course builder
	 * assets on the course bundle frontend screen
	 *
	 * @since 2.2.0
	 *
	 * @param mixed $screen screen value to update.
	 *
	 * @return mixed
	 */
	public static function filter_builder_screen( $screen ) {
		if ( ! is_admin() ) {
			$bundle_id = Utils::get_bundle_id();
			if ( CourseBundle::POST_TYPE === get_post_type( $bundle_id ) ) {
				$screen = 'frontend';
			}
		}
		return $screen;
	}

	/**
	 * Save course bundle
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public static function update_course_bundle() {
		$bundle_id = Utils::get_bundle_id();

		// Verify nonce.
		$nonce = Input::post( tutor()->nonce, '' );
		if ( ! wp_verify_nonce( $nonce, tutor()->nonce_action ) ) {
			tutor_set_flash_message(
				__( 'Nonce verification failed, please try again!', 'tutor-pro' ),
				FlashMessage::WARNING
			);
			return;
		}

		// Check user is authorized to perform this action.
		$is_admin  = current_user_can( 'administrator' );
		$is_author = Utils::is_bundle_author( $bundle_id );
		if ( ! $is_admin && ! $is_author ) {
			tutor_set_flash_message(
				tutor_utils()->error_message(),
				FlashMessage::WARNING
			);
			return;
		}

		$title   = Input::post( 'title', '' );
		$content = Input::post( 'content', '', Input::TYPE_KSES_POST );
		$slug    = Input::post( 'post_name', '' );

		$post_data = array(
			'ID'           => $bundle_id,
			'post_title'   => $title,
			'post_content' => $content,
			'post_name'    => $slug,
		);

		$submit_action = Input::post( 'course_submit_btn', '' );

		// Set bundle status.
		if ( 'save_course_as_draft' === $submit_action ) {
			$post_data['post_status'] = 'draft';
		} elseif ( 'submit_for_review' === $submit_action ) {
			$post_data['post_status'] = 'pending';
		} elseif ( 'publish_course' === $submit_action ) {
			$can_publish_course = (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );
			if ( $can_publish_course || current_user_can( 'administrator' ) ) {
				$post_data['post_status'] = 'publish';
			} else {
				$post_data['post_status'] = 'pending';
			}
		}

		$update = wp_update_post( $post_data );

		if ( is_wp_error( $update ) ) {
			tutor_set_flash_message(
				__( 'Course bundle update failed, please try again!', 'tutor-pro' ),
				FlashMessage::DANGER,
			);
			return;
		} else {
			// Update thumbnail.
			$thumbnail_id = (int) tutor_utils()->array_get( 'tutor_course_thumbnail_id', $_POST );
			General::update_post_thumbnail( $bundle_id, $thumbnail_id );

			tutor_set_flash_message( __( 'Course bundle updated successfully!', 'tutor-pro' ) );
			return;
		}
	}

}
