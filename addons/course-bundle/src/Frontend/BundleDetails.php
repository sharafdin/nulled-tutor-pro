<?php
/**
 * Bundle Details Page Logic Handler.
 *
 * @package TutorPro\CourseBundle
 * @subpackage Frontend
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Frontend;

use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\CustomPosts\ManagePostMeta;
use TutorPro\CourseBundle\MetaBoxes\BundlePrice;
use TutorPro\CourseBundle\Models\BundleModel;
use TutorPro\CourseBundle\Utils;

/**
 * Bundle Details Class
 *
 * @since 2.2.0
 */
class BundleDetails {

	/**
	 * Register hooks.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'load_single_bundle_template' ), 99 );
		add_filter( 'tutor_course_about_title', array( $this, 'add_bundle_title' ) );
		add_filter( 'tutor/course/single/sidebar/metadata', array( $this, 'add_bundle_metadata' ), 11, 2 );
		add_filter( 'tutor_course_single_tags', array( $this, 'bundle_tags' ), 10, 2 );
		add_action( 'tutor_after_course_details_wc_cart_price', array( $this, 'add_discount_info' ), 10, 2 );
		add_filter( 'tutor_load_single_sidebar_actions', array( $this, 'load_single_sidebar_actions' ), 10, 2 );
		add_filter( 'tutor/course/single/entry-box/is_enrolled', array( $this, 'content_for_enrolled_user' ), 10, 2 );

		/**
		 * Bypass pre-requisites for bundle courses.
		 *
		 * @since 2.2.0
		 */
		add_filter( 'tutor_pro_show_prerequisites_courses', array( $this, 'bypass_course_prerequisites' ), 10, 2 );
		add_filter( 'tutor_pro_prerequisites_redirect', array( $this, 'bypass_course_prerequisites' ), 10, 2 );

