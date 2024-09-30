<?php
/**
 * BuddyPress class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.3.5
 */

namespace TUTOR_BP;

if ( ! defined( 'ABSPATH' ) )
	exit;

class BuddyPressMessages {

	public function __construct() {
		/**
		 * BuddyPress Message Header
		 */
		add_action('bp_before_message_thread_content', array($this, 'bp_before_message_thread_content'), 99);
		add_action('wp_ajax_tutor_bp_retrieve_user_records_for_thread', array($this, 'tutor_bp_retrieve_user_records_for_thread'));
	}

	/**
	 * BuddyPress Message Thread Header
	 *
	 * @since v.1.5.0
	 */

	public function bp_before_message_thread_content(){
		global $wp_query;
		$thread_id = (int) tutor_utils()->array_get('query.page', $wp_query);

		echo '<div id="tutor-bp-thread-wrap">';
		echo $this->generate_before_message_thread($thread_id);
		echo '</div>';
	}

	public function generate_before_message_thread($message_thread_id = 0){
		if ($message_thread_id) {
			$recipients      = \BP_Messages_Thread::get_recipients_for_thread( $message_thread_id );
			$current_user_id = get_current_user_id();
			if ( isset( $recipients[ $current_user_id ] ) ) {
				unset( $recipients[ $current_user_id ] );
			}

			if ( tutor_utils()->count( $recipients ) ) {
				ob_start();
				tutor_load_template( 'buddypress.message_thread_recipients', compact( 'recipients' ), true );
				return ob_get_clean();
			}
		}
		return '';
	}

	public function tutor_bp_retrieve_user_records_for_thread(){
		tutor_utils()->checking_nonce();

		$thread_id = (int) tutor_utils()->array_get('thread_id', $_POST);
		if ($thread_id){
			wp_send_json_success(array('thread_head_html' => $this->generate_before_message_thread($thread_id) ));
		}
		wp_send_json_error();
	}

}