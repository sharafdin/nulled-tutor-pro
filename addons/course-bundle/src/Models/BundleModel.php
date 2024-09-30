<?php
/**
 * Model for bundle data.
 *
 * @package TutorPro\CourseBundle
 * @subpackage Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Models;

use Tutor\Cache\TutorCache;
use Tutor\Helpers\QueryHelper;
use Tutor\Models\CourseModel;
use Tutor\Models\QuizModel;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;

/**
 * BundleModel Class.
 *
 * @since 2.2.0
 */
class BundleModel {

	/**
	 * Ribbon types
	 *
	 * @var string
	 */
	const RIBBON_PERCENTAGE = 'in_percentage';
	const RIBBON_AMOUNT     = 'in_amount';
	const RIBBON_NONE       = 'none';

	/**
	 * Get bundle courses
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 *
	 * @return mixed
	 */
	public static function get_bundle_courses( $bundle_id ) {
		$course_ids = self::get_bundle_course_ids( $bundle_id );
		if ( 0 === count( $course_ids ) ) {
			return array();
		}

		global $wpdb;
		$in_clause = QueryHelper::prepare_in_clause( $course_ids );
		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$courses = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * from {$wpdb->posts}
				WHERE post_type=%s
				AND ID IN({$in_clause})
				",
				CourseModel::POST_TYPE
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $courses;
	}

	/**
	 * Get bundle meta data like total course, topic, quiz etc.
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 *
	 * @return array
	 */
	public static function get_bundle_meta( $bundle_id ) {
		$cache_key = "bundle_meta_{$bundle_id}";
		$arr       = TutorCache::get( $cache_key );

		if ( false === $arr ) {
			$arr = array(
				'total_courses'        => 0,
				'total_topics'         => 0,
				'total_quizzes'        => 0,
				'total_assignments'    => 0,
				'total_video_contents' => 0,
				'total_video_duration' => 0,
				'total_resources'      => 0,
				'total_duration'       => 0,
			);

			$course_ids = self::get_bundle_course_ids( $bundle_id );
			$meta       = tutor_utils()->get_course_meta_data( $course_ids );

			if ( is_array( $meta ) && count( $meta ) ) {
				foreach ( $meta as $course_id => $course_meta ) {
					$arr['total_topics']      += $course_meta['topics'];
					$arr['total_assignments'] += $course_meta['tutor_assignments'];
					$arr['total_quizzes']     += $course_meta['tutor_quiz'];
				}
			}

			$total_lessons = count( $course_ids ) ? tutor_utils()->get_course_content_ids_by( tutor()->lesson_post_type, tutor()->course_post_type, $course_ids ) : array();

			$arr['total_courses']        = count( $course_ids );
			$arr['total_duration']       = self::get_bundle_duration( $course_ids );
			$arr['total_video_contents'] = count( $total_lessons );

			foreach ( $course_ids as $course_id ) {
				$total_attachments = tutor_utils()->get_attachments(
					$course_id,
					CourseModel::ATTACHMENT_META_KEY,
					true
				);

				$arr['total_resources'] += $total_attachments;
			}

			TutorCache::set( $cache_key, $arr );
		}

		return $arr;
	}

	/**
	 * Get bundle subtotal price.
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 *
	 * @return int|float
	 */
	public static function get_bundle_subtotal( $bundle_id ) {
		$courses = self::get_bundle_course_ids( $bundle_id );
		$total   = 0;
		foreach ( $courses as $course_id ) {
			$price = tutils()->get_raw_course_price( $course_id );
			if ( $price->regular_price > 0 ) {
				$total += $price->regular_price;
			}
		}

		return $total;
	}

	/**
	 * Count total courses in a bundle.
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 *
	 * @return int
	 */
	public static function get_total_courses_in_bundle( $bundle_id ) {
		$course_ids = self::get_bundle_course_ids( $bundle_id );
		return count( $course_ids );
	}

	/**
	 * Get bundle course ids.
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id course bundle id.
	 *
	 * @return array
	 */
	public static function get_bundle_course_ids( $bundle_id ) {
		$id_str = get_post_meta( $bundle_id, CourseBundle::BUNDLE_COURSE_IDS_META_KEY, true );
		return empty( $id_str ) ? array() : explode( ',', $id_str );
	}

