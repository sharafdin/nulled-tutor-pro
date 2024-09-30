<?php
/**
 * Register post types
 *
 * @package TutorPro\CourseBundle\CustomPosts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\CustomPosts;

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom posts
 */
class RegisterPosts {

	const PARENT_MENU_SLUG = 'tutor';

	/**
	 * Register Hooks
	 *
	 * @since 2.2.0
	 */
	public function __construct() {
		add_action( 'init', __CLASS__ . '::register_post_types' );
		add_filter( 'parent_file', __CLASS__ . '::update_parent_file' );
		add_action( 'admin_footer', __CLASS__ . '::highlight_submenu' );
	}

	/**
	 * Register custom posts
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public static function register_post_types(): void {

		// Add available post type classes.
		$types = array(
			CourseBundle::class,
		);

		foreach ( $types as $type ) {
			register_post_type(
				$type::get_post_type(),
				$type::get_post_args()
			);
		}
	}

	/**
	 * Update parent file
	 *
	 * Show Tutor main menu active when editing course-bundle
	 * custom post type
	 *
	 * @since 2.2.0
	 *
	 * @param string $parent_file current parent file.
	 *
	 * @return string
	 */
	public static function update_parent_file( $parent_file ) {
		global $current_screen, $submenu_file;
		$post_type = $current_screen->post_type;
		if ( CourseBundle::POST_TYPE === $post_type ) {
			$parent_file = self::PARENT_MENU_SLUG;
		}
		return $parent_file;
	}

	/**
	 * Highlight course-bundle submenu
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public static function highlight_submenu() {
		?>
		<script>
			var tutorCurrentPostType = '<?php echo esc_html( get_current_screen()->post_type ); ?>';

			if ( tutorCurrentPostType === 'course-bundle' ) {
				// Add 'current' class to the Course Bundles submenu item
				submenu = document.querySelector('#toplevel_page_tutor .wp-submenu li a[href="admin.php?page=course-bundle"]');
				if (submenu) {
					submenu.closest('li').classList.add('current');
				}
			}
		</script>
		<?php
	}

}
