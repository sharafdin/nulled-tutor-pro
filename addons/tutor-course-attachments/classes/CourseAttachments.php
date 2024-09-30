<?php
/**
 * Handle Course Attachments
 *
 * @package TutorPro/Addons
 * @subpackage CourseAttachment
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.0.0
 */

namespace TUTOR_CA;

use TUTOR\Input;
use TUTOR\Tutor_Base;

/**
 * Class CourseAttachments
 */
class CourseAttachments extends Tutor_Base {

	/**
	 * Open mode.
	 *
	 * @var string
	 */
	private $open_mode = 'tutor_pro_attachment_open_type';

	/**
	 * Register hook
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'tutor/frontend_course_edit/after/course_builder', array( $this, 'register_meta_box_in_frontend' ), 12 );

		add_filter( 'tutor_course/single/nav_items', array( $this, 'add_course_nav_item' ), 10, 2 );

		/**
		 * Listen only save_post will hook for every post type
		 * course / lesson / quizz etc
		 * removed save_post_courses hook to avoid redundancy
		 *
		 * @since 1.8.9
		*/
		add_action( 'save_post', array( $this, 'save_course_meta' ) );
		add_action( 'save_tutor_course', array( $this, 'save_course_meta' ) );

		add_filter( 'tutor/options/extend/attr', array( $this, 'add_option' ) );
		add_filter( 'tutor_pro_attachment_open_mode', array( $this, 'set_open_open_mode' ) );
	}

	/**
	 * Set open mode.
	 *
	 * @return mixed
	 */
	public function set_open_open_mode() {
		return tutor_utils()->get_option( $this->open_mode );
	}

	/**
	 * Add option to tutor settings.
	 *
	 * @param array $attr attributes.
	 *
	 * @return array
	 */
	public function add_option( $attr ) {

		$attr['course']['blocks']['block_course']['fields'][] = array(
			'key'     => $this->open_mode,
			'type'    => 'radio_horizontal_full',
			'label'   => __( 'Attachment Open Mode', 'tutor-pro' ),
			'default' => 'download',
			'options' => array(
				'download' => __( 'Download', 'tutor-pro' ),
				'view'     => __( 'View in new tab', 'tutor-pro' ),
			),
			'desc'    => __( 'How you want users to view attached files.', 'tutor-pro' ),
		);

		return $attr;
	}

	/**
	 * Merge resources tab with course nav items
	 *
	 * @param array $items items.
	 * @param int   $course_id course id.
	 *
	 * @return array course nav items
	 */
	public function add_course_nav_item( $items, $course_id ) {
		/**
		 * Check settings if admin & instructor as course access and
		 * current user has permission to edit course then user should
		 * access course attachments without enrollment.
		 *
		 * @since v2.0.5
		 */
		$is_enabled           = tutor_utils()->get_option( 'course_content_access_for_ia' );
		$can_user_edit_course = tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id );
		$require_enrolment    = ! ( $is_enabled && $can_user_edit_course ); // Admin and instructor of the course can see resource tab.

		if ( is_single() && $course_id ) {
			$items['resources'] = array(
				'title'             => __( 'Resources', 'tutor-pro' ),
				'method'            => array( $this, 'load_resource_tab_content' ),
				'require_enrolment' => $require_enrolment,
			);
		}
		return $items;
	}

	/**
	 * Load resource tab content.
	 *
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public function load_resource_tab_content( $course_id ) {
		get_tutor_posts_attachments();
	}

	/**
	 * Register meta box.
	 *
	 * @return void
	 */
	public function register_meta_box() {
		$course_post_type = tutor()->course_post_type;

		/**
		 * Check is allow private file upload
		 */
		tutor_meta_box_wrapper(
			'tutor-course-attachments',
			__( 'Attachments (private files)', 'tutor-pro' ),
			array( $this, 'course_attachments_metabox' ),
			$course_post_type,
			'advanced',
			'high',
			'tutor-admin-post-meta'
		);
	}

	/**
	 * Register meta box in frontend.
	 *
	 * @return void
	 */
	public function register_meta_box_in_frontend() {
		//phpcs:ignore
		course_builder_section_wrap( $this->course_attachments_metabox( $echo = false ), __( 'Course Attachments', 'tutor-pro' ) );
	}

	/**
	 * Course attachments metabox.
	 *
	 * @param boolean $echo print or not.
	 *
	 * @return mixed
	 */
	public function course_attachments_metabox( $echo = true ) {
		ob_start();
		include TUTOR_CA()->path . 'views/metabox/course-attachments-metabox.php';
		$content = ob_get_clean();

		if ( $echo ) {
			echo $content;//phpcs:ignore
		} else {
			return $content;
		}
	}

	/**
	 * Upload attachment only if $_POST[tutor_attachments]
	 * is not empty else delete
	 * it will remove empty data in db
	 *
	 * @since 1.8.9
	 *
	 * @param init $post_id post id.
	 */
	public function save_course_meta( $post_id ) {
		// Attachments.
		$attachments           = array();
		$attachments_main_edit = tutor_utils()->avalue_dot( '_tutor_attachments_main_edit', $_POST );//phpcs:ignore

		// Make sure it is post editor.
		if ( ! $attachments_main_edit ) {
			return;
		}

		// Get unique attachment ID. User might add single media multiple times.
		if ( Input::has( 'tutor_attachments' ) ) {
			$attachments = tutor_utils()->sanitize_array( $_POST['tutor_attachments'] );//phpcs:ignore
			$attachments = array_unique( $attachments );
		}

		// Update assignment meta if at least one exist.
		// Otherwise delete the meta.
		if ( ! empty( $attachments ) ) {
			update_post_meta( $post_id, '_tutor_attachments', $attachments );
		} else {
			delete_post_meta( $post_id, '_tutor_attachments' );
		}
	}
}
