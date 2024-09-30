<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Init filter hooks
 *
 * @package TutorPro\Filter
 *
 * @since v2.0.9
 */

namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains filter hooks
 */
class Filters {

	/**
	 * Register hooks
	 */
	public function __construct() {
		add_filter( 'tutor_qna_text_editor', __CLASS__ . '::filter_text_editor' );
		// Filter MCE buttons.
		add_filter( 'mce_external_plugins', __CLASS__ . '::filter_external_plugins' );
		add_filter( 'tutor_course_details_sidebar_attr', array( $this, 'course_details_sidebar_attr' ) );
		add_filter( 'tutor_validate_lesson_complete', array( $this, 'validate_video_lesson_complete' ), 10, 3 );
	}

	/**
	 * Restrict user to complete a video lesson if not watched required percentage.
	 *
	 * @since 2.2.4
	 *
	 * @param bool $validated validation status.
	 * @param int  $user_id user id.
	 * @param int  $lesson_id lesson id.
	 *
	 * @return bool
	 */
	public function validate_video_lesson_complete( $validated, $user_id, $lesson_id ) {
		$video_info = tutor_utils()->get_video_info( $lesson_id );
		$source_key = is_object( $video_info ) && 'html5' !== $video_info->source ? 'source_' . $video_info->source : null;
		$has_source = ( is_object( $video_info ) && $video_info->source_video_id ) || ( isset( $source_key ) ? $video_info->$source_key : null );

		if ( $has_source ) {
			$completion_mode                 = tutor_utils()->get_option( 'course_completion_process' );
			$is_strict_mode                  = ( 'strict' === $completion_mode );
			$control_video_lesson_completion = (bool) tutor_utils()->get_option( 'control_video_lesson_completion', false );
			$required_percentage             = (int) tutor_utils()->get_option( 'required_percentage_to_complete_video_lesson', 80 );
			$video_duration                  = floatval( $video_info->duration_sec ?? 0 );

			$best_watch_time = tutor_utils()->get_lesson_reading_info( $lesson_id, $user_id, 'video_best_watched_time' );
			if ( $is_strict_mode && $control_video_lesson_completion && $video_duration &&  $best_watch_time ) {
				$watched_percentage = 0;
				try {
					$watched_percentage = ( $best_watch_time / $video_duration ) * 100;
				} catch ( \Exception $e ) {
					$watched_percentage = 0;
				}

				if ( $watched_percentage < $required_percentage ) {
					return false;
				}
			}
		}

		return $validated;
	}

	/**
	 * Extend course sidebar attribute.
	 *
	 * @since 2.2.3
	 *
	 * @param mixed $attr_str attributes in string.
	 *
	 * @return string
	 */
	public function course_details_sidebar_attr( $attr_str ) {
		$is_tutor_sticky_sidebar = tutor_utils()->get_option( 'enable_sticky_sidebar', false, true, true );
		if ( $is_tutor_sticky_sidebar ) {
			return 'data-tutor-sticky-sidebar';
		}
		return $attr_str;
	}

	/**
	 * For pro user show wp_editor
	 *
	 * @param string $editor  editor to filter.
	 *
	 * @return string  wp_editor
	 */
	public static function filter_text_editor( string $editor ) {
		ob_start();
		wp_editor(
			'',
			'tutor_qna_text_editor',
			tutor_utils()->text_editor_config(
				array(
					'plugins' => 'codesample',
					'tinymce' => array(
						'toolbar1' => 'bold,italic,underline,link,unlink,removeformat,image,bullist,codesample',
						'toolbar2' => '',
						'toolbar3' => '',
					),
				)
			)
		);
		return ob_get_clean();
	}

	/**
	 * Load codesample external TinyMCE plugin
	 *
	 * It will load on the single_course page only
	 *
	 * @since v2.0.10
	 *
	 * @param array $plugins  available plugins.
	 *
	 * @return return  associative array (key => plugin url)
	 */
	public static function filter_external_plugins( array $plugins ) {
		if ( is_single_course() ) {
			$plugins['codesample'] = 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.1.2/plugins/codesample/plugin.min.js';
		}
		return $plugins;
	}
}
