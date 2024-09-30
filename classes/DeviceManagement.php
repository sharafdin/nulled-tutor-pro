<?php
/**
 * Manage allowed device a user
 *
 * @package TutorPro\DeviceManagement
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.10
 */

namespace TUTOR_PRO;

use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\SessionHelper;
use TUTOR\Input;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DeviceManagement class
 */
class DeviceManagement {

	/**
	 * Page slug
	 *
	 * @var string
	 */
	const SLUG = 'manage-login-sessions';

	/**
	 * Key name will based on device fingerprint
	 * like: tutor_{fingerprint}
	 *
	 * To store additional info like device info, user
	 * location, login time
	 *
	 * @var string
	 */
	const LOGIN_INFO_KEY = 'tutor_login_';

	/**
	 * Store user id in session to remove
	 * active login session
	 *
	 * @var int
	 */
	const USER_ID_KEY = 'tutor_tmp_user_id';

	/**
	 * Cooke expiry time in seconds
	 * 86400 * 30 = 30 days
	 *
	 * @var int
	 */
	private static $cookie_expiry = 86400 * 30;

	/**
	 * Fingerprint hash
	 *
	 * @var string
	 */
	private static $fingerprint = null;

	/**
	 * Register hooks
	 *
	 * @since 2.1.10
	 */
	public function __construct() {
		add_filter( 'tutor_pro_settings_auth_tab', __CLASS__ . '::config_settings', 1 );

		// If feature not enabled then return.
		if ( ! self::is_enabled() ) {
			return;
		}

		add_filter( 'authenticate', __CLASS__ . '::validate_limit_login', 100 );
		add_filter( 'tutor_dashboard/nav_items/settings/nav_items', __CLASS__ . '::register_nav' );
		add_filter( 'load_dashboard_template_part_from_other_location', __CLASS__ . '::load_template' );

		add_action( 'wp', __CLASS__ . '::tutor_track_activity' );

		add_action( 'wp_logout', __CLASS__ . '::remove_device' );

		add_action( 'tutor_before_student_details_btn', __CLASS__ . '::clear_session_btn' );

		add_action( 'wp_ajax_tutor_remove_device_manually', __CLASS__ . '::remove_device_manually' );
		add_action( 'wp_ajax_tutor_clear_active_sessions', __CLASS__ . '::clear_active_sessions' );
		add_action( 'wp_ajax_nopriv_tutor_remove_all_active_logins', __CLASS__ . '::remove_all_active_logins' );

		add_action( 'tutor_after_student_signup', __CLASS__ . '::add_new_login_device' );
		add_action( 'tutor_after_instructor_signup', __CLASS__ . '::add_new_login_device' );

	}

	/**
	 * Get cookie name
	 *
	 * @since 2.2.2
	 *
	 * @return string
	 */
	public static function cookie_name() {
		if ( ! defined( 'COOKIEHASH' ) ) {
			return 'tutor_' . md5( get_site_option( 'siteurl' ) );
		} else {
			return 'tutor_' . COOKIEHASH;
		}
	}

	/**
	 * Update last active time and monitor user has active session.
	 *
	 * @since 2.1.10
	 *
	 * @return void
	 */
	public static function tutor_track_activity() {
		$user_id = get_current_user_id();
		if ( ! $user_id || false === self::is_enabled() ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! self::is_applicable_limit_login( $user->roles ) ) {
			return $user;
		}

		$current_fingerprint = self::get_current_device_fingerprint();
		$current_device      = null;
		$has_session         = false;

		// Check current user has a session.
		$devices = self::get_logged_in_devices( $user_id );
		foreach ( $devices as $device ) {
			$fingerprint = self::get_fingerprint( $device->meta_key );
			if ( $current_fingerprint === $fingerprint ) {
				$has_session    = true;
				$current_device = $device;
				break;
			}
		}

		if ( false === $has_session ) {
			wp_logout();
		} else {
			// Update current user last active time.
			if ( ! is_null( $current_device ) ) {
				$info             = json_decode( $current_device->meta_value );
				$info->login_time = tutor_time();

				global $wpdb;
				$table = $wpdb->usermeta;
				$where = array( 'umeta_id' => $current_device->umeta_id );

				QueryHelper::update( $table, array( 'meta_value' => json_encode( $info ) ), $where );
			}
		}
	}

