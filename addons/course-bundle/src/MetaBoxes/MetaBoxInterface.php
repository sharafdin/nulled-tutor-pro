<?php
/**
 * Meta box Interface
 *
 * @package TutorPro\CourseBundle\MetaBoxes
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\MetaBoxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface MetaBoxInterface {

	/**
	 * Get id
	 *
	 * @return string
	 */
	public function get_id() : string;

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function get_title() : string;

	/**
	 * Get screen
	 *
	 * @return void
	 */
	public function get_screen();

	/**
	 * Get context
	 *
	 * @return string
	 */
	public function get_context(): string;

	/**
	 * Get priority
	 *
	 * @return string
	 */
	public function get_priority(): string;

	/**
	 * Get arguments
	 *
	 * @return mixed
	 */
	public function get_args();

	/**
	 * Callback function
	 *
	 * @return mixed
	 */
	public function callback();
}
