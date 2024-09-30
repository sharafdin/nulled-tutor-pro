<?php
/**
 * Tutor Assignments
 *
 * @package TutorPro
 * @subpackage Addons\TutorAssignments
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR_ASSIGNMENTS;

use TUTOR\Course;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use TUTOR\Tutor_Base;

/**
 * Class Assignments
 *
 * @since 1.0.0
 */
class Assignments extends Tutor_Base {

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 * @since 2.6.2 added $allow_hooks param.
	 *
	 * @param bool $allow_hooks default true.
	 */
	public function __construct( $allow_hooks = true ) {
		parent::__construct();

		if ( $allow_hooks ) {
			add_action( 'tutor_admin_register', array( $this, 'register_menu' ) );
			add_action( 'tutor_course_builder_after_btn_group', array( $this, 'add_assignments_btn' ), 11, 1 );
			add_action( 'wp_ajax_tutor_load_assignments_builder_modal', array( $this, 'tutor_load_assignments_builder_modal' ) );
			add_action( 'wp_ajax_tutor_modal_create_or_update_assignment', array( $this, 'tutor_modal_create_or_update_assignment' ) );
			add_filter( 'tutor_course_contents_post_types', array( $this, 'tutor_course_contents_post_types' ) );
			add_filter( 'post_type_link', array( $this, 'change_assignment_single_url' ), 1, 2 );
			add_action( 'wp_ajax_tutor_start_assignment', array( $this, 'tutor_start_assignment' ) );// Handle assignment submit form.
			add_action( 'tutor_action_tutor_assignment_submit', array( $this, 'tutor_assignment_submit' ) );
			add_action( 'tutor_action_tutor_evaluate_assignment_submission', array( $this, 'tutor_evaluate_assignment_submission' ) );
			add_filter( 'tutor_dashboard/instructor_nav_items', array( $this, 'frontend_dashboard_nav_items' ) );
			add_action( 'tutor/assignment/right_icon_area', array( $this, 'show_assignment_submitted_icon' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'tutor_script_text_domain' ), 100 );
			add_action( 'wp_ajax_delete_tutor_course_assignment_submission', array( $this, 'delete_tutor_course_assignment_submission' ) );
			add_action( 'delete_tutor_course_progress', array( $this, 'delete_tutor_course_progress' ), 10, 2 );
			add_action( 'wp_ajax_tutor_remove_assignment_attachment', array( __CLASS__, 'remove_assignment_attachment' ) );
			add_action( 'tutor_assignment/evaluate/after', array( $this, 'do_auto_course_complete' ), 10, 3 );
			add_filter( 'tutor/options/extend/attr', array( $this, 'extend_settings_option' ) );
		}
	}

	/**
	 * Extend settings options.
	 *
	 * @since 2.6.0
	 *
	 * @param array $attr settings options.
	 *
	 * @return array
	 */
	public function extend_settings_option( $attr ) {
		$assignment_url = site_url() . '/' . $this->course_base_permalink . '/sample-course/<code>' . $this->assignment_base_permalink . '</code>/sample-assignment/';

		$attr['advanced']['blocks'][1]['fields'][] = array(
			'key'     => 'assignment_permalink_base',
			'type'    => 'text',
			'label'   => __( 'Assignment Permalink', 'tutor-pro' ),
			'default' => 'assignments',
			'desc'    => $assignment_url,
		);

		return $attr;
	}

	/**
	 * Load scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_admin_scripts() {
		if ( 'tutor-assignments' === Input::get( 'page' ) ) {
			wp_enqueue_script( 'assignment-list-js', tutor_pro()->url . 'addons/tutor-assignments/assets/js/assignment.js', array( 'jquery', 'wp-i18n' ), TUTOR_PRO_VERSION, true );

			// @since 1.8.0
			wp_enqueue_style( 'assignments-css', TUTOR_ASSIGNMENTS()->url . 'assets/css/assignments.css', array(), TUTOR_PRO_VERSION );
		}
	}

	/**
	 * Load text domain handled script after all enqueue_scripts
	 * registered functions
	 *
	 * @since 1.9.0
	 */
	public function tutor_script_text_domain() {
		wp_set_script_translations( 'assignment-list-js', 'tutor-pro', tutor_pro()->path . 'languages/' );
	}

