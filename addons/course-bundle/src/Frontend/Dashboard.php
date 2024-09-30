<?php
/**
 * Manage dashboard for course bundle.
 *
 * @package TutorPro\CourseBundle
 * @subpackage Frontend
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Frontend;

use TutorPro\CourseBundle\CustomPosts\CourseBundle;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Class
 *
 * @since 2.2.0
 */
class Dashboard {

	/**
	 * Register hooks
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor_wishlist_post_types', array( $this, 'add_wishlist_post_types' ) );
		add_filter( 'tutor_pro_create_new_course_button', array( $this, 'change_create_course_button' ) );

	}

	/**
	 * Change  create course button.
	 *
	 * @since 2.2.0
	 *
	 * @param string $btn btn HTML.
	 *
	 * @return string
	 */
	public function change_create_course_button( $btn ) {
		global $wp_query;
		$query_vars   = $wp_query->query_vars;
		$is_dashboard = isset( $query_vars['tutor_dashboard_page'] );

		if ( $is_dashboard && 'my-bundles' === $query_vars['tutor_dashboard_page'] ) {
			ob_start();
			?>
			<a href="#" data-source="frontend" class="tutor-add-new-course-bundle tutor-btn tutor-btn-outline-primary">
				<i class="tutor-icon-plus-square tutor-my-n4 tutor-mr-8"></i>
				<?php esc_html_e( 'Create a New Bundle', 'tutor-pro' ); ?>
			</a>
			<?php
			return ob_get_clean();
		}

		return $btn;
	}

	/**
	 * Add course bundle post type to wishlist post types.
	 *
	 * @since 2.2.0
	 *
	 * @param array $post_types post types.
	 *
	 * @return array
	 */
	public function add_wishlist_post_types( $post_types ) {
		$post_types[] = CourseBundle::POST_TYPE;
		return $post_types;
	}
}
