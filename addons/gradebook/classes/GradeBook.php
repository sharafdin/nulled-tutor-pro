<?php
/**
 * Gradebook Addon Logic
 *
 * @package TutorPro\Addons
 * @subpackage Gradebook
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_GB;

use TUTOR\Backend_Page_Trait;
use TUTOR\Input;
use TUTOR\User;

/**
 * Class Gradebook
 */
class GradeBook {

	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */

	use Backend_Page_Trait;
	/**
	 * Page Title
	 *
	 * @var $page_title
	 */
	public $page_title;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Register hooks
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'tutor_admin_register', array( $this, 'register_menu' ) );

		add_action( 'wp_ajax_add_new_gradebook', array( $this, 'add_new_gradebook' ) );
		add_action( 'wp_ajax_update_gradebook', array( $this, 'update_gradebook' ) );
		add_action( 'tutor_action_delete_gradebook', array( $this, 'delete_gradebook' ) );

		add_action( 'tutor_quiz/attempt_ended', array( $this, 'quiz_attempt_ended' ) );
		add_filter( 'tutor_assignment/single/results/after', array( $this, 'filter_assignment_result' ), 10, 3 );
		add_filter( 'tutor_course/single/nav_items', array( $this, 'add_course_nav_item' ), 10, 2 );

		add_action( 'tutor_action_gradebook_result_list_bulk_actions', array( $this, 'gradebook_result_list_bulk_actions' ), 10, 0 );
		add_action( 'delete_tutor_course_progress', array( $this, 'delete_gradebook_on_retake' ), 11, 2 );

		// Install Sample Grade Data.
		add_action( 'wp_ajax_import_gradebook_sample_data', array( $this, 'import_gradebook_sample_data' ) );

		// Provide final gradebook.
		add_action( 'tutor_gradebook_get_final_stats', array( $this, 'final_gradebook' ), 10, 2 );

		// Generate gradebook on various events.
		add_action( 'tutor_quiz/attempt_ended', array( $this, 'gradebook_generator_wrapper' ), 10, 3 );
		add_action( 'tutor_assignment/evaluate/after', array( $this, 'generate_grade' ) );
		add_action( 'tutor_assignment/evaluate/after', array( $this, 'gradebook_generator_wrapper' ), 10, 3 );
		add_action( 'tutor_quiz/answer/review/after', array( $this, 'gradebook_generator_wrapper' ), 10, 3 );
		add_action( 'delete_tutor_course_progress', array( $this, 'gradebook_generate' ), 10, 2 );

		$this->page_title = __( 'Gradebook', 'tutor' );

		/**
		 * Handle bulk action
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_gradebook_bulk_action', array( $this, 'gradebook_bulk_action' ) );
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @since v2.0.0
	 *
	 * @return array
	 */
	public function prpare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}

	/**
	 * Load Tab
	 *
	 * @since v2.0.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return array
	 */
	public function tabs_key_value( $course_id = '' ): array {
		$gradebooks = get_generated_gradebooks(
			array(
				'course_id' => $course_id,
			)
		);

		$tabs = array(
			array(
				'key'   => 'overview',
				'title' => __( 'Overview', 'tutor-pro' ),
				'value' => is_array( $gradebooks->res ) ? count( $gradebooks->res ) : 0,
				'url'   => '?page=tutor_gradebook&data=overview',
			),
			array(
				'key'   => 'grade-settings',
				'title' => __( 'Grade Settings', 'tutor-pro' ),
				'value' => $this->get_grade_settings_count(),
				'url'   => '?page=tutor_gradebook&sub_page=gradebooks&data=grade-settings',
			),
		);
		return $tabs;
	}

	/**
	 * Grade generator
	 *
	 * @param int $variable_id variable id.
	 * @param int $course_id course id.
	 * @param int $student_id student id.
	 *
	 * @return void
	 */
	public function gradebook_generator_wrapper( $variable_id, $course_id, $student_id ) {
		$this->gradebook_generate( $course_id, $student_id );
	}

	/**
	 * Final grade
	 *
	 * @param mixed $response response.
	 * @param int   $course_id course id.
	 *
	 * @return mixed
	 */
	public function final_gradebook( $response, $course_id ) {
		$grade = get_generated_gradebook( 'final', $course_id );
		return tutor_gradebook_get_stats( $grade );
	}

	/**
	 * Admin script
	 *
	 * @param string $page page name.
	 * @return void
	 */
	public function admin_scripts( $page ) {
		if ( 'tutor-lms-pro_page_tutor_gradebook' === $page ) {
			wp_enqueue_script( 'tutor-gradebook', TUTOR_GB()->url . 'assets/js/gradebook.js', array(), TUTOR_GB()->version, true );
		}
	}

	/**
	 * Register menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_submenu_page( 'tutor', __( 'Gradebook', 'tutor-pro' ), __( 'Gradebook', 'tutor-pro' ), 'manage_tutor', 'tutor_gradebook', array( $this, 'tutor_gradebook' ) );
	}

	/**
	 * Gradebook page.
	 *
	 * @return void
	 */
	public function tutor_gradebook() {
		include TUTOR_GB()->path . 'views/pages/grade_book.php';
	}

	/**
	 * Add new gradebook
	 *
	 * @return void
	 */
	public function add_new_gradebook() {
		global $wpdb;

		// Checking nonce.
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$required_fields = apply_filters(
			'tutor_gradebook_required_fields',
			array(
				'grade_name'   => __( 'Grade name field is required', 'tutor-pro' ),
				'percent_from' => __( 'Minimum percentile is required', 'tutor-pro' ),
				'percent_to'   => __( 'Maximum percentile is required', 'tutor-pro' ),
			)
		);

		$validation_errors = array();

		if ( tutor_utils()->count( $validation_errors ) ) {
			wp_send_json_error( $validation_errors );
		}

		$percent_from = Input::post( 'percent_from', 0, Input::TYPE_INT );
		$data         = array(
			'grade_name'   => Input::post( 'grade_name' ),
			'grade_point'  => Input::post( 'grade_point' ),
			'percent_from' => $percent_from,
			'percent_to'   => Input::post( 'percent_to' ),
			'grade_config' => maybe_serialize( tutor_utils()->array_get( 'grade_config', $_POST ) ), //phpcs:ignore
		);

		$wpdb->insert( $wpdb->tutor_gradebooks, $data );
		$gradebook_id = (int) $wpdb->insert_id;
		if ( $gradebook_id ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
		exit();
	}

	/**
	 * Update gradebook
	 *
	 * @return void
	 */
	public function update_gradebook() {
		global $wpdb;

		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$required_fields = apply_filters(
			'tutor_gradebook_required_fields',
			array(
				'grade_name'   => __( 'Grade name field is required', 'tutor-pro' ),
				'percent_from' => __( 'Minimum grade percentile is required', 'tutor-pro' ),
				'percent_to'   => __( 'Maximum grade percentile is required', 'tutor-pro' ),
			)
		);

		$validation_errors = array();

		if ( tutor_utils()->count( $validation_errors ) ) {
			wp_send_json_error( $validation_errors );
			exit();
		}

		$gradebook_id = Input::post( 'gradebook_id' );
		$percent_from = Input::post( 'percent_from', 0, Input::TYPE_INT );
		$data         = array(
			'grade_name'   => Input::post( 'grade_name' ),
			'grade_point'  => Input::post( 'grade_point' ),
			'percent_from' => $percent_from,
			'percent_to'   => Input::post( 'percent_to' ),
			'grade_config' => maybe_serialize( tutor_utils()->array_get( 'grade_config', $_POST ) ), //phpcs:ignore
		);

		$update = $wpdb->update( $wpdb->tutor_gradebooks, $data, array( 'gradebook_id' => $gradebook_id ) );
		if ( $update ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
		exit();
	}

	/**
	 * Delete gradebook
	 *
	 * @return void
	 */
	public function delete_gradebook() {

		if ( ! User::is_admin() ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		global $wpdb;
		$gradebook_id = (int) sanitize_text_field( tutor_utils()->array_get( 'gradebook_id', $_GET ) );
		$wpdb->delete( $wpdb->tutor_gradebooks, array( 'gradebook_id' => $gradebook_id ) );

		tutor_flash_set( 'success', __( 'The grade has been deleted successfully.', 'tutor-pro' ) );
		wp_redirect( admin_url( 'admin.php?page=tutor_gradebook&sub_page=gradebooks' ) );
		exit();
	}

	/**
	 * Get grade settings count
	 *
	 * @return int
	 */
	private function get_grade_settings_count() {
		global $wpdb;

		$settings_count = $wpdb->get_var(
			"SELECT COUNT(*) AS settings_count 
			FROM {$wpdb->tutor_gradebooks}"
		);

		return $settings_count ? $settings_count : 0;
	}

	/**
	 * Get max gradepoint
	 *
	 * @return int|float
	 */
	private function get_max_gradepoint() {
		global $wpdb;

		$point = $wpdb->get_var(
			"SELECT grade_point 
			FROM {$wpdb->tutor_gradebooks} 
			GROUP BY percent_to 
			HAVING MAX(percent_to) 
			ORDER BY percent_to DESC LIMIT 1"
		);

		return $point ? $point : 0;
	}

	/**
	 * Quiz attempt ended.
	 *
	 * @param int $attempt_id attempt id.
	 *
	 * @return void
	 */
	public function quiz_attempt_ended( $attempt_id ) {
		global $wpdb;

		$attempt           = tutor_utils()->get_attempt( $attempt_id );
		$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
		//phpcs:disable
		$gradebook = $wpdb->get_row(
			"SELECT * FROM {$wpdb->tutor_gradebooks} 
			WHERE percent_from <= {$earned_percentage} 
				AND percent_to >= {$earned_percentage} 
			ORDER BY gradebook_id ASC LIMIT 1"
		);

		if ( ! $gradebook ) {
			return;
		}

		$gradebook_data = array(
			'user_id'            => $attempt->user_id,
			'course_id'          => $attempt->course_id,
			'quiz_id'            => $attempt->quiz_id,
			'gradebook_id'       => $gradebook->gradebook_id,
			'result_for'         => 'quiz',
			'grade_name'         => $gradebook->grade_name,
			'grade_point'        => $this->get_max_gradepoint(),
			'earned_grade_point' => $gradebook->grade_point,
			'generate_date'      => date( 'Y-m-d H:i:s' ),
			'update_date'        => date( 'Y-m-d H:i:s' ),
		);

		$gradebook_result_id = 0;
		$gradebook_result    = $wpdb->get_row(
			"SELECT * FROM {$wpdb->tutor_gradebooks_results} 
			WHERE result_for = 'quiz' 
			AND user_id = {$attempt->user_id} 
			AND course_id = {$attempt->course_id} 
			AND quiz_id = {$attempt->quiz_id} "
		);
		//phpcs:enable

		if ( $gradebook_result ) {
			$gradebook_result_id = $gradebook_result->gradebook_result_id;
			// Update Gradebook Result.
			unset( $gradebook_data['generate_date'] );
			$wpdb->update( $wpdb->tutor_gradebooks_results, $gradebook_data, array( 'gradebook_result_id' => $gradebook_result->gradebook_result_id ) );
		} else {
			$wpdb->insert( $wpdb->tutor_gradebooks_results, $gradebook_data );
			$gradebook_result_id = (int) $wpdb->insert_id;
		}

		do_action( 'tutor_gradebook/quiz_result/after', $gradebook_result_id );
	}

	/**
	 * Generate grade.
	 *
	 * @param int $submitted_id submitted id.
	 *
	 * @return void
	 */
	public function generate_grade( $submitted_id ) {
		global $wpdb;

		do_action( 'tutor_gradebook/assignment_generate/before', $submitted_id );
		do_action( 'tutor_gradebook/generate/before' );

		$submitted_info = tutor_utils()->get_assignment_submit_info( $submitted_id );
		if ( $submitted_info ) {
			$max_mark   = tutor_utils()->get_assignment_option( $submitted_info->comment_post_ID, 'total_mark', 10 );
			$given_mark = get_comment_meta( $submitted_id, 'assignment_mark', true );

			$earned_percentage = ( $given_mark > 0 ) ? ( number_format( ( $given_mark * 100 ) / $max_mark ) ) : 0;
			//phpcs:disable
			$gradebook = $wpdb->get_row(
				"SELECT * FROM {$wpdb->tutor_gradebooks} 
			WHERE percent_from <= {$earned_percentage} 
			AND percent_to >= {$earned_percentage} ORDER BY gradebook_id ASC LIMIT 1  "
			);

			$gradebook_data = apply_filters(
				'tutor_gradebook_data',
				array(
					'user_id'            => $submitted_info->user_id,
					'course_id'          => $submitted_info->comment_parent,
					'assignment_id'      => $submitted_info->comment_post_ID,
					'gradebook_id'       => $gradebook->gradebook_id,
					'result_for'         => 'assignment',
					'grade_name'         => $gradebook->grade_name,
					'grade_point'        => $this->get_max_gradepoint(),
					'earned_grade_point' => $gradebook->grade_point,
					'earned_percent'     => $earned_percentage,
					'generate_date'      => date( 'Y-m-d H:i:s' ),
					'update_date'        => date( 'Y-m-d H:i:s' ),
				)
			);

			$gradebook_result_id = 0;
			$gradebook_result    = $wpdb->get_row(
				"SELECT * FROM {$wpdb->tutor_gradebooks_results} 
				WHERE result_for = 'assignment' 
					AND user_id = {$submitted_info->user_id} 
					AND course_id = {$submitted_info->comment_parent} 
					AND assignment_id = {$submitted_info->comment_post_ID} "
			);

			if ( $gradebook_result ) {
				$gradebook_result_id = (int) $gradebook_result->gradebook_result_id;
				// Update Gradebook Result.
				unset( $gradebook_data['generate_date'] );
				$wpdb->update(
					$wpdb->tutor_gradebooks_results,
					$gradebook_data,
					array( 'gradebook_result_id' => $gradebook_result->gradebook_result_id )
				);
			} else {
				$wpdb->insert( $wpdb->tutor_gradebooks_results, $gradebook_data );
				$gradebook_result_id = (int) $wpdb->insert_id;
			}

			do_action( 'tutor_gradebook/assignment_generate/after', $gradebook_result_id );
			do_action( 'tutor_gradebook/generate/after', $gradebook_result_id );
			//phpcs:enable
		}
	}

	/**
	 * Filter assignment result.
	 *
	 * @param mixed $content content.
	 * @param int   $submit_id submit id.
	 * @param int   $assignment_id assignment id.
	 *
	 * @return mixed
	 */
	public function filter_assignment_result( $content, $submit_id, $assignment_id ) {

		$max_mark   = tutor_utils()->get_assignment_option( $assignment_id, 'total_mark' );
		$pass_mark  = tutor_utils()->get_assignment_option( $assignment_id, 'pass_mark' );
		$given_mark = get_comment_meta( $submit_id, 'assignment_mark', true );
		$grade      = get_generated_gradebook( 'assignment', $assignment_id );

		ob_start();
		?>

		<div class="assignment-result-wrap">
			<h4><?php echo sprintf( __( 'You received %1$s points out of %2$s', 'tutor-pro' ), "<span class='received-marks'>{$given_mark}</span>", "<span class='out-of-marks'>{$max_mark}</span>" );//phpcs:ignore ?></h4>
			<h4 class="submitted-assignment-grade">
				<?php
				esc_html_e( 'Your grade is ', 'tutor-pro' );

				echo tutor_generate_grade_html( $grade );//phpcs:ignore
				echo $given_mark >= $pass_mark ? "<span class='submitted-assignment-grade-pass'> (" . __( 'Passed', 'tutor-pro' ) . ')</span>' : "<span class='submitted-assignment-grade-failed'> (" . __( 'Failed', 'tutor-pro' ) . ')</span>';//phpcs:ignore
				?>
			</h4>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Generate gradebook
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public function gradebook_generate( $course_id, $user_id ) {
		global $wpdb;

		$course_contents   = tutor_utils()->get_course_contents_by_id( $course_id );
		$previous_gen_item = get_generated_gradebook( 'all', $course_id );

		$require_gradding = array();

		// Prepare the posts that requires grading.
		if ( tutor_utils()->count( $course_contents ) ) {
			foreach ( $course_contents as $content ) {
				if ( 'tutor_quiz' === $content->post_type || 'tutor_assignments' === $content->post_type ) {
					$require_gradding[] = $content;
				}
			}
		}

		/**
		 * Delete if not exists
		 */
		if ( tutor_utils()->count( $previous_gen_item ) ) {
			$quiz_assignment_ids = wp_list_pluck( $require_gradding, 'ID' );

			if ( tutor_utils()->count( $quiz_assignment_ids ) ) {

				foreach ( $previous_gen_item as $previous_item ) {
					if ( $previous_item->quiz_id && ! in_array( $previous_item->quiz_id, $quiz_assignment_ids ) ) {
						$wpdb->delete( $wpdb->tutor_gradebooks_results, array( 'quiz_id' => $previous_item->quiz_id ) );
					}
					if ( $previous_item->assignment_id && ! in_array( $previous_item->assignment_id, $quiz_assignment_ids ) ) {
						$wpdb->delete( $wpdb->tutor_gradebooks_results, array( 'assignment_id' => $previous_item->assignment_id ) );
					}
				}
			} else {
				$wpdb->delete( $wpdb->tutor_gradebooks_results, array( 'course_id' => $course_id ) );
			}
		}

		// Check if there is anything to generate grade for.
		if ( ! tutor_utils()->count( $require_gradding ) ) {
			return;
		}

		// Regenerate grading.
		if ( tutor_utils()->count( $require_gradding ) ) {

			// Strip array indexes.
			$require_graddings = array_values( $require_gradding );

			// Loop through posts that needs grading.
			foreach ( $require_graddings as $course_item ) {
				$earned_percentage = 'pending';

				if ( 'tutor_quiz' === $course_item->post_type ) {
					// Grading for quiz.
					// Get Attempt by grading method.
					$attempt = tutor_utils()->get_quiz_attempt( $course_item->ID, $user_id );
					if ( $attempt ) {
						$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
					}
				} elseif ( 'tutor_assignments' === $course_item->post_type ) {
					// Grading for assignment.
					$submitted_info = tutor_utils()->is_assignment_submitted( $course_item->ID, $user_id );
					if ( ! $submitted_info || ! get_post_meta( $submitted_info->comment_ID, 'evaluate_time', true ) ) {
						// Skip if the assignment is not yet evaluated.
						continue;
					}
					if ( $submitted_info ) {
						$submitted_id = $submitted_info->comment_ID;
						$max_mark     = tutor_utils()->get_assignment_option( $submitted_info->comment_post_ID, 'total_mark', 10 );

						$given_mark = get_comment_meta( $submitted_id, 'assignment_mark', true );
						if ( $given_mark ) {
							$earned_percentage = $given_mark > 0 ? ( number_format( ( $given_mark * 100 ) / $max_mark ) ) : 0;
						}
					}
				}

				if ( $earned_percentage > 100 ) {
					$earned_percentage = 100;
				}

				//phpcs:disable
				if ( 'pending' === $earned_percentage ) {
					$gradebook = false;
				} else {
					$gradebook = $wpdb->get_row( "SELECT * FROM {$wpdb->tutor_gradebooks} WHERE percent_from <= {$earned_percentage} AND percent_to >= {$earned_percentage} ORDER BY gradebook_id ASC LIMIT 1  " );
				}

				if ( ! $gradebook ) {
					continue;
				}

				$gradebook_data   = array(
					'user_id'            => $user_id,
					'course_id'          => $course_id,
					'gradebook_id'       => $gradebook->gradebook_id,
					'grade_name'         => $gradebook->grade_name,
					'grade_point'        => $this->get_max_gradepoint(),
					'earned_grade_point' => $gradebook->grade_point,
					'earned_percent'     => $earned_percentage,
					'generate_date'      => date( 'Y-m-d H:i:s' ),
					'update_date'        => date( 'Y-m-d H:i:s' ),
				);
				$gradebook_result = false;

				if ( 'tutor_quiz' === $course_item->post_type ) {
					$gradebook_data['quiz_id']    = $course_item->ID;
					$gradebook_data['result_for'] = 'quiz';

					$gradebook_result = $wpdb->get_row(
						"SELECT * FROM {$wpdb->tutor_gradebooks_results} 
							WHERE result_for = 'quiz' 
							AND user_id = {$user_id} 
							AND course_id = {$course_id} 
							AND quiz_id = {$course_item->ID} "
					);

				} elseif ( 'tutor_assignments' === $course_item->post_type ) {
					$gradebook_data['assignment_id'] = $course_item->ID;
					$gradebook_data['result_for']    = 'assignment';

					$gradebook_result = $wpdb->get_row(
						"SELECT * FROM {$wpdb->tutor_gradebooks_results} 
							WHERE result_for = 'assignment' 
							AND user_id = {$user_id} 
							AND course_id = {$course_id} 
							AND assignment_id = {$course_item->ID} "
					);
				}

				if ( $gradebook_result ) {
					// Update Gradebook Result.
					unset( $gradebook_data['generate_date'] );
					$wpdb->update( $wpdb->tutor_gradebooks_results, $gradebook_data, array( 'gradebook_result_id' => $gradebook_result->gradebook_result_id ) );
				} else {
					$wpdb->insert( $wpdb->tutor_gradebooks_results, $gradebook_data );
				}
			}

			$results = $wpdb->get_row(
				"SELECT AVG(earned_percent) as earned_percent,
                AVG(earned_grade_point) as earned_grade_point
                FROM {$wpdb->tutor_gradebooks_results} 
                WHERE user_id = {$user_id} 
                AND course_id = {$course_id} 
                AND result_for !='final' "
			);

			if ( $results && $gradebook = get_gradebook_by_percent( $results->earned_percent ) ) {
				$gradebook_data = array(
					'user_id'            => $user_id,
					'course_id'          => $course_id,
					'gradebook_id'       => $gradebook->gradebook_id,
					'result_for'         => 'final',
					'grade_name'         => $gradebook->grade_name,
					'grade_point'        => $this->get_max_gradepoint(),
					'earned_grade_point' => number_format( $results->earned_grade_point, 2 ),
					'earned_percent'     => $results->earned_percent,
					'generate_date'      => date( 'Y-m-d H:i:s' ),
					'update_date'        => date( 'Y-m-d H:i:s' ),
				);

				$generated_final = $wpdb->get_row(
					"SELECT * FROM {$wpdb->tutor_gradebooks_results} 
                    WHERE user_id = {$user_id} AND course_id = {$course_id} AND result_for ='final' "
				);

				if ( $generated_final ) {
					unset( $gradebook_data['generate_date'], $gradebook_data['result_for'] );

					$wpdb->update(
						$wpdb->tutor_gradebooks_results,
						$gradebook_data,
						array( 'gradebook_result_id' => $generated_final->gradebook_result_id )
					);
				} else {
					$wpdb->insert( $wpdb->tutor_gradebooks_results, $gradebook_data );
				}
			}
		}
		//phpcs:enable
	}

	/**
	 * Gradebook view
	 *
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public function generate_gradebook_html( $course_id ) {
		require dirname( __DIR__ ) . '/views/gradebook.php';
	}

	/**
	 * Get quiz earned number percent.
	 *
	 * @param integer $quiz_id quiz id.
	 * @param integer $user_id user id.
	 *
	 * @return void
	 */
	public function get_quiz_earned_number_percent( $quiz_id = 0, $user_id = 0 ) {
		$quiz_grade_method = get_tutor_option( 'quiz_grade_method' );
		echo esc_html( $quiz_grade_method );
	}

	/**
	 * Course nav item
	 *
	 * @param array $items items.
	 * @param int   $course_id course id.
	 *
	 * @return array
	 */
	public function add_course_nav_item( $items, $course_id ) {
		if ( is_single() && get_the_ID() ) {
			$gading_content = get_grading_contents_by_course_id();

			if ( tutor_utils()->count( $gading_content ) ) {
				$items['gradebook'] = array(
					'title'             => __( 'Gradebook', 'tutor-pro' ),
					'method'            => array( $this, 'generate_gradebook_html' ),
					'require_enrolment' => true,
				);
			}
		}

		return $items;
	}

	/**
	 * Result list bulk action.
	 *
	 * @return void
	 */
	public function gradebook_result_list_bulk_actions() {
		tutor_utils()->checking_nonce( 'get' );

		$action = sanitize_text_field( tutor_utils()->array_get( 'action', $_GET ) );
		if ( '-1' === $action ) {
			return;
		}

		global $wpdb;
		$gradebooks_result_ids = tutor_utils()->array_get( 'gradebooks_result_ids', $_GET );

		if ( 'regenerate_gradebook' === $action ) {
			if ( tutor_utils()->count( $gradebooks_result_ids ) ) {
				foreach ( $gradebooks_result_ids as $result_id ) {
					$result = get_generated_gradebook( 'byID', $result_id );
					$this->gradebook_generate( $result->course_id, $result->user_id );
				}

				tutor_flash_set( 'success', __( 'Gradebook has been re-generated', 'tutor-pro' ) );
			}
		}

		if ( 'trash' === $action ) {
			if ( tutor_utils()->count( $gradebooks_result_ids ) ) {
				foreach ( $gradebooks_result_ids as $result_id ) {
					$result = get_generated_gradebook( 'byID', $result_id );
					$wpdb->delete(
						$wpdb->tutor_gradebooks_results,
						array(
							'user_id'   => $result->user_id,
							'course_id' => $result->course_id,
						)
					);
				}
				tutor_flash_set( 'warning', __( 'Gradebook has been deleted', 'tutor-pro' ) );
			}
		}

		wp_redirect( tutor_utils()->referer() );
		exit();
	}

	/**
	 * Delete gradebook on course retake
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public function delete_gradebook_on_retake( $course_id, $user_id ) {
		global $wpdb;
		$wpdb->delete(
			$wpdb->tutor_gradebooks_results,
			array(
				'user_id'   => $user_id,
				'course_id' => $course_id,
			)
		);
	}

	/**
	 * Import Sample Grade Data
	 */
	public function import_gradebook_sample_data() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$data = "INSERT INTO {$wpdb->tutor_gradebooks} (grade_name, grade_point, grade_point_to, percent_from, percent_to, grade_config) VALUES
                ('A+', '4.0', NULL, 90, 100, 'a:1:{s:11:\"grade_color\";s:7:\"#27ae60\";}'),
                ('A', '3.50', NULL, 80, 89, 'a:1:{s:11:\"grade_color\";s:7:\"#1bbc9b\";}'),
                ('A-', '3.0', NULL, 70, 79, 'a:1:{s:11:\"grade_color\";s:7:\"#43bca4\";}'),
                ('B+', '2.50', NULL, 60, 69, 'a:1:{s:11:\"grade_color\";s:7:\"#1f3a93\";}'),
                ('B', '2.0', NULL, 50, 59, 'a:1:{s:11:\"grade_color\";s:7:\"#2574a9\";}'),
                ('B-', '1.5', NULL, 40, 49, 'a:1:{s:11:\"grade_color\";s:7:\"#19b5fe\";}'),
                ('C', '1.0', NULL, 30, 39, 'a:1:{s:11:\"grade_color\";s:7:\"#9a13b3\";}'),
                ('F', '0.0', NULL, 0, 29, 'a:1:{s:11:\"grade_color\";s:7:\"#d71830\";}');";

		$wpdb->query( $data );//phpcs:ignore
		wp_send_json_success();
	}

}
