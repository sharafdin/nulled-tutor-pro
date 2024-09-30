<?php
/**
 * Manage event database operations
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Models
 */

namespace TutorPro\GoogleMeet\Models;

use Tutor\Helpers\QueryHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Execute events database query
 */
class EventsModel {

	/**
	 * Post type
	 *
	 * @since v2.1.0
	 *
	 * @var string
	 */
	const POST_TYPE = 'tutor-google-meet';

	/**
	 * Events post meta keys
	 *
	 * Will store start-end datetime & event details on separate meta key
	 *
	 * @since v2.1.0
	 *
	 * @var array
	 */
	const POST_META_KEYS = array(
		'tutor-google-meet-start-datetime',
		'tutor-google-meet-end-datetime',
		'tutor-google-meet-event-details',
		'tutor-google-meet-link',
	);

	/**
	 * Undocumented function
	 *
	 * @param string $context  active or expired, active will
	 * ongoing & currently active meeting.
	 *
	 * @param array  $sorting_args sorting args supported index:
	 *  course_id, search_term, author_id(comma separated: 1,2), date.
	 *  For ex: [course_id => 1, search => abc].
	 *  Note: date must be this format: YYYY-MM-DD.
	 *
	 * @param array  $paging_args pagination args for ex:
	 *  [limit => 10, offset => 0 ].
	 *
	 * @return array
	 */
	public static function get( string $context, array $sorting_args, array $paging_args, $only_course_meeting = false ): array {
		global $wpdb;

		$context     = $context;
		$course_id   = $sorting_args['course_id'];
		$search_term = $sorting_args['search_term'];
		$author_id   = $sorting_args['author_id'];
		$date        = $sorting_args['date'];
		$limit       = $paging_args['limit'];
		$offset      = $paging_args['offset'];

		$course_type = tutor()->course_post_type;
		$topic_type  = tutor()->topics_post_type;

		$context_clause = '';
		if ( 'active' === $context ) {
			$context_clause = 'AND NOW() < end_date.meta_value';
		} elseif ( 'expired' === $context ) {
			$context_clause = 'AND NOW() > end_date.meta_value';
		}

		$course_clause = '';
		if ( '' !== $course_id ) {
			if ( $only_course_meeting ) {
				$course_clause = "AND course.ID = $course_id";
			} else {
				$course_clause = "AND course.ID = $course_id OR course.post_parent = $course_id";
			}
		}

		$search_clause = 'AND ( course.post_title LIKE %s OR meeting.post_title LIKE %s )';
		$search_term   = '%' . $wpdb->esc_like( $search_term ) . '%';

		$author_clause = '';
		if ( '' !== $author_id ) {
			$author_clause = "AND meeting.post_author IN ( $author_id )";
		}

		$date_clause = '';
		if ( '' !== $date ) {
			$date_clause = "AND ( DATE(start_date.meta_value) = CAST( '$date' AS DATE ) OR DATE(end_date.meta_value) = CAST( '$date' AS DATE ) )";
		}

		// Get the meetings from Database
		$meetings    = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				meeting.ID,
				meeting.post_author,
				meeting.post_title,
				meeting.post_content,
				meeting.post_parent,
				meeting.post_type,
				meeting.post_name,
				start_date.meta_value,
				end_date.meta_value,
				event_details.meta_value AS event_details,
				(
				CASE 
					WHEN NOW() BETWEEN start_date.meta_value AND end_date.meta_value THEN 'ongoing'
					WHEN NOW() > end_date.meta_value THEN 'expired'
					ELSE 'start_meeting'
				END) AS meeting_status

				FROM {$wpdb->posts} AS meeting

				INNER JOIN {$wpdb->postmeta} AS start_date
					ON start_date.post_id = meeting.ID
					AND start_date.meta_key = 'tutor-google-meet-start-datetime'

				INNER JOIN {$wpdb->postmeta} AS end_date
					ON end_date.post_id = meeting.ID
					AND end_date.meta_key = 'tutor-google-meet-end-datetime'

				INNER JOIN {$wpdb->postmeta} AS event_details
					ON event_details.post_id = meeting.ID
					AND event_details.meta_key = 'tutor-google-meet-event-details'

				INNER JOIN {$wpdb->posts} AS course
					ON course.ID = meeting.post_parent

				WHERE 1 = %d
					{$context_clause}
					{$author_clause}
					{$course_clause}
					{$date_clause}
					{$search_clause}

				ORDER BY end_date.meta_value ASC

				LIMIT %d, %d
				",
				1,
				$search_term,
				$search_term,
				$offset,
				$limit
			)
		);
		$total_found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				
				FROM {$wpdb->posts} AS meeting

				INNER JOIN {$wpdb->postmeta} AS start_date
					ON start_date.post_id = meeting.ID
					AND start_date.meta_key = 'tutor-google-meet-start-datetime'

				INNER JOIN {$wpdb->postmeta} AS end_date
					ON end_date.post_id = meeting.ID
					AND end_date.meta_key = 'tutor-google-meet-end-datetime'

				INNER JOIN {$wpdb->posts} AS course
					ON course.ID = meeting.post_parent
					
				WHERE 1 = %d
					{$context_clause}
					{$author_clause}
					{$course_clause}
					{$date_clause}
					{$search_clause}
				",
				1,
				$search_term,
				$search_term
			)
		);

		$result = array(
			'meetings'    => $meetings,
			'total_found' => $total_found,
		);
		return $result;
	}

	/**
	 * Insert event post data
	 *
	 * @since v2.1.0
	 *
	 * @param array $post_args post argument for creating post.
	 *
	 * @return mixed  on success post id or WP_Error on failure
	 */
	public static function insert( array $post_args ) {
		return wp_insert_post( $post_args );
	}

	/**
	 * Update event post data
	 *
	 * @since v2.1.0
	 *
	 * @param array $post_args post argument for creating post.
	 *
	 * @return mixed  on success post id or WP_Error on failure
	 */
	public static function update( array $post_args ) {
		return wp_update_post( $post_args );
	}

	/**
	 * Update meeting start datetime meta value
	 *
	 * @since v2.1.0
	 *
	 * @param int    $post_id  post ID.
	 * @param string $meta_value post meta value.
	 *
	 * @return void
	 */
	public static function update_start_datetime( int $post_id, string $meta_value ) {
		update_post_meta( $post_id, self::POST_META_KEYS[0], $meta_value );
	}

	/**
	 * Update meeting end datetime meta value
	 *
	 * @since v2.1.0
	 *
	 * @param int    $post_id  post ID.
	 * @param string $meta_value post meta value.
	 *
	 * @return void
	 */
	public static function update_end_datetime( int $post_id, string $meta_value ) {
		update_post_meta( $post_id, self::POST_META_KEYS[1], $meta_value );
	}

	/**
	 * Update meeting details meta value
	 *
	 * @since v2.1.0
	 *
	 * @param int   $post_id  post ID.
	 * @param mixed $meta_value post meta value.
	 *
	 * @return void
	 */
	public static function update_meeting_details( int $post_id, $meta_value ) {
		update_post_meta( $post_id, self::POST_META_KEYS[2], $meta_value );
	}

	/**
	 * Update all post meta
	 *
	 * @since v2.1.0
	 *
	 * @param int   $post_id  post ID.
	 * @param array $meta_value values array, ex: array(value1,value1,value3).
	 *
	 * @return void
	 */
	public static function update_all_meta( int $post_id, array $meta_value ) {
		foreach ( self::POST_META_KEYS as $key => $meta_key ) {
			update_post_meta( $post_id, $meta_key, $meta_value[ $key ] );
		}
	}

	/**
	 * Delete tutor-google-meet post event with meta
	 * data, permanently.
	 *
	 * @since v2.1.0
	 *
	 * @param int $post_id  event post id.
	 *
	 * @return bool
	 */
	public static function delete( int $post_id ) {
		return QueryHelper::delete_post_with_meta(
			array(
				'post_type' => self::POST_TYPE,
				'id'        => $post_id,
			)
		);
	}
}
