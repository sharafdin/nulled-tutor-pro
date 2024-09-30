<?php
/**
 * Manage Course Bundle Archive.
 *
 * @package TutorPro\CourseBundle
 * @subpackage Frontend
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Frontend;

use TUTOR\Input;
use Tutor\Models\CourseModel;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\CustomPosts\ManagePostMeta;
use TutorPro\CourseBundle\MetaBoxes\BundlePrice;
use TutorPro\CourseBundle\Models\BundleModel;
use TutorPro\CourseBundle\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BundleArchive Class
 *
 * @since 2.2.0
 */
class BundleArchive {
	/**
	 * Register hooks.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor_course_archive_post_types', array( $this, 'add_post_types' ) );
		add_action( 'tutor_before_course_category_filter', array( $this, 'extend_course_filter' ) );
		add_filter( 'tutor_course_filter_args', array( $this, 'course_filter_args' ) );
		add_filter( 'tutor_course_thumbnail_placeholder', array( $this, 'bundle_thumbnail_placeholder' ), 10, 2 );
		add_action( 'tutor_after_course_loop_rating', array( $this, 'add_bundle_info' ) );
		add_filter( 'tutor_show_course_ratings', array( $this, 'show_course_ratings' ), 10, 2 );
		add_filter( 'tutor_course_students', array( $this, 'bundle_students' ), 10, 2 );
		add_filter( 'tutor_course/loop/start/button', array( $this, 'loop_start_button' ), 10, 2 );
	}

	/**
	 * Add post types for course archive.
	 *
	 * @since 2.2.3
	 *
	 * @param array $post_types post types.
	 *
	 * @return array
	 */
	public function add_post_types( $post_types ) {
		$post_types[] = CourseBundle::POST_TYPE;
		return $post_types;
	}

	/**
	 * Bundle loop button after purchase.
	 *
	 * @since 2.2.0
	 *
	 * @param string $button button HTML.
	 * @param int    $post_id post id.
	 *
	 * @return string
	 */
	public function loop_start_button( $button, $post_id ) {
		if ( CourseBundle::POST_TYPE !== get_post_type( $post_id ) ) {
			return $button;
		}

		ob_start();
		$link         = get_the_permalink( $post_id );
		$button_class = 'tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block';
		?>
		<a href="<?php echo esc_attr( $link ); ?>" class="<?php echo esc_attr( $button_class ); ?>">
			<?php esc_html_e( 'Bundle Details', 'tutor-pro' ); ?>
		</a>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get total enrolled for bundle.
	 *
	 * @since 2.2.0
	 *
	 * @param int $total_enrolled total enrolled.
	 * @param int $post_id post id.
	 *
	 * @return int
	 */
	public function bundle_students( $total_enrolled, $post_id ) {
		if ( CourseBundle::POST_TYPE !== get_post_type( $post_id ) ) {
			return $total_enrolled;
		}

		return BundleModel::get_total_bundle_sold( $post_id );
	}

	/**
	 * Rating star hide for bundle card.
	 *
	 * @since 2.2.0
	 *
	 * @param bool $show_ratings show ratings.
	 * @param int  $post_id post id.
	 *
	 * @return bool
	 */
	public function show_course_ratings( $show_ratings, $post_id ) {
		if ( CourseBundle::POST_TYPE !== get_post_type( $post_id ) ) {
			return $show_ratings;
		}

		return false;
	}

	/**
	 * Set bundle thumbnail placeholder.
	 *
	 * @since 2.2.0
	 *
	 * @param string $placeholder_image placeholder image.
	 * @param int    $post_id post id.
	 *
	 * @return string
	 */
	public function bundle_thumbnail_placeholder( $placeholder_image, $post_id ) {
		if ( CourseBundle::POST_TYPE !== get_post_type( $post_id ) ) {
			return $placeholder_image;
		}

		return Utils::asset_url( 'images/bundle-placeholder.svg' );
	}

	/**
	 * Add bundle info to course loop thumbnail.
	 *
	 * @since 2.2.0
	 *
	 * @param int $post_id post id.
	 *
	 * @return void
	 */
	public function add_bundle_info( $post_id ) {
		if ( CourseBundle::POST_TYPE !== get_post_type( $post_id ) ) {
			return;
		}

		$bundle_course_ids = BundleModel::get_bundle_course_ids( $post_id );
		$ribbon_type       = ManagePostMeta::get_ribbon_type( $post_id );
		$bundle_sale_price = BundlePrice::get_bundle_sale_price( $post_id );
		?>
			<!-- Show bundle discount badge -->

			<?php if ( BundleModel::RIBBON_NONE !== $ribbon_type && $bundle_sale_price > 0 ) : ?>
			<div class="tutor-bundle-discount-info">
				<div class="tutor-bundle-save-text"><?php esc_html_e( 'SAVE', 'tutor-pro' ); ?></div>
				<div class="tutor-bundle-save-amount"><?php echo esc_html( BundlePrice::get_bundle_discount_by_ribbon( $post_id, $ribbon_type ) ); ?></div>
			</div>
			<?php endif; ?>

			<!-- Show bundle course count badge -->
			<div class="tutor-bundle-course-count-badge">
				<span class="tutor-icon-layer"></span>
				<span class="tutor-bundle-course-count-number"><?php echo esc_html( count( $bundle_course_ids ) ); ?></span>
				<span class="tutor-bundle-course-count-text"> - <?php echo esc_html( __( 'course bundle', 'tutor-pro' ) ); ?></span>
			</div>
		<?php
	}

	/**
	 * Extend course archive page filter.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function extend_course_filter() {
		require_once Utils::template_path( 'bundle-archive/filters.php' );
	}

	/**
	 * Change course archive page filter args.
	 *
	 * @since 2.2.0
	 *
	 * @param array $args arguments.
	 *
	 * @return array
	 */
	public function course_filter_args( $args ) {
		$post_types        = array( CourseModel::POST_TYPE, CourseBundle::POST_TYPE );
		$args['post_type'] = $post_types;

		if ( Input::has( 'tutor-course-filter-type' ) ) {
			$type = Input::sanitize_request_data( 'tutor-course-filter-type' );
			if ( 'bundle' === $type ) {
				$args['post_type'] = CourseBundle::POST_TYPE;
			} elseif ( 'course' === $type ) {
				$args['post_type'] = CourseModel::POST_TYPE;
			} else {
				$args['post_type'] = $post_types;
			}
		}

		return $args;
	}
}