	/**
	 * Check weather limit login is enabled
	 *
	 * @since 2.1.10
	 *
	 * @return boolean
	 */
	public static function is_enabled() {
		return tutor_utils()->get_option( 'enable_limit_active_device' );
	}

	/**
	 * Check if limit login is applicable for the
	 * current user
	 *
	 * @since 2.1.10
	 *
	 * @param array $roles user roles.
	 *
	 * @return boolean
	 */
	public static function is_applicable_limit_login( array $roles = array() ) {
		$is_instructor_or_admin = false;

		if ( ! count( $roles ) ) {
			if ( ! get_current_user_id() ) {
				return false;
			} else {
				$user  = get_userdata( get_current_user_id() );
				$roles = $user->roles;
			}
		}

		foreach ( $roles as $role ) {
			if ( 'administrator' === $role || tutor()->instructor_role === $role ) {
				$is_instructor_or_admin = true;
				break;
			}
		}

		return self::is_enabled() && ! $is_instructor_or_admin;
	}

	/**
	 * Get fingerprint from metakey.
	 *
	 * @since 2.1.10
	 *
	 * @param string $meta_key meta key.
	 *
	 * @return string fingerprint string.
	 */
	public static function get_fingerprint( $meta_key ) {
		return str_replace( self::LOGIN_INFO_KEY, '', $meta_key );
	}

	/**
	 * Generate unique device id
	 *
	 * @since 2.1.10
	 *
	 * @return string
	 */
	public static function get_current_device_fingerprint() {
		if ( is_null( self::$fingerprint ) ) {

			// $agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

			// $user_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

			$auth_cookie = '';

			if ( isset( $_COOKIE[ self::cookie_name() ] ) ) {
				$auth_cookie = sanitize_text_field( wp_unslash( $_COOKIE[ self::cookie_name() ] ) );
			} else {
				$cookie = wp_rand();
				setcookie( self::cookie_name(), $cookie, time() + self::$cookie_expiry, '/' );

				$auth_cookie = $cookie;
			}

			self::$fingerprint = md5( $auth_cookie );
		}

		return self::$fingerprint;

	}

	/**
	 * Get current user's device info
	 *
	 * Combination of Device like : Desktop, mobile &
	 * platform like: Windows, Mac
	 *
	 * @since 2.1.10
	 *
	 * @return array
	 */
	public static function get_current_device_info() {
		$u_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		$device   = 'Laptop';
		$platform = 'Linux';
		$bname    = 'Unknown';

		$is_mob = is_numeric( strpos( strtolower( $u_agent ), 'mobile' ) );
		$is_tab = is_numeric( strpos( strtolower( $u_agent ), 'tablet' ) );

		// Platform check.
		$is_win     = is_numeric( strpos( strtolower( $u_agent ), 'windows' ) );
		$is_android = is_numeric( strpos( strtolower( $u_agent ), 'android' ) );
		$is_iphone  = is_numeric( strpos( strtolower( $u_agent ), 'iphone' ) );
		$is_ipad    = is_numeric( strpos( strtolower( $u_agent ), 'ipad' ) );
		$is_mac     = is_numeric( strpos( strtolower( $u_agent ), 'mac' ) );
		$is_ios     = $is_iphone || $is_ipad;

		if ( $is_mob ) {
			$device = 'Mobile';
		} elseif ( $is_tab ) {
			$device = 'Tablet';
		}

		if ( $is_ios ) {
			$platform = 'iOS';
		} elseif ( $is_android ) {
			$platform = 'Android';
		} elseif ( $is_win ) {
			$platform = 'Windows';
		} elseif ( $is_mac ) {
			$platform = 'Mac';
		}

		// Next get the name of the useragent yes seperately and for good reason.
		if ( preg_match( '/MSIE/i', $u_agent ) && ! preg_match( '/Opera/i', $u_agent ) ) {
			$bname = 'Internet Explorer';
		} elseif ( preg_match( '/Firefox/i', $u_agent ) ) {
			$bname = 'Mozilla Firefox';
		} elseif ( preg_match( '/OPR/i', $u_agent ) ) {
			$bname = 'Opera';
		} elseif ( preg_match( '/Chrome/i', $u_agent ) && ! preg_match( '/Edge/i', $u_agent ) ) {
			$bname = 'Google Chrome';
		} elseif ( preg_match( '/Safari/i', $u_agent ) && ! preg_match( '/Edge/i', $u_agent ) ) {
			$bname = 'Apple Safari';
		} elseif ( preg_match( '/Netscape/i', $u_agent ) ) {
			$bname = 'Netscape';
		} elseif ( preg_match( '/Edge/i', $u_agent ) ) {
			$bname = 'Edge';
		} elseif ( preg_match( '/Trident/i', $u_agent ) ) {
			$bname = 'Internet Explorer';
		}

		$device_info = array(
			'device'  => $device,
			'os'      => $platform,
			'browser' => $bname,
		);
		return $device_info;
	}

