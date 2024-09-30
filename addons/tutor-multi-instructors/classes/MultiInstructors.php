<?php
/**
 * Tutor Multi Instructor
 *
 * @author themeum
 * @link https://themeum.com
 * @package TutorPro\MultiInstructors
 */

namespace TUTOR_MT;

use TUTOR\Input;
use Tutor\Helpers\QueryHelper;

/**
 * Handle multi instructors logics
 */
class MultiInstructors {

	/**
	 * Register Hooks
	 */
	public function __construct() {
		// Modal Perform.
		add_action( 'wp_ajax_tutor_add_instructors_to_course', array( $this, 'tutor_add_instructors_to_course' ) );
		add_action( 'wp_ajax_detach_instructor_from_course', array( $this, 'detach_instructor_from_course' ) );
		add_action( 'wp_ajax_tutor_course_instructor_search', array( $this, 'tutor_course_instructor_search' ) );

		if ( tutor_utils()->get_option( 'enable_course_marketplace' ) ) {
			// Backend editor metabox.
			add_action(
				'add_meta_boxes',
				function() {
					tutor_meta_box_wrapper( 'tutor-instructors', __( 'Instructors', 'tutor' ), array( $this, 'instructors_metabox' ), tutor()->course_post_type, 'advanced', 'default', 'tutor-admin-post-meta' );
				}
			);

			// Front editor metabox.
			add_action( 'tutor/frontend_course_edit/after/course_builder', array( $this, 'front_metabox' ) );
		}
		/**
		 * Change the main instructor of the course
		 *
		 * @since v2.0.7
		 */
		$course_post_type = tutor()->course_post_type;
		add_action( "save_post_{$course_post_type}", __CLASS__ . '::change_main_instructor' );
	}

	/**
	 * Handle ajax request, search instructor
	 *
	 * @return void wp_json response
	 */
	public function tutor_course_instructor_search() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		// Gather data
		$course_id    = (int) sanitize_text_field( $_POST['course_id'] );
		$search_terms = sanitize_text_field( tutor_utils()->avalue_dot( 'search_terms', $_POST ) );

		$shortlisted = isset( $_POST['shortlisted'] ) ? $_POST['shortlisted'] : array();
		$shortlisted = array_filter(
			$shortlisted,
			function( $id ) {
				return is_numeric( $id );
			}
		);

		// Check if user can manage the course
		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		// Get already added instructor list
		$saved_instructors = tutor_utils()->get_instructors_by_course( $course_id );
		$saved_instructors = $saved_instructors ? wp_list_pluck( $saved_instructors, 'ID' ) : array();
		$saved_instructors = array_merge( $saved_instructors, $shortlisted );
		$saved_instructors = array_unique( $saved_instructors );
		$not_in_sql        = '';

		$instructors = array();

		// Exclude already added instructors from the search query
		if ( count( $saved_instructors ) ) {
			$instructor_not_in_ids = implode( ',', $saved_instructors );
			$not_in_sql           .= "AND user.ID NOT IN($instructor_not_in_ids) ";
		}