	/**
	 * Get bundle course authors.
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id course bundle id.
	 *
	 * @return mixed
	 */
	public static function get_bundle_course_authors( $bundle_id ) {
		$courses = self::get_bundle_course_ids( $bundle_id );
		if ( 0 === count( $courses ) ) {
			return array();
		}

		global $wpdb;
		$in_clause = QueryHelper::prepare_in_clause( $courses );

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$authors = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
                    DISTINCT um.user_id,
                    u.display_name,
                    u.user_email,
                    tutor_job_title.meta_value AS designation
				FROM {$wpdb->usermeta} um
				    LEFT JOIN {$wpdb->users} u 
                        ON u.ID = um.user_id
                    LEFT JOIN {$wpdb->usermeta} tutor_job_title
						    ON tutor_job_title.user_id = um.user_id
						   AND tutor_job_title.meta_key = '_tutor_profile_job_title'
				WHERE um.meta_key=%s
				    AND um.meta_value IN ({$in_clause})",
				'_tutor_instructor_course_id'
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $authors;
	}

	/**
	 * Get bundle course categories.
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id course bundle id.
	 *
	 * @return mixed
	 */
	public static function get_bundle_course_categories( $bundle_id ) {
		$courses = self::get_bundle_course_ids( $bundle_id );
		if ( 0 === count( $courses ) ) {
			return array();
		}

		global $wpdb;
		$in_clause = QueryHelper::prepare_in_clause( $courses );

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$categories = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				DISTINCT terms.term_id, terms.name, terms.slug 
			  FROM 
				{$wpdb->terms} AS terms 
				INNER JOIN {$wpdb->term_taxonomy} AS taxonomy ON terms.term_id = taxonomy.term_id 
				INNER JOIN {$wpdb->term_relationships} AS relationships ON taxonomy.term_taxonomy_id = relationships.term_taxonomy_id 
			  WHERE 
				relationships.object_id IN ({$in_clause}) 
				AND taxonomy.taxonomy = %s",
				'course-category'
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $categories;
	}