	/**
	 * Get user location by ip address
	 *
	 * @since 2.1.10
	 *
	 * @param string $user_ip optional user ip.
	 *
	 * @return array
	 */
	public static function get_current_user_location( $user_ip = '' ) {
		if ( '' === $user_ip ) {
			$user_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		}

		$details = json_decode( file_get_contents( "http://ipinfo.io/{$user_ip}/json" ) );

		$location = array(
			'country' => $details->country ?? '',
			'city'    => $details->city ?? '',
		);

		return $location;
	}

	/**
	 * Get total allowed device for a user
	 *
	 * @since 2.1.10
	 *
	 * @return integer
	 */
	public static function get_allowed_device() {
		return (int) tutor_utils()->get_option( 'device_limit', 1 );
	}

	/**
	 * Get total used device of a user
	 *
	 * @since 2.1.10
	 *
	 * @param int $user_id user id.
	 *
	 * @return integer
	 */
	public static function get_users_used_device( int $user_id ) {
		$device = self::get_current_device_fingerprint();
		$used   = get_user_meta( $user_id, self::LOGIN_INFO_KEY . $device );

		return is_array( $used ) ? count( $used ) : 0;
	}

	/**
	 * Config settings
	 *
	 * @since 2.1.9
	 *
	 * @param array $attr array attrs.
	 *
	 * @return array
	 */
	public static function config_settings( array $attr ): array {
		/**
		 * Email verification section under auth settings tab
		 */
		$settings = array(
			'label'      => __( 'Manage Active Login Sessions', 'tutor-pro' ),
			'slug'       => 'limit_active_device',
			'block_type' => 'uniform',
			'fields'     => array(
				array(
					'key'           => 'enable_limit_active_device',
					'type'          => 'toggle_switch',
					'label'         => __( 'Limit Active Login Sessions', 'tutor-pro' ),
					'label_title'   => '',
					'default'       => 'off',
					'desc'          => __( 'Toggle to limit the number of active sessions for a concurrent user login.', 'tutor-pro' ),
					'toggle_fields' => 'device_limit',
				),
				array(
					'key'         => 'device_limit',
					'type'        => 'number',
					'number_type' => 'integer',
					'label'       => __( 'Maximum Active Sessions', 'tutor-pro' ),
					'desc'        => __( 'Set the maximum number of active login sessions allowed per user.', 'tutor-pro' ),
					'default'     => '1',
					'min'         => '1',
				),
			),
		);

		array_push( $attr['authentication']['blocks'], $settings );
		return $attr;
	}

	/**
	 * Register manage device nav menu
	 *
	 * @param array $tabs profile navigation tabs.
	 *
	 * @return array
	 */
	public static function register_nav( $tabs ) {

		if ( ! self::is_applicable_limit_login() ) {
			return $tabs;
		}

		$nav_link = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings/' . self::SLUG );

		$new_tab = array(
			'url'   => esc_url( $nav_link ),
			'title' => __( 'Manage Login Sessions', 'tutor' ),
			'role'  => false,
		);

		$tabs[ self::SLUG ] = $new_tab;

		return apply_filters( 'tutor_manage_device_nav', $tabs );
	}

	/**
	 * Load device management template
	 *
	 * Based on query_vars filter template path
	 *
	 * @since 2.1.10
	 *
	 * @param string $location default file location.
	 *
	 * @return string
	 */
	public static function load_template( $location ) {

		if ( ! self::is_applicable_limit_login() ) {
			return;
		}

		$page_name          = get_query_var( 'pagename' );
		$dashboard_sub_page = get_query_var( 'tutor_dashboard_sub_page' );

		$dashboard_page_id = (int) tutor_utils()->get_option( 'tutor_dashboard_page_id' );
		$dashboard_page    = get_post( $dashboard_page_id );

		// Current page is dashboard & sub page is device-management.
		if ( $page_name === $dashboard_page->post_name && self::SLUG === $dashboard_sub_page ) {
			$template = tutor_pro()->templates . 'device-management.php';
			if ( file_exists( $template ) ) {
				$location = $template;
			}
		}

		return $location;
	}

