<?php
/**
 * Course Bundle custom post
 *
 * @package TutorPro\CourseBundle\CustomPosts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\CustomPosts;

use TutorPro\CourseBundle\CustomPosts\PostInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage course bundle post type
 */
class CourseBundle implements PostInterface {

	/**
	 * Post type
	 *
	 * @var string
	 */
	const POST_TYPE = 'course-bundle';

	/**
	 * Post meta key
	 *
	 * @var string
	 */
	const BUNDLE_COURSE_IDS_META_KEY = 'bundle-course-ids';

	/**
	 * Register hook
	 *
	 * @since 2.2.0
	 */
	public function __construct() {
		add_action( 'init', __CLASS__ . '::register_post_type' );
	}

	/**
	 * Get post type
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public static function get_post_type(): string {
		return self::POST_TYPE;
	}

	/**
	 * Register post type
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	public static function get_post_args(): array {
		$is_enabled_gutenberg = (bool) tutor_utils()->get_option( 'enable_gutenberg_course_edit', false );

		$labels = array(
			'name'               => _x( 'Tutor Course Bundle', 'post type general name', 'tutor-pro' ),
			'singular_name'      => _x( 'Course Bundle', 'post type singular name', 'tutor-pro' ),
			'menu_name'          => _x( 'Course Bundles', 'admin menu', 'tutor-pro' ),
			'name_admin_bar'     => _x( 'Course Bundle', 'add new on admin bar', 'tutor-pro' ),
			'add_new'            => _x( 'Add New', 'Add new course bundle', 'tutor-pro' ),
			'add_new_item'       => __( 'Add New', 'tutor-pro' ),
			'new_item'           => __( 'New Course Bundle', 'tutor-pro' ),
			'edit_item'          => __( 'Edit Course Bundle', 'tutor-pro' ),
			'view_item'          => __( 'View Course Bundle', 'tutor-pro' ),
			'all_items'          => __( 'Course Bundles', 'tutor-pro' ),
			'search_items'       => __( 'Search Course Bundle', 'tutor-pro' ),
			'parent_item_colon'  => __( 'Parent Course Bundle', 'tutor-pro' ),
			'not_found'          => __( 'No Course Bundle found.', 'tutor-pro' ),
			'not_found_in_trash' => __( 'No Course Bundle found in Trash.', 'tutor-pro' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'tutor-pro' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => true,
			'show_in_rest'       => $is_enabled_gutenberg,
			'capability_type'    => 'post',
			'query_var'          => true,
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'rewrite'            => array(
				'slug'       => self::POST_TYPE,
				'with_front' => true,
			),
			'menu_icon'          => 'dashicons-list-view',
			'capabilities'       => array(
				'edit_post'          => 'edit_tutor_course',
				'read_post'          => 'read_tutor_course',
				'delete_post'        => 'delete_tutor_course',
				'delete_posts'       => 'delete_tutor_courses',
				'edit_posts'         => 'edit_tutor_courses',
				'edit_others_posts'  => 'edit_others_tutor_courses',
				'publish_posts'      => 'publish_tutor_courses',
				'read_private_posts' => 'read_private_tutor_courses',
				'create_posts'       => 'edit_tutor_courses',
			),
		);

		return $args;
	}
}
