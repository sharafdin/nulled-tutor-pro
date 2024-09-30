<?php
/**
 * Quiz Export Import class
 *
 * @package TutorPro/Addons
 * @subpackage QuizImportExport
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.5.6
 */

namespace QUIZ_IMPORT_EXPORT;

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class QuizImportExport
 */
class QuizImportExport {

	/**
	 * Register hooks
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'quiz_script_backend_callback' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'quiz_script_frontend_callback' ) );
		add_action( 'wp_ajax_quiz_export_data', array( $this, 'quiz_export_data_callback' ) );
		add_action( 'wp_ajax_quiz_import_data', array( $this, 'quiz_import_data' ) );
		add_action( 'tutor_course_builder_before_quiz_btn_action', array( $this, 'quiz_action_button_callback' ) );
		add_action( 'tutor_course_builder_after_btn_group', array( $this, 'quiz_import_button_callback' ), 99 );
	}

	/**
	 * Import button callback
	 *
	 * @param int $topic_id topic id.
	 *
	 * @return void
	 */
	public function quiz_import_button_callback( $topic_id ) {
		?>
		<span class="tutor-import-quiz-button">
			<input name="csv_file" class="tutor-csv-file" data-topic="<?php echo esc_attr( $topic_id ); ?>" type="file" accept=".csv" />
			<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
				<i class="tutor-icon-import-o tutor-fs-6 tutor-mr-8" area-hidden="true"></i>
				<?php esc_html_e( 'Import Quiz', 'tutor-pro' ); ?>
			</button>
		</span>
		<?php
	}

	/**
	 * Quiz action button callback
	 *
	 * @param string $quiz_id quiz id.
	 *
	 * @return void
	 */
	public function quiz_action_button_callback( $quiz_id = '' ) {
		if ( $quiz_id ) {
			?>
			<a href="#quiz-builder-export" class="btn-csv-download tutor-iconic-btn" data-id="<?php echo esc_attr( $quiz_id ); ?>">
				<span class="tutor-icon-export" area-hidden="true"></span>
			</a>
			<?php
		}
	}

