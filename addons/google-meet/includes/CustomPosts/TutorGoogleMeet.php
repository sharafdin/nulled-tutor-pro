<?php
/**
 * Register custom post type for google meet
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\CustomPosts
 */

namespace TutorPro\GoogleMeet\CustomPosts;

use TutorPro\GoogleMeet\Models\EventsModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register post type
 */
class TutorGoogleMeet {

	/**
	 * Register hooks
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register post type
	 *
	 * @since v2.1.0
	 *
	 * @return void
	 */
	public function register_post_type(): void {

		$labels = array(
			'name'               => _x( 'Tutor Google Meet', 'post type general name', 'tutor-pro' ),
			'singular_name'      => _x( 'Google Meet', 'post type singular name', 'tutor-pro' ),
			'menu_name'          => _x( 'Google Meet', 'admin menu', 'tutor-pro' ),
			'name_admin_bar'     => _x( 'Google Meet', 'add new on admin bar', 'tutor-pro' ),
			'add_new'            => _x( 'Add New', 'Add new meet', 'tutor-pro' ),
			'add_new_item'       => __( 'Add New Meet', 'tutor-pro' ),
			'new_item'           => __( 'New Meet', 'tutor-pro' ),
			'edit_item'          => __( 'Edit Meet', 'tutor-pro' ),
			'view_item'          => __( 'View Meet', 'tutor-pro' ),
			'all_items'          => __( 'Google Meet', 'tutor-pro' ),
			'search_items'       => __( 'Search Meet', 'tutor-pro' ),
			'parent_item_colon'  => __( 'Parent Meet', 'tutor-pro' ),
			'not_found'          => __( 'No Meet found.', 'tutor-pro' ),
			'not_found_in_trash' => __( 'No Meets found in Trash.', 'tutor-pro' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Description.', 'tutor-pro' ),
			'public'              => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => EventsModel::POST_TYPE ),
			'menu_icon'           => 'dashicons-list-view',
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array( 'title', 'editor' ),
			'exclude_from_search' => true,
		);

		register_post_type( EventsModel::POST_TYPE, $args );
	}
}
