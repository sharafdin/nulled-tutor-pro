<?php
/**
 * Backend Course Bundle Listing
 *
 * @package TutorPro\CourseBundle
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Backend;

use TUTOR\Backend_Page_Trait;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use TUTOR\User;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\Models\BundleModel;

/**
 * BundleList Class.
 *
 * @since 2.2.0
 */
class BundleList {

	use Backend_Page_Trait;

	/**
	 * Register hooks.
	 *
	 * @since 2.2.0
	 *
	 * @param bool $register_hooks register hooks.
	 *
	 * @return void|null
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'wp_ajax_tutor_bundle_list_bulk_action', array( $this, 'handle_bulk_action' ) );
		add_action( 'wp_ajax_tutor_change_bundle_status', array( $this, 'change_bundle_status' ) );
		add_action( 'wp_ajax_tutor_bundle_delete', array( $this, 'delete_bundle' ) );
		add_action( 'trashed_post', array( $this, 'redirect_to_bundle_list_page' ) );

		add_action( 'save_post_' . CourseModel::POST_TYPE, array( $this, 'assign_category_to_bundle' ), 100 );
		add_action( 'save_post_' . CourseBundle::POST_TYPE, array( $this, 'assign_bundle_category' ), 100 );
	}

	/**
	 * Assign course category to bundle category
	 *
	 * @since 2.6.0
	 *
	 * @param int $post_id post id.
	 *
	 * @return void
	 */
	public function assign_category_to_bundle( $post_id ) {
		if ( CourseModel::POST_TYPE !== get_post_type( $post_id ) ) {
			return;
		}

		$bundle_id = BundleModel::get_bundle_id_by_course( $post_id );
		if ( ! $bundle_id ) {
			return;
		}

		$this->assign_bundle_category( $bundle_id );
	}

	/**
	 * Assign bundle category.
	 *
	 * @param int $post_id post id.
	 *
	 * @return void
	 */
	public static function assign_bundle_category( $post_id ) {
		if ( CourseBundle::POST_TYPE !== get_post_type( $post_id ) ) {
			return;
		}

		$categories = BundleModel::get_bundle_course_categories( $post_id );
		$cat_ids    = array_column( $categories, 'term_id' );

		wp_set_post_terms( $post_id, $cat_ids, 'course-category' );
	}

	/**
	 * After trash a bundle direct to the bundle list page
	 *
	 * @since 2.2.4
	 *
	 * @param integer $post_id int bundle id.
	 *
	 * @return void
	 */
	public static function redirect_to_bundle_list_page( int $post_id ): void {
		$post = get_post( $post_id );
		if ( CourseBundle::POST_TYPE === $post->post_type ) {
			$is_gutenberg_enabled = tutor_utils()->get_option( 'enable_gutenberg_course_edit' );
			if ( ! $is_gutenberg_enabled ) {
				wp_safe_redirect( admin_url( 'admin.php?page=course-bundle' ) );
				exit;
			}
		}
	}

	/**
	 * Get bundle delete restriction message.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public static function get_delete_restriction_message() {
		return __( 'This bundle has enrolled student. It can not be deleted', 'tutor-pro' );
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	public function prepare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_publish(),
			$this->bulk_action_pending(),
			$this->bulk_action_draft(),
		);

		$active_tab = Input::get( 'data', '' );

		if ( 'trash' === $active_tab ) {
			array_push( $actions, $this->bulk_action_delete() );
		}
		if ( 'trash' !== $active_tab ) {
			array_push( $actions, $this->bulk_action_trash() );
		}

		if ( ! current_user_can( 'administrator' ) ) {
			$can_trash_post = tutor_utils()->get_option( 'instructor_can_delete_course' ) && current_user_can( 'edit_tutor_course' );
			if ( ! $can_trash_post ) {
				$actions = array_filter(
					$actions,
					function ( $val ) {
						return 'trash' !== $val['value'];
					}
				);
			}
		}
		return apply_filters( 'tutor_bundle_bulk_actions', $actions );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @since 2.2.0
	 *
	 * @param string  $category_slug category slug.
	 * @param integer $post_id bundle ID.
	 * @param string  $date selected date | optional.
	 * @param string  $search search by user name or email | optional.
	 *
	 * @return array
	 */
	public function tabs_key_value( $category_slug, $post_id, $date, $search ): array {
		$url = get_pagenum_link();

		$all       = self::count( 'all', $category_slug, $post_id, $date, $search );
		$mine      = self::count( 'mine', $category_slug, $post_id, $date, $search );
		$published = self::count( 'publish', $category_slug, $post_id, $date, $search );
		$draft     = self::count( 'draft', $category_slug, $post_id, $date, $search );
		$pending   = self::count( 'pending', $category_slug, $post_id, $date, $search );
		$trash     = self::count( 'trash', $category_slug, $post_id, $date, $search );
		$private   = self::count( 'private', $category_slug, $post_id, $date, $search );
		$future    = self::count( 'future', $category_slug, $post_id, $date, $search );

		$tabs = array(
			array(
				'key'   => 'all',
				'title' => __( 'All', 'tutor-pro' ),
				'value' => $all,
				'url'   => $url . '&data=all',
			),
			array(
				'key'   => 'mine',
				'title' => __( 'Mine', 'tutor-pro' ),
				'value' => $mine,
				'url'   => $url . '&data=mine',
			),
			array(
				'key'   => 'published',
				'title' => __( 'Published', 'tutor-pro' ),
				'value' => $published,
				'url'   => $url . '&data=published',
			),
			array(
				'key'   => 'draft',
				'title' => __( 'Draft', 'tutor-pro' ),
				'value' => $draft,
				'url'   => $url . '&data=draft',
			),
			array(
				'key'   => 'pending',
				'title' => __( 'Pending', 'tutor-pro' ),
				'value' => $pending,
				'url'   => $url . '&data=pending',
			),
			array(
				'key'   => 'future',
				'title' => __( 'Scheduled', 'tutor-pro' ),
				'value' => $future,
				'url'   => $url . '&data=future',
			),
			array(
				'key'   => 'private',
				'title' => __( 'Private', 'tutor-pro' ),
				'value' => $private,
				'url'   => $url . '&data=private',
			),
			array(
				'key'   => 'trash',
				'title' => __( 'Trash', 'tutor-pro' ),
				'value' => $trash,
				'url'   => $url . '&data=trash',
			),
		);

		if ( ! tutor_utils()->get_option( 'instructor_can_delete_course' ) && ! current_user_can( 'administrator' ) ) {
			unset( $tabs[7] );
		}
		return apply_filters( 'tutor_bundle_tabs', $tabs );
	}