	/**
	 * Get total sold number of a course bundle.
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id course bundle id.
	 *
	 * @return int
	 */
	public static function get_total_bundle_sold( $bundle_id ) {
		global $wpdb;

		$cache_key = "tutor_bundle_sold_{$bundle_id}";
		$count     = TutorCache::get( $cache_key );
		if ( false === $count ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) 
					FROM {$wpdb->posts}
					WHERE post_type = %s
					AND post_status = %s 
					AND post_parent = %d",
					'tutor_enrolled',
					'completed',
					$bundle_id
				)
			);

			TutorCache::set( $cache_key, $count );
		}

		return $count;
	}

	/**
	 * Get bundle id by course id.
	 *
	 * @since 2.2.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return int|bool  bundle id or false if course is not in a bundle.
	 */
	public static function get_bundle_id_by_course( $course_id ) {
		global $wpdb;

		$data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * 
        		FROM {$wpdb->postmeta}
        		WHERE meta_key = %s
				AND meta_value LIKE %s",
				'bundle-course-ids',
				"%{$course_id}%"
			)
		);

		return is_object( $data ) ? $data->post_id : false;
	}

	/**
	 * Delete a course bundle.
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id course bundle id.
	 *
	 * @return bool
	 */
	public static function delete_bundle( $bundle_id ) {
		if ( get_post_type( $bundle_id ) !== CourseBundle::POST_TYPE ) {
			return false;
		}

		wp_delete_post( $bundle_id, true );
		return true;
	}

	/**
	 * Update course bundle ids
	 *
	 * @since 2.2.0
	 *
	 * @param int   $bundle_id bundle id.
	 * @param array $course_ids course ids array, ex: [1,2,3].
	 *
	 * @return bool
	 */
	public static function update_bundle_course_ids( int $bundle_id, array $course_ids ): bool {
		// Validate.
		if ( ! $bundle_id ) {
			return false;
		}

		if ( CourseBundle::POST_TYPE !== get_post_type( $bundle_id ) ) {
			return false;
		}

		// Update post meta.
		update_post_meta(
			$bundle_id,
			CourseBundle::BUNDLE_COURSE_IDS_META_KEY,
			implode( ',', $course_ids )
		);

		return true;
	}

	/**
	 * Remove a course from bundle & update bundle course ids meta
	 *
	 * @since 2.2.0
	 *
	 * @param integer $course_id course id to remove.
	 * @param integer $bundle_id bundle id.
	 *
	 * @return bool
	 */
	public static function remove_course_from_bundle( int $course_id, int $bundle_id ) {
		$course_ids = self::get_bundle_course_ids( $bundle_id );

		// Remove course from bundle.
		$course_ids = array_diff( $course_ids, array( $course_id ) );

		return self::update_bundle_course_ids( $bundle_id, $course_ids );
	}

	/**
	 * Get bundles by a instructor
	 *
	 * @since 2.2.0
	 *
	 * @param integer      $instructor_id instructor id.
	 * @param array|string $post_status post status.
	 * @param integer      $offset offset.
	 * @param integer      $limit limit.
	 * @param boolean      $count_only count only.
	 *
	 * @return array|null|object
	 */
	public static function get_bundles_by_instructor( $instructor_id = 0, $post_status = array( 'publish' ), int $offset = 0, int $limit = PHP_INT_MAX, $count_only = false ) {
		global $wpdb;
		$offset        = sanitize_text_field( $offset );
		$limit         = sanitize_text_field( $limit );
		$instructor_id = tutils()->get_user_id( $instructor_id );

		if ( empty( $post_status ) || 'any' === $post_status ) {
			$where_post_status = '';
		} else {
			if ( ! is_array( $post_status ) ) {
				$post_status = array( $post_status );
			}

			$statuses          = "'" . implode( "','", $post_status ) . "'";
			$where_post_status = "AND $wpdb->posts.post_status IN({$statuses}) ";
		}

		$select_col   = $count_only ? " COUNT(DISTINCT $wpdb->posts.ID) " : " $wpdb->posts.* ";
		$limit_offset = $count_only ? '' : " LIMIT $offset, $limit ";

		//phpcs:disable
		$query = $wpdb->prepare(
			"SELECT $select_col
			FROM 	$wpdb->posts
			WHERE	1 = 1 {$where_post_status}
				AND $wpdb->posts.post_type = %s
				AND $wpdb->posts.post_author = %d
			ORDER BY $wpdb->posts.post_date DESC $limit_offset",
			CourseBundle::POST_TYPE,
			$instructor_id
		);

		return $count_only ? $wpdb->get_var( $query ) : $wpdb->get_results( $query, OBJECT );
		//phpcs:enable
	}

	/**
	 * Get bundle duration in seconds
	 *
	 * It will merge all the course durations and return in seconds
	 *
	 * @since 2.2.0
	 *
	 * @param array $course_ids course ids array.
	 *
	 * @return integer
	 */
	public static function get_bundle_duration( array $course_ids ): int {
		$total_duration = 0;
		if ( ! count( $course_ids ) ) {
			return $total_duration;
		}

		// Merge all course durations.
		foreach ( $course_ids as $id ) {
			$duration         = get_post_meta( $id, '_course_duration', true );
			$duration_hours   = (int) tutor_utils()->avalue_dot( 'hours', $duration ) * 3600;
			$duration_minutes = (int) tutor_utils()->avalue_dot( 'minutes', $duration ) * 60;
			$duration_seconds = (int) tutor_utils()->avalue_dot( 'seconds', $duration );

			$total_duration += $duration_hours + $duration_minutes + $duration_seconds;
		}

		return $total_duration;
	}

	/**
	 * Convert seconds to human readable time
	 *
	 * It will convert seconds in hour, min & seconds
	 *
	 * @since 2.2.0
	 *
	 * @param int  $seconds seconds.
	 * @param bool $echo echo or return.
	 *
	 * @return string|void
	 */
	public static function convert_seconds_into_human_readable_time( int $seconds, $echo = true ) {
		$hours             = floor( $seconds / 3600 );
		$minutes           = floor( ( $seconds % 3600 ) / 60 );
		$remaining_seconds = $seconds % 60;

		$human_readable_time = sprintf( '%02d:%02d:%02d', $hours, $minutes, $remaining_seconds );

		if ( $echo ) {
			echo esc_html( $human_readable_time );
		} else {
			return $human_readable_time;
		}
	}

	/**
	 * Get bundle ribbon options
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	public static function get_ribbon_display_options(): array {
		$currency_symbol = tutor_utils()->currency_symbol();

		$options = array(
			self::RIBBON_PERCENTAGE => __( 'Show Discount % Off', 'tutor-pro' ),
			self::RIBBON_AMOUNT     => sprintf( __( 'Show Discounted Amount (%s)', 'tutor-pro' ), $currency_symbol ), //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
			self::RIBBON_NONE       => __( 'Show None', 'tutor-pro' ),
		);

		return apply_filters( 'tutor_pro_bundle_ribbon_display_options', $options );
	}

	/**
	 * Enroll a user to bundle courses
	 *
	 * @since 2.2.2
	 *
	 * @param int $bundle_id bundle id.
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public static function enroll_to_bundle_courses( $bundle_id, $user_id ) {
		$bundle_course_ids = self::get_bundle_course_ids( $bundle_id );
		if ( count( $bundle_course_ids ) > 0 ) {
			foreach ( $bundle_course_ids as $course_id ) {
				add_filter(
					'tutor_enroll_data',
					function( $data ) {
						$data['post_status'] = 'completed';
						return $data;
					}
				);
				tutor_utils()->do_enroll( $course_id, 0, $user_id );
			}
		}
	}
}