	/**
	 * Quiz script for backend.
	 *
	 * @param string $hook hook.
	 *
	 * @return void
	 */
	public function quiz_script_backend_callback( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}
		wp_enqueue_script( 'quiz-import-export', QUIZ_IMPORT_EXPORT()->url . 'assets/js/quiz-import-export.js', array(), QUIZ_IMPORT_EXPORT()->version, true );
		wp_enqueue_style( 'quiz-import-export', QUIZ_IMPORT_EXPORT()->url . 'assets/css/quiz-import-export.css', array(), QUIZ_IMPORT_EXPORT()->version );
	}

	/**
	 * Quiz script for frontend.
	 *
	 * @return void
	 */
	public function quiz_script_frontend_callback() {
		//phpcs:ignore
		if ( strpos( $_SERVER['REQUEST_URI'], 'create-course' ) !== false ) {
			wp_enqueue_script( 'quiz-import-export', QUIZ_IMPORT_EXPORT()->url . 'assets/js/quiz-import-export.js', array(), QUIZ_IMPORT_EXPORT()->version, true );
			wp_enqueue_style( 'quiz-import-export', QUIZ_IMPORT_EXPORT()->url . 'assets/css/quiz-import-export.css', array(), QUIZ_IMPORT_EXPORT()->version );
		}
	}

	/**
	 * Quiz Export Data
	 *
	 * @return void
	 */
	public function quiz_export_data_callback() {
		tutor_utils()->checking_nonce();

		$user_id   = get_current_user_id();
		$quiz_id   = Input::post( 'quiz_id' );
		$course_id = tutor_utils()->get_course_id_by( 'quiz', $quiz_id );

		if ( ! tutor_utils()->can_user_edit_course( $user_id, $course_id ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		global $wpdb;

		if ( $quiz_id ) {
			// Get all questions under the quiz.
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT question_id, 
									question_title, 
									question_description,
									answer_explanation,
									question_type, 
									question_mark, 
									question_settings, 
									question_order
							FROM {$wpdb->prefix}tutor_quiz_questions
							WHERE quiz_id = %d",
					$quiz_id
				)
			);

			// Place holder array for final data.
			$final_data = array();

			$content_post = get_post( $quiz_id );
			$meta         = get_post_meta( $quiz_id, 'tutor_quiz_option', true );

			$_temp = array();

			$_temp[]      = 'settings';
			$_temp[]      = '"' . addslashes( $content_post->post_title ) . '"';
			$_temp[]      = '"' . addslashes( str_replace( array( "\r\n", "\n", "\r" ), '\n', $content_post->post_content ) ) . '"';
			$_temp[]      = isset( $meta['time_limit']['time_value'] ) ? $meta['time_limit']['time_value'] : '';
			$_temp[]      = isset( $meta['time_limit']['time_type'] ) ? $meta['time_limit']['time_type'] : '';
			$_temp[]      = isset( $meta['hide_quiz_time_display'] ) ? $meta['hide_quiz_time_display'] : '';
			$_temp[]      = isset( $meta['attempts_allowed'] ) ? $meta['attempts_allowed'] : '';
			$_temp[]      = isset( $meta['passing_grade'] ) ? $meta['passing_grade'] : '';
			$_temp[]      = isset( $meta['max_questions_for_answer'] ) ? $meta['max_questions_for_answer'] : '';
			$_temp[]      = isset( $meta['quiz_auto_start'] ) ? $meta['quiz_auto_start'] : '';
			$_temp[]      = isset( $meta['question_layout_view'] ) ? $meta['question_layout_view'] : '';
			$_temp[]      = isset( $meta['questions_order'] ) ? $meta['questions_order'] : '';
			$_temp[]      = isset( $meta['hide_question_number_overview'] ) ? $meta['hide_question_number_overview'] : '';
			$_temp[]      = isset( $meta['short_answer_characters_limit'] ) ? $meta['short_answer_characters_limit'] : '';
			$final_data[] = $_temp;

			if ( ! empty( $results ) ) {

				// Loop through every single question to get answers.
				foreach ( $results as $key => $value ) {
					$temp = array();

					// Get the answers for the question.
					$question_results = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT answer_title, 
								answer_view_format, 
								is_correct, 
								image_id, 
								answer_two_gap_match, 
								answer_order
						FROM {$wpdb->prefix}tutor_quiz_question_answers
						WHERE belongs_question_id = %d
							AND belongs_question_type=%s",
							$value->question_id,
							$value->question_type
						)
					);

					$settings = maybe_unserialize( $value->question_settings );

					$temp[] = 'question';
					$temp[] = '"' . addslashes( $value->question_title ) . '"';
					$temp[] = '"' . str_replace( array( "\r\n", "\n", "\r" ), '\n', addslashes( $value->question_description ) ) . '"';
					$temp[] = $value->question_type;
					$temp[] = $value->question_mark;
					$temp[] = $value->question_order;
					$temp[] = isset( $settings['answer_required'] ) ? 1 : '';
					$temp[] = isset( $settings['randomize_question'] ) ? 1 : '';
					$temp[] = isset( $settings['show_question_mark'] ) ? 1 : '';
					$temp[] = '"' . str_replace( array( "\r\n", "\n", "\r" ), '\n', addslashes( $value->answer_explanation ) ) . '"'; // Index Position 9.

					$final_data[] = $temp;

					if ( ! empty( $question_results ) ) {

						// Loop through answers and assign answer meta data to the exportable array.
						foreach ( $question_results as $key => $value ) {
							$answer_temp   = array();
							$answer_temp[] = 'answer';
							$answer_temp[] = '"' . addslashes( $value->answer_title ) . '"';
							$answer_temp[] = $value->answer_view_format;
							$answer_temp[] = $value->is_correct;
							$answer_temp[] = $value->image_id;
							$answer_temp[] = $value->answer_two_gap_match;
							$answer_temp[] = $value->answer_order;
							$final_data[]  = $answer_temp;
						}
					}
				}
			}

			wp_send_json_success(
				array(
					'title'            => $content_post->post_title,
					'output_quiz_data' => $final_data,
				)
			);

		} else {
			wp_send_json_error();
		}

		die();
	}


	/**
	 * Quiz Import Data
	 *
	 * @return void
	 */
	public function quiz_import_data() {
		tutor_utils()->checking_nonce();

		global $wpdb;

		$file_type = '';
		$file_size = 0;
		$has_csv   = false;
		$file_name = '';

		if ( isset( $_FILES['csv_file'] ) ) {
			$has_csv   = true;
			$csv = $_FILES['csv_file']; //phpcs:ignore
			$file_name = sanitize_text_field( $csv['tmp_name'] );
			$file_type = sanitize_text_field( $csv['type'] );
			$file_size = sanitize_text_field( $csv['size'] );
		}

		if ( $has_csv
			&& in_array( $file_type, array( 'text/csv', 'application/vnd.ms-excel' ), true )
			&& $file_size > 0 ) {

			$topic_id = Input::post( 'topic_id' );

			if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor-pro' ) ) );
			}

			$file    = fopen( $file_name, 'r' );
			$quiz_id   = $question_id = $question_type = $quiz_title = ''; //phpcs:ignore

			// Read each line from CSV file.
			while ( ( $column = fgetcsv( $file, $file_size, ',' ) ) !== false ) {

				if ( 'settings' === $column[0] ) {
					$next_order_id = tutor_utils()->get_next_course_content_order_id( $topic_id );
					$quiz_title    = stripslashes( $column[1] );

					$quiz_id = wp_insert_post(
						array(
							'post_type'    => 'tutor_quiz',
							'post_name'    => tutor_utils()->get_unique_slug( sanitize_title( $quiz_title, 'untitled-quiz' ) ),
							'post_title'   => $quiz_title,
							'post_content' => stripslashes( str_replace( '\\n', PHP_EOL, $column[2] ) ),
							'post_status'  => 'publish',
							'post_author'  => get_current_user_id(),
							'post_parent'  => $topic_id,
							'menu_order'   => $next_order_id,
						)
					);

					$_time_value = isset( $column[3] ) ? $column[3] : '';
					$_time_type  = isset( $column[4] ) ? $column[4] : '';
					$_time       = isset( $column[5] ) ? $column[5] : '';
					$_attempts   = isset( $column[6] ) ? $column[6] : '';
					$_grade      = isset( $column[7] ) ? $column[7] : '';
					$_max_q      = isset( $column[8] ) ? $column[8] : '';
					$_start      = isset( $column[9] ) ? $column[9] : '';
					$_layout     = isset( $column[10] ) ? $column[10] : '';
					$_order      = isset( $column[11] ) ? $column[11] : '';
					$_overview   = isset( $column[12] ) ? $column[12] : '';
					$_limit      = isset( $column[13] ) ? $column[13] : '';

					if ( $_time_value || $_time_type || $_time || $_attempts || $_grade || $_max_q || $_start || $_layout || $_order || $_overview || $_limit ) {
						$temp = array();
						if ( $_time_value || $_time_type ) {
							$temp['time_limit']                    = array(
								'time_value' => $_time_value ? $_time_value : '0',
								'time_type'  => $_time_type ? $_time_type : 'minutes',
							);
							$temp['attempts_allowed']              = $_attempts ? $_attempts : 10;
							$temp['passing_grade']                 = $_grade ? $_grade : 80;
							$temp['max_questions_for_answer']      = $_max_q ? $_max_q : 10;
							$temp['question_layout_view']          = $_layout ? $_layout : '';
							$temp['questions_order']               = $_order ? $_order : 'rand';
							$temp['short_answer_characters_limit'] = $_limit ? $_limit : 200;
							$temp['hide_quiz_time_display']        = $_time ? $_time : 0;
							$temp['quiz_auto_start']               = $_start ? $_start : 0;
							$temp['hide_question_number_overview'] = $_overview ? $_overview : 0;

							update_post_meta( $quiz_id, 'tutor_quiz_option', $temp );
						}
					}
				}

				if ( 'question' === $column[0] ) {
					$question_type      = $column[3];
					$answer_explanation = isset( $column[9] ) ? stripslashes( str_replace( '\\n', PHP_EOL, $column[9] ) ) : '';
					$question_data      = array(
						'quiz_id'              => $quiz_id,
						'question_title'       => stripslashes( $column[1] ),
						'question_description' => stripslashes( str_replace( '\\n', PHP_EOL, $column[2] ) ),
						'answer_explanation'   => $answer_explanation,
						'question_type'        => $question_type,
						'question_mark'        => $column[4],
						'question_settings'    => maybe_serialize(
							array(
								'question_type'      => $column[3],
								'answer_required'    => $column[6],
								'randomize_question' => $column[7],
								'question_mark'      => $column[4],
								'show_question_mark' => $column[8],
							)
						),
						'question_order'       => $column[5],
					);
					$wpdb->insert( $wpdb->prefix . 'tutor_quiz_questions', $question_data );
					$question_id = $wpdb->insert_id;
				}

				if ( 'answer' === $column[0] ) {
					$answer_data = array(
						'belongs_question_id'   => $question_id,
						'belongs_question_type' => $question_type,
						'answer_title'          => stripslashes( $column[1] ),
						'is_correct'            => $column[3],
						'image_id'              => $column[4],
						'answer_two_gap_match'  => $column[5],
						'answer_view_format'    => $column[2],
						'answer_settings'       => '',
						'answer_order'          => $column[6],
					);
					$wpdb->insert( $wpdb->prefix . 'tutor_quiz_question_answers', $answer_data );
				}
			}

			ob_start();
			tutor_load_template_from_custom_path(
				tutor()->path . '/views/fragments/quiz-list-single.php',
				array(
					'quiz_id'    => $quiz_id,
					'topic_id'   => $topic_id,
					'quiz_title' => get_the_title( $quiz_id ),
				),
				false
			);

			wp_send_json_success( array( 'html' => ob_get_clean() ) );

		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid File', 'tutor-pro' ) ) );
		}
	}
}