	/**
	 * Validate limit login
	 *
	 * @since 2.1.10
	 *
	 * @param mixed $user user object or wp_error.
	 *
	 * @return mixed
	 */
	public static function validate_limit_login( $user ) {
		// If not user return.
		if ( is_wp_error( $user ) || is_null( $user ) ) {
			return $user;
		}

		// If not subscriber then return.
		if ( ! self::is_applicable_limit_login( $user->roles ) ) {
			return $user;
		}

		$is_existing_device = self::is_existing_device( $user->ID );

		if ( $is_existing_device ) {
			return $user;
		} else {
			$has_device_limit = self::has_device_limit( $user->ID );

			if ( $has_device_limit ) {
				self::add_new_login_device( $user->ID );
				return $user;
			}

			$alert_msg  = __( 'You have exceeded the maximum number of active login sessions allowed per user. ', 'tutor-pro' );
			$alert_msg .= '<div id="tutor-remove-logins-wrapper">';
			$alert_msg .= '<a href="javascript:" id="tutor-remove-active-logins" class="tutor-color-primary"> ' . __( 'Clear All Active Logins', 'tutor-pro' );
			$alert_msg .= '</a></div>';

			// Add user id in session.
			SessionHelper::set( self::USER_ID_KEY, $user->ID );
			return new WP_Error( 'tutor_login_limit', $alert_msg );
		}

	}