		$search_sql = '';
		if ( $search_terms ) {
			$search_sql = "AND (user.user_login like '%{$search_terms}%' 
							OR user.user_nicename like '%{$search_terms}%' 
							OR user.display_name like '%{$search_terms}%') ";
		}

		// Final search query
		$instructors = $wpdb->get_results(
			"SELECT user.ID, user.display_name, user.user_email
			FROM {$wpdb->users} user
			INNER JOIN {$wpdb->usermeta} meta ON user.ID = meta.user_id AND meta.meta_key = '_tutor_instructor_status' AND meta.meta_value = 'approved'
			WHERE 1=1 {$not_in_sql} {$search_sql} limit 10 "
		);

		// Search result
		$search_result = '';
		if ( is_array( $instructors ) && count( $instructors ) ) {
			foreach ( $instructors as $instructor ) {
				$search_result .= '<div class="tutor-instructor-search-single" data-user_id="' . $instructor->ID . '">
					' . tutor_utils()->get_tutor_avatar( $instructor->ID, 'md' ) . '
					<div class="instructor-name tutor-nowrap-ellipsis tutor-ml-12">
						<div class="tutor-fs-6 tutor-color-black tutor-fw-medium">' . $instructor->display_name . '</div>
						<div class="tutor-fs-7 tutor-color-muted">' . $instructor->user_email . '</div>
					</div>
					<div class="tutor-instructor-single-action"><span class="tutor-iconic-btn tutor-shortlist-instructor"><i class="tutor-icon-plus-o"></i></span></div>
				</div>';
			}
		} else {
			$search_result .= '<div class="tutor-text-center">
								<span>' . __( 'No instructor found!', 'tutor' ) . '</span>
							</div>';
		}
		$post               = get_post( $course_id );
		$main_instructor_id = is_a( $post, 'WP_Post' ) ? $post->post_author : 0;
		// shortlisted
		$shortlisted_html = '';
		foreach ( $shortlisted as $id ) {
			$instructor = get_userdata( $id );
			ob_start();
			include TUTOR_MT()->path . '/views/user-card.php';
			$shortlisted_html .= ob_get_clean();
		}
		$shortlisted_html = '<div class="tutor-fs-6 tutor-fw-medium tutor-color-secondary tutor-mb-8">' . __( 'New Instructors', 'tutor-pro' ) . '</div>
							<div class="tutor-course-available-instructors">' . $shortlisted_html . '</div>';

		wp_send_json_success(
			array(
				'search_result'     => $search_result,
				'shortlisted'       => $shortlisted_html,
				'shortlisted_count' => count( $shortlisted ),
			)
		);
	}

	/**
	 * Meta box view
	 *
	 * @param boolean $echo  should echo the content or not.
	 * @return string  if echo false then return content
	 */
	public function instructors_metabox( $echo = true ) {
		ob_start();
		include TUTOR_MT()->path . 'views/metabox/instructors-metabox.php';
		$content = ob_get_clean();

		if ( $echo ) {
			//phpcs:ignore
			echo $content;
		} else {
			return $content;
		}
	}

	/**
	 * Show meta box on the front-end course builder
	 *
	 * @return void
	 */
	public function front_metabox() {
		$post = isset( $_GET['course_ID'] ) ? get_post( $_GET['course_ID'] ) : '';
		course_builder_section_wrap( $this->instructors_metabox( $echo = false ), __( 'Instructors', 'tutor' ) );
		do_action( 'tutor/frontend_course_edit/after/instructors', $post );
	}

	/**
	 * Get HTML output of instructor metabox
	 *
	 * @param integer $course_id
	 * @return string HTML output
	 *
	 * @since 2.1.0
	 */
	public function get_instructor_metabox_output( int $course_id ) {
		global $post;
		$post = get_post( $course_id );

		ob_start();
		tutor_load_template_from_custom_path(
			dirname( __DIR__ ) . '/views/metabox/instructors-metabox.php',
			array(
				'post' => $post,
			),
			false
		);
		return ob_get_clean();
	}

	/**
	 * Handle ajax request for adding multi instructor to a course
	 *
	 * @return void   wp_json response
	 */
	public function tutor_add_instructors_to_course() {
		tutor_utils()->checking_nonce();

		$course_id      = Input::post( 'course_id', 0, Input::TYPE_INT );
		$instructor_ids = tutor_utils()->avalue_dot( 'tutor_instructor_ids', $_POST );

		if ( 0 === $course_id || ! is_array( $instructor_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid Request', 'tutor-pro' ) ) );
		}

		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor-pro' ) ) );
		}

		$instructor_ids = array_filter(
			$instructor_ids,
			function( $id ) {
				return is_numeric( $id );
			}
		);

		foreach ( $instructor_ids as $instructor_id ) {
			add_user_meta( $instructor_id, '_tutor_instructor_course_id', $course_id );
		}

		wp_send_json_success( array( 'output' => $this->get_instructor_metabox_output( $course_id ) ) );
	}

	/**
	 * Remove instructor from a course
	 *
	 * @return void
	 */
	public function detach_instructor_from_course() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$instructor_id = Input::post( 'instructor_id', 0, Input::TYPE_INT );
		$course_id     = Input::post( 'course_id', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$wpdb->delete(
			$wpdb->usermeta,
			array(
				'user_id'    => $instructor_id,
				'meta_key'   => '_tutor_instructor_course_id',
				'meta_value' => $course_id,
			)
		);
		wp_send_json_success();
	}

	/**
	 * Change the main instructor of the course
	 *
	 * @since v2.0.7
	 *
	 * @param int $post_id  current post id (course).
	 *
	 * @return void
	 */
	public static function change_main_instructor( int $post_id ): void {
		global $wpdb;
		$author_id = Input::post( 'post_author_override', 0, Input::TYPE_INT );
		$table     = $wpdb->posts;

		if ( $author_id ) {
			if ( current_user_can( 'manage_options' ) ) {
				$where = array(
					'ID' => $post_id,
				);
				$data  = array(
					'post_author' => $author_id,
				);
				do_action( 'tutor_before_change_main_instructor', $post_id, $author_id );
				$update = QueryHelper::update( $table, $data, $where );
				/**
				 * Update course lesson author id so that
				 * new author can edit lesson from wp editor
				 *
				 * @since v2.1.0
				 */
				self::update_course_content_author( $post_id, $author_id );

				do_action( 'tutor_after_change_main_instructor', $post_id, $author_id, $update );
			}
		}
	}

	/**
	 * Update course content author id typically
	 * after change main instructor
	 *
	 * Note: For now only lesson author id is updating.
	 *
	 * @since v2.1.0
	 *
	 * @param int $course_id  course id.
	 * @param int $author_id  new author id.
	 *
	 * @return bool
	 */
	public static function update_course_content_author( int $course_id, int $author_id ): bool {
		global $wpdb;
		$response   = false;
		$lesson_ids = tutor_utils()->get_course_content_ids_by(
			tutor()->lesson_post_type,
			tutor()->course_post_type,
			$course_id
		);
		if ( is_array( $lesson_ids ) && count( $lesson_ids ) ) {
			$ids      = implode( ',', $lesson_ids );
			$response = QueryHelper::update_where_in(
				$wpdb->posts,
				array( 'post_author' => $author_id ),
				$ids
			);
		}
		return $response;
	}

}
