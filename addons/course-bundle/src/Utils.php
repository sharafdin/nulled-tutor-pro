<?php
/**
 * Utils class
 *
 * @package TutorPro\CourseBundle
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle;

use TUTOR\Input;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\Models\BundleModel;

/**
 * Utils Class.
 *
 * @since 2.2.0
 */
class Utils {
	/**
	 * Get view path.
	 *
	 * @since 2.2.0
	 *
	 * @param string $path path.
	 *
	 * @return string
	 */
	public static function view_path( $path = null ) {
		$final_path = TUTOR_COURSE_BUNDLE_DIR . 'views';
		if ( $path ) {
			$final_path .= '/' . $path;
		}
		return $final_path;
	}

	/**
	 * Get template path.
	 *
	 * @since 2.2.0
	 *
	 * @param string $path path.
	 *
	 * @return string
	 */
	public static function template_path( $path = null ) {
		$final_path = TUTOR_COURSE_BUNDLE_DIR . 'templates';
		if ( $path ) {
			$final_path .= '/' . $path;
		}
		return $final_path;
	}

	/**
	 * Get asset URL.
	 *
	 * @since 2.2.0
	 *
	 * @param string $url url of assets.
	 *
	 * @return string
	 */
	public static function asset_url( $url = null ) {
		$final_url = plugin_dir_url( TUTOR_COURSE_BUNDLE_FILE ) . 'assets';
		if ( $url ) {
			$final_url .= '/' . $url;
		}
		return $final_url;
	}

	/**
	 * Get bundle author avatars.
	 *
	 * @since 2.2.0
	 *
	 * @param int  $bundle_id course bundle id.
	 * @param bool $print_names print names along with avatars.
	 *
	 * @return void
	 */
	public static function get_bundle_author_avatars( $bundle_id, $print_names = false ) {
		$authors       = BundleModel::get_bundle_course_authors( $bundle_id );
		$total_authors = count( $authors );

		if ( 0 === $total_authors ) {
			echo esc_html( '...' );
			return;
		}

		$first_author   = $authors[0];
		$avatar_authors = array_slice( $authors, 0, 3 );
		?>
		<div class="tutor-bundle-authors">
		<?php
		foreach ( $avatar_authors as $author ) {
			echo wp_kses(
				tutor_utils()->get_tutor_avatar( $author->user_id, 'sm' ),
				tutor_utils()->allowed_avatar_tags()
			);
		}

		?>
		<div>
			<?php
			if ( $print_names ) :
				// Print Jhon & 2 Others.
				echo esc_html( $first_author->display_name );
				if ( $total_authors > 1 ) {
					echo esc_html( ' & ' . ( $total_authors - 1 ) . ' ' . _n( 'Other', 'Others', $total_authors - 1, 'tutor-pro' ) );
				}
			endif;
			?>
		</div>
		<!-- end of author names -->
		</div>
		<!-- end of tutor-bundle-authors -->
		<?php
	}

	/**
	 * Course bundle empty state.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public static function course_bundle_empty_state() {
		$empty_state_img = self::asset_url( 'images/empty-state.png' );
		?>
		<div class="tutor-course-bundle-empty-state tutor-flex-center">
			<img src="<?php echo esc_url( $empty_state_img ); ?>" alt="<?php esc_html_e( 'No course added', 'tutor-pro' ); ?>">
			<p class="tutor-fs-5">
			<?php esc_html_e( 'Select courses to see overview', 'tutor-pro' ); ?>
			</p>
		</div>
			<?php
	}

	/**
	 * Get current bundle id
	 *
	 * It will first look at the query string, then the post data.
	 *
	 * @return int
	 */
	public static function get_bundle_id() {
		$id = 0;
		if ( is_admin() ) {
			$id = Input::post( 'post', 0, Input::TYPE_INT );
		} else {
			$id = Input::get( 'bundle-id', 0, Input::TYPE_INT );
		}
		return (int) $id ? $id : get_the_ID();
	}

	/**
	 * Check if user is bundle author
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 * @param int $user_id   user id, default to current user.
	 *
	 * @return bool
	 */
	public static function is_bundle_author( int $bundle_id, int $user_id = 0 ): bool {
		$post_type = get_post_type( $bundle_id );

		if ( CourseBundle::POST_TYPE !== $post_type ) {
			return false;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$post_author = (int) get_post_field( 'post_author', $bundle_id );
		return $user_id === $post_author;
	}

	/**
	 * Check is bundle single page
	 *
	 * @since 2.2.0
	 *
	 * @return boolean
	 */
	public static function is_bundle_single_page() {
		global $wp_query;
		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && CourseBundle::POST_TYPE === $wp_query->query_vars['post_type'] ) {
			return true;
		} else {
			return false;
		}
	}
}
