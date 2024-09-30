<?php

/**
 * TutorZoom Class
 *
 * @package TUTOR
 *
 * @since v.1.7.1
 */

namespace TUTOR_ZOOM;

use TUTOR\Tutor_Base;
use TUTOR\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Zoom extends Tutor_Base {

	private $api_key;
	private $settings_key;
	private $zoom_meeting_post_type;
	private $zoom_meeting_base_slug;
	private $zoom_meeting_post_meta;

	function __construct( $register_hooks = true ) {
		parent::__construct();

		$this->api_key                = 'tutor_zoom_api';
		$this->settings_key           = 'tutor_zoom_settings';
		$this->zoom_meeting_post_type = 'tutor_zoom_meeting';
		$this->zoom_meeting_base_slug = 'tutor-zoom-meeting';
		$this->zoom_meeting_post_meta = '_tutor_zm_data';

		if ( ! $register_hooks ) {
			// This object is used for reusable method calls
			return;
		}

		add_action( 'init', array( $this, 'register_zoom_post_types' ) );

		/**
		 * Register all admin scripts
		 *
		 * use same admin scripts on the front end for zoom
		 *
		 * @since 1.9.4
		 */
		add_action( 'wp_loaded', array( $this, 'register_admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'admin_scripts_frontend' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'tutor_script_text_domain' ), 100 );

		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'tutor_admin_register', array( $this, 'register_menu' ) );

		add_filter( 'tutor_course_contents_post_types', array( $this, 'tutor_course_contents_post_types' ) );

		// Saving zoom settings
		add_action( 'wp_ajax_tutor_save_zoom_api', array( $this, 'tutor_save_zoom_api' ) );
		add_action( 'wp_ajax_tutor_save_zoom_settings', array( $this, 'tutor_save_zoom_settings' ) );

		// Add meeting button options
		add_action( 'add_meta_boxes', function(){
			tutor_meta_box_wrapper( 'zoome-meeting-for-course', __( 'Zoom Meeting', 'tutor' ), array($this, 'add_meetings_metabox'), tutor()->course_post_type, 'advanced', 'default', 'tutor-admin-post-meta' );
		});
		add_action( 'tutor/frontend_course_edit/after/course_builder', array( $this, 'add_meta_box_frontend' ) );

		add_action( 'tutor_course_builder_after_btn_group', array( $this, 'add_meeting_option_in_topic' ), 12, 2 );

		// Meeting modal form and save action
		add_action( 'wp_ajax_tutor_zoom_save_meeting', array( $this, 'tutor_zoom_save_meeting' ) );

		add_action( 'wp_ajax_tutor_zoom_delete_meeting', array( $this, 'tutor_zoom_delete_meeting' ) );

		add_action( 'tutor_course/single/before/topics', array( $this, 'tutor_zoom_course_meeting' ) );
		add_filter( 'template_include', array( $this, 'load_meeting_template' ), 99 );

		/**
		 * Apply filters on tutor nav items add zoom menu
		 *
		 * Load zoom template from zoom addons
		 *
		 * @since 1.9.4
		 */
		add_filter( 'tutor_dashboard/instructor_nav_items', array( $this, 'add_zoom_menu' ) );
		add_filter( 'load_dashboard_template_part_from_other_location', array( $this, 'load_zoom_template' ) );

		add_action( 'tutor/course/builder/content/tutor_zoom_meeting', array( $this, 'course_builder_row' ), 10, 4 );

		/**
		 * Is zoom lesson completed
		 *
		 * @since v2.0.0
		 */
		add_action( 'tutor_is_zoom_lesson_done', array( __CLASS__, 'is_zoom_lesson_done' ), 10, 3 );
		add_action( 'tutor/zoom/right_icon_area', array( $this, 'right_icon_area' ), 10, 2 );

		/**
		 * Show admin notice to update Zoom API authentication
		 * from JWT to Server-to-Server-OAuth
		 *
		 * @since 2.2.0
		 */
		add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );
		add_action( 'wp_footer', array( $this, 'show_notice_frontend' ) );

		add_filter( 'post_type_link', array( $this, 'change_zoom_single_url' ), 1, 2 );
	}

	/**
	 * Change zoom meeting single URL
	 *
	 * @since 2.6.0
	 *
	 * @param string  $post_link post link.
	 * @param integer $id id.
	 *
	 * @return string
	 */
	public function change_zoom_single_url( $post_link, $id = 0 ) {
		$post = get_post( $id );

		if ( is_object( $post ) && 'tutor_zoom_meeting' === $post->post_type ) {
			$course_id = tutor_utils()->get_course_id_by( 'zoom_lesson', $post->ID );
			$course    = get_post( $course_id );

			if ( is_object( $course ) ) {
				return home_url( "/{$this->course_base_permalink}/{$course->post_name}/zoom-lessons/" . $post->post_name . '/' );
			} else {
				return home_url( "/{$this->course_base_permalink}/sample-course/zoom-lessons/" . $post->post_name . '/' );
			}
		}

		return $post_link;
	}

	public function course_builder_row( $meeting, $topic, $course_id, $index = null ) {
		$row_id           = 'tutor-zoom-meeting-' . $meeting->ID;
		$id_string_delete = 'tutor-zoom-meet-del-modal-id' . $meeting->ID;
		?>
		<div data-course_content_id="<?php echo $meeting->ID; ?>" id="<?php echo $row_id; ?>" class="course-content-item tutor-zoom-meeting-item tutor-zoom-meeting-<?php echo $meeting->ID; ?>">
			<div class="tutor-course-content-top tutor-d-flex tutor-align-center">
				<span class="tutor-icon-hamburger-menu tutor-cursor-move tutor-px-12"></span>
				<a href="javascript:;" class="<?php echo $topic->ID>0 ? 'tutor-zoom-meeting-modal-open-btn' : ''; ?>" data-tutor-modal-target="<?php echo $topic->ID>0 ? 'tutor-zoom-modal-cb-'.$meeting->ID : ''; ?>">
					<?php echo __( 'Zoom', 'tutor' ) . ' ' . $index . ': ' . stripslashes( $meeting->post_title ); ?>
				</a>

				<div class="tutor-course-content-top-right-action">
					<?php if($topic->ID>0): ?>
						<a href="javascript:;" class="tutor-zoom-meeting-modal-open-btn tutor-iconic-btn" data-tutor-modal-target="tutor-zoom-modal-cb-<?php echo $meeting->ID; ?>">
							<span class="tutor-icon-edit" area-hidden="true"></span>
						</a>
					<?php endif; ?>

					<a href="javascript:;" class="tutor-iconic-btn" data-tutor-modal-target="<?php echo $id_string_delete; ?>">
						<span class="tutor-icon-trash-can-line" area-hidden="true"></span>
					</a>
				</div>
			</div>
			<?php
				// update Modal
				$this->tutor_zoom_meeting_modal_content( $meeting->ID, $topic->ID, $course_id, 'course-builder', 'tutor-zoom-modal-cb-' . $meeting->ID );
				// Delete confirmation modal
				tutor_load_template( 'modal.confirm', array(
					'id' => $id_string_delete,
					'image' => 'icon-trash.svg',
					'title' => __('Do You Want to Delete This Meeting?', 'tutor-pro'),
					'content' => __('Are you sure you want to delete this meeting permanently? Please confirm your choice.', 'tutor-pro'),
					'yes' => array(
						'text' => __('Yes, Delete This', 'tutor-pro'),
						'class' => 'tutor-list-ajax-action',
						'attr' => array(
							'data-request_data=\'{"meeting_id":"'. $meeting->ID .'", "action":"tutor_zoom_delete_meeting"}\'', 
							'data-delete_element_id="' . $row_id . '"'
						)
					),
				));
			?>
		</div>
		<?php
	}

	public function register_zoom_post_types() {

		$labels = array(
			'name'               => _x( 'Meetings', 'post type general name', 'tutor-pro' ),
			'singular_name'      => _x( 'Meeting', 'post type singular name', 'tutor-pro' ),
			'menu_name'          => _x( 'Meetings', 'admin menu', 'tutor-pro' ),
			'name_admin_bar'     => _x( 'Meeting', 'add new on admin bar', 'tutor-pro' ),
			'add_new'            => _x( 'Add New', $this->zoom_meeting_post_type, 'tutor-pro' ),
			'add_new_item'       => __( 'Add New Meeting', 'tutor-pro' ),
			'new_item'           => __( 'New Meeting', 'tutor-pro' ),
			'edit_item'          => __( 'Edit Meeting', 'tutor-pro' ),
			'view_item'          => __( 'View Meeting', 'tutor-pro' ),
			'all_items'          => __( 'Meetings', 'tutor-pro' ),
			'search_items'       => __( 'Search Meetings', 'tutor-pro' ),
			'parent_item_colon'  => __( 'Parent Meetings:', 'tutor-pro' ),
			'not_found'          => __( 'No Meeting found.', 'tutor-pro' ),
			'not_found_in_trash' => __( 'No Meetings found in Trash.', 'tutor-pro' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Description.', 'tutor-pro' ),
			'public'              => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => $this->zoom_meeting_base_slug ),
			'menu_icon'           => 'dashicons-list-view',
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array( 'title', 'editor' ),
			'exclude_from_search' => true,
		);

		register_post_type( $this->zoom_meeting_post_type, $args );
	}

	/**
	 * Register all admin scripts so that later
	 *
	 * we can enqueue on admin_scripts hook or wp_enqueue_scripts hook
	 *
	 * @since 1.9.4
	 */
	public function register_admin_scripts() {
		wp_register_script( 'tutor_zoom_timepicker_js', TUTOR_ZOOM()->url . 'assets/js/lib/jquery-ui-timepicker.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), TUTOR_PRO_VERSION, true );
		wp_register_script( 'tutor_zoom_admin_js', TUTOR_ZOOM()->url . 'assets/js/admin.js', array( 'jquery' ), TUTOR_PRO_VERSION, true );
		wp_register_script( 'tutor_zoom_common_js', TUTOR_ZOOM()->url . 'assets/js/common.js', array( 'jquery', 'jquery-ui-datepicker' ), TUTOR_PRO_VERSION, true );
		wp_register_style( 'tutor_zoom_timepicker_css', TUTOR_ZOOM()->url . 'assets/css/jquery-ui-timepicker.css', false, TUTOR_PRO_VERSION );
		wp_register_style( 'tutor_zoom_common_css', TUTOR_ZOOM()->url . 'assets/css/common.css', false, TUTOR_PRO_VERSION );
		wp_register_style( 'tutor_zoom_admin_css', TUTOR_ZOOM()->url . 'assets/css/admin.css', false, TUTOR_PRO_VERSION );
	}

	/**
	 * Enqueue admin scripts
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'tutor_zoom_timepicker_js' );
		wp_enqueue_script( 'tutor_zoom_admin_js' );
		wp_enqueue_script( 'tutor_zoom_common_js' );

		wp_enqueue_style( 'tutor_zoom_timepicker_css' );
		wp_enqueue_style( 'tutor_zoom_common_css' );
		wp_enqueue_style( 'tutor_zoom_admin_css' );
	}
	/**
	 * Load admin scripts on the frontend that is need for zoom
	 *
	 * @since 1.9.4
	 */
	public function admin_scripts_frontend() {
		wp_enqueue_script( 'tutor_zoom_timepicker_js' );
		wp_enqueue_script( 'tutor_zoom_admin_js' );
		wp_enqueue_script( 'tutor_zoom_common_js' );

		wp_enqueue_style( 'tutor_zoom_timepicker_css' );
		wp_enqueue_style( 'tutor_zoom_common_css' );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function frontend_scripts() {
		global $wp_query;
		$is_frontend_course_builder = tutor_utils()->is_tutor_frontend_dashboard( 'create-course' );
		$is_single_zoom_page        = ( is_single() && ! empty( $wp_query->query['post_type'] ) && $wp_query->query['post_type'] === 'tutor_zoom_meeting' );

		if ( $wp_query->is_page && $is_frontend_course_builder ) {
			wp_enqueue_script( 'tutor_zoom_timepicker_js', TUTOR_ZOOM()->url . 'assets/js/lib/jquery-ui-timepicker.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), TUTOR_PRO_VERSION, true );
			wp_enqueue_style( 'tutor_zoom_timepicker_css', TUTOR_ZOOM()->url . 'assets/css/jquery-ui-timepicker.css', false, TUTOR_PRO_VERSION );
			wp_enqueue_script( 'tutor_zoom_common_js', TUTOR_ZOOM()->url . 'assets/js/common.js', array( 'jquery', 'jquery-ui-datepicker' ), TUTOR_PRO_VERSION, true );
			wp_enqueue_style( 'tutor_zoom_common_css', TUTOR_ZOOM()->url . 'assets/css/common.css', false, TUTOR_PRO_VERSION );
		}

		if ( is_single_course() || $is_single_zoom_page ) {
			wp_enqueue_script( 'tutor_zoom_moment_js', TUTOR_ZOOM()->url . 'assets/js/lib/moment.min.js', array(), TUTOR_PRO_VERSION, true );
			wp_enqueue_script( 'tutor_zoom_moment_tz_js', TUTOR_ZOOM()->url . 'assets/js/lib/moment-timezone-with-data.min.js', array(), TUTOR_PRO_VERSION, true );
			wp_enqueue_script( 'tutor_zoom_countdown_js', TUTOR_ZOOM()->url . 'assets/js/lib/jquery.countdown.min.js', array( 'jquery' ), TUTOR_PRO_VERSION, true );
		}

		if ( is_single_course() || $is_single_zoom_page || $is_frontend_course_builder || ( isset( $wp_query->query_vars['tutor_dashboard_page'] ) && $wp_query->query_vars['tutor_dashboard_page'] == 'zoom' ) ) {
			wp_enqueue_script( 'tutor_zoom_frontend_js', TUTOR_ZOOM()->url . 'assets/js/frontend.js', array( 'jquery' ), TUTOR_PRO_VERSION, true );
			wp_enqueue_style( 'tutor_zoom_frontend_css', TUTOR_ZOOM()->url . 'assets/css/frontend.css', false, TUTOR_PRO_VERSION );
		}
	}

	public function register_menu() {
		add_submenu_page( 'tutor', __( 'Zoom', 'tutor-pro' ), __( 'Zoom', 'tutor-pro' ), 'manage_tutor_instructor', 'tutor_zoom', array( $this, 'tutor_zoom' ) );
	}

	public function tutor_course_contents_post_types( $post_types ) {
		$post_types[] = $this->zoom_meeting_post_type;

		return $post_types;
	}

	public function add_meetings_metabox( $echo = true ) {
		$current_course_id 	= ! is_admin() && isset( $_GET['course_ID'] ) ? $_GET['course_ID'] : get_the_ID();
		$post 				= get_post( $current_course_id );

		$user_id    = get_current_user_id();
		$settings   = json_decode( get_user_meta( $user_id, $this->api_key, true ), true );
		$api_key    = ( ! empty( $settings['api_key'] ) ) ? $settings['api_key'] : '';
		$api_secret = ( ! empty( $settings['api_secret'] ) ) ? $settings['api_secret'] : '';

		if ( is_a( $post, 'WP_POST' ) && $post->post_type == tutor()->course_post_type && ! empty( $api_key ) && ! empty( $api_secret ) ) {
			$course_id = $post->ID;
			$topic_id  = 0;
			if ( $echo ) {
				echo '<div class="tutor-course-builder-section">
				<div id="tutor-zoom-metabox-wrap">';
					require TUTOR_ZOOM()->path . 'views/metabox/meetings.php';
				echo '	</div>
					</div>';
			} else {
				ob_start();
				?>
				<div class="tutor-course-builder-section">
					<div id="tutor-zoom-metabox-wrap">
						<?php require TUTOR_ZOOM()->path . 'views/metabox/meetings.php' ;?>
					</div>
				</div>
				<?php
				return ob_get_clean();
			}
		}
	}

	public function add_meeting_option_in_topic( $topic_id, $course_id ) {
		$user_id    = get_current_user_id();
		$settings   = json_decode( get_user_meta( $user_id, $this->api_key, true ), true );
		$api_key    = ( ! empty( $settings['api_key'] ) ) ? $settings['api_key'] : '';
		$api_secret = ( ! empty( $settings['api_secret'] ) ) ? $settings['api_secret'] : '';
		$new_modal_ = 'tutor-zoom-new-lesson-' . $topic_id;

		if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
			?>
			<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" data-tutor-modal-target="<?php echo $new_modal_; ?>">
				<i class="tutor-icon-brand-zoom tutor-mr-8" area-hidden="true"></i>
				<?php _e( 'Zoom Live Lesson', 'tutor-pro' ); ?>
			</button>
			<?php

			$this->tutor_zoom_meeting_modal_content( 0, $topic_id, $course_id, 'course-builder', $new_modal_ );
		}
	}

	public function tutor_zoom_meeting_modal_content( $meeting_id, $topic_id, $course_id, $click_form, $modal_id = null ) {

		$meeting_host     = $this->get_users_options();
		$timezone_options = require dirname( __DIR__ ) . '/includes/timezone.php';
		$modal_id         = $modal_id ? $modal_id : 'tutor-zoom-meeting-modal-' . $meeting_id;

		$post         = null;
		$meeting_data = null;
		if ( 0 != $meeting_id ) {
			$post          = get_post( $meeting_id );
			$meeting_start = get_post_meta( $meeting_id, '_tutor_zm_start_datetime', true );
			$meeting_data  = get_post_meta( $meeting_id, $this->zoom_meeting_post_meta, true );
			$meeting_data  = json_decode( $meeting_data, true );
		}

		!is_array($meeting_data) ? $meeting_data=null : 0;

		$start_date     = '';
		$start_time     = '';
		$host_id        = (! empty( $meeting_data ) && ! empty($meeting_data['host_id'])) ? $meeting_data['host_id'] : '';
		$title          = (! empty( $meeting_data ) && ! empty($meeting_data['topic'])) ? wp_strip_all_tags( $meeting_data['topic'] ) : '';
		$summary        = (! empty( $post ) 		&& ! empty($post->post_content)) ? $post->post_content : '';
		$timezone       = (! empty( $meeting_data ) && ! empty($meeting_data['timezone'])) ? $meeting_data['timezone'] : '';
		$duration       = (! empty( $meeting_data ) && ! empty($meeting_data['duration'])) ? $meeting_data['duration'] : 60;
		$duration_unit  = ! empty( $post ) 			? get_post_meta( $meeting_id, '_tutor_zm_duration_unit', true ) : 'min';
		$password       = (! empty( $meeting_data ) && ! empty($meeting_data['password'])) ? $meeting_data['password'] : '';
		$auto_recording = (! empty( $meeting_data ) && ! empty($meeting_data['settings'])) ? $meeting_data['settings']['auto_recording'] : $this->get_settings( 'auto_recording' );

		// Fallback meeting title
		if(empty($title) && !empty($post)){
			$title = $post->post_title;
		}

		if ( ! empty( $meeting_data ) ) {
			$input_date = \DateTime::createFromFormat( 'Y-m-d H:i:s', $meeting_start );
			$start_date = $input_date->format( 'd/m/Y' );
			$start_time = $input_date->format( 'h:i A' );
			$duration   = ( $duration_unit == 'hr' ) ? $duration / 60 : $duration;
		}

		require TUTOR_ZOOM()->path . 'views/template/meeting-editor.php';
	}

	/**
	 * Save meeting
	 */
	public function tutor_zoom_save_meeting() {
		tutor_utils()->checking_nonce();

		$course_id = (int) sanitize_text_field( $_POST['course_id'] );

		if ( ! tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		// Check if API key updated.
		if ( ! $this->has_account_id() ) {
			wp_send_json_error(
				array(
					'post_id' => false,
					'message' => __( 'Invalid Api Credentials', 'tutor-pro' ),
				)
			);
		}

		// Prepare linking data.
		$meeting_id          = (int) sanitize_text_field( $_POST['meeting_id'] );
		$existing_meeting_id = $meeting_id;
		$topic_id            = (int) sanitize_text_field( $_POST['topic_id'] );
		$click_form          = sanitize_text_field( $_POST['click_form'] );

		// Prepare auth data
		$user_id    = get_current_user_id();
		$settings   = json_decode( get_user_meta( $user_id, $this->api_key, true ), true );
		$api_key    = ( ! empty( $settings['api_key'] ) ) ? $settings['api_key'] : '';
		$api_secret = ( ! empty( $settings['api_secret'] ) ) ? $settings['api_secret'] : '';


		// Respond error if api key or secret not set
		if ( empty( $api_key ) || empty( $api_secret ) ) {
			wp_send_json_error(
				array(
					'post_id' => false,
					'message' => __( 'Invalid Api Credentials', 'tutor-pro' ),
				)
			);
		}

		// Validate post value
		$post_keys = array(
			'meeting_host',
			'meeting_title',
			'meeting_timezone',
			'meeting_date',
			'meeting_time',
		);

		foreach ( $post_keys as $key ) {
			if ( empty( $_POST[ $key ] ) ) {
				wp_send_json_error( array( 'message' => __( 'All fields required!', 'tutor-pro' ) ) );
				return;
			}
		}

		// If set, then create the meeting
		$host_id    = sanitize_text_field( $_POST['meeting_host'] );
		$title      = sanitize_text_field( $_POST['meeting_title'] );
		$summary    = ! empty( $_POST['meeting_summary'] ) ? sanitize_text_field( $_POST['meeting_summary'] ) : '';
		$timezone   = sanitize_text_field( $_POST['meeting_timezone'] );
		$start_date = sanitize_text_field( $_POST['meeting_date'] );
		$start_time = sanitize_text_field( $_POST['meeting_time'] );

		$input_duration = ! empty( $_POST['meeting_duration'] ) ? intval( $_POST['meeting_duration'] ) : 60;
		$duration_unit  = ! empty( $_POST['meeting_duration_unit'] ) ? $_POST['meeting_duration_unit'] : 'min';
		$password       = ! empty( $_POST['meeting_password'] ) ? sanitize_text_field( $_POST['meeting_password'] ) : '';

		$join_before_host   = ( $this->get_settings( 'join_before_host' ) ) ? true : false;
		$host_video         = ( $this->get_settings( 'host_video' ) ) ? true : false;
		$participants_video = ( $this->get_settings( 'participants_video' ) ) ? true : false;
		$mute_participants  = ( $this->get_settings( 'mute_participants' ) ) ? true : false;
		$enforce_login      = ( $this->get_settings( 'enforce_login' ) ) ? true : false;
		$auto_recording     = ! empty( $_POST['auto_recording'] ) ? sanitize_text_field( $_POST['auto_recording'] ) : '';

		if ( false === date_create_from_format( 'd/m/Y', $start_date ) ) {
			$start_date = tutor_get_formated_date( 'd/m/Y', $start_date );
		}
		$input_date    = \DateTime::createFromFormat( 'd/m/Y h:i A', $start_date . ' ' . $start_time );
		if(!$input_date){
			wp_send_json_error( array('message' => __('Invalid Time Format', 'tutor-pro')));
			return;
		}
		
		$meeting_start = $input_date->format( 'Y-m-d\TH:i:s' );

		$duration = ( $duration_unit == 'hr' ) ? $input_duration * 60 : $input_duration;
		$data     = array(
			'topic'      => $title,
			'type'       => 2,
			'start_time' => $meeting_start,
			'timezone'   => $timezone,
			'duration'   => $duration,
			'password'   => $password,
			'settings'   => array(
				'join_before_host'  => $join_before_host,
				'host_video'        => $host_video,
				'participant_video' => $participants_video,
				'mute_upon_entry'   => $mute_participants,
				'auto_recording'    => $auto_recording,
				'enforce_login'     => $enforce_login,
			),
		);

		// save post
		$menu_order = tutor_utils()->get_next_course_content_order_id( $topic_id, $meeting_id );

		$post_content = array(
			'ID'           => $meeting_id ? $meeting_id : 0,
			'post_title'   => $title,
			'post_name'    => sanitize_title( $title ),
			'post_content' => $summary,
			'post_type'    => $this->zoom_meeting_post_type,
			'post_parent'  => $topic_id ? $topic_id : $course_id,
			'post_status'  => 'publish',
			'menu_order'   => $menu_order,
		);
		

		// save zoom meeting
		$post_id = wp_insert_post( $post_content );
		!$meeting_id ? $meeting_id=$post_id : 0;

		$meeting_data = get_post_meta( $post_id, $this->zoom_meeting_post_meta, true );
		$meeting_data = json_decode( $meeting_data, true );

		// Interact with zoom
		$zoom_endpoint = tutor_utils()->get_package_object( true, '\Zoom\Endpoint\Meetings', $api_key, $api_secret );
		if ( ! empty( $meeting_data ) && isset( $meeting_data['id'] ) ) {
			// Update existing meeting id if id provided
			$zoom_endpoint->update( $meeting_data['id'], $data );
			$saved_meeting = $zoom_endpoint->meeting( $meeting_data['id'] );
			do_action( 'tutor_zoom_after_update_meeting', $post_id );
		} else {
			// Or create new meeting
			$saved_meeting = $zoom_endpoint->create( $host_id, $data );
			update_post_meta( $post_id, '_tutor_zm_for_course', $course_id );
			update_post_meta( $post_id, '_tutor_zm_for_topic', $topic_id );

			do_action( 'tutor_zoom_after_save_meeting', $post_id );
		}

		// Update meeting meta data
		update_post_meta( $post_id, '_tutor_zm_start_date', $input_date->format( 'Y-m-d' ) );
		update_post_meta( $post_id, '_tutor_zm_start_datetime', $input_date->format( 'Y-m-d H:i:s' ) );
		update_post_meta( $post_id, '_tutor_zm_duration', $input_duration );
		update_post_meta( $post_id, '_tutor_zm_duration_unit', $duration_unit );
		update_post_meta( $post_id, $this->zoom_meeting_post_meta, json_encode( $saved_meeting ) );

		// Prepare response HTML to be shown after meeting create/update
		$course_contents  = '';
		$selector         = '';
		$replace_selector = '';
		if ( $click_form == 'course-builder' ) {
			// If from course builder under topic
			ob_start();
			$single_meetings = $this->get_meetings( null, null, null, array(), true, $meeting_id );
			foreach($single_meetings as $single_meeting){
				$this->course_builder_row( $single_meeting, (object) array( 'ID' => $topic_id ), $course_id );
			}
			$course_contents  = ob_get_clean();

			$selector         = 'course-builder';
			$replace_selector = $existing_meeting_id ? '#tutor-zoom-meeting-' . $existing_meeting_id : '';

		} elseif ( $click_form == 'metabox' ) {
			// If from standalone meeting outside topic
			ob_start();
			require TUTOR_ZOOM()->path . 'views/metabox/meetings.php';
			$course_contents = ob_get_clean();
			$selector        = '#tutor-zoom-metabox-wrap';
		}

		// Provide the form again, otherwise new zoom lesson modal shows the previous input contents
		$modal_html = '';
		if(!$existing_meeting_id && $topic_id) {		
			$new_modal_ = 'tutor-zoom-new-lesson-' . $topic_id;

			ob_start();
			$this->tutor_zoom_meeting_modal_content( 0, $topic_id, $course_id, 'course-builder', $new_modal_ );
			$modal_html = ob_get_clean();
		}

		// Finally send the response
		wp_send_json_success(
			array(
				'message'           => __( 'Meeting Successfully Saved', 'tutor-pro' ),
				'course_contents'   => $course_contents,
				'selector'          => $selector,
				'replace_selector'  => $replace_selector,
				'editor_modal_html'	=> $modal_html
			)
		);
	}

	/**
	 * Delete meeting
	 */
	public function tutor_zoom_delete_meeting() {
		tutor_utils()->checking_nonce();

		$post_id = (int) sanitize_text_field( $_POST['meeting_id'] );
		$course_id = wp_get_post_parent_id( $post_id );
		if ( tutor()->topics_post_type === get_post_type( $course_id ) ) {
			$course_id = wp_get_post_parent_id( $course_id );
		}

		if ( ! tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		// Check if API key updated.
		if ( ! $this->has_account_id() ) {
			wp_send_json_error(
				array(
					'post_id' => false,
					'message' => __( 'Invalid Api Credentials', 'tutor-pro' ),
				)
			);
		}

		$user_id    = get_current_user_id();
		$settings   = json_decode( get_user_meta( $user_id, $this->api_key, true ), true );
		$api_key    = ( ! empty( $settings['api_key'] ) ) ? $settings['api_key'] : '';
		$api_secret = ( ! empty( $settings['api_secret'] ) ) ? $settings['api_secret'] : '';

		if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
			$meeting_data = get_post_meta( $post_id, $this->zoom_meeting_post_meta, true );
			$meeting_data = json_decode( $meeting_data, true );

			$zoom_endpoint = tutor_utils()->get_package_object( true, '\Zoom\Endpoint\Meetings', $api_key, $api_secret );

			if(isset($meeting_data['id'])){
				$meeting_info = $zoom_endpoint->meeting($meeting_data['id']);

				if(is_array($meeting_info) && isset($meeting_info['id'])){
					$zoom_endpoint->remove( $meeting_data['id'] );
				}
			}

			wp_delete_post( $post_id, true );

			do_action( 'tutor_zoom_after_delete_meeting', $post_id );

			wp_send_json_success(
				array(
					'post_id' => $post_id,
					'message' => __( 'Meeting Successfully Deleted', 'tutor-pro' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'post_id' => false,
					'message' => __( 'Invalid Api Credentials', 'tutor-pro' ),
				)
			);
		}
	}

	/**
	 * Get zoom meetings based on time context like expired, active, and currently running
	 */
	public function get_meetings( $limit = 10, $page = 1, $context = '', $args = array(), $get_from_topic = true, $meeting_id = null ) {
		global $wpdb;

		$filter_clause = '';

		$limit_offset  = '';
		$limit_offset .= ' ORDER BY ' . ( ! empty( $args['order_by'] ) ? $args['order_by'] : '_meta_start.meta_value' );
		$limit_offset .= ' ' . ( ! empty( $args['order'] ) ? $args['order'] : 'DESC' );
		$limit_offset .= ! ( $limit === null ) ? ' LIMIT ' . $limit . ' OFFSET ' . ( ( $page - 1 ) * $limit ) : '';

		// Get meetings by id
		if ( $meeting_id ) {
			$meeting_id     = ! is_array( $meeting_id ) ? array( $meeting_id ) : 0;
			$meeting_ids    = implode( ',', $meeting_id );
			$filter_clause  .= ' AND _meeting.ID IN(' . $meeting_ids . ')';
		}

		// Course ID filter
		if ( ! empty( $args['course_id'] ) ) {
			$topic_ids = array();

			if ( $get_from_topic ) {
				$topic_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND post_parent=%d",
						'topics',
						$args['course_id']
					)
				);
			}

			$parent_ids = array_merge( $topic_ids, array( $args['course_id'] ) );
			$parent_ids = implode( ',', $parent_ids );

			$filter_clause .= ' AND _meeting.post_parent IN (' . $parent_ids . ')';
		}

		// Author filter
		if ( ! empty( $args['author'] ) ) {
			$filter_clause .= ' AND _meeting.post_author=' . $args['author'];
		}

		// Search filter
		if ( ! empty( $args['search'] ) ) {
			$filter_clause .= ' AND _meeting.post_title LIKE "%' . sanitize_text_field( $args['search'] ) . '%"';
		}

		// Date filter
		if ( ! empty( $args['date'] ) ) {
			$filter_clause .= ' AND DATE(_meta_start.meta_value)=\'' . $args['date'] . '\'';
		}

		$context_clause = '';
		if ( ! ( $context === null ) ) {
			$math_operator = $context == 'active' ? '>' : '<';

			$context_clause = ' AND ((
				_meta_unit.meta_value=\'min\'
				AND (_meta_start.meta_value + INTERVAL _meta_duration.meta_value MINUTE)' . $math_operator . 'NOW()
			) OR (
				_meta_unit.meta_value=\'hr\'
				AND (_meta_start.meta_value + INTERVAL _meta_duration.meta_value HOUR)' . $math_operator . 'NOW()
			))';
		}

		// Get the meetings from Database
		$meetings = $wpdb->get_results(
			"SELECT DISTINCT _meeting.*,
				_meta_start.meta_value AS meeting_starts_at,
				(_meta_start.meta_value + INTERVAL _meta_duration.meta_value MINUTE)<NOW() AS is_expired,
				(NOW()>_meta_start.meta_value AND NOW()<_meta_start.meta_value + INTERVAL _meta_duration.meta_value MINUTE) AS is_running,
				_meta_start.meta_value>NOW() AS is_upcoming
			FROM {$wpdb->posts} _meeting
				INNER JOIN {$wpdb->postmeta} _meta_start ON _meeting.ID=_meta_start.post_id
				INNER JOIN {$wpdb->postmeta} _meta_duration ON _meeting.ID=_meta_duration.post_id
				INNER JOIN {$wpdb->postmeta} _meta_unit ON _meeting.ID=_meta_unit.post_id
			WHERE _meeting.post_type='tutor_zoom_meeting'
				AND _meta_start.meta_key='_tutor_zm_start_datetime'
				AND _meta_unit.meta_key='_tutor_zm_duration_unit'
				AND _meta_duration.meta_key='_tutor_zm_duration'
				{$filter_clause}
				{$context_clause}
				{$limit_offset}"
		);

		return $meetings;
	}


	private function get_option_data( $key, $data ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return false;
		}
		if ( ! $key ) {
			return $data;
		}
		if ( array_key_exists( $key, $data ) ) {
			return apply_filters( $key, $data[ $key ] );
		}
	}

	private function get_transient_key() {
		$user_id       = get_current_user_id();
		$transient_key = 'tutor_zoom_users_' . $user_id;
		return $transient_key;
	}

	public function get_api( $key = null ) {
		$user_id  = get_current_user_id();
		$api_data = json_decode( get_user_meta( $user_id, $this->api_key, true ), true );
		return $this->get_option_data( $key, $api_data );
	}

	private function get_settings( $key = null ) {
		$user_id       = get_current_user_id();
		$settings_data = json_decode( get_user_meta( $user_id, $this->settings_key, true ), true );
		return $this->get_option_data( $key, $settings_data );
	}

	public function tutor_zoom() {
		require TUTOR_ZOOM()->path . 'views/pages/main.php';
	}

	public function tutor_save_zoom_api() {
		tutor_utils()->checking_nonce();

		if ( ! User::has_any_role( array( User::ADMIN, User::INSTRUCTOR ) ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}
		$api_data = (array) isset( $_POST[ $this->api_key ] ) ? $_POST[ $this->api_key ] : array();
		$api_data = apply_filters( 'tutor_zoom_api_input', $api_data );

		if ( empty( $api_data['api_key'] ) || empty( $api_data['api_secret'] ) || empty( $api_data['account_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => 'Please fill up all the fields',
					'tutor-pro',
				)
			);
		}

		do_action( 'tutor_save_zoom_api_before' );
		$user_id = get_current_user_id();
		update_user_meta( $user_id, $this->api_key, json_encode( $api_data ) );
		do_action( 'tutor_save_zoom_api_after' );

		// Validate before saving.
		if ( ! $this->tutor_check_api_connection( $api_data ) ) {
			delete_user_meta( $user_id, $this->api_key );
			wp_send_json_error( array( 'message' => __( 'Please recheck your API Key and Secret Key', 'tutor-pro' ) ) );
			return;
		}

		wp_send_json_success( array( 'message' => __( 'You can now add live classes to any course!', 'tutor-pro' ) ) );
	}

	public function tutor_save_zoom_settings() {
		tutor_utils()->checking_nonce();

		if ( ! User::has_any_role( array( User::ADMIN, User::INSTRUCTOR ) ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		do_action( 'tutor_save_zoom_settings_before' );
		$settings = (array) isset( $_POST[ $this->settings_key ] ) ? $_POST[ $this->settings_key ] : array();
		$settings = apply_filters( 'tutor_zoom_settings_input', $settings );
		$user_id  = get_current_user_id();
		update_user_meta( $user_id, $this->settings_key, json_encode( $settings ) );
		do_action( 'tutor_save_zoom_settings_after' );
		wp_send_json_success( array( 'message' => __( 'Settings Updated', 'tutor-pro' ) ) );
	}

	private function tutor_check_api_connection( $settings ) {
		$transient_key = $this->get_transient_key();
		delete_transient( $transient_key ); // delete temporary cache
		$users = $this->tutor_zoom_get_users( $settings );
		return ! empty( $users );
	}

	/**
	 * Get Zoom Users from Zoom API
	 *
	 * @return array
	 */
	public function tutor_zoom_get_users( $settings = null ) {
		$user_id       = get_current_user_id();
		$transient_key = $this->get_transient_key();
		$users         = get_transient( $transient_key );
		$settings      = $settings ? $settings : json_decode( get_user_meta( $user_id, $this->api_key, true ), true );

		if ( empty( $users ) ) {
			$api_key    = ( ! empty( $settings['api_key'] ) ) ? $settings['api_key'] : '';
			$api_secret = ( ! empty( $settings['api_secret'] ) ) ? $settings['api_secret'] : '';
			if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
				$users      = array();
				$users_data = tutor_utils()->get_package_object( true, '\Zoom\Endpoint\Users', $api_key, $api_secret );
				$users_list = $users_data->userlist();
				if ( ! empty( $users_list ) && ! empty( $users_list['users'] ) ) {
					$users = $users_list['users'];
					set_transient( $transient_key, $users, 36000 );
				}
			} else {
				$users = array();
			}
		}
		return $users;
	}

	/**
	 * Get Zoom Users
	 *
	 * @return array
	 */
	public function get_users_options() {
		$users = $this->tutor_zoom_get_users();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$first_name       = $user['first_name'];
				$last_name        = $user['last_name'] ?? '';
				$email            = $user['email'];
				$id               = $user['id'];
				$user_list[ $id ] = $first_name . ' ' . $last_name . ' (' . $email . ')';
			}
		} else {
			return array();
		}
		return $user_list;
	}

	/**
	 * Load zoom meeting template
	 *
	 * @return array
	 */
	public function tutor_zoom_course_meeting() {
		ob_start();
		tutor_load_template( 'single.course.zoom-meetings', null, true );
		$output = apply_filters( 'tutor_course/single/zoom_meetings', ob_get_clean() );
		echo $output;
	}

	/**
	 * Load zoom meeting template
	 *
	 * @return array
	 */
	public function load_meeting_template( $template ) {
		global $wp_query, $post;
		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] === $this->zoom_meeting_post_type ) {
			if ( is_user_logged_in() ) {
				$content_type       = ( get_post_type( $post->post_parent ) === tutor()->course_post_type ) ? 'topic' : 'lesson';
				$has_content_access = tutor_utils()->has_enrolled_content_access( $content_type, $post->ID );
				if ( $has_content_access ) {
					$template = tutor_get_template( 'single-zoom-meeting', true );
				} else {
					$template = tutor_get_template( 'single.lesson.required-enroll' ); // You need to enroll first
				}
			} else {
				$template = tutor_get_template( 'login' );
			}
			return $template;
		}
		return $template;
	}

	/**
	 * Add zoom menu on the tutor dashboard (frontend)
	 *
	 * @return array
	 *
	 * @since 1.9.4
	 */
	public function add_zoom_menu( $nav_items ) {
		do_action( 'before_zoom_menu_add_on_frontend' );
		$new_items 	= array(
			'zoom' =>  array( 'title' => __( 'Zoom', 'tutor-pro' ), 'auth_cap' => tutor()->instructor_role, 'icon' => 'tutor-icon-brand-zoom' )
		);
		$nav_items = array_merge( $nav_items, $new_items );

		return apply_filters( 'after_zoom_menu_add_on_frontend', $nav_items );
	}

	/**
	 * If request is for zoom then load template from addons
	 *
	 * @param String
	 *
	 * @return String
	 *
	 * @since 1.9.4
	 */
	public function load_zoom_template( $location ) {
		global $wp_query;
		$query_vars = $wp_query->query_vars;

		if ( isset( $query_vars['tutor_dashboard_page'] ) && $query_vars['tutor_dashboard_page'] == 'zoom' ) {
			$location = TUTOR_ZOOM()->path . '/templates/main.php';
		}
		return $location;
	}

	public function tutor_script_text_domain() {
		wp_set_script_translations( 'tutor_zoom_admin_js', 'tutor-pro', tutor_pro()->path . 'languages/' );
		wp_set_script_translations( 'tutor_zoom_frontend_js', 'tutor-pro', tutor_pro()->path . 'languages/' );
	}

	/**
	 * Check is zoom lesson mark as done or not
	 *
	 * @param string $value default value.
	 * @param string $lesson_id zoom lesson id.
	 * @param string $user_id id of student.
	 *
	 * @return bool | true on success | false on failure
	 */
	public static function is_zoom_lesson_done( $value, $lesson_id, $user_id ) : bool {
		$lesson_id = sanitize_text_field( tutor_utils()->get_post_id( $lesson_id ) );
		$user_id   = sanitize_text_field( tutor_utils()->get_user_id( $user_id ) );

		if ( $lesson_id && $user_id ) {
			$meta_key = '_tutor_completed_lesson_id_' . $lesson_id;
			$count    = get_user_meta( $user_id, $meta_key, true );
			return $count ? true : false;
		}
		return false;
	}

	/**
	 * Show icon on the right side of title on course spot light section
	 *
	 * @param int $post_id | zoom post id.
	 *
	 * @return void
	 */
	public function right_icon_area( int $post_id, $lock_icon=false ) : void {
		$post_id = sanitize_text_field( $post_id );
		$user_id = get_current_user_id();
		$is_completed = self::is_zoom_lesson_done( '', $post_id, $user_id );
		if ( $is_completed ) {
			echo "<input type='checkbox' class='tutor-form-check-input tutor-form-check-circle' disabled='disabled' readonly='readonly' checked='checked'/>";
		} else {
			if($lock_icon) {
				echo '<i class="tutor-icon-lock-line tutor-fs-7 tutor-color-muted tutor-mr-4" area-hidden="true"></i>';
			} else {
				echo "<input type='checkbox' class='tutor-form-check-input tutor-form-check-circle' disabled='disabled' readonly='readonly'/>";
			}
		}
	}

	/**
	 * Add meta box on the front end course build
	 *
	 * @since v2.0.0
	 */
	public function add_meta_box_frontend() {
		course_builder_section_wrap( $this->add_meetings_metabox( $echo = false ), __( 'Zoom Meeting', 'tutor-pro' ) );
	}

	/**
	 * Show admin notice if Zoom API not updated
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function show_admin_notice() {
		$account_id = $this->get_api( 'account_id' );
		if ( ! $account_id ) {
			$wrapper_class = 'notice notice-error tutor-py-8';
			$this->alert_msg( $wrapper_class );
		}
	}

	/**
	 * Zoom API update alert message
	 *
	 * @since 2.2.0
	 *
	 * @param string $wrapper_class notice wrapper css class.
	 * @param string $link_btn_class link btn class.
	 * @param bool   $waring_border set true to show warning border.
	 *
	 * @return void
	 */
	public function alert_msg( string $wrapper_class, string $link_btn_class = '', $waring_border = false ) {
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>" style="<?php echo esc_attr( $waring_border ? "border:1px solid var(--tutor-color-warning);" : '' ); ?>">
			<div class="tutor-fs-5 tutor-fw-medium tutor-mb-0 tutor-color-black tutor-mb-12">
				<?php esc_html_e( 'API Update Required', 'tutor-pro' ); ?>
			</div>
			<div class="tutor-fs-7">
				<div>
					<?php echo sprintf( __( "The <a href='https://developers.zoom.us/docs/internal-apps/jwt-faq/' target='_blank'>Zoom JWT app type will be deprecated!</a> You must migrate to the new server-to-server OAuth app and update your API setup by September 1, 2023.", 'tutor-pro' ) ); ?>
				</div>
				<a class="<?php echo esc_attr( $link_btn_class ); ?>"  href="https://docs.themeum.com/tutor-lms/addons/zoom-integration" target="_blank">
					<?php esc_html_e( 'Check how to migrate from JWT to Server-to-Server OAuth app type', 'tutor-pro' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Check whether user updated Zoom API or not
	 *
	 * @since 2.2.0
	 *
	 * @return boolean
	 */
	public function has_account_id() {
		$account_id = $this->get_api( 'account_id' );
		return $account_id ? true : false;
	}

	/**
	 * Show notice on the frontend if Zoom API not updated
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function show_notice_frontend() {
		// Return if current user is not administrator or instructor.
		if ( ! current_user_can( 'administrator' ) && ! current_user_can( tutor()->instructor_role ) ) {
			return;
		}

		// Return if Zoom API already updated.
		if ( $this->has_account_id() ) {
			return;
		}

		// Return if user already landed on the set api page.
		$dashboard_page     = get_query_var( 'tutor_dashboard_page', '' );
		$dashboard_sub_page = get_query_var( 'tutor_dashboard_sub_page', '' );
		if ( 'zoom' === $dashboard_page && 'set-api' === $dashboard_sub_page ) {
			return;
		}

		tutor_snackbar(
			__( 'The Zoom JWT app type will be deprecated!', 'tutor-pro' ),
			array(
				array(
					'title' => __( 'Migrate', 'tutor-pro' ),
					'class' => 'tutor-btn tutor-btn-primary tutor-btn-sm',
					'href'  => tutor_utils()->tutor_dashboard_url( 'zoom/set-api' ),
				),
				array(
					'title'  => __( 'Documentation', 'tutor-pro' ),
					'class'  => 'tutor-btn tutor-btn-outline-primary tutor-btn-sm',
					'href'   => 'https://docs.themeum.com/tutor-lms/tutorials/how-to-migrate-from-zoom-jwt-to-server-to-server-oauth/',
					'target' => '_blank',
				),
			),
			'tutor-icon-warning'
		);
	}
}
