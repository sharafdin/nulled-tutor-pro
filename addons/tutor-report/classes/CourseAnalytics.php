<?php
/**
 * Handle all course analytics related data
 * 
 * @since  1.9.9
 */
namespace TUTOR_REPORT;

defined( 'ABSPATH' ) || exit;
class CourseAnalytics {

    public function __construct()
    {
        
    }

    /**
     * Get course enrollment list with student info
     * 
     * @param $course_id int | required
     * 
     * @period string | optional ( today | monthly | yearly ) if not provide then it will 
     * 
     * retrieve all records
     * 
     * @param $start_date string | optional 
     * 
     * @param $end_date string | optional
     * 
     * @return array
     * 
     * @since 1.9.9
     */
    public static function course_enrollments_with_student_details( int $course_id ) {
		global $wpdb;
        $course_id          = sanitize_text_field( $course_id );
        $course_completed   = 0;
        $course_inprogress  = 0;

		$enrollments = $wpdb->get_results($wpdb->prepare(
			"SELECT enroll.ID AS enroll_id, enroll.post_author AS enroll_author, user.*, course.ID AS course_id
                FROM {$wpdb->posts} AS enroll
                LEFT JOIN {$wpdb->users} AS user ON user.ID = enroll.post_author
                LEFT JOIN {$wpdb->posts} AS course ON course.ID = enroll.post_parent
                WHERE enroll.post_type = %s
                    AND enroll.post_status = %s
                    AND enroll.post_parent = %d
			",
			'tutor_enrolled',
			'completed',
			$course_id
		) );

        foreach( $enrollments as $enrollment ) {
            $course_progress = tutor_utils()->get_course_completed_percent( $course_id, $enrollment->enroll_author);
            if ( $course_progress == 100 ) {
                $course_completed++;
            } else {
                $course_inprogress++;
            }
        }

        return array(
            'enrollments'       => $enrollments,
            'total_completed'   => $course_completed,
            'total_inprogress'  => $course_inprogress,
            'total_enrollments' => count( $enrollments )
        );
    } 

    public static function course_question_answer( int $course_id): array {
        global $wpdb;
        $course_id          = sanitize_text_field( $course_id );
        $course_post_type 	= tutor()->course_post_type;

        $qa = $wpdb->get_results($wpdb->prepare(
            "SELECT post.ID AS course_id, post.post_title AS course_title, c.* 
                FROM {$wpdb->posts} AS post 
                    INNER JOIN {$wpdb->comments} AS c ON c.comment_post_ID = post.ID AND c.comment_type = %s
                    INNER JOIN {$wpdb->users} AS u ON u.ID = c.user_id
                WHERE post_type = %s
                AND post_status = %s 
                AND post.ID = %d 
            ",
            'tutor_q_and_a',    
            $course_post_type,
            'publish',
            $course_id
        ));
        
        return array(
            'question_answers'  => $qa,
            'total_q_a'         => count($qa)
        );
    } 

    public static function submitted_assignment_by_course( int $course_id ): array {
        global $wpdb;
        $course_id   = sanitize_text_field( $course_id );
        $assignments = $wpdb->get_results($wpdb->prepare(
            "SELECT c.* 
                FROM {$wpdb->comments} AS c 
                    INNER JOIN {$wpdb->posts} AS assignment ON assignment.ID = c.comment_post_ID 
                    INNER JOIN {$wpdb->posts} AS topic ON topic.ID = assignment.post_parent 
                    INNER JOIN {$wpdb->posts} AS post ON post.ID = topic.post_parent 
                WHERE post.ID = %d 
                AND c.comment_type = %s 
                AND c.comment_approved = %s
            ",
            $course_id,
            'tutor_assignment',
            'submitted'
        ));
        
        return array(
            'assignments'       => $assignments,
            'total_assignments' => count($assignments)
        );
    }  

}