		/**
		 * Bypass course expiry
		 *
		 * @since 2.2.0
		 */
		add_filter( 'tutor_pro_check_course_expiry', array( $this, 'bypass_course_expiry' ), 10, 2 );
		add_filter( 'tutor_pro_show_course_expire_info', array( $this, 'bypass_course_expiry' ), 10, 2 );
	}

	/**
	 * Bypass course expire for bundle course.
	 *
	 * @since 2.2.0
	 *
	 * @param bool $bool true or false.
	 * @param int  $course_id course id.
	 *
	 * @return bool
	 */
	public function bypass_course_expiry( $bool, $course_id ) {
		$user_id = get_current_user_id();
		// If user not logged in.
		if ( ! $user_id ) {
			return $bool;
		}

		// Find which bundle has this course.
		$bundle_id = BundleModel::get_bundle_id_by_course( $course_id );
		if ( ! $bundle_id ) {
			return $bool;
		}

		$course_enrolled = tutor_utils()->is_enrolled( $course_id, $user_id );
		$bundle_enrolled = tutor_utils()->is_enrolled( $bundle_id, $user_id );

		if ( $course_enrolled && $bundle_enrolled ) {
			/**
			 * Course expire will not work for who are enrolled with bundle.
			 *
			 * @since 2.2.0
			 */
			return false;
		}

		return $bool;
	}

	/**
	 * Load_single_sidebar_actions
	 *
	 * @since 2.2.0
	 *
	 * @param  boolean $bool bool value.
	 * @param  int     $post_id post id.
	 *
	 * @return boolean
	 */
	public function load_single_sidebar_actions( $bool, $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( CourseBundle::POST_TYPE !== $post_type ) {
			return $bool;
		}

		$user_id        = get_current_user_id();
		$post_author_id = get_the_author_meta( 'ID' );

		if ( $user_id === $post_author_id ) {
			return false;
		}

		return $bool;
	}

	/**
	 * Bypass course pre-requisites for bundle courses.
	 *
	 * @since 2.2.0
	 *
	 * @param bool $bool bool value.
	 * @param int  $course_id post id.
	 *
	 * @return bool
	 */
	public function bypass_course_prerequisites( $bool, $course_id ) {
		$user_id = get_current_user_id();
		// If user not logged in.
		if ( ! $user_id ) {
			return $bool;
		}

		// Find which bundle has this course.
		$bundle_id = BundleModel::get_bundle_id_by_course( $course_id );
		if ( ! $bundle_id ) {
			return $bool;
		}

		$course_enrolled = tutor_utils()->is_enrolled( $course_id, $user_id );
		$bundle_enrolled = tutor_utils()->is_enrolled( $bundle_id, $user_id );

		if ( $course_enrolled && $bundle_enrolled ) {
			/**
			 * Pre-requisites restriction will not work for who are enrolled with bundle.
			 *
			 * @since 2.2.0
			 */
			return false;
		}

		return $bool;
	}

	/**
	 * Show entry box content for enrolled user.
	 *
	 * @since 2.2.0
	 *
	 * @param string $content HTML content.
	 * @param int    $post_id post id.
	 *
	 * @return string
	 */
	public function content_for_enrolled_user( $content, $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( CourseBundle::POST_TYPE !== $post_type ) {
			return $content;
		}

		ob_start();
		$enrolled_courses_link = tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses' );
		?>
		<div class="tutor-course-progress-wrapper tutor-mb-32">
			<a href="<?php echo esc_url( $enrolled_courses_link ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-block">
				<?php esc_html_e( 'Explore Courses', 'tutor-pro' ); ?>
			</a>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Add discount ribbon info.
	 *
	 * @since 2.2.0
	 *
	 * @param object $product wc product.
	 * @param int    $bundle_id bundle id.
	 *
	 * @return void
	 */
	public function add_discount_info( $product, $bundle_id ) {
		$post_type = get_post_type( $bundle_id );
		if ( CourseBundle::POST_TYPE !== $post_type ) {
			return;
		}

		$ribbon_type = ManagePostMeta::get_ribbon_type( $bundle_id );
		if ( BundleModel::RIBBON_NONE === $ribbon_type ) {
			return;
		}

		$discount          = BundlePrice::get_bundle_discount_by_ribbon( $bundle_id, $ribbon_type );
		$bundle_sale_price = BundlePrice::get_bundle_sale_price( $bundle_id );

		if ( $bundle_sale_price <= 0 ) {
			return;
		}
		?>
			<div class="tutor-d-flex tutor-align-center tutor-gap-1">
				<?php
					/* translators: %s: discount value */
					echo esc_html( sprintf( __( '%s off', 'tutor-pro' ), $discount ) );
				?>
			</div>
		<?php
	}

	/**
	 * Get all tags  for the bundle courses.
	 *
	 * @since 2.2.0
	 *
	 * @param array $tags tags.
	 * @param int   $bundle_id bundle id.
	 *
	 * @return array
	 */
	public function bundle_tags( $tags, $bundle_id ) {
		$post_type = get_post_type( $bundle_id );
		if ( CourseBundle::POST_TYPE !== $post_type ) {
			return $tags;
		}

		$courses = BundleModel::get_bundle_course_ids( $bundle_id );
		if ( 0 === count( $courses ) ) {
			return $tags;
		}

		$tags = wp_get_object_terms( $courses, 'course-tag', array( 'ids' ) );

		return $tags;
	}

	/**
	 * Add bundle meta data.
	 *
	 * @since 2.2.0
	 *
	 * @param array $metadata meta data.
	 * @param int   $bundle_id bundle id.
	 *
	 * @return array
	 */
	public function add_bundle_metadata( $metadata, $bundle_id ) {
		$post_type = get_post_type( $bundle_id );
		if ( CourseBundle::POST_TYPE !== $post_type ) {
			return $metadata;
		}

		$overview       = BundleModel::get_bundle_meta( $bundle_id );
		$total_enrolled = BundleModel::get_total_bundle_sold( $bundle_id );
		$total_course   = BundleModel::get_total_courses_in_bundle( $bundle_id );

		//phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment
		return array(
			array(
				'icon_class' => 'tutor-icon-book-open-o',
				'label'      => __( 'Total Courses', 'tutor-pro' ),
				'value'      => sprintf( __( '%s Total Courses', 'tutor-pro' ), $total_course ),
			),
			array(
				'icon_class' => 'tutor-icon-mortarboard',
				'label'      => __( 'Total Enrolled', 'tutor-pro' ),
				'value'      => sprintf( __( '%s Total Enrolled', 'tutor-pro' ), $total_enrolled ),
			),
			array(
				'icon_class' => 'tutor-icon-clock-line',
				'label'      => __( 'Duration', 'tutor-pro' ),
				'value'      => sprintf( __( '%s Duration', 'tutor-pro' ), BundleModel::convert_seconds_into_human_readable_time( $overview['total_duration'] ?? 0, false ) ),
			),
			array(
				'icon_class' => 'tutor-icon-video-camera-o',
				'label'      => __( 'Video Content', 'tutor-pro' ),
				'value'      => sprintf( __( '%s Video Content', 'tutor-pro' ), $overview['total_video_contents'] ?? 0 ),
			),
			array(
				'icon_class' => 'tutor-icon-download',
				'label'      => __( 'Downloadable Resources', 'tutor-pro' ),
				'value'      => sprintf( __( '%s Downloadable Resources', 'tutor-pro' ), $overview['total_resources'] ?? 0 ),
			),
			array(
				'icon_class' => 'tutor-icon-circle-question-mark',
				'label'      => __( 'Quiz Papers', 'tutor-pro' ),
				'value'      => sprintf( __( '%s Quiz Papers', 'tutor-pro' ), $overview['total_quizzes'] ?? 0 ),
			),
		);
		//phpcs:enable WordPress.WP.I18n.MissingTranslatorsComment
	}

	/**
	 * Add bundle title.
	 *
	 * @since 2.2.0
	 *
	 * @param string $title title.
	 *
	 * @return string
	 */
	public function add_bundle_title( $title ) {
		if ( Utils::is_bundle_single_page() ) {
			return __( 'About Bundle', 'tutor-pro' );
		}

		return $title;
	}

	/**
	 * Load single bundle details template.
	 *
	 * @since 2.2.0
	 *
	 * @param string $template template.
	 *
	 * @return string
	 */
	public function load_single_bundle_template( $template ) {
		if ( Utils::is_bundle_single_page() ) {
			do_action( 'single_bundle_template_before_load', get_the_ID() );
			wp_reset_postdata();
			return Utils::template_path( 'single-course-bundle.php' );
		}

		return $template;
	}
}
