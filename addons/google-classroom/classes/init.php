<?php
/**
 * Google Classroom Init
 *
 * @package TutorPro\Addons
 * @subpackage GoogleClassroom
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_GC;

use TUTOR\Input;
use TUTOR\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//phpcs:ignore
class init {
	//phpcs:disable
	private $gc_dashboard_url;
	private $gc_stream_slug               = 'google-classroom-stream';
	private $gc_dashboard_slug            = 'tutor-google-classroom';
	private $gc_code_privilege            = 'tutor_gc_classrooom_code_only_for_logged_in';
	public static $google_callback_string = 'tutor-google-classroom-callback';
	private $gc_metabox;
	//phpcs:enable

	/**
	 * Constructor
	 *
	 * @return void|null
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		$addon_config = tutor_utils()->get_addon_config( TUTOR_GC()->basename );
		$is_enable    = (bool) tutor_utils()->avalue_dot( 'is_enable', $addon_config );
		if ( ! $is_enable ) {
			return;
		}

		$this->gc_metabox = array(
			'tutor_gc_enable_classroom_stream' => __( 'Enable Google Classroom Stream', 'tutor-pro' ),
			'tutor_gc_show_stream_files'       => __( 'Show Google Classroom Files in Stream', 'tutor-pro' ),
			'tutor_gc_include_classroom_files' => __( 'Include Google Classroom Files in Resources', 'tutor-pro' ),
		);

		$this->gc_dashboard_url = get_admin_url( null, 'admin.php?page=' . $this->gc_dashboard_slug );
		spl_autoload_register( array( $this, 'loader' ) );

		$this->register_hooks();
	}

	/**
	 * Class loader.
	 *
	 * @param string $class_name class name.
	 *
	 * @return void
	 */
	public function loader( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			$class_name = preg_replace(
				array( '/([a-z])([A-Z])/', '/\\\/' ),
				array( '$1$2', DIRECTORY_SEPARATOR ),
				$class_name
			);

			$class_name = str_replace( 'TUTOR_GC' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = TUTOR_GC()->path . $class_name . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	private function register_hooks() {

		add_action( 'tutor_admin_register', array( $this, 'add_sub_menu' ) );
		add_action( 'wp_loaded', array( $this, 'save_token' ) );
		add_action( 'wp_loaded', array( $this, 'reset_tutor_student_password' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_script' ) );

		add_action( 'wp_ajax_tutor_gc_load_more_stream', array( $this, 'stream_tab_content' ) );
		add_action( 'wp_ajax_tutor_gc_credential_save', array( $this, 'save_credential' ) );
		add_action( 'wp_ajax_tutor_gc_class_action', array( $this, 'dispatch_class_action' ) );
		add_action( 'wp_ajax_tutor_gc_classroom_code_privilege', array( $this, 'set_classroom_code_privilege' ) );
		add_action( 'wp_ajax_tutor_gc_credential_upgrade', array( $this, 'upgrade_credential' ) );
		add_action( 'wp_ajax_nopriv_tutor_gc_student_set_password', array( $this, 'set_student_password' ) );

		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ), 10, 2 );
		add_action( 'save_post_' . tutor()->course_post_type, array( $this, 'save_course_meta' ) );

		add_filter( 'tutor_course/single/nav_items', array( $this, 'stream_tab' ), 10, 2 );
		add_filter( 'tutor_course/single/start/button', array( $this, 'add_start_course_button' ), 10, 2 );
		add_action( 'tutor_global/after/attachments', array( $this, 'load_gc_attachments' ), 10, 3 );
		add_action( 'tutor/dashboard_course_builder_form_field_after', array( $this, 'course_connection_metabox_frontend' ) );

		add_shortcode( 'tutor_gc_classes', array( $this, 'tutor_gc_classes' ) );
	}

	/**
	 * Load scripts
	 *
	 * @return void
	 */
	public function load_admin_scripts() {
		$page         = tutor_utils()->array_get( 'page', $_GET );
		$gc_dashboard = function_exists( 'is_admin' ) && is_admin() && $page && $page == $this->gc_dashboard_slug;

		if ( $gc_dashboard ) {
			wp_enqueue_style( 'tutor-gc-dashboard-style', TUTOR_GC()->url . 'assets/css/classroom-dashboard.css', array(), TUTOR_PRO_VERSION );
			wp_enqueue_script( 'tutor-gc-dashboard-script', TUTOR_GC()->url . 'assets/js/classroom-dashboard.js', array( 'jquery' ), TUTOR_PRO_VERSION, true );
		}
	}

	/**
	 * Frontend scripts
	 *
	 * @return void
	 */
	public function load_frontend_script() {
		wp_enqueue_style( 'tutor-gc-frontend-style', TUTOR_GC()->url . 'assets/css/classroom-frontend.css', array(), TUTOR_PRO_VERSION );
		wp_enqueue_script( 'tutor-gc-frontend-js', TUTOR_GC()->url . 'assets/js/classroom-frontend.js', array( 'jquery' ), TUTOR_PRO_VERSION, true );
	}

	/**
	 * Metabox register
	 *
	 * @param string $post_type post type.
	 * @param object $post post object.
	 *
	 * @return void
	 */
	public function register_metabox( $post_type = null, $post = null ) {
		if ( $post && ( new Classroom( null, null, true ) )->is_google_class( $post->ID ) ) {
			$course_post_type = tutor()->course_post_type;
			tutor_meta_box_wrapper( 'tutor-gc-course-connection-side', __( 'Connect Tutor Course', 'tutor-pro' ), array( $this, 'course_connection_metabox' ), $course_post_type, 'side', 'default', 'tutor-admin-post-meta' );
		}
	}

	/**
	 * Save course meta.
	 *
	 * @param int $post_ID post id.
	 *
	 * @return void
	 */
	public function save_course_meta( $post_ID ) {
		$additional_data_edit = tutor_utils()->avalue_dot( '_tutor_course_additional_data_edit', $_POST ); //phpcs:ignore

		if ( $additional_data_edit ) {
			foreach ( $this->gc_metabox as $key => $title ) {
				$value = Input::has( $key ) ? 'yes' : 'no';
				update_post_meta( $post_ID, $key, $value );
			}
		}
	}

	/**
	 * Course connection meta box.
	 *
	 * @param object $post post.
	 *
	 * @return void
	 */
	public function course_connection_metabox( $post ) {

		foreach ( $this->gc_metabox as $key => $title ) {

			$checked = get_post_meta( $post->ID, $key, true );
			$checked = ( empty( $checked ) || 'yes' === $checked ) ? 'checked="checked"' : '';

			?>
				<div class="tutor-course-sidebar-settings-item">
					<label for="<?php echo esc_attr( $key ); ?>">
						<input id="<?php echo esc_attr( $key ); ?>" type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="yes" <?php echo esc_attr( $checked ); ?> />
						<?php esc_html_e( $title, 'tutor-pro' ); //phpcs:ignore ?>
					</label>
				</div>
			<?php
		}
	}

	/**
	 * Course connection metabox frontend
	 *
	 * @param object $post post.
	 *
	 * @return void
	 */
	public function course_connection_metabox_frontend( $post ) {

		$post_id = is_object( $post ) ? ( $post->ID ? $post->ID : 0 ) : 0;

		if ( ! ( new Classroom( null, null, true ) )->is_google_class( $post_id ) ) {
			// Make sure it is imported google class.
			return;
		}
		?>
			<div class="tutor-course-builder-section tutor-course-builder-info">
				<div class="tutor-course-builder-section-title">
					<span class="tutor-fs-5 tutor-fw-bold tutor-color-secondary">
						<i class="tutor-icon-down" area-hidden="true"></i>
						<span>
							<?php esc_html_e( 'Connect Tutor Course', 'tutor-pro' ); ?>
						</span>
					</span>
				</div>
				<div class="tutor-course-builder-section-content">
					<div class="tutor-frontend-builder-item-scope">
						<div class="tutor-form-group">
							<?php $this->course_connection_metabox( $post ); ?>
						</div>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * Add sub-menu
	 *
	 * @return void
	 */
	public function add_sub_menu() {
		add_submenu_page( 'tutor', __( 'Google Classroom', 'tutor-pro' ), __( 'Google Classroom', 'tutor-pro' ), 'manage_tutor_instructor', $this->gc_dashboard_slug, array( $this, 'admin_page_content' ) );
	}

	/**
	 * Admin page content.
	 *
	 * @return void
	 */
	public function admin_page_content() {
		$classroom               = new Classroom();
		$is_code_for_only_logged = $this->is_class_restricted();
		include TUTOR_GC()->path . '/views/classroom-dashboard.php';
	}

	/**
	 * Is class restricted.
	 *
	 * @return boolean
	 */
	private function is_class_restricted() {
		return get_option( $this->gc_code_privilege ) == 'yes';
	}

	/**
	 * Stream tab.
	 *
	 * @param array $nav_menus nav menu items.
	 * @param int   $course_id course id.
	 *
	 * @return array
	 */
	public function stream_tab( $nav_menus, $course_id ) {
		if ( ( new Classroom( null, null, true ) )->is_google_class( $course_id ) && $this->is_stream_enabled( $course_id ) ) {
			$nav_menus[ $this->gc_stream_slug ] = array(
				'title'             => __( 'Stream', 'tutor-pro' ),
				'method'            => array( $this, 'stream_tab_content' ),
				'require_enrolment' => true,
			);
		}

		return $nav_menus;
	}

	/**
	 * Check stream enabled
	 *
	 * @param int $course_id course id.
	 *
	 * @return boolean
	 */
	private function is_stream_enabled( $course_id = null ) {
		! $course_id ? $course_id = 0 : 0;
		$value                    = get_post_meta( $course_id, 'tutor_gc_enable_classroom_stream', true );
		return ( empty( $value ) || 'yes' === $value );
	}

	/**
	 * Check stream file enabled.
	 *
	 * @param int $course_id course id.
	 *
	 * @return boolean
	 */
	private function is_stream_file_enabled( $course_id ) {
		$value = get_post_meta( $course_id, 'tutor_gc_show_stream_files', true );
		return ( empty( $value ) || 'yes' === $value );
	}

	/**
	 * Is resource file enabled.
	 *
	 * @param int $course_id course id.
	 *
	 * @return boolean
	 */
	private function is_resource_file_enabled( $course_id ) {
		$value = get_post_meta( $course_id, 'tutor_gc_include_classroom_files', true );
		return ( empty( $value ) || 'yes' === $value );
	}

	/**
	 * Stream tab content.
	 *
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public function stream_tab_content( $course_id = null ) {
		strtolower( $_SERVER['REQUEST_METHOD'] ) != 'get' ? tutor_utils()->checking_nonce() : 0; //phpcs:ignore

		if ( $this->is_stream_enabled( $course_id ) ) {

			$course_id = Input::post( 'course_id', $course_id, Input::TYPE_INT );
			if ( ! $course_id ) {
				return;
			}

			$classroom  = new Classroom( null, $course_id );
			$next_token = Input::post( 'next_token', null );

			$classroom_info    = $classroom->get_remote_class( $course_id );
			$_stream           = $classroom->get_stream( $course_id, $next_token );
			$classroom_stream  = isset( $_stream['announcements'] ) ? $_stream['announcements'] : array();
			$stream_next_token = isset( $_stream['next_token'] ) ? $_stream['next_token'] : '';

			$show_stream_files = $this->is_stream_file_enabled( $course_id );

			if ( ! Input::has( 'course_id' ) ) {
				include dirname( __DIR__ ) . '/views/components/stream.php';
				return;
			}

			ob_start();
			include dirname( __DIR__ ) . '/views/components/stream-individual.php';
			$html = ob_get_clean();

			exit(
				json_encode(
					array(
						'html'       => $html,
						'next_token' => $stream_next_token,
					)
				)
			);
		}
	}

	/**
	 * Add start course button.
	 *
	 * @param mixed $content content.
	 * @param int   $course_id course id.
	 *
	 * @return mixed
	 */
	public function add_start_course_button( $content, $course_id ) {
		$classroom_url = ( new Classroom( null, null, true ) )->is_google_class( $course_id, true );

		if ( $classroom_url ) {
			ob_start();
			include dirname( __DIR__ ) . '/views/components/start-class.php';
			$content = ob_get_clean();
		}

		return $content;
	}

	/**
	 * Load GC attachments.
	 *
	 * @return void
	 */
	public function load_gc_attachments() {
		$local_id = get_the_ID();

		if ( $this->is_resource_file_enabled( $local_id ) ) {

			$classroom       = new Classroom( null, $local_id );
			$materials_array = $classroom->get_all_remote_attachments( $local_id );
			include dirname( __DIR__ ) . '/views/components/materials.php';
		}
	}

	/**
	 * Save credentials.
	 *
	 * @return void
	 */
	public function save_credential() {
		tutor_utils()->checking_nonce();

		if ( ! User::has_any_role( array( User::ADMIN, User::INSTRUCTOR ) ) ) {
			wp_die( esc_html( tutor_utils()->error_message() ) );
		}

		if ( isset( $_FILES['credential'], $_FILES['credential']['error'] ) && 0 == $_FILES['credential']['error'] ) {
			// Save credential file if exist and no error.
			( new Classroom() )->save_credential( $_FILES['credential'] );//phpcs:ignore
		}

		header( 'Location: ' . $this->gc_dashboard_url );
		exit;
	}

	/**
	 * Reset student password.
	 *
	 * @return void
	 */
	public function reset_tutor_student_password() {
		//phpcs:ignore
		if ( ! strpos( $_SERVER['REQUEST_URI'], Classroom::$password_reset_base ) || ! isset( $_GET['token'] ) ) {
			return;
		}

		if ( is_user_logged_in() ) {
			wp_redirect( get_home_url() );
			exit;
		}

		tutor_utils()->tutor_custom_header();

		if ( ! ( new Classroom() )->is_reset_token_valid( Input::get( 'token' ) ) ) {
			echo '<div class="tutor-color-warning tutor-text-center tutor-my-62">' . esc_html__( 'Invalid Token or Password is already set.', 'tutor-pro' ) . '</div>';
		} else {
			include dirname( __DIR__ ) . '/views/components/password-setup.php';
		}

		tutor_utils()->tutor_custom_footer();
		exit;
	}

	/**
	 * Save token.
	 *
	 * @return void
	 */
	public function save_token() {
		//phpcs:ignore
		if ( ! strpos( $_SERVER['REQUEST_URI'], self::$google_callback_string ) ) {
			// It is Other page request.
			return;
		}

		if ( ! Input::get( 'code' ) ) {
			echo 'No token.';
		} else {
			( new Classroom() )->save_token( Input::get( 'code' ) );
			header( 'Location: ' . $this->gc_dashboard_url );
		}

		exit;
	}

	/**
	 * Dispatch class action.
	 *
	 * @return void
	 */
	public function dispatch_class_action() {
		tutor_utils()->checking_nonce();

		$action   = Input::post( 'action_name' );
		$local_id = Input::post( 'post_id', '' );

		if ( ! User::has_any_role( array( User::ADMIN, User::INSTRUCTOR ) ) ) {
			wp_die( esc_html( tutor_utils()->error_message() ) );
		}
		if ( 'import' === $action ) {
			$this->import_class();
			return;
		}

		if ( ! is_numeric( $local_id ) ) {
			return;
		}

		switch ( $action ) {
			case 'publish':
				wp_publish_post( $local_id );
				break;
			case 'trash':
				wp_trash_post( $local_id );
				break;
			case 'delete':
				wp_delete_post( $local_id, true );
				break;
			case 'restore':
				wp_untrash_post( $local_id );
				break;
		}

		$status = 'trash' === $action ? 'trash' : get_post_field( 'post_status', $local_id );

		$response = 'delete' === $action ?
		array(
			'class_status' => 'not-imported',
			'status_text'  => 'Not Imported',
		) :
		array(
			'class_status' => $status,
			'status_text'  => ucfirst( $status ),
		);

		exit( json_encode( $response ) );
	}

	/**
	 * Import class
	 *
	 * @return void
	 */
	private function import_class() {

		$class_id = Input::post( 'class_id' );

		$enroll_student = 'yes' === Input::post( 'enroll_student', '' );
		$local_id       = ( new Classroom() )->import_class( $class_id, $enroll_student );
		$is_valid       = is_numeric( $local_id );

		$status = $is_valid ? get_post_field( 'post_status', $local_id ) : '';

		$response = ! $is_valid ? null :
		array(
			'post_id'      => $local_id,
			'edit_link'    => get_edit_post_link( $local_id, '' ),
			'preview_link' => get_permalink( $local_id ),
			'class_status' => $status,
			'status_text'  => ucfirst( $status ),
		);

		exit( $response ? json_encode( $response ) : '' ); //phpcs:ignore
	}

	/**
	 * Set classroom code privilege
	 *
	 * @return void
	 */
	public function set_classroom_code_privilege() {
		tutor_utils()->checking_nonce();

		if ( ! User::has_any_role( array( User::ADMIN, User::INSTRUCTOR ) ) ) {
			wp_die( esc_html( tutor_utils()->error_message() ) );
		}
		//phpcs:ignore
		if ( ! isset( $_POST['enabled'] ) || ! in_array( $_POST['enabled'], array( 'yes', 'no' ) ) ) {
			return;
		}

		update_option( $this->gc_code_privilege, Input::post( 'enabled' ) );
	}

	/**
	 * Upgrade credential
	 *
	 * @return void
	 */
	public function upgrade_credential() {
		( new Classroom() )->upgrade_credential_serial();
	}

	/**
	 * Set student password.
	 *
	 * @return void
	 */
	public function set_student_password() {
		tutor_utils()->checking_nonce();

		$token    = Input::post( 'token', '' );
		$password = Input::post( 'password', '' );

		( new Classroom() )->set_student_password( $token, $password );

		exit;
	}

	/**
	 * GC classes
	 *
	 * @param array $attr attr.
	 *
	 * @return mixed
	 */
	public function tutor_gc_classes( $attr = array() ) {

		$page = Input::get( 'class_page', 1, Input::TYPE_INT );
		( ! is_numeric( $page ) || $page < 1 ) ? $page = 1 : 0;

		$google_classes      = ( new Classroom() )->get_imported_class_list( $page );
		$is_class_restricted = $this->is_class_restricted();

		// Define responsive class.
		$column       = is_array( $attr ) ? ( isset( $attr['max-column'] ) ? $attr['max-column'] : 3 ) : 3;
		$column       = is_numeric( $column ) ? (int) $column : 0;
		$column_class = '';

		switch ( $column ) {
			case 1:
				$column_class = 'tutor-col-12';
				break;
			case 2:
				$column_class = 'tutor-col-12 tutor-col-sm-6';
				break;
			case 3:
				$column_class = 'tutor-col-12 tutor-col-sm-6 tutor-col-md-4';
				break;
			case 4:
				$column_class = 'tutor-col-12 tutor-col-sm-6 tutor-col-md-4 tutor-col-lg-3';
				break;
			case 6:
				$column_class = 'tutor-col-12 tutor-col-sm-6 tutor-col-md-4 tutor-col-lg-3 tutor-col-xl-2';
				break;
			default:
				$column_class = 'tutor-col-12 tutor-col-sm-6 tutor-col-md-4';
		}

		ob_start();
		include dirname( __DIR__ ) . '/views/components/class-list-shortcode.php';
		return ob_get_clean();
	}
}
