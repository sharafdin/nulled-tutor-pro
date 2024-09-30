<?php
/**
 * PaidMembershipsPro class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.3.5
 */

namespace TUTOR_BP;

if ( ! defined( 'ABSPATH' ) )
	exit;

class BuddyPressGroupSettings extends \BP_Group_Extension {

	public function __construct() {

		$args = array(
			'slug' => 'group-course-settings',
			'name' => __('Course Settings', 'tutor-pro'),
			'enable_nav_item'	=> false
		);
		parent::init( $args );
	}

	function display( $group_id = null ) {

	}

	/**
	 * settings_screen() is the catch-all method for displaying the content
	 * of the edit, create, and Dashboard admin panels
	 */
	function settings_screen( $group_id = NULL ) {
		$group_status = groups_get_groupmeta( $group_id, 'bp_course_attached', true );
		$activities = maybe_unserialize(groups_get_groupmeta($group_id, '_tutor_bp_group_activities', true));

		if ( !empty($courses) ) { ?>
			<div class="bp-learndash-group-course">
				<h4>Group Course</h4>


			</div><br><br/><br/>
			<?php
		}


		?>
		<div class="bp-learndash-course-activity-checkbox">

			<h4>Course Activities</h4>

			<p> <?php  _e('Which Tutor LMS activity should be displayed in this group?','tutor-pro'); ?></p>

			<div class="tutor-bp-group-activities">

				<label>
					<input type="checkbox" name="tutor_bp_group_activities[user_enrolled_course]" value="1" <?php echo $this->is_checked('user_enrolled_course', $activities)
					?> > <?php _e('User Enrolled a course', 'tutor-pro'); ?>
				</label>

				<label>
					<input type="checkbox" name="tutor_bp_group_activities[user_course_start]" value="1" <?php echo $this->is_checked('user_course_start', $activities)
					?> > <?php _e('User Starts a course', 'tutor-pro'); ?>
				</label>

				<label>
					<input type="checkbox" name="tutor_bp_group_activities[user_completed_course]" value="1" <?php echo $this->is_checked('user_completed_course', $activities)
					?> > <?php _e('User completes a course','tutor-pro'); ?>
				</label>

				<label>
					<input type="checkbox" name="tutor_bp_group_activities[user_creates_lesson]" value="1" <?php echo $this->is_checked('user_creates_lesson', $activities)
					?> > <?php _e('User creates a lesson', 'tutor-pro'); ?>
				</label>
				<label>
					<input type="checkbox" name="tutor_bp_group_activities[user_updated_lesson]" value="1" <?php echo $this->is_checked('user_updated_lesson', $activities)
					?> > <?php _e('User updated a lesson', 'tutor-pro'); ?>
				</label>


                <label>
                    <input type="checkbox" name="tutor_bp_group_activities[user_started_quiz]" value="1" <?php echo $this->is_checked('user_started_quiz', $activities)
					?> > <?php _e('User started quiz', 'tutor-pro'); ?>
                </label>
                <label>
                    <input type="checkbox" name="tutor_bp_group_activities[user_finished_quiz]" value="1" <?php echo $this->is_checked('user_finished_quiz', $activities)
					?> > <?php _e('User finished quiz', 'tutor-pro'); ?>
                </label>

			</div>
		</div><br/>
		<?php

	}

	/**
	 * settings_screen_save() contains the catch-all logic for saving
	 * settings from the edit, create, and Dashboard admin panels
	 */
	function settings_screen_save( $group_id = NULL ) {
		$tutor_bp_course_activities = tutor_utils()->array_get('tutor_bp_group_activities', $_POST);
		groups_update_groupmeta( $group_id, '_tutor_bp_group_activities', $tutor_bp_course_activities );
	}

	/**
	 * @param $value
	 * @param $array
	 *
	 * @return string
     *
     * Checked based on given value
	 */
	public function is_checked( $value , $array ) {
		$checked = '';
		if ( is_array($array) && array_key_exists( $value, $array ) ) {
			$checked = 'checked';
		}
		return $checked;
	}




}