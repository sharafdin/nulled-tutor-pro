<?php
/**
 * General class for hook logic
 *
 * @package TutorPro
 *
 * @since 2.0.0
 */

namespace TUTOR_PRO;

use TUTOR\Input;
use Tutor\Models\CourseModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class General
 *
 * @since 2.0.0
 */
class General {

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_action_tutor_add_course_builder', array( $this, 'tutor_add_course_builder' ) );
		add_filter( 'frontend_course_create_url', array( $this, 'frontend_course_create_url' ) );
		add_filter( 'template_include', array( $this, 'fs_course_builder' ), 99 );

		add_filter( 'tutor/options/extend/attr', array( $this, 'extend_settings_option' ) );
		add_filter( 'tutor_course_builder_logo_src', array( $this, 'tutor_course_builder_logo_src' ) );
		add_filter( 'tutor_email_logo_src', array( $this, 'tutor_email_logo_src' ) );
		add_filter( 'tutor_pages', array( $this, 'add_login_page' ), 10, 1 );

		add_action( 'tutor_after_lesson_completion_button', array( $this, 'add_course_completion_button' ), 10, 4 );
	}

	/**
	 * Show course completion button after all course content done.
	 *
	 * @since 2.4.0
	 *
	 * @param int   $course_id course id.
	 * @param int   $user_id user id.
	 * @param bool  $is_course_completed course completed or not.
	 * @param array $course_stats course progress stats.
	 *
	 * @return void
	 */
	public function add_course_completion_button( $course_id, $user_id, $is_course_completed, $course_stats ) {
		if ( false === $is_course_completed
			&& $course_stats['completed_count'] === $course_stats['total_count']
			&& CourseModel::can_complete_course( $course_id, $user_id ) ) {
			?>
			<div class="tutor-topbar-complete-btn tutor-mr-20">
				<form method="post">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce, false ); ?>
					<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>"/>
					<input type="hidden" name="tutor_action" value="tutor_complete_course"/>
					<button type="submit" 
						class="tutor-topbar-mark-btn tutor-btn tutor-btn-primary tutor-ws-nowrap"
						name="complete_course" value="complete_course">
						<span class="tutor-icon-circle-mark-line tutor-mr-8" area-hidden="true"></span>
						<span><?php esc_html_e( 'Complete Course', 'tutor-pro' ); ?></span>
					</button>
				</form>
			</div>
			<?php
		}
	}

	/**
	 * Add login page
	 *
	 * @since 2.1.0
	 *
	 * @param array $pages page list.
	 *
	 * @return array
	 */
	public function add_login_page( array $pages ) {
		return array( 'tutor_login_page' => __( 'Tutor Login', 'tutor' ) ) + $pages;
	}

	/**
	 * Process course submission from frontend course builder
	 *
	 * @since v.1.3.4
	 *
	 * @return void
	 */
	public function tutor_add_course_builder() {
		tutor_utils()->checking_nonce();

		$user_id          = get_current_user_id();
		$course_post_type = tutor()->course_post_type;

		//phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		$course_ID = Input::post( 'course_ID', 0, Input::TYPE_INT );
		$post_ID   = Input::post( 'post_ID', 0, Input::TYPE_INT );

		if ( ! tutor_utils()->can_user_edit_course( $user_id, $post_ID ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor-pro' ) ) );
		}

		$post   = get_post( $post_ID );
		$update = true;

		/**
		 * Update the post
		 */

		$content   = Input::post( 'content', '', Input::TYPE_KSES_POST );
		$title     = Input::post( 'title', '' );
		$tax_input = tutor_utils()->array_get( 'tax_input', $_POST ); //phpcs:ignore
		$slug      = Input::post( 'post_name', '' );

		$post_data = array(
			'ID'           => $post_ID,
			'post_title'   => $title,
			'post_content' => $content,
			'post_name'    => $slug,
		);

		// Publish or Pending...
		$message       = null;
		$show_modal    = true;
		$submit_action = Input::post( 'course_submit_btn', '' );

		if ( 'save_course_as_draft' === $submit_action ) {
			$post_data['post_status'] = 'draft';
			$message                  = __( 'Course has been saved as a draft.', 'tutor-pro' );
			$show_modal               = false;
		} elseif ( 'submit_for_review' === $submit_action ) {
			$post_data['post_status'] = 'pending';
			$message                  = __( 'Course has been submitted for review.', 'tutor-pro' );
		} elseif ( 'publish_course' === $submit_action ) {
			$can_publish_course = (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );
			if ( $can_publish_course || current_user_can( 'administrator' ) ) {
				$post_data['post_status'] = 'publish';
				$message                  = __( 'Course has been published.', 'tutor-pro' );
			} else {
				$post_data['post_status'] = 'pending';
				$message                  = __( 'Course has been submitted for review.', 'tutor-pro' );
			}
		}

		if ( $message ) {
			update_user_meta( $user_id, 'tutor_frontend_course_message_expires', time() + 5 );
			update_user_meta(
				$user_id,
				'tutor_frontend_course_action_message',
				array(
					'message'    => $message,
					'show_modal' => $show_modal,
				)
			);
		}
		wp_update_post( $post_data );

		/**
		 * Setting Thumbnail
		 */
		$_thumbnail_id = Input::post( 'tutor_course_thumbnail_id', 0, Input::TYPE_INT );
		self::update_post_thumbnail( $post_ID, $_thumbnail_id );

		/**
		 * Adding taxonomy
		 */
		if ( tutor_utils()->count( $tax_input ) ) {
			foreach ( $tax_input as $taxonomy => $tags ) {
				$taxonomy_obj = get_taxonomy( $taxonomy );
				if ( ! $taxonomy_obj ) {
					/* translators: %s: taxonomy name */
					_doing_it_wrong( __FUNCTION__, sprintf( __( 'Invalid taxonomy: %s.' ), $taxonomy ), '4.4.0' );//phpcs:ignore
					continue;
				}

				// array = hierarchical, string = non-hierarchical.
				if ( is_array( $tags ) ) {
					$tags = array_filter( $tags );
				}
				wp_set_post_terms( $post_ID, $tags, $taxonomy );
			}
		}

		do_action( 'save_tutor_course', $post_ID, $post_data );

		if ( wp_doing_ajax() ) {
			wp_send_json_success();
		} else {

			/**
			 * If update request not comes from edit page, redirect it to edit page
			 */
			$edit_mode = (int) sanitize_text_field( tutor_utils()->array_get( 'course_ID', $_GET ) );
			if ( ! $edit_mode ) {
				$edit_page_url = add_query_arg( array( 'course_ID' => $post_ID ) );
				wp_redirect( $edit_page_url );
				die();
			}

			/**
			 * Finally redirect it to previous page to avoid multiple post request
			 */
			$redirect_option_enabled = (bool) tutor_utils()->get_option( 'enable_redirect_on_course_publish_from_frontend' );
			if ( 'publish_course' === $submit_action && true === $redirect_option_enabled ) {
				$target_url = tutor_utils()->tutor_dashboard_url( 'my-courses' );
				wp_safe_redirect( $target_url );
			} else {
				wp_safe_redirect( tutor_utils()->referer() );
			}

			die();
		}
		die();
	}


	/**
	 * Frontend Course builder url
	 *
	 * @return string
	 */
	public function frontend_course_create_url() {
		return tutor_utils()->get_tutor_dashboard_page_permalink( 'create-course' );
	}


	/**
	 * Include Dashboard
	 *
	 * @param string $template template.
	 *
	 * @return bool|string
	 */
	public function fs_course_builder( $template ) {
		global $wp_query;

		if ( $wp_query->is_page ) {
			$student_dashboard_page_id = (int) tutor_utils()->get_option( 'tutor_dashboard_page_id' );
			if ( get_the_ID() === $student_dashboard_page_id ) {
				if ( tutor_utils()->array_get( 'tutor_dashboard_page', $wp_query->query_vars ) === 'create-course' ) {
					if ( is_user_logged_in() ) {
						$template = tutor_get_template( 'dashboard.create-course' );
					} else {
						$template = tutor_get_template( 'login' );
					}
				}
			}
		}

		return $template;
	}


	/**
	 * Extend settings options.
	 *
	 * @param array $attr settings options.
	 *
	 * @return array
	 */
	public function extend_settings_option( $attr ) {

		$pages = tutor_utils()->get_pages();

		array_unshift(
			$attr['design']['blocks']['block_course']['fields'],
			array(
				'key'   => 'tutor_frontend_course_page_logo_id',
				'type'  => 'upload_full',
				'label' => __( 'Course Builder Page Logo', 'tutor' ),
				'desc'  => __(
					'<p>Size: <strong>700x430 pixels;</strong> File Support:<strong>jpg, .jpeg or .png.</strong></p>',
					'tutor'
				),
			)
		);

		$attr['advanced']['blocks'][0]['fields'][] = array(
			'key'         => 'hide_admin_bar_for_users',
			'type'        => 'toggle_switch',
			'label'       => __( 'Hide Admin Bar and Restrict Access to WP Admin for Instructors', 'tutor' ),
			'label_title' => '',
			'default'     => 'off',
			'desc'        => __( 'Enable this to hide the WordPress Admin Bar from Frontend site, and restrict access to the WP Admin panel.', 'tutor' ),
		);

		/**
		 * Settings option added in Course > Course section.
		 *
		 * @since 2.0.6
		 */
		$attr['course']['blocks']['block_course']['fields'][] = array(
			'key'         => 'enable_redirect_on_course_publish_from_frontend',
			'type'        => 'toggle_switch',
			'label'       => __( 'Redirect Instructor to "My Courses" once Publish button is Clicked', 'tutor' ),
			'default'     => 'off',
			'label_title' => '',
			'desc'        => __( 'Enable to Redirect an Instructor to the "My Courses" Page once he clicks on the "Publish" button', 'tutor' ),
		);

		/**
		 * Hide quiz attempts details from frontend
		 *
		 * @since 2.0.7
		 */
		$attr['course']['blocks']['block_quiz']['fields'][] = array(
			'key'     => 'hide_quiz_details',
			'type'    => 'toggle_switch',
			'label'   => __( 'Hide Quiz Details from the Students', 'tutor' ),
			'default' => 'off',
			'desc'    => __( 'If enabled, the students will not be able to see their quiz attempts details', 'tutor' ),
		);

		/**
		 * Show enrollment box on top of page when mobile view
		 *
		 * @since 2.0.9
		 */
		$attr['design']['blocks']['course-details']['fields'][] = array(
			'key'     => 'enrollment_box_position_in_mobile',
			'type'    => 'select',
			'label'   => __( 'Position of the Enrollment Box in Mobile View', 'tutor-pro' ),
			'default' => 'bottom',
			'options' => array(
				'top'    => __( 'On Page Top', 'tutor-pro' ),
				'bottom' => __( 'On Page Bottom', 'tutor-pro' ),
			),
			'desc'    => __( 'You can decide where you want to show Enrollment Box on your Course Details page by selecting an option from here', 'tutor-pro' ),
		);

		/**
		 * Login page option
		 *
		 * @since 2.1.0
		 */
		$login_option = array(
			'key'     => 'tutor_login_page',
			'type'    => 'select',
			'label'   => __( 'Login Page', 'tutor' ),
			'default' => '0',
			'options' => $pages,
			'desc'    => __( 'This page will be used as the login page for both the students and the instructors.', 'tutor' ),
		);
		/**
		 * Invoice generate settings for manual enrollment
		 *
		 * @since 2.1.4
		 */
		$invoice_options = array(
			'key'         => 'tutor_woocommerce_invoice',
			'type'        => 'toggle_switch',
			'label'       => __( 'Generate WooCommerce Order', 'tutor-pro' ),
			'label_title' => '',
			'default'     => 'off',
			'desc'        => __( 'If you want to create an WooCommerce Order to keep Track of your Sales Report for Manual Enrolment', 'tutor' ),
		);

		tutor_utils()->add_option_after(
			'enable_tutor_native_login',
			$attr['advanced']['blocks'][1]['fields'],
			$login_option
		);
		tutor_utils()->add_option_after(
			'tutor_woocommerce_order_auto_complete',
			$attr['monetization']['blocks']['block_options']['fields'],
			$invoice_options
		);

		$attr['design']['blocks']['course-details']['fields'][0]['group_options'][] = array(
			'key'         => 'enable_sticky_sidebar',
			'type'        => 'toggle_single',
			'label'       => __( 'Sticky Sidebar', 'tutor-pro' ),
			'label_title' => __( 'Disable', 'tutor-pro' ),
			'default'     => 'off',
			'desc'        => __( 'Enable sticky sidebar on course details on scroll', 'tutor-pro' ),
		);

		// TODO implement later.
		/**
		* $attr['design']['blocks']['course-details']['fields'][0]['group_options'][] = array(
		* 'key'         => 'enable_sticky_topbar',
		* 'type'        => 'toggle_single',
		* 'label'       => __( 'Sticky Topbar', 'tutor-pro' ),
		* 'label_title' => __( 'Disable', 'tutor-pro' ),
		* 'default'     => 'off',
		* 'desc'        => __( 'Enable sticky topbar on course details on scroll', 'tutor-pro' ),
		* );
		*/

		/**
		 * Lesson video completion control.
		 *
		 * @since 2.2.4
		 */
		tutor_utils()->add_option_after(
			'enable_lesson_classic_editor',
			$attr['course']['blocks']['block_lesson']['fields'],
			array(
				'key'           => 'control_video_lesson_completion',
				'type'          => 'toggle_switch',
				'label'         => __( 'Video Lesson Completion Control', 'tutor-pro' ),
				'default'       => 'off',
				'desc'          => __( 'Enable to set the minimum video watch % for lesson completion, only works with Tutor Player.', 'tutor-pro' ),
				'toggle_fields' => 'required_percentage_to_complete_video_lesson',
			)
		);

		tutor_utils()->add_option_after(
			'control_video_lesson_completion',
			$attr['course']['blocks']['block_lesson']['fields'],
			array(
				'key'         => 'required_percentage_to_complete_video_lesson',
				'type'        => 'number',
				'number_type' => 'integer',
				'label'       => __( 'Set Required Percentage', 'tutor-pro' ),
				'default'     => 80,
				'max'         => 100,
				'desc'        => __( 'Specify the minimum video watch % learners must watch to mark the lesson as complete.', 'tutor-pro' ),
			)
		);

		return $attr;
	}

	/**
	 * Course builder logo src
	 *
	 * @param string $url url.
	 *
	 * @return string
	 */
	public function tutor_course_builder_logo_src( $url ) {
		$media_id = (int) get_tutor_option( 'tutor_frontend_course_page_logo_id' );
		if ( $media_id ) {
			return wp_get_attachment_url( $media_id );
		}
		return $url;
	}

	/**
	 * Email logo src
	 *
	 * @param string $url url.
	 * @param mixed  $size size.
	 *
	 * @return string
	 */
	public function tutor_email_logo_src( $url = null, $size = null ) {
		$hotlink_protection = (bool) get_tutor_option( ContentSecurity::HOTLINKING_OPTION );
		$media_id_or_url    = get_tutor_option( 'tutor_email_template_logo_id' );

		if ( $hotlink_protection ) {
			return esc_url( $media_id_or_url );
		}

		if ( (int) $media_id_or_url ) {
			return wp_get_attachment_image_url( $media_id_or_url, 'full' );
		}

		return $url;
	}

	/**
	 * Update post thumbnail
	 *
	 * It will update post thumbnail meta if thumbnail_id is set
	 * otherwise it will remove existing meta
	 *
	 * Used from frontend course & bundle builder
	 *
	 * @since 2.2.0
	 *
	 * @param int $post_id required post id.
	 * @param int $thumbnail_id thumbnail id.
	 *
	 * @return void
	 */
	public static function update_post_thumbnail( int $post_id, int $thumbnail_id = 0 ) {
		$thumbnail_id = (int) sanitize_text_field( $thumbnail_id );
		$product_id   = tutor_utils()->get_course_product_id( $post_id );

		if ( $thumbnail_id ) {
			update_post_meta( $post_id, '_thumbnail_id', $thumbnail_id );

			// Update product thumbnail.
			set_post_thumbnail( $product_id, $thumbnail_id );
		} else {
			delete_post_meta( $post_id, '_thumbnail_id' );
		}
	}
}
