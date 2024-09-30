<?php
/**
 * Manage frontend things for google meet
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Frontend
 */

namespace TutorPro\GoogleMeet\Frontend;

use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Models\EventsModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage frontend functions, hooks & other stuff
 */
class Frontend {

	/**
	 * Register hooks
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		add_filter( 'tutor_dashboard/instructor_nav_items', __CLASS__ . '::register_dashboard_menu' );
		add_action( 'load_dashboard_template_part_from_other_location', __CLASS__ . '::load_template' );
		add_filter( 'template_include', __CLASS__ . '::load_meeting_template', 100 );
		add_filter( 'tutor_google_meet_lesson_done', __CLASS__ . '::is_lesson_completed', 99, 3 );
		add_action( 'tutor_course/single/before/topics', __CLASS__ . '::show_meeting_on_course_info_tab' );
		add_action( 'tutor/google_meet/right_icon_area', __CLASS__ . '::right_icon_area', 10, 2 );
	}

	/**
	 * Register menu on frontend dashboard
	 *
	 * @param array $nav_items tutor available nav items.
	 *
	 * @return array
	 */
	public static function register_dashboard_menu( array $nav_items ): array {
		do_action( 'tutor_pro_before_google_meet_frontend_menu_add', $nav_items );

		$nav_items['google-meet'] = array(
			'title'    => __( 'Google Meet', 'tutor-pro' ),
			'auth_cap' => tutor()->instructor_role,
			'icon'     => 'tutor-icon-brand-google-meet',
		);

		$nav_items = apply_filters( 'tutor_pro_after_google_meet_menu', $nav_items );
		do_action( 'tutor_pro_google_meet_after_frontend_menu_register' );
		return $nav_items;
	}

	/**
	 * Load Dashboard template
	 *
	 * @param string $template template that is going to be loaded.
	 *
	 * @return string  template path to load
	 */
	public static function load_template( $template ) {
		global $wp_query;
		$plugin_data = GoogleMeet::meta_data();
		$query_vars  = $wp_query->query_vars;

		if ( isset( $query_vars['tutor_dashboard_page'] ) && 'google-meet' === $query_vars['tutor_dashboard_page'] ) {
			$template = $plugin_data['templates'] . 'dashboard/main.php';
			if ( file_exists( $template ) ) {
				return apply_filters( 'tutor_pro_google_meet_main_template', $template );
			}
		}
		return $template;
	}

	/**
	 * Load lesson template on the course spotlight
	 * section
	 *
	 * @since v2.1.0
	 *
	 * @param string $template  template path.
	 *
	 * @return bool|string
	 */
	public static function load_meeting_template( $template ) {
		global $wp_query, $post;
		$plugin_data = GoogleMeet::meta_data();

		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && EventsModel::POST_TYPE === $wp_query->query_vars['post_type'] ) {
			if ( is_user_logged_in() ) {
				$content_type = ( get_post_type( $post->post_parent ) === tutor()->course_post_type ) ? 'topic' : 'lesson';

				$has_content_access = tutor_utils()->has_enrolled_content_access( $content_type, $post->ID );
				if ( $has_content_access ) {
					$template = $plugin_data['templates'] . 'single-meeting.php';
				} else {
					// You need to enroll first.
					$template = tutor_get_template( 'single.lesson.required-enroll' );
				}
			} else {
				$template = tutor_get_template( 'login' );
			}
			return $template;
		}
		return $template;
	}

	/**
	 * Filter hook for google meet lesson progress count
	 *
	 * If particular lesson return true then on the course progress
	 * it will increase the number.
	 *
	 * @since v2.1.0
	 *
	 * @param bool $value initial complete status, false.
	 * @param int  $lesson_id google meet lesson id.
	 * @param int  $user_id current user id.
	 *
	 * @return boolean
	 */
	public static function is_lesson_completed( bool $value, $lesson_id, $user_id ) : bool {
		$lesson_id = sanitize_text_field( tutor_utils()->get_post_id( $lesson_id ) );
		$user_id   = sanitize_text_field( tutor_utils()->get_user_id( $user_id ) );

		if ( $lesson_id && $user_id ) {
			$meta_key = '_tutor_completed_lesson_id_' . $lesson_id;
			$value    = get_user_meta( $user_id, $meta_key, true );
			$value    = $value ? true : false;
		}
		return $value;
	}

	/**
	 * Render ongoing meetings on the course info tab
	 * frontend course details page.
	 *
	 * @since v2.1.0
	 *
	 * @return void
	 */
	public static function show_meeting_on_course_info_tab() {
		$plugin_data = GoogleMeet::meta_data();
		$template    = $plugin_data['templates'] . 'meeting-parts/meeting-collapsible.php';
		if ( file_exists( $template ) ) {
			tutor_load_template_from_custom_path(
				$template
			);
		} else {
			echo esc_html( $template . ' not exists' );
		}
	}

	/**
	 * Manage Lesson right lock icon
	 *
	 * @since v2.1.0
	 *
	 * @param integer $post_id   content post id.
	 * @param boolean $lock_icon should show lock icon or not.
	 *
	 * @return void
	 */
	public static function right_icon_area( int $post_id, $lock_icon = false ) : void {
		$post_id      = sanitize_text_field( $post_id );
		$user_id      = get_current_user_id();
		$is_completed = self::is_lesson_completed( false, $post_id, $user_id );
		if ( $is_completed ) {
			echo "<input type='checkbox' class='tutor-form-check-input tutor-form-check-circle' disabled='disabled' readonly='readonly' checked='checked'/>";
		} else {
			if ( $lock_icon ) {
				echo '<i class="tutor-icon-lock-line tutor-fs-7 tutor-color-muted tutor-mr-4" area-hidden="true"></i>';
			} else {
				echo "<input type='checkbox' class='tutor-form-check-input tutor-form-check-circle' disabled='disabled' readonly='readonly'/>";
			}
		}
	}
}
