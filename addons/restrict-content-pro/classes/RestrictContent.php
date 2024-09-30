<?php
/**
 * Restrict Content PRO integration Logic
 *
 * @package TutorPro/Addons
 * @subpackage RestrictContentPro
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.5.6
 */

namespace TUTOR_RC;

use TUTOR\Tutor_Base;

/**
 * Class RestrictContent
 *
 * @since 1.5.6
 */
class RestrictContent extends Tutor_Base {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'tutor_course_loop_price', array( $this, 'tutor_add_to_cart_for_course_list' ), 100, 1 );
		add_filter( 'tutor/course/single/entry-box/free', array( $this, 'tutor_add_to_cart_for_course_details' ) );
		add_action( 'tutor_lesson_load_before', array( $this, 'check_subscription' ) );
	}

	/**
	 * Check subscription
	 *
	 * @return void
	 */
	public function check_subscription() {
		global $post, $wpdb;
		$monetize_by = get_tutor_option( 'monetize_by' );

		if ( 'restrict-content-pro' === $monetize_by ) {
			$has_membership_access = false;
			$course_id             = tutor_utils()->get_course_id_by_content( get_the_ID() );
			$user_id               = get_current_user_id();

			if ( tutor_utils()->is_enrolled( $course_id ) ) {
				if ( function_exists( 'rcp_user_can_access' ) ) {
					if ( rcp_user_can_access( $user_id, $course_id ) ) {
						$has_membership_access = true;
					}
				}
				if ( ! $has_membership_access ) {
					//phpcs:ignore
					$wpdb->query( "UPDATE {$wpdb->posts} SET post_status = 'expired' WHERE post_type = 'tutor_enrolled' AND post_parent = {$course_id} AND post_author = {$user_id}" );
				}
			}
		}
	}

	/**
	 * Add to card for course list.
	 *
	 * @param string $html html.
	 *
	 * @return string
	 */
	public function tutor_add_to_cart_for_course_list( $html ) {
		global $post, $wp_query;
		/**
		 * If user enrolled or has course access then don't need to alter the
		 * button html since user already enrolled.
		 *
		 * @since v2.0.5
		 */
		$is_enrolled          = tutor_utils()->is_enrolled( get_the_ID(), get_current_user_id() );
		$is_enabled           = tutor_utils()->get_option( 'course_content_access_for_ia' );
		$can_user_edit_course = tutor_utils()->can_user_edit_course( get_current_user_id(), get_the_ID() );

		if ( $is_enrolled || ( $is_enabled && $can_user_edit_course ) ) {
			return $html;
		}
		$monetize_by = get_tutor_option( 'monetize_by' );

		if ( 'restrict-content-pro' !== $monetize_by ) {
			return $html;
		}

		if ( function_exists( 'rcp_user_can_access' ) ) {
			$has_membership_access = \rcp_user_can_access( get_current_user_id(), $post->ID );
			if ( $has_membership_access ) {
				return $html;
			} else {
				ob_start();
				// check post restriction.
				$restrictions = \rcp_get_post_restrictions( $post->ID );

				// get membership levels.
				$levels = array();
				if ( is_array( $restrictions ) && isset( $restrictions['membership_levels'] ) ) {
					$levels = $restrictions['membership_levels'];
				}
				$level_name = '';
				if ( is_array( $levels ) ) {
					foreach ( $levels as $level ) {
						$membership  = \rcp_get_membership_level( $level );
						$level_name .= $membership->name . ', ';
					}
				}
				?>
				<?php if ( $level_name ) : ?>
				<div class="tutor-rcp-membership-levels tutor-mb-12">
					<strong>
						<?php esc_html_e( 'Available on: ', 'tutor-pro' ); ?>
					</strong>
					<span>
						<?php echo esc_html( rtrim( $level_name, ', ' ) ); ?>
					</span>
				</div>
				<?php endif; ?>
				<div class="tutor-restrict-content-message-wrapper tutor-justify-center tutor-align-center tutor-flex-column">
					<a class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block" href="<?php echo esc_url( rcp_get_registration_page_url() ); ?>">
						<?php esc_html_e( 'Get Membership', 'tutor-pro' ); ?>
					</a>
				</div>
				<?php
				return apply_filters( 'tutor_restrict_content_html', ob_get_clean() );
			}
		}

		return $html;
	}

	/**
	 * Add to cart from course details
	 *
	 * @param string $html html.
	 *
	 * @return string
	 */
	public function tutor_add_to_cart_for_course_details( $html ) {
		global $post;
		$monetize_by = get_tutor_option( 'monetize_by' );
		$login_url   = tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true ) ? '' : wp_login_url( tutor()->current_url );

		if ( 'restrict-content-pro' !== $monetize_by ) {
			return $html;
		}

		if ( function_exists( 'rcp_user_can_access' ) ) {
			$has_membership_access = \rcp_user_can_access( get_current_user_id(), $post->ID );
			if ( $has_membership_access ) {
				?>
			<div class="tutor-course-sidebar-card-btns <?php echo is_user_logged_in() ? '' : 'tutor-course-entry-box-login'; ?>" data-login_url="<?php echo esc_url( $login_url ); ?>">
				<form class="tutor-enrol-course-form" method="post">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
					<input type="hidden" name="tutor_course_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
					<input type="hidden" name="tutor_course_action" value="_tutor_course_enroll_now">
					<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block tutor-mt-24 tutor-enroll-course-button">
						<?php esc_html_e( 'Enroll Now', 'tutor' ); ?>
					</button>
				</form>
			</div>
			<div class="tutor-fs-7 tutor-color-muted tutor-mt-20 tutor-text-center">
				<?php esc_html_e( 'This course is under your membership plan', 'tutor' ); ?>
			</div>
				<?php
				return;
			} else {
				ob_start();
				// check post restriction.
				$restrictions = \rcp_get_post_restrictions( $post->ID );
				// get membership levels.
				$levels = array();
				if ( is_array( $restrictions ) && isset( $restrictions['membership_levels'] ) ) {
					$levels = $restrictions['membership_levels'];
				}
				$level_name = '';
				if ( is_array( $levels ) ) {
					foreach ( $levels as $level ) {
						$membership  = \rcp_get_membership_level( $level );
						$level_name .= $membership->name . ', ';
					}
				}

				?>
				<?php if ( $level_name ) : ?>
				<div class="tutor-rcp-membership-levels tutor-mb-12">
					<div class="tutor-fs-6 tutor-color-muted">
						<?php esc_html_e( 'Available on: ', 'tutor-pro' ); ?>
					</div>
					<div class="tutor-fs-7 tutor-fw-medium tutor-color-black">
						<?php echo esc_html( rtrim( $level_name, ', ' ) ); ?>
					</div>
				</div>
				<?php endif; ?>
				<div class="tutor-rcp-membership-content">
					<div class="tutor-fs-6 tutor-color-secondary tutor-mb-24">
						<?php echo wp_kses_post( apply_filters( 'tutor_restrict_content_msg', rcp_get_restricted_content_message() ) ); ?>
					</div>

					<a class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block" href="<?php echo esc_url( rcp_get_registration_page_url() ); ?>">
						<?php esc_html_e( 'Get Membership', 'tutor-pro' ); ?>
					</a>
				</div>
				<?php
				return apply_filters( 'tutor_restrict_content_html', ob_get_clean() );
			}
		}

		return $html;
	}

	/**
	 * Course price.
	 *
	 * @param string $html html.
	 *
	 * @return string
	 */
	public function tutor_course_price( $html ) {
		$monetize_by = get_tutor_option( 'monetize_by' );

		if ( 'restrict-content-pro' === $monetize_by ) {
			return '';
		}

		return $html;
	}
}