	/**
	 * Count bundles by status & filters
	 * Count all | min | published | pending | draft
	 *
	 * @since 2.2.0
	 *
	 * @param string $status | required.
	 * @param string $category_slug category | optional.
	 * @param string $post_id selected id | optional.
	 * @param string $date selected date | optional.
	 * @param string $search_term search by user name or email | optional.
	 *
	 * @return int
	 */
	protected static function count( string $status, $category_slug = '', $post_id = '', $date = '', $search_term = '' ): int {
		$user_id       = get_current_user_id();
		$status        = sanitize_text_field( $status );
		$post_id       = sanitize_text_field( $post_id );
		$date          = sanitize_text_field( $date );
		$search_term   = sanitize_text_field( $search_term );
		$category_slug = sanitize_text_field( $category_slug );

		$args = array(
			'post_type' => CourseBundle::POST_TYPE,
		);

		if ( 'all' === $status || 'mine' === $status ) {
			$args['post_status'] = array( 'publish', 'pending', 'draft', 'private', 'future' );
		} else {
			$args['post_status'] = array( $status );
		}

		// Author query.
		if ( 'mine' === $status || ! current_user_can( 'administrator' ) ) {
			$args['author'] = $user_id;
		}

		$date_filter = sanitize_text_field( $date );

		$year  = gmdate( 'Y', strtotime( $date_filter ) );
		$month = gmdate( 'm', strtotime( $date_filter ) );
		$day   = gmdate( 'd', strtotime( $date_filter ) );

		// Add date query.
		if ( '' !== $date_filter ) {
			$args['date_query'] = array(
				array(
					'year'  => $year,
					'month' => $month,
					'day'   => $day,
				),
			);
		}

		if ( '' !== $post_id ) {
			$args['p'] = $post_id;
		}

		// Search filter.
		if ( '' !== $search_term ) {
			$args['s'] = $search_term;
		}

		// Category filter.
		if ( '' !== $category_slug ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'course-category',
					'field'    => 'slug',
					'terms'    => $category_slug,
				),
			);
		}

		$the_query = new \WP_Query( $args );

		return ! is_null( $the_query ) && isset( $the_query->found_posts ) ? $the_query->found_posts : $the_query;
	}

	/**
	 * Handle bulk action.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function handle_bulk_action() {

		tutor_utils()->checking_nonce();

		$action   = Input::post( 'bulk-action', '' );
		$bulk_ids = Input::post( 'bulk-ids', '' );

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			if ( current_user_can( 'edit_tutor_course' ) ) {
				$can_publish_course = tutor_utils()->get_option( 'instructor_can_publish_course' );

				if ( 'publish' === $action && ! $can_publish_course ) {
					wp_send_json_error( tutor_utils()->error_message() );
				}
			} else {
				wp_send_json_error( tutor_utils()->error_message() );
			}
		}

		if ( '' === $action || '' === $bulk_ids ) {
			wp_send_json_error( array( 'message' => __( 'Please select appropriate action', 'tutor-pro' ) ) );
			exit;
		}

		if ( 'delete' === $action ) {
			do_action( 'before_tutor_bundle_bulk_action_delete', $bulk_ids );

			$deleted = self::bulk_delete( $bulk_ids );

			/**
			 * Delete error handle.
			 *
			 * @var \WP_Error|bool $deleted
			 */
			if ( is_wp_error( $deleted ) ) {
				wp_send_json_error(
					array(
						'message' => $deleted->get_error_message(),
					)
				);
			}

			if ( true === $deleted ) {
				do_action( 'after_tutor_bundle_bulk_action_delete', $bulk_ids );
				wp_send_json_success();
			} else {
				wp_send_json_error( array( 'message' => __( 'Could not delete selected bundles', 'tutor-pro' ) ) );
			}
		}

		/**
		 * Do action before bundle update
		 *
		 * @param string $action (publish | pending | draft | trash).
		 * @param array $bulk_ids, course id.
		 */
		do_action( 'before_tutor_bundle_bulk_action_update', $action, $bulk_ids );

		$update_status = self::update_bundle_status( $action, $bulk_ids );

		do_action( 'after_tutor_bundle_bulk_action_update', $action, $bulk_ids );

		$update_status ? wp_send_json_success() : wp_send_json_error(
			array(
				'message' => 'Could not update bundle status',
				'tutor-pro',
			)
		);

		exit;
	}

	/**
	 * Handle ajax request for updating bundle status
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public static function change_bundle_status() {
		tutor_utils()->checking_nonce();

		$status = Input::post( 'status' );
		$id     = Input::post( 'id' );
		$bundle = get_post( $id );

		if ( CourseBundle::POST_TYPE !== $bundle->post_type ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {

			if ( ! tutor_utils()->can_user_manage( 'course', $bundle->ID ) ) {
				wp_send_json_error( tutor_utils()->error_message() );
			}

			$can_delete_bundle  = tutor_utils()->get_option( 'instructor_can_delete_course' );
			$can_publish_bundle = tutor_utils()->get_option( 'instructor_can_publish_course' );

			if ( 'publish' === $status && ! $can_publish_bundle ) {
				wp_send_json_error( tutor_utils()->error_message() );
			}

			if ( $can_delete_bundle && 'trash' === $status ) {
				$trash_bundle = wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => $status,
					)
				);
				if ( $trash_bundle ) {
					wp_send_json_success( __( 'Bundle successfully trashed', 'tutor-pro' ) );
				}
			}
		}

		$args = array(
			'ID'          => $id,
			'post_status' => $status,
		);

		if ( 'future' === $bundle->post_status && 'publish' === $status ) {
			$args['post_status']   = 'publish';
			$args['post_date']     = current_time( 'mysql' );
			$args['post_date_gmt'] = current_time( 'mysql', 1 );
		}

		wp_update_post( $args );
		wp_send_json_success();
		exit;
	}

	/**
	 * Handle ajax request for deleting bundle
	 *
	 * @since 2.2.0
	 *
	 * @return void json response
	 */
	public static function delete_bundle() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		$roles = array( User::ADMIN, User::INSTRUCTOR );
		if ( ! User::has_any_role( $roles ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$bundle_id = Input::post( 'id', 0, Input::TYPE_INT );
		if ( BundleModel::get_total_bundle_sold( $bundle_id ) > 0 ) {
			wp_send_json_error( self::get_delete_restriction_message() );
		}

		$delete = BundleModel::delete_bundle( $bundle_id );

		if ( $delete ) {
			wp_send_json_success( __( 'Bundle successfully deleted', 'tutor-pro' ) );
		} else {
			wp_send_json_error( __( 'Could not delete bundle', 'tutor-pro' ) );
		}

		exit;
	}

	/**
	 * Execute bulk delete action
	 *
	 * @since 2.2.0
	 *
	 * @param string $bulk_ids ids that need to update.
	 *
	 * @return bool
	 */
	public static function bulk_delete( $bulk_ids ): bool {
		$bulk_ids = explode( ',', sanitize_text_field( $bulk_ids ) );

		foreach ( $bulk_ids as $bundle_id ) {
			if ( BundleModel::get_total_bundle_sold( $bundle_id ) > 0 ) {
				// If selected bundle has enrolled student. Delete operation is not allowed.
				return new \WP_Error( 'bundle_has_enrolled_student', self::get_delete_restriction_message() );
			}
		}

		foreach ( $bulk_ids as $post_id ) {

			BundleModel::delete_bundle( $post_id );
		}

		return true;
	}

	/**
	 * Update bundle status
	 *
	 * @param string $status for updating bundle status.
	 * @param string $bulk_ids comma separated ids.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	public static function update_bundle_status( string $status, $bulk_ids ): bool {
		global $wpdb;
		$post_table = $wpdb->posts;
		$status     = sanitize_text_field( $status );
		$bulk_ids   = sanitize_text_field( $bulk_ids );

		$update = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$post_table} SET post_status = %s WHERE ID IN ($bulk_ids)", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$status
			)
		);

		return true;
	}
}
