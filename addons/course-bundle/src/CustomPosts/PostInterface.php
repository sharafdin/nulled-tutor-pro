<?php
/**
 * Post Interface
 *
 * @package TutorPro\CourseBundle\CustomPost
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\CustomPosts;

/**
 * Custom post interface
 */
interface PostInterface {

	/**
	 * Get post type
	 */
	public static function get_post_type(): string;

	/**
	 * Get post args
	 */
	public static function get_post_args(): array;
}