	/**
	 * Delete single assignment ajax callback
	 */
	public function delete_tutor_course_assignment_submission() {

		// Check the request authenticity.
		tutor_utils()->checking_nonce();

		// All good, let's proceed.
		$submitted_id = Input::post( 'assignment_id', 0, Input::TYPE_INT );

		if ( $submitted_id ) {
			if ( ! tutor_utils()->can_user_manage( 'assignment_submission', $submitted_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor-pro' ) ) );
				return;
			}

			$this->delete_assignment_submission( $submitted_id );
			wp_send_json_success( array( 'message' => __( 'Assignment deleted', 'tutor-pro' ) ) );
		}
	}

	/**
	 * Register menu
	 *
	 * @return void
	 */
	public function register_menu() {
		add_submenu_page(
			'tutor',
			__( 'Submitted Assignments', 'tutor-pro' ),
			__( 'Assignments', 'tutor-pro' ),
			'manage_tutor_instructor',
			'tutor-assignments',
			array(
				$this,
				'tutor_assignments_page',
			)
		);
	}

	/**
	 * Frontend dashboard nav item for assignments.
	 *
	 * @param array $nav_items nav items.
	 *
	 * @return array
	 */
	public function frontend_dashboard_nav_items( $nav_items ) {
		$nav_items['assignments'] = array(
			'title'    => __( 'Assignments', 'tutor-pro' ),
			'icon'     => 'tutor-icon-assignment',
			'auth_cap' => tutor()->instructor_role,
		);
		return $nav_items;
	}

	/**
	 * Assignment page.
	 *
	 * @return void
	 */
	public function tutor_assignments_page() {
		if ( tutor_utils()->array_get( 'view_assignment', $_GET ) ) {
			$assignment_submitted_id = (int) sanitize_text_field( tutor_utils()->array_get( 'view_assignment', $_GET ) );
			include TUTOR_ASSIGNMENTS()->path . '/views/pages/submitted_assignment.php';
		} else {
			include TUTOR_ASSIGNMENTS()->path . '/views/pages/assignments.php';
		}
	}

	/**
	 * Add assignment button
	 *
	 * @param int $topic_id topic id.
	 *
	 * @return void
	 */
	public function add_assignments_btn( $topic_id ) {
		?>
		<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-create-assignments-btn" data-topic-id="<?php echo esc_attr( $topic_id ); ?>">
			<i class="tutor-icon-plus-square tutor-mr-12"></i>
			<?php esc_html_e( 'Assignments', 'tutor-pro' ); ?>
		</button>
		<?php
	}

	/**
	 * Assignment builder modal.
	 *
	 * @return void
	 */
	public function tutor_load_assignments_builder_modal() {
		tutor_utils()->checking_nonce();

		$assignment_id = Input::post( 'assignment_id', 0, Input::TYPE_INT );
		$topic_id      = Input::post( 'topic_id', 0, Input::TYPE_INT );

		/**
		 * If Assignment Not Exists, provide dummy
		 */
		$post_arr = array(
			'ID'           => 0,
			'post_type'    => 'tutor_assignments',
			'post_title'   => __( 'Assignments', 'tutor-pro' ),
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $topic_id,
		);

		$post = $assignment_id ? get_post( $assignment_id ) : (object) $post_arr;

		ob_start();
		include TUTOR_ASSIGNMENTS()->path . 'views/modal/assignments.php';
		$output = ob_get_clean();

		wp_send_json_success(
			array(
				'output' => $output,
			)
		);
	}

	/**
	 * Update assignment
	 */
	public function tutor_modal_create_or_update_assignment() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$user_id           = get_current_user_id();
		$assignment_id     = Input::post( 'assignment_id', 0, Input::TYPE_INT );
		$assignment_action = 0 === $assignment_id ? 'create' : 'update';
		$topic_id          = Input::post( 'current_topic_id', 0, Input::TYPE_INT );
		$course_id         = tutor_utils()->get_course_id_by( 'topic', $topic_id );

		if ( ! tutor_utils()->can_user_edit_course( $user_id, $course_id ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$title             = Input::post( 'assignment_title', '' );
		$lesson_content    = Input::post( 'assignment_content', '', Input::TYPE_KSES_POST );
		$assignment_option = tutor_utils()->avalue_dot( 'assignment_option', $_POST ); //phpcs:ignore
		$attachments       = tutor_utils()->avalue_dot( 'tutor_assignment_attachments', $_POST ); //phpcs:ignore

		if ( isset( $assignment_option['time_duration'], $assignment_option['time_duration']['value'] ) ) {
			$assignment_option['time_duration']['value'] = (int) $assignment_option['time_duration']['value'];
		}

		$assignment_data = array(
			'post_type'    => 'tutor_assignments',
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_parent'  => $topic_id,
			'post_title'   => $title,
			'post_name'    => sanitize_title( $title ),
			'post_content' => $lesson_content,
			'menu_order'   => tutor_utils()->get_next_course_content_order_id( $topic_id, $assignment_id ),
		);

		// @since v2.0.0
		$total_mark = isset( $assignment_option['total_mark'] ) ? sanitize_text_field( $assignment_option['total_mark'] ) : 0;
		$pass_mark  = isset( $assignment_option['pass_mark'] ) ? sanitize_text_field( $assignment_option['pass_mark'] ) : 0;

		// Create or update assignment post.
		if ( 'create' === $assignment_action ) {
			$assignment_id = wp_insert_post( $assignment_data );

			if ( ! $assignment_id ) {
				wp_send_json_error(
					array(
						'message' => __( 'Couldn\'t create assignment' ),
					)
				);
			}

			update_post_meta( $assignment_id, '_tutor_course_id_for_assignments', $course_id );
		} else {
			if ( ! tutor_utils()->can_user_manage( 'assignment', $assignment_id ) ) {
				wp_send_json_error( tutor_utils()->error_message() );
			}
			$assignment_data['ID'] = $assignment_id;
			wp_update_post( $assignment_data );
		}

		// Update assignment options as meta.
		update_post_meta( $assignment_id, 'assignment_option', $assignment_option );

		// Update assignment total_mark & pass_mark meta @since v2.0.0.
		update_post_meta( $assignment_id, '_tutor_assignment_total_mark', $total_mark );
		update_post_meta( $assignment_id, '_tutor_assignment_pass_mark', $pass_mark );

		// Update assignment attachments.
		if ( tutor_utils()->count( $attachments ) ) {
			update_post_meta( $assignment_id, '_tutor_assignment_attachments', $attachments );
		} else {
			delete_post_meta( $assignment_id, '_tutor_assignment_attachments' );
		}

		// Run the create/update hook.
		$hook_name = 'create' === $assignment_action ? 'tutor_assignment_created' : 'tutor_assignment_updated';
		do_action( $hook_name, $assignment_id );

		// Load course contents metabox.
		ob_start();
		include tutor()->path . 'views/metabox/course-contents.php';
		$course_contents = ob_get_clean();

		// Send response.
		wp_send_json_success(
			array(
				'course_contents' => $course_contents,
			)
		);
	}

	/**
	 * Course contents post types
	 *
	 * @param array $post_types post types.
	 *
	 * @return array
	 */
	public function tutor_course_contents_post_types( $post_types ) {
		$post_types[] = 'tutor_assignments';

		return $post_types;
	}

	/**
	 * Change Assignment single URL
	 *
	 * @since 1.3.3
	 *
	 * @param string  $post_link post link.
	 * @param integer $id id.
	 *
	 * @return string
	 */
	public function change_assignment_single_url( $post_link, $id = 0 ) {
		$post = get_post( $id );

		if ( is_object( $post ) && 'tutor_assignments' === $post->post_type ) {
			$course_id = tutor_utils()->get_course_id_by( 'assignment', $post->ID );
			$course    = get_post( $course_id );

			if ( is_object( $course ) ) {
				return home_url( "/{$this->course_base_permalink}/{$course->post_name}/{$this->assignment_base_permalink}/" . $post->post_name . '/' );
			} else {
				return home_url( "/{$this->course_base_permalink}/sample-course/{$this->assignment_base_permalink}/" . $post->post_name . '/' );
			}
		}

		return $post_link;
	}

	/**
	 * Start assignment.
	 *
	 * @return void
	 */
	public function tutor_start_assignment() {
		tutor_utils()->checking_nonce();
		global $wpdb;

		$assignment_id = Input::post( 'assignment_id', 0, Input::TYPE_INT );
		$course_id     = tutor_utils()->get_course_id_by( 'assignment', $assignment_id );
		$user_id       = get_current_user_id();
		$user          = get_userdata( $user_id );
		$gmdate        = gmdate( 'Y-m-d H:i:s' );
		$site_date 	   = wp_date( 'Y-m-d H:i:s' );

		$is_enrolled = tutor_utils()->is_enrolled( $course_id, $user_id );
		if ( ! $is_enrolled ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$is_running_submit = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(comment_ID) FROM {$wpdb->comments}
			WHERE comment_type = 'tutor_assignment'
			AND user_id = %d
			AND comment_post_ID = %d ",
				$user_id,
				$assignment_id
			)
		);

		if ( $is_running_submit ) {
			wp_send_json_error( __( 'An assignment is submitting currently, please submit previous one first', 'tutor-pro' ) );
		}

		do_action( 'tutor_before_assignment_submit_start' );
		$data = apply_filters(
			'tutor_assignment_start_submitting_data',
			array(
				'comment_post_ID'  => $assignment_id,
				'comment_author'   => $user->user_login,
				'comment_date'     => $site_date, // Submit Finished.
				'comment_date_gmt' => $gmdate, // Submit Started.
				'comment_approved' => 'submitting', // submitting, submitted.
				'comment_agent'    => 'TutorLMSPlugin',
				'comment_type'     => 'tutor_assignment',
				'comment_parent'   => $course_id,
				'user_id'          => $user_id,
			)
		);

		$wpdb->insert( $wpdb->comments, $data );
		$comment_id = (int) $wpdb->insert_id;
		do_action( 'tutor_after_assignment_submit_start', $comment_id );

		wp_send_json_success( __( 'Answer has been added successfully', 'tutor-pro' ) );
	}

	/**
	 * Submit assignment.
	 *
	 * @return void
	 */
	public function tutor_assignment_submit() {
		tutor_utils()->checking_nonce();

		$store_data                       = new \stdClass();
		$store_data->update_id            = Input::get( 'update-assignment', 0, Input::TYPE_INT );
		$store_data->assignment_id        = Input::post( 'assignment_id', 0, Input::TYPE_INT );
		$store_data->assignment_answer    = Input::post( 'tutor-global-text-editor', '', Input::TYPE_KSES_POST );
		$store_data->allowed_upload_files = (int) tutor_utils()->get_assignment_option( $store_data->assignment_id, 'upload_files_limit' );
		$store_data->assignment_submit_id = tutor_utils()->is_assignment_submitting( $store_data->assignment_id );
		$store_data->course_id            = tutor_utils()->get_course_id_by( 'assignment', $store_data->assignment_id );
		$store_data->student_id           = get_current_user_id();

		$assignment_id = $this->update_assignment_submit( $store_data );

		wp_safe_redirect( get_permalink( $assignment_id ) );
		exit;
	}

	/**
	 * Store the data to submit the assignment
	 *
	 * @since 2.6.2
	 *
	 * @param object $store_data date object.
	 *
	 * @return int
	 */
	public function insert_assignment_submit( $store_data ) {
		global $wpdb;

		$assignment_id        = $store_data->assignment_id;
		$assignment_answer    = $store_data->assignment_answer;
		$allowed_upload_files = $store_data->allowed_upload_files;
		$assignment_submit_id = $store_data->assignment_submit_id;
		$course_id            = $store_data->course_id;
		$student_id           = $store_data->student_id;

		if ( in_array( $assignment_answer, array( '', '<p>&nbsp;</p>', '<p><br data-mce-bogus="1"></p>' ), true ) ) {
			tutor_utils()->redirect_to( get_permalink( $assignment_id ), __( 'Assignment answer is required', 'tutor-pro' ), 'error' );
			exit;
		}

		do_action( 'tutor_assignment/before/submit', $assignment_submit_id );

		$date = gmdate( 'Y-m-d H:i:s' );

		$data_array = array(
			'comment_post_ID'  => $assignment_id,
			'comment_author'   => 'student',
			'comment_date'     => $date, // Submit Finished.
			'comment_date_gmt' => wp_date( 'Y-m-d H:i:s' ), // Submit Started.
			'comment_agent'    => 'TutorLMSPlugin',
			'comment_type'     => 'tutor_assignment',
			'comment_parent'   => $course_id,
			'user_id'          => $student_id,
			'comment_content'  => $assignment_answer,
			'comment_approved' => 'submitted', // submitting, submitted.
		);

		$data = apply_filters(
			'tutor_assignment_submit_updating_data',
			$data_array
		);

		$wpdb->insert(
			$wpdb->comments,
			$data
		);

		$assignment_submit_id = (int) $wpdb->insert_id;

		$upload_attachment = $this->handle_assignment_attachment_uploads( $assignment_id );

		if ( $allowed_upload_files && is_array( $upload_attachment ) && count( $upload_attachment ) ) {
			// Insert attachments.
			if ( tutor_utils()->count( $upload_attachment ) ) {
				update_comment_meta( $assignment_submit_id, 'uploaded_attachments', json_encode( $upload_attachment, JSON_UNESCAPED_UNICODE ) );
			}
		}

		if ( 0 !== $assignment_submit_id ) {
			do_action( 'tutor_assignment/after/submitted', $assignment_submit_id );
		}

		return $assignment_id;
	}

	/**
	 * Update the data to submit the assignment
	 *
	 * @since 2.6.2
	 *
	 * @param object $store_data date object.
	 *
	 * @return int
	 */
	public function update_assignment_submit( $store_data ) {
		global $wpdb;

		$update_id            = $store_data->update_id;
		$assignment_id        = $store_data->assignment_id;
		$assignment_answer    = $store_data->assignment_answer;
		$allowed_upload_files = $store_data->allowed_upload_files;
		$assignment_submit_id = $store_data->assignment_submit_id;

		if ( in_array( $assignment_answer, array( '', '<p>&nbsp;</p>', '<p><br data-mce-bogus="1"></p>' ), true ) ) {
			tutor_utils()->redirect_to( get_permalink( $assignment_id ), __( 'Assignment answer is required', 'tutor-pro' ), 'error' );
			exit;
		}

		do_action( 'tutor_assignment/before/submit', $assignment_submit_id );

		$date = gmdate( 'Y-m-d H:i:s' );

		$data_array = array(
			'comment_content'  => $assignment_answer,
			'comment_date'     => $date, // Submit Finished.
			'comment_approved' => 'submitted', // submitting, submitted.
		);

		$data = apply_filters(
			'tutor_assignment_submit_updating_data',
			$data_array
		);

		$upload_attachment = $this->handle_assignment_attachment_uploads( $assignment_id );

		if ( $allowed_upload_files && is_array( $upload_attachment ) && count( $upload_attachment ) ) {
			if ( $update_id ) {
				// Update attachments.
				$existing_attachments = get_comment_meta( $update_id, 'uploaded_attachments', true );
				$existing_attachments = json_decode( $existing_attachments );

				if ( is_array( $existing_attachments ) && count( $existing_attachments ) ) {
					$attachments = $this->prepare_attachment( $existing_attachments, $upload_attachment );
				} else {
					$attachments = $upload_attachment;
				}
				update_comment_meta( $update_id, 'uploaded_attachments', json_encode( $attachments, JSON_UNESCAPED_UNICODE ) );
			} else {
				// Insert attachments.
				if ( tutor_utils()->count( $upload_attachment ) ) {
					update_comment_meta( $assignment_submit_id, 'uploaded_attachments', json_encode( $upload_attachment, JSON_UNESCAPED_UNICODE ) );
				}
			}
		}

		$wpdb->update(
			$wpdb->comments,
			$data,
			array(
				'comment_ID' => $update_id ? $update_id : $assignment_submit_id,
			)
		);

		if ( 0 !== $assignment_submit_id ) {
			do_action( 'tutor_assignment/after/submitted', $assignment_submit_id );
		}

		return $assignment_id;
	}

	/**
	 * Handle file upload during assignment submit
	 *
	 * @since 1.0.0
	 *
	 * @param integer $assignment_id assignment id.
	 *
	 * @return array
	 */
	public function handle_assignment_attachment_uploads( $assignment_id = 0 ) {
		if ( ! $assignment_id ) {
			return;
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$attached_files  = array();
		$allow_to_upload = (int) tutor_utils()->get_assignment_option( $assignment_id, 'upload_files_limit' );

		if ( ! empty( $_FILES['attached_assignment_files'] ) ) {
			$files       = $_FILES['attached_assignment_files']; //phpcs:ignore
			$max_size_mb = (int) tutor_utils()->get_assignment_option( $assignment_id, 'upload_file_size_limit', 2 );

			foreach ( $files['name'] as $key  => $value ) {
				$file_size  = $files['size'][ $key ];
				$size_in_mb = round( $file_size / ( 1024 * 1024 ) );

				if ( $size_in_mb > $max_size_mb ) {
					exit( esc_html( sprintf( __( 'Maximum attachment upload size allowed is %d MB', 'tutor-pro' ), $max_size_mb ) ) ); //phpcs:ignore
				}
			}

			foreach ( $files['name'] as $key => $value ) {
				if ( $files['name'][ $key ] ) {
					$file = array(
						'name'     => $files['name'][ $key ],
						'type'     => $files['type'][ $key ],
						'tmp_name' => $files['tmp_name'][ $key ],
						'error'    => $files['error'][ $key ],
						'size'     => $files['size'][ $key ],
					);

					$upload_overrides = array(
						'test_form' => false,
					);
					$movefile         = wp_handle_upload( $file, $upload_overrides );

					if ( $movefile && ! isset( $movefile['error'] ) ) {
						$file_path = $movefile['file'];
						unset( $movefile['file'] );
						$upload_dir = wp_get_upload_dir();

						$file_sub_path = str_replace( trailingslashit( $upload_dir['basedir'] ), '', $file_path );
						$file_name     = str_replace( trailingslashit( $upload_dir['path'] ), '', $file_path );

						$movefile['uploaded_path'] = $file_sub_path;
						$movefile['name']          = $file_name;

						$attached_files[] = $movefile;
					} else {
						/**
						 * Error generated by _wp_handle_upload()
						 *
						 * @see _wp_handle_upload() in wp-admin/includes/file.php
						 */
						echo $movefile['error']; //phpcs:ignore
					}
				}
			}
		}

		return $attached_files;
	}

	/**
	 * Evaluate assignment submission
	 */
	public function tutor_evaluate_assignment_submission() {
		try {
			tutor_utils()->checking_nonce();
			$date = gmdate( 'Y-m-d H:i:s' );

			do_action( 'tutor_assignment/evaluate/before' );

			// Get data from request.
			$submitted_id    = Input::post( 'assignment_submitted_id', 0, Input::TYPE_INT );
			$evaluate_fields = tutor_utils()->array_get( 'evaluate_assignment', $_POST ); //phpcs:ignore

			// Get assignment info.
			$submitted_assignment = tutor_utils()->get_assignment_submit_info( $submitted_id );
			$course_id            = $submitted_assignment->comment_parent;
			$student_id           = $submitted_assignment->user_id;

			if ( ! tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id ) ) {
				wp_send_json_error( tutor_utils()->error_message() );
			}

			foreach ( $evaluate_fields as $field_key => $field_value ) {
				update_comment_meta( $submitted_id, $field_key, $field_value );
			}

			update_comment_meta( $submitted_id, 'evaluate_time', $date );

			// Assignment mark meta update @since v2.0.0.
			$assignment_post_id = Input::post( 'assignment_post_id', 0, Input::TYPE_INT );
			$assignment_mark    = isset( $evaluate_fields['assignment_mark'] ) ? sanitize_text_field( $evaluate_fields['assignment_mark'] ) : 0;
			update_post_meta( $assignment_post_id, '_tutor_assignment_evaluate_mark', $assignment_mark );

			do_action( 'tutor_assignment/evaluate/after', $submitted_id, $course_id, $student_id );

			wp_send_json_success( __( 'Assignment Evaluated', 'tutor-pro' ) );
		} catch ( \Throwable $th ) {
			wp_send_json_error( $th->getMessage() );
		}
	}

	/**
	 * Do auto course complete after evaluate an assignment.
	 *
	 * @since 2.4.0
	 *
	 * @param int $submitted_id submission id.
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public function do_auto_course_complete( $submitted_id, $course_id, $user_id ) {
		if ( CourseModel::can_autocomplete_course( $course_id, $user_id ) ) {
			CourseModel::mark_course_as_completed( $course_id, $user_id );
			Course::set_review_popup_data( $user_id, $course_id );
		}
	}

	/**
	 * Show check icon for assignment.
	 *
	 * @param object  $post post.
	 * @param boolean $lock_icon lock icon.
	 *
	 * @return void
	 */
	public function show_assignment_submitted_icon( $post, $lock_icon = false ) {
		if ( 'tutor_assignments' === $post->post_type ) {
			$is_submitted = tutor_utils()->is_assignment_submitted( $post->ID );

			if ( $is_submitted && 'submitted' === $is_submitted->comment_approved ) {
				$result       = self::get_assignment_result( $post->ID, get_current_user_id() );
				$result_class = '';
				if ( 'pending' === $result ) {
					$result_class = 'tutor-check-pending';
				}
				if ( 'fail' === $result ) {
					$result_class = 'tutor-check-fail';
				}
				echo "<input type='checkbox' class='tutor-form-check-input tutor-form-check-circle " . esc_attr( $result_class ) . "' disabled='disabled' readonly='readonly' checked='checked' />";
			} elseif ( $lock_icon ) {
					echo '<i class="tutor-icon-lock-line tutor-fs-7 tutor-color-muted tutor-mr-4" area-hidden="true"></i>';
			} else {
				echo "<input type='checkbox' class='tutor-form-check-input tutor-form-check-circle' disabled='disabled' readonly='readonly' />";
			}
		}
	}

	/**
	 * Delete assignment by ID
	 *
	 * @since 1.9.5
	 *
	 * @param int $submitted_id submitted id.
	 *
	 * @return void
	 */
	private function delete_assignment_submission( $submitted_id ) {

		// Delete assignment attachments.
		$root_dir    = wp_get_upload_dir()['basedir'];
		$attachments = get_comment_meta( $submitted_id, 'uploaded_attachments', true );
		$attachments = @json_decode( $attachments, true );
		if ( is_array( $attachments ) ) {
			foreach ( $attachments as $attachment ) {
				if ( isset( $attachment['uploaded_path'] ) ) {
					$path = $root_dir . DIRECTORY_SEPARATOR . $attachment['uploaded_path'];
					file_exists( $path ) ? unlink( $path ) : 0;
				}
			}
		}

		// Delete assignment rows.
		global $wpdb;
		$wpdb->delete( $wpdb->comments, array( 'comment_ID' => $submitted_id ) );
		$wpdb->delete( $wpdb->commentmeta, array( 'comment_id' => $submitted_id ) );
	}

	/**
	 * Delete course progress.
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public function delete_tutor_course_progress( $course_id, $user_id ) {
		global $wpdb;
		$submission_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT comment_ID
				FROM {$wpdb->comments}
				WHERE comment_type=%s AND user_id=%d",
				'tutor_assignment',
				$user_id
			)
		);

		// Loop through IDs and delete.
		foreach ( $submission_ids as $id ) {
			$this->delete_assignment_submission( $id );
		}
	}

	/**
	 * Provide attachment files after merge existing and new attachments
	 *
	 * @param array $existing_attachments | existing assignment attachments.
	 * @param array $upload_attachments | new uploaded attachments.
	 *
	 * @return array | merged attachments files
	 *
	 * @since v2.0.0
	 */
	protected function prepare_attachment( array $existing_attachments, array $upload_attachments ): array {
			$merge_all_files = array_merge( $existing_attachments, $upload_attachments );
			return is_array( $merge_all_files ) ? $merge_all_files : array();
	}

	/**
	 * Delete attachment handle ajax request
	 *
	 * @return void
	 *
	 * @since v2.0.0
	 */
	public static function remove_assignment_attachment(): void {
		tutor_utils()->checking_nonce();
		$assignment_comment_id = Input::post( 'assignment_comment_id' );
		$file_name             = Input::post( 'file_name' );

		$assignment = tutor_utils()->get_assignment_submit_info( $assignment_comment_id );

		if ( ! $assignment ) {
			wp_send_json_error();
		}

		$course_id = $assignment->comment_parent;
		$user_id   = get_current_user_id();

		$is_enrolled = tutor_utils()->is_enrolled( $course_id, $user_id );
		if ( ! $is_enrolled ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$update = self::delete_attachment( $assignment_comment_id, $file_name );

		if ( $update ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Functionality for the delete attachment
	 *
	 * @param int    $assignment_comment_id comment id.
	 * @param string $file_name name of the file to delete.
	 *
	 * @return int|bool
	 */
	public static function delete_attachment( $assignment_comment_id, $file_name ) {
		$submitted_attachments = get_comment_meta( $assignment_comment_id, 'uploaded_attachments' );
		$updated_files         = array();
		$upload_dir            = wp_upload_dir();
		$file_path             = null;

		if ( is_array( $submitted_attachments ) && count( $submitted_attachments ) ) {
			foreach ( $submitted_attachments as $key => $attach ) {
				$attachments = json_decode( $attach );
				foreach ( $attachments as $attach ) {
					if ( $file_name == $attach->name ) {
						$file_path = trailingslashit( $upload_dir['basedir'] ) . $attach->uploaded_path ?? '';
						continue;
					}
					array_push( $updated_files, $attach );
				}
			}
		}

		if ( ! empty( $file_path ) && file_exists( $file_path ) ) {
			unlink( $file_path );
		}

		return update_comment_meta( $assignment_comment_id, 'uploaded_attachments', json_encode( $updated_files ) );
	}

	/**
	 * Check weather assignment evaluated or not
	 *
	 * @param int $assignment_id | assignment id to check.
	 *
	 * @return int | comment id on success 0 on failure
	 *
	 * @since v2.0.0
	 */
	public static function is_evaluated( int $assignment_id ): int {
		global $wpdb;
		$assignment_id = sanitize_text_field( $assignment_id );
		$id            = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT comment_ID
					FROM $wpdb->comments
					WHERE comment_post_ID = %d
						AND comment_type = %s
				",
				$assignment_id,
				'tutor_assignment'
			)
		);
		$evaluate      = get_comment_meta( $id, 'evaluate_time', true );
		return $evaluate ? (int) $evaluate : 0;
	}

	/**
	 * Get assignment result.
	 *
	 * @since 2.4.0
	 *
	 * @param int $assignment_id assignment id.
	 * @param int $user_id user id.
	 *
	 * @return string pending, pass, fail.
	 */
	public static function get_assignment_result( $assignment_id, $user_id ) {
		$submitted_assignment = tutor_utils()->is_assignment_submitted( $assignment_id, $user_id );
		$submit_id            = $submitted_assignment->comment_ID ?? 0;
		$is_reviewed          = get_comment_meta( $submit_id, 'evaluate_time', true );
		if ( ! $is_reviewed ) {
			return 'pending';
		}

		$pass_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'pass_mark' );
		$given_mark = get_comment_meta( $submitted_assignment->comment_ID, 'assignment_mark', true );
		if ( $given_mark >= $pass_mark ) {
			return 'pass';
		} else {
			return 'fail';
		}
	}
}
