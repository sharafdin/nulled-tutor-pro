<?php
/**
 * Manage topics google events
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\TopicsEvent
 */

namespace TutorPro\GoogleMeet\TopicsEvent;

use TutorPro\GoogleMeet\GoogleEvent\GoogleEvent;
use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Models\EventsModel;
use TutorPro\GoogleMeet\Utilities\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Manage topics event create & lists
 */
class TopicsEvent {
	/**
	 * Register hooks
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		add_action( 'tutor_course_builder_after_btn_group', array( $this, 'add_meeting_option_in_topic' ), 15, 2 );
		add_filter( 'tutor_course_contents_post_types', array( $this, 'tutor_course_contents_post_types' ), 100 );
		add_action( 'tutor/course/builder/content/tutor-google-meet', array( $this, 'render_topic_events' ), 100, 4 );
	}

	/**
	 * Register live meet create button on topic
	 *
	 * @param int $topic_id  topic id.
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public function add_meeting_option_in_topic( $topic_id, $course_id ) {
		$google_client = new GoogleEvent();
		$plugin_data   = GoogleMeet::meta_data();
		if ( $google_client->is_app_permitted() ) {
			?>
			<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" data-tutor-modal-target="tutor-google-meet-topic-modal-<?php echo esc_attr( $topic_id ); ?>">
				<i class="tutor-icon-brand-google-meet tutor-mr-8" area-hidden="true"></i>
				<?php esc_html_e( 'Meet Live Lesson', 'tutor-pro' ); ?>
			</button>
			<?php
			Utilities::load_template_as_modal(
				'tutor-google-meet-topic-modal-' . $topic_id,
				$plugin_data['views'] . 'modal/meeting-create-update.php',
				__( 'Google Meet', 'tutor-pro' ),
				array(
					array(
						'label' => __( 'Cancel', 'tutor-pro' ),
						'class' => 'tutor-btn tutor-btn-outline-primary',
						'id'    => '',
						'type'  => 'button',
						'attr'  => 'data-tutor-modal-close',
					),
					array(
						'label' => __( 'Create Meeting', 'tutor-pro' ),
						'class' => 'tutor-btn tutor-btn-primary tutor-gm-create-new-meeting',
						'id'    => '',
						'type'  => 'submit',
					),
				),
				array(
					'course_id' => $topic_id,
				),
				'',
				'tutor-gm-topic-create-modal'
			);
		}
	}

	/**
	 * Add tutor-google-meet post type as course
	 * content post types
	 *
	 * It will add a hook course builder topic area so that
	 * we can execute our functions
	 *
	 * @param array $post_types  tutor available post types.
	 *
	 * @return array
	 */
	public function tutor_course_contents_post_types( array $post_types ): array {
		$post_types[] = EventsModel::POST_TYPE;
		return $post_types;
	}

	/**
	 * Render topics events list
	 *
	 * @param object $event  event post content.
	 * @param object $topic topic post content.
	 * @param int    $course_id  course id.
	 * @param int    $counter counter index.
	 *
	 * @return void
	 */
	public function render_topic_events( $event, $topic, $course_id, $counter ) {
		$plugin_data = GoogleMeet::meta_data();
		$topic_event = $plugin_data['views'] . 'topic/content.php';
		if ( file_exists( $topic_event ) ) {
			tutor_load_template_from_custom_path(
				$topic_event,
				array(
					'event'         => $event,
					'counter_index' => $counter,
				),
				false
			);
		} else {
			echo esc_html( $topic_event . ' file not exist' );
		}
	}
}