	/**
	 * Add new login device for a user to manage active login sessions.
	 *
	 * @since 2.2.2
	 *
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public static function add_new_login_device( $user_id ) {
		$new_device = self::get_current_device_fingerprint();

		add_user_meta(
			$user_id,
			self::LOGIN_INFO_KEY . $new_device,
			wp_json_encode( self::device_location_time_map() )
		);
	}

	/**
	 * Check if current device is existing
	 *
	 * @since 2.1.10
	 *
	 * @param int $user_id required user id.
	 *
	 * @return boolean
	 */
	public static function is_existing_device( int $user_id ): bool {
		global $wpdb;

		$current_device = self::get_current_device_fingerprint();

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
                    FROM $wpdb->usermeta AS meta
                    WHERE meta.meta_key = %s
					AND user_id = %d
                ",
				self::LOGIN_INFO_KEY . $current_device,
				$user_id
			)
		);

		return is_array( $result ) && count( $result );
	}

	/**
	 * Check if a user have login device limit
	 *
	 * @since 2.1.10
	 *
	 * @param int $user_id required user id.
	 *
	 * @return integer
	 */
	public static function has_device_limit( int $user_id ): int {
		$allowed_device = self::get_allowed_device();

		$used_device = self::get_logged_in_devices( $user_id );

		$used_device = is_array( $used_device ) ? count( $used_device ) : 0;

		return $allowed_device - $used_device;
	}

	/**
	 * Remove user device after logout
	 *
	 * @since 2.1.10
	 *
	 * @param int $user_id logout user id.
	 *
	 * @return void
	 */
	public static function remove_device( $user_id ) {
		$user = get_userdata( $user_id );

		if ( ! is_a( $user, 'WP_User' ) ) {
			return;
		}

		if ( ! self::is_applicable_limit_login( $user->roles ) ) {
			return;
		}

		$device_fingerprint = self::get_current_device_fingerprint();

		// Remove additional info.
		delete_user_meta(
			$user_id,
			self::LOGIN_INFO_KEY . $device_fingerprint
		);

		// Remove cookie.
		unset( $_COOKIE[ self::cookie_name() ] );
	}

	/**
	 * Concat device info like: desktop & user
	 * location like: country, city & login time
	 *
	 * @since 2.1.10
	 *
	 * @return array
	 */
	private static function device_location_time_map() {
		$device_info = self::get_current_device_info();
		$location    = self::get_current_user_location();

		$login_time = array(
			'login_time' => tutor_time(),
		);

		return $device_info + $location + $login_time;
	}

	/**
	 * Get user's logged in device info
	 *
	 * @since 2.1.10
	 *
	 * @param integer $user_id user id.
	 *
	 * @return wpdb::get_results
	 */
	public static function get_logged_in_devices( int $user_id ) {
		global $wpdb;

		$user_id = sanitize_text_field( $user_id );
		$key     = self::LOGIN_INFO_KEY;

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
					FROM {$wpdb->usermeta} AS meta
					WHERE meta.meta_key LIKE %s
						AND meta.user_id = %d
				",
				"%$key%",
				$user_id
			)
		);

		return $result;
	}

	/**
	 * Remove user's logged-in device & logout if necessary
	 *
	 * @since 2.1.10
	 *
	 * @return void wp_json response
	 */
	public static function remove_device_manually() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$response = array(
			'msg'         => __( 'Something went wrong, please try again!', 'tutor-pro' ),
			'redirect_to' => false,
		);

		$table    = $wpdb->usermeta;
		$umeta_id = Input::post( 'umeta_id' );
		$where    = array( 'umeta_id' => $umeta_id );

		$meta = QueryHelper::get_row( $table, $where, 'umeta_id' );

		if ( $meta ) {
			$delete = QueryHelper::delete( $table, $where );

			if ( $delete ) {
				$response['msg'] = __( 'Device removed successfully!' );

				$device_fingerprint = str_replace( self::LOGIN_INFO_KEY, '', $meta->meta_key );

				// If user removing current device then log him out.
				if ( self::get_current_device_fingerprint() === $device_fingerprint ) {
					wp_logout();
					$response['redirect_to'] = tutor_utils()->tutor_dashboard_url( '?nocache=' . time() );
				}

				wp_send_json_success( $response );
			} else {
				$response['msg'] = __( 'Device removed failed!' );
				wp_send_json_error( $response );
			}
		} else {
			$response['msg'] = __( 'Invalid user meta ID', 'tutor-pro' );
			wp_send_json_error( $response );
		}
	}

	/**
	 * Clear session button
	 *
	 * Admin will be able to clear student's logged-in session
	 *
	 * @since 2.1.10
	 *
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public static function clear_session_btn( int $user_id ) {
		$active_sessions = self::get_logged_in_devices( $user_id );
		$has_sessions    = is_array( $active_sessions ) && count( $active_sessions );
		?>
		<a href="javascript:" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-clear-sessions" data-student-id="<?php echo esc_attr( $user_id ); ?>" <?php echo esc_attr( ! $has_sessions ? 'disabled' : '' ); ?>>
			<?php esc_html_e( 'Clear Sessions', 'tutor-pro' ); ?>
		</a>
		<?php
	}

	/**
	 * Clear active session of a student
	 *
	 * @since 2.1.10
	 *
	 * @return void wp_json response
	 */
	public static function clear_active_sessions() {
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		} else {
			$user_id = Input::post( 'user_id' );
			$delete  = self::clear_all_sessions_by_user_id( $user_id );

			if ( $delete ) {
				wp_send_json_success( __( 'Session cleared successfully', 'tutor-pro' ) );
			} else {
				wp_send_json_error( __( 'Session clear failed, please try again!', 'tutor-pro' ) );
			}
		}
	}

	/**
	 * Clear all active sessions by user id
	 *
	 * @since 2.1.10
	 *
	 * @param int $user_id user id.
	 *
	 * @return mixed
	 */
	public static function clear_all_sessions_by_user_id( $user_id ) {
		global $wpdb;

		$meta_key = self::LOGIN_INFO_KEY;

		$delete = $wpdb->query(
			$wpdb->prepare(
				"DELETE
					FROM {$wpdb->usermeta}
					WHERE meta_key LIKE %s
						AND user_id = %d
				",
				"%$meta_key%",
				$user_id
			)
		);

		return $delete;
	}

	/**
	 * Remove all active login by users
	 *
	 * @return void wp_json response
	 */
	public static function remove_all_active_logins() {
		tutor_utils()->checking_nonce();

		$user_id = SessionHelper::get( self::USER_ID_KEY );
		$user    = get_userdata( $user_id );

		if ( $user ) {
			$delete = self::clear_all_sessions_by_user_id( $user->ID );

			if ( $delete ) {
				wp_send_json_success( __( 'All the active login removed!', 'tutor-pro' ) );

				// Clear tmp user id.
				SessionHelper::unset( self::USER_ID_KEY );
			} else {
				wp_send_json_error( __( 'Remove active login session failed', 'tutor-pro' ) );
			}
		} else {
			wp_send_json_error( __( 'Invalid User ID', 'tutor-pro' ) );
		}
	}
}
