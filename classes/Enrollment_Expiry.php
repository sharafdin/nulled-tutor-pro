<?php
/**
 * Manage Course Enrollment Expire
 *
 * @package TutorPro\EnrollmentExpiry
 * @subpackage Frontend
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enrollment Expiry Class
 *
 * @since 2.2.0
 */
class Enrollment_Expiry {
	/**
	 * Register hooks
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor_course_settings_tabs', array( $this, 'settings_attr' ) );
		add_action( 'single_course_template_before_load', array( $this, 'cancel_expired_enrolment' ), 10, 1 );
		add_action( 'tutor_before_enrolment_check', array( $this, 'cancel_expired_enrolment' ), 10, 2 );

		add_action( 'tutor_course/single/entry/after', array( $this, 'show_expires_info_not_enrolled' ) );
		add_action( 'tutor_course/single/entry/after', array( $this, 'show_expires_info' ) );
		add_filter( 'tutor/options/extend/attr', array( $this, 'setting_field' ), 12 );
	}

	/**
	 * Check is enabled.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	private function is_enabled() {
		return (bool) get_tutor_option( 'enrollment_expiry_enabled' );
	}

	/**
	 * Settings field.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attr attr.
	 *
	 * @return array
	 */
	public function setting_field( $attr ) {
		$attr['course']['blocks']['block_course']['fields'][] = array(
			'key'         => 'enrollment_expiry_enabled',
			'type'        => 'toggle_switch',
			'label'       => __( 'Enrollment Expiration', 'tutor' ),
			'label_title' => '',
			'default'     => 'off',
			'desc'        => __( 'Enable to allow enrollment expiration feature in all courses.', 'tutor' ),
		);

		return $attr;
	}

	/**
	 * Settings attributes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args args.
	 *
	 * @return array
	 */
	public function settings_attr( $args ) {
		if ( ! $this->is_enabled() ) {
			return $args;
		}

		$args['general']['fields']['_tutor_course_settings[enrollment_expiry]'] = array(
			'type'        => 'number',
			'number_type' => 'integer',
			'label'       => __( 'Enrollment Expiration', 'tutor-pro' ),
			'value'       => (int) tutor_utils()->get_course_settings( get_the_ID(), 'enrollment_expiry', 0 ),
			'desc'        => __( 'Student\'s enrollment will be removed after this number of days. Set 0 for lifetime enrollment.', 'tutor-pro' ),
		);

		return $args;
	}

	/**
	 * Cancel course enrolment if course expire
	 *
	 * @since 2.0.0
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return void|null
	 */
	public function cancel_expired_enrolment( $course_id, $user_id = null ) {

		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ! $user_id && ! is_user_logged_in() ) {
			return;
		}

		$check_expiry = apply_filters( 'tutor_pro_check_course_expiry', true, $course_id );
		if ( ! $check_expiry ) {
			return;
		}

		global $wpdb;

		$expiry = get_tutor_course_settings( $course_id, 'enrollment_expiry' );
		if ( ! is_numeric( $expiry ) || $expiry < 1 ) {
			return;
		}

		$expired_date = tutor_time() - ( ( 60 * 60 * 24 ) * $expiry );
		$current_id   = $user_id ? $user_id : get_current_user_id();

		$ids = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts}
            WHERE post_author={$current_id}
                AND post_parent={$course_id}
                AND post_type='tutor_enrolled'
                AND post_status='completed'
                AND UNIX_TIMESTAMP(post_date)<{$expired_date}"
		);

		if ( is_array( $ids ) && count( $ids ) ) {

			$wpdb->query( "UPDATE {$wpdb->posts} SET post_status='cancel' WHERE ID IN ( " . implode( ',', $ids ) . ' )' );

			foreach ( $ids as $id ) {
				do_action( 'tutor_enrollment/after/expired', $id );
			}
		}
	}

	/**
	 * Show expire info for non-enrolled student.
	 *
	 * @since 2.0.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return void|null
	 */
	public function show_expires_info_not_enrolled( $course_id ) {

		if ( ! $this->is_enabled() || tutor_utils()->is_enrolled( $course_id, get_current_user_id() ) ) {
			return;
		}

		$show_info = apply_filters( 'tutor_pro_show_course_expire_info', true, $course_id );
		if ( ! $show_info ) {
			return;
		}

		$expiry     = get_tutor_course_settings( $course_id, 'enrollment_expiry' );
		$is_limited = is_numeric( $expiry ) && $expiry >= 1;

		$validity = $is_limited ? $expiry . ' ' . ( $expiry > 1 ? __( 'days', 'tutor-pro' ) : __( 'day', 'tutor-pro' ) ) : __( 'Lifetime', 'tutor-pro' );

		echo '<div class="enrolment-expire-info tutor-fs-7 tutor-color-muted tutor-d-flex tutor-align-center tutor-mt-12">
				<i class="tutor-icon-calender-line tutor-mr-8"></i> ' .
				__( 'Enrollment validity', 'tutor-pro' ), ': 
				<span class="tutor-ml-4">' . apply_filters( 'tutor_course_expire_validity', $validity, $course_id ) . '</span>
			</div>';
	}

	/**
	 * Show expire info.
	 *
	 * @since 2.0.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return void|null
	 */
	public function show_expires_info( $course_id ) {

		if ( ! $this->is_enabled() ) {
			return;
		}

		$enrolment = tutor_utils()->is_enrolled( $course_id, get_current_user_id() );

		$show_info = apply_filters( 'tutor_pro_show_course_expire_info', true, $course_id );
		if ( ! $show_info ) {
			return;
		}

		if ( $enrolment ) {
			$expiry   = get_tutor_course_settings( $course_id, 'enrollment_expiry' );
			$validity = apply_filters( 'tutor_course_expire_validity', __( 'Lifetime', 'tutor-pro' ), $course_id );
			if ( ! is_numeric( $expiry ) || $expiry < 1 ) {
				?>
				<p class="enrolment-expire-info tutor-fs-7 tutor-color-muted tutor-d-flex tutor-align-center tutor-mt-4">
					<i class="tutor-icon-calender-line tutor-mr-8"></i>
					<?php esc_html_e( 'Enrollment validity:', 'tutor-pro' ); ?>
					<span class="tutor-ml-4">
						<?php echo esc_html( $validity ); ?>
					</span>
				</p>
				<?php
				return;
			}

			$date = date_create( $enrolment->post_date );
			date_add( $date, date_interval_create_from_date_string( $expiry . ' days' ) );

			$validity = date_format( $date, get_option( 'date_format' ) );
			$text     = __( 'Enrollment valid until', 'tutor-pro' );

			echo '<p class="enrolment-expire-info tutor-fs-7 tutor-color-muted tutor-d-flex tutor-align-center tutor-mt-4">
					<i class="tutor-icon-calender-line tutor-mr-8"></i> ' .
					$text . ' 
					<span class="tutor-ml-4">' . $validity . '</span>
				</p>';
		}
	}
}
