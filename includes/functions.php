<?php

/**
 * @param string $type
 * @param int $ref_id
 * @param int $user_id
 *
 * @return array|bool|null|object|void
 *
 * @since v.1.4.2
 */

if ( ! function_exists( 'get_generated_gradebook' ) ) {
	function get_generated_gradebook( $type = 'final', $ref_id = 0, $user_id = 0 ) {
		global $wpdb;

		$user_id = tutor_utils()->get_user_id( $user_id );

		$res = false;
		if ( $type === 'all' ) {
			$res = $wpdb->get_results(
				"SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND course_id = {$ref_id} 
					AND result_for != 'final' "
			);

		} elseif ( $type === 'quiz' ) {

			$res = $wpdb->get_row(
				"SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND quiz_id = {$ref_id} 
					AND result_for = 'quiz' "
			);

		} elseif ( $type === 'assignment' ) {
			$res = $wpdb->get_row(
				"SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND assignment_id = {$ref_id} 
					AND result_for = 'assignment' "
			);
		} elseif ( $type === 'final' ) {
			$res = $wpdb->get_row(
				"SELECT {$wpdb->tutor_gradebooks_results}.*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND course_id = {$ref_id} 
					AND result_for = 'final' "
			);

		} elseif ( $type === 'byID' ) {
			$res = $wpdb->get_row(
				"SELECT {$wpdb->tutor_gradebooks_results}.*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE gradebook_result_id = {$ref_id};"
			);
		}

		return $res;
	}
}

/**
 * @param int $course_id
 * @param int $user_id
 *
 * @return array|null|object|void
 *
 * Get assignment gradebook by course
 */

if ( ! function_exists( 'get_assignment_gradebook_by_course' ) ) {
	function get_assignment_gradebook_by_course( $course_id = 0, $user_id = 0 ) {
		global $wpdb;

		$user_id = tutor_utils()->get_user_id( $user_id );

		$res = $wpdb->get_row(
			"SELECT {$wpdb->tutor_gradebooks_results}.grade_point, 
			COUNT({$wpdb->tutor_gradebooks_results}.earned_percent) AS res_count, 
			AVG({$wpdb->tutor_gradebooks_results}.earned_percent) as earned_percent,
                FORMAT(AVG({$wpdb->tutor_gradebooks_results}.earned_grade_point), 2) as earned_grade_point,
                grade_config 
				FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
				WHERE course_id = {$course_id} AND user_id = {$user_id} AND result_for = 'assignment' "
		);

		$res_count = (int) $res->res_count;
		if ( ! $res_count ) {
			return false;
		}

		return $res;
	}
}

/**
 * @param int $course_id
 * @param int $user_id
 *
 * @return array|null|object|void
 *
 * Get quiz gradebook by course
 */

if ( ! function_exists( 'get_quiz_gradebook_by_course' ) ) {
	function get_quiz_gradebook_by_course( $course_id = 0, $user_id = 0 ) {
		global $wpdb;

		$user_id = tutor_utils()->get_user_id( $user_id );

		$res = $wpdb->get_row(
			"SELECT {$wpdb->tutor_gradebooks_results}.grade_point, 
				COUNT({$wpdb->tutor_gradebooks_results}.earned_percent) AS res_count, 
                AVG({$wpdb->tutor_gradebooks_results}.earned_percent) as earned_percent,
                FORMAT(AVG({$wpdb->tutor_gradebooks_results}.earned_grade_point), 2) as earned_grade_point, grade_config 
					FROM {$wpdb->tutor_gradebooks_results}
						LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} AND result_for = 'quiz' 
			"
		);

		$res_count = (int) $res->res_count;
		if ( ! $res_count ) {
			return false;
		}

		return $res;

	}
}

/**
 * @param int $percent
 *
 * @return array|null|object|void
 *
 * Get gradebook by percent
 */
if ( ! function_exists( 'get_gradebook_by_percent' ) ) {
	function get_gradebook_by_percent( $percent = 0 ) {
		if ( ! $percent ) {
			return false;
		}
		global $wpdb;
		$gradebook = $wpdb->get_row(
			"SELECT * FROM {$wpdb->tutor_gradebooks} 
		WHERE percent_from <= {$percent} 
		AND percent_to >= {$percent} ORDER BY gradebook_id ASC LIMIT 1  "
		);

		return $gradebook;
	}
}

/**
 * @param int $point
 *
 * @return array|bool|null|object|void
 *
 * Get gradebook by point
 */
if ( ! function_exists( 'get_gradebook_by_point' ) ) {
	function get_gradebook_by_point( $point = 0 ) {
		if ( ! $point ) {
			return false;
		}
		global $wpdb;
		$gradebook = $wpdb->get_row( "SELECT * FROM {$wpdb->tutor_gradebooks} WHERE grade_point <= '{$point}' ORDER BY grade_point DESC LIMIT 1 " );
		return $gradebook;
	}
}
/**
 * @param $grade
 *
 * @return mixed|void
 *
 * Generate Grade HTML
 */

if ( ! function_exists( 'tutor_generate_grade_html' ) ) {
	function tutor_generate_grade_html( $grade, $style = 'bgfill' ) {
		if ( ! $grade ) {
			return;
		}

		// Get grade object if it is grade ID in fact
		if ( ! is_object( $grade ) ) {
			global $wpdb;
			$grade = $wpdb->get_row(
				"SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config 
				FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
				WHERE gradebook_result_id = {$grade} "
			);
		}

		if ( ! $grade || empty( $grade->earned_grade_point ) ) {
			// No grade found
			return;
		}

		// Prepare config
		$config                                = maybe_unserialize( $grade->grade_config );
		$stat                                  = tutor_gradebook_get_stats( $grade );
		( $stat && $stat['config'] ) ? $config = $stat['config'] : 0;

		if ( $style === null ) {
			return $stat;
		}

				ob_start();
		$bgcolor = tutor_utils()->array_get( 'grade_color', $config );

		if ( $style === 'bgfill' ) {
			echo "<span class='gradename-bg {$style}' style='background-color: {$bgcolor};'>
					{$stat['gradename']}
				</span> ";
		} else {
			echo "<span class='gradename-outline {$style}' style='color: {$bgcolor};'>
					{$stat['gradename']}
				</span> ";
		}

		if ( $stat['gradepoint'] ) {
			echo "<span class='gradebook-earned-grade-point'>
				{$stat['gradepoint']}
			</span>";
		}

		return ob_get_clean();
	}
}

if ( ! function_exists( 'tutor_gradebook_get_stats' ) ) {
	function tutor_gradebook_get_stats( $grade ) {

		$grade_name       = '';
		$grade_point      = '';
		$grade_point_only = '';
		$config           = null;

		$gradebook_scale = get_tutor_option( 'gradebook_scale' );

		// Get grade name
		if ( ! empty( $grade->grade_name ) ) {
			$grade_name = $grade->grade_name;
		} else {
			$new_grade = get_gradebook_by_point( $grade->earned_grade_point );
			if ( $new_grade ) {
				$grade_name = $new_grade->grade_name;
				$config     = maybe_unserialize( $new_grade->grade_config );
			}
		}

		// Get grade point
		if ( get_tutor_option( 'gradebook_enable_grade_point' ) ) {
			$grade_point_only = ! empty( $grade->earned_grade_point ) ? $grade->earned_grade_point : $grade->grade_point;
			$grade_point      = $grade_point_only;
		}

		// Add scale
		if ( get_tutor_option( 'gradebook_show_grade_scale' ) ) {
			$separator   = get_tutor_option( 'gradebook_scale_separator', '/' );
			$grade_point = $grade_point . $separator . $gradebook_scale;
		}

		return array(
			'gradename'       => $grade_name,
			'gradepoint'      => $grade_point,
			'gradescale'      => $gradebook_scale,
			'gradepoint_only' => $grade_point_only,
			'config'          => $config,
		);
	}
}

/**
 * @param $gradebook_id
 *
 * @return array|bool|null|object|void
 *
 * Get gradebook by gradebook id
 */

if ( ! function_exists( 'get_gradebook_by_id' ) ) {
	function get_gradebook_by_id( $gradebook_id ) {
		global $wpdb;
		$gradebook = $wpdb->get_row( "SELECT * FROM {$wpdb->tutor_gradebooks} WHERE gradebook_id = {$gradebook_id} " );
		if ( $gradebook ) {
			$gradebook->grade_config = maybe_unserialize( tutor_utils()->array_get( 'grade_config', $gradebook ) );

			return $gradebook;
		}

		return false;
	}
}

function get_grading_contents_by_course_id( $course_id = 0 ) {
	global $wpdb;

	$course_id = tutor_utils()->get_post_id( $course_id );
	$contents  = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT items.* FROM {$wpdb->posts} topic
				INNER JOIN {$wpdb->posts} items ON topic.ID = items.post_parent 
				WHERE topic.post_parent = %d 
				AND items.post_status = 'publish' 
				AND (items.post_type = 'tutor_quiz' || items.post_type = 'tutor_assignments') 
				order by topic.menu_order ASC, items.menu_order ASC;",
			$course_id
		)
	);

	return $contents;
}

/**
 * Get gradebook list
 *
 * @param array $config | config for filter / sorting query.
 * @return object
 */
function get_generated_gradebooks( $config = array() ) {
	global $wpdb;

	$default_attr = array(
		'course_id' => 0,
		'start'     => '0',
		'limit'     => '20',
		'order'     => isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC',
		'order_by'  => 'gradebook_result_id',
		'date'      => isset( $_GET['date'] ) ? sanitize_text_field( tutor_get_formated_date( 'Y-m-d', $_GET['date'] ) ) : '',
	);
	$attr         = array_merge( $default_attr, $config );
	extract( $attr );
	$gradebooks = array(
		'count' => 0,
		'res'   => false,
	);

	$term = sanitize_text_field( tutor_utils()->array_get( 'search', $_GET ) );
	// Prepare filters.
	$filter_sql = '';

	if ( $course_id ) {
		$filter_sql .= " AND gradebook_result.course_id = {$course_id} ";
	}
	if ( $term ) {
		$filter_sql .= " AND (course.post_title LIKE '%{$term}%' OR student.display_name LIKE '%{$term}%' ) ";
	}
	if ( '' !== $date ) {
		$filter_sql .= " AND DATE(gradebook_result.update_date) = CAST('$date' AS DATE) ";
	}
	$order = sanitize_sql_orderby( $order );

	$gradebooks['count'] = $wpdb->get_var(
		"SELECT COUNT(gradebook_result.gradebook_result_id) total_res
				FROM {$wpdb->tutor_gradebooks_results} gradebook_result
					LEFT JOIN {$wpdb->posts} course ON gradebook_result.course_id = course.ID
					LEFT  JOIN {$wpdb->users} student ON gradebook_result.user_id = student.ID
				WHERE gradebook_result.result_for = 'final' {$filter_sql} ;
			"
	);
	$gradebooks['res']   = $wpdb->get_results(
		"SELECT gradebook_result.*, 
			(SELECT COUNT(quizzes.quiz_id) FROM {$wpdb->tutor_gradebooks_results} quizzes WHERE quizzes.user_id = gradebook_result.user_id AND quizzes.course_id = gradebook_result.course_id AND quizzes.result_for = 'quiz') as quiz_count,

			(SELECT COUNT(assignments.assignment_id) FROM {$wpdb->tutor_gradebooks_results} assignments WHERE assignments.user_id = gradebook_result.user_id AND assignments.course_id = gradebook_result.course_id AND assignments.result_for = 'assignment') as assignment_count,
		grade_config,
		student.display_name,
		course.post_title as course_title
		FROM {$wpdb->tutor_gradebooks_results} gradebook_result
			LEFT JOIN {$wpdb->tutor_gradebooks} gradebook ON gradebook_result.gradebook_id = gradebook.gradebook_id
			LEFT JOIN {$wpdb->posts} course ON gradebook_result.course_id = course.ID
			LEFT  JOIN {$wpdb->users} student ON gradebook_result.user_id = student.ID
		WHERE gradebook_result.result_for = 'final' {$filter_sql} 
		ORDER BY gradebook_result.generate_date {$order}
		LIMIT {$start}, {$limit}
		"
	);

	$gradebooks = (object) $gradebooks;

	return $gradebooks;
}

/**
 * Get list of global timezones
 *
 * @return array
 */
function tutor_global_timezone_lists() {
	return array(
		'Pacific/Midway'                 => '(GMT-11:00) Midway Island, Samoa ',
		'Pacific/Pago_Pago'              => '(GMT-11:00) Pago Pago ',
		'Pacific/Honolulu'               => '(GMT-10:00) Hawaii ',
		'America/Anchorage'              => '(GMT-8:00) Alaska ',
		'America/Vancouver'              => '(GMT-7:00) Vancouver ',
		'America/Los_Angeles'            => '(GMT-7:00) Pacific Time (US and Canada) ',
		'America/Tijuana'                => '(GMT-7:00) Tijuana ',
		'America/Phoenix'                => '(GMT-7:00) Arizona ',
		'America/Edmonton'               => '(GMT-6:00) Edmonton ',
		'America/Denver'                 => '(GMT-6:00) Mountain Time (US and Canada) ',
		'America/Mazatlan'               => '(GMT-6:00) Mazatlan ',
		'America/Regina'                 => '(GMT-6:00) Saskatchewan ',
		'America/Guatemala'              => '(GMT-6:00) Guatemala ',
		'America/El_Salvador'            => '(GMT-6:00) El Salvador ',
		'America/Managua'                => '(GMT-6:00) Managua ',
		'America/Costa_Rica'             => '(GMT-6:00) Costa Rica ',
		'America/Tegucigalpa'            => '(GMT-6:00) Tegucigalpa ',
		'America/Winnipeg'               => '(GMT-5:00) Winnipeg ',
		'America/Chicago'                => '(GMT-5:00) Central Time (US and Canada) ',
		'America/Mexico_City'            => '(GMT-5:00) Mexico City ',
		'America/Panama'                 => '(GMT-5:00) Panama ',
		'America/Bogota'                 => '(GMT-5:00) Bogota ',
		'America/Lima'                   => '(GMT-5:00) Lima ',
		'America/Caracas'                => '(GMT-4:30) Caracas ',
		'America/Montreal'               => '(GMT-4:00) Montreal ',
		'America/New_York'               => '(GMT-4:00) Eastern Time (US and Canada) ',
		'America/Indianapolis'           => '(GMT-4:00) Indiana (East) ',
		'America/Puerto_Rico'            => '(GMT-4:00) Puerto Rico ',
		'America/Santiago'               => '(GMT-4:00) Santiago ',
		'America/Halifax'                => '(GMT-3:00) Halifax ',
		'America/Montevideo'             => '(GMT-3:00) Montevideo ',
		'America/Araguaina'              => '(GMT-3:00) Brasilia ',
		'America/Argentina/Buenos_Aires' => '(GMT-3:00) Buenos Aires, Georgetown ',
		'America/Sao_Paulo'              => '(GMT-3:00) Sao Paulo ',
		'Canada/Atlantic'                => '(GMT-3:00) Atlantic Time (Canada) ',
		'America/St_Johns'               => '(GMT-2:30) Newfoundland and Labrador ',
		'America/Godthab'                => '(GMT-2:00) Greenland ',
		'Atlantic/Cape_Verde'            => '(GMT-1:00) Cape Verde Islands ',
		'Atlantic/Azores'                => '(GMT+0:00) Azores ',
		'UTC'                            => '(GMT+0:00) Universal Time UTC ',
		'Etc/Greenwich'                  => '(GMT+0:00) Greenwich Mean Time ',
		'Atlantic/Reykjavik'             => '(GMT+0:00) Reykjavik ',
		'Africa/Nouakchott'              => '(GMT+0:00) Nouakchott ',
		'Europe/Dublin'                  => '(GMT+1:00) Dublin ',
		'Europe/London'                  => '(GMT+1:00) London ',
		'Europe/Lisbon'                  => '(GMT+1:00) Lisbon ',
		'Africa/Casablanca'              => '(GMT+1:00) Casablanca ',
		'Africa/Bangui'                  => '(GMT+1:00) West Central Africa ',
		'Africa/Algiers'                 => '(GMT+1:00) Algiers ',
		'Africa/Tunis'                   => '(GMT+1:00) Tunis ',
		'Europe/Belgrade'                => '(GMT+2:00) Belgrade, Bratislava, Ljubljana ',
		'CET'                            => '(GMT+2:00) Sarajevo, Skopje, Zagreb ',
		'Europe/Oslo'                    => '(GMT+2:00) Oslo ',
		'Europe/Copenhagen'              => '(GMT+2:00) Copenhagen ',
		'Europe/Brussels'                => '(GMT+2:00) Brussels ',
		'Europe/Berlin'                  => '(GMT+2:00) Amsterdam, Berlin, Rome, Stockholm, Vienna ',
		'Europe/Amsterdam'               => '(GMT+2:00) Amsterdam ',
		'Europe/Rome'                    => '(GMT+2:00) Rome ',
		'Europe/Stockholm'               => '(GMT+2:00) Stockholm ',
		'Europe/Vienna'                  => '(GMT+2:00) Vienna ',
		'Europe/Luxembourg'              => '(GMT+2:00) Luxembourg ',
		'Europe/Paris'                   => '(GMT+2:00) Paris ',
		'Europe/Zurich'                  => '(GMT+2:00) Zurich ',
		'Europe/Madrid'                  => '(GMT+2:00) Madrid ',
		'Africa/Harare'                  => '(GMT+2:00) Harare, Pretoria ',
		'Europe/Warsaw'                  => '(GMT+2:00) Warsaw ',
		'Europe/Prague'                  => '(GMT+2:00) Prague Bratislava ',
		'Europe/Budapest'                => '(GMT+2:00) Budapest ',
		'Africa/Tripoli'                 => '(GMT+2:00) Tripoli ',
		'Africa/Cairo'                   => '(GMT+2:00) Cairo ',
		'Africa/Johannesburg'            => '(GMT+2:00) Johannesburg ',
		'Europe/Helsinki'                => '(GMT+3:00) Helsinki ',
		'Africa/Nairobi'                 => '(GMT+3:00) Nairobi ',
		'Europe/Sofia'                   => '(GMT+3:00) Sofia ',
		'Europe/Istanbul'                => '(GMT+3:00) Istanbul ',
		'Europe/Athens'                  => '(GMT+3:00) Athens ',
		'Europe/Bucharest'               => '(GMT+3:00) Bucharest ',
		'Asia/Nicosia'                   => '(GMT+3:00) Nicosia ',
		'Asia/Beirut'                    => '(GMT+3:00) Beirut ',
		'Asia/Damascus'                  => '(GMT+3:00) Damascus ',
		'Asia/Jerusalem'                 => '(GMT+3:00) Jerusalem ',
		'Asia/Amman'                     => '(GMT+3:00) Amman ',
		'Europe/Moscow'                  => '(GMT+3:00) Moscow ',
		'Asia/Baghdad'                   => '(GMT+3:00) Baghdad ',
		'Asia/Kuwait'                    => '(GMT+3:00) Kuwait ',
		'Asia/Riyadh'                    => '(GMT+3:00) Riyadh ',
		'Asia/Bahrain'                   => '(GMT+3:00) Bahrain ',
		'Asia/Qatar'                     => '(GMT+3:00) Qatar ',
		'Asia/Aden'                      => '(GMT+3:00) Aden ',
		'Africa/Khartoum'                => '(GMT+3:00) Khartoum ',
		'Africa/Djibouti'                => '(GMT+3:00) Djibouti ',
		'Africa/Mogadishu'               => '(GMT+3:00) Mogadishu ',
		'Europe/Kiev'                    => '(GMT+3:00) Kiev ',
		'Asia/Dubai'                     => '(GMT+4:00) Dubai ',
		'Asia/Muscat'                    => '(GMT+4:00) Muscat ',
		'Asia/Tehran'                    => '(GMT+4:30) Tehran ',
		'Asia/Kabul'                     => '(GMT+4:30) Kabul ',
		'Asia/Baku'                      => '(GMT+5:00) Baku, Tbilisi, Yerevan ',
		'Asia/Yekaterinburg'             => '(GMT+5:00) Yekaterinburg ',
		'Asia/Tashkent'                  => '(GMT+5:00) Tashkent ',
		'Asia/Karachi'                   => '(GMT+5:00) Islamabad, Karachi ',
		'Asia/Calcutta'                  => '(GMT+5:30) India ',
		'Asia/Kolkata'                   => '(GMT+5:30) Mumbai, Kolkata, New Delhi ',
		'Asia/Kathmandu'                 => '(GMT+5:45) Kathmandu ',
		'Asia/Novosibirsk'               => '(GMT+6:00) Novosibirsk ',
		'Asia/Almaty'                    => '(GMT+6:00) Almaty ',
		'Asia/Dacca'                     => '(GMT+6:00) Dacca ',
		'Asia/Dhaka'                     => '(GMT+6:00) Astana, Dhaka ',
		'Asia/Krasnoyarsk'               => '(GMT+7:00) Krasnoyarsk ',
		'Asia/Bangkok'                   => '(GMT+7:00) Bangkok ',
		'Asia/Saigon'                    => '(GMT+7:00) Vietnam ',
		'Asia/Jakarta'                   => '(GMT+7:00) Jakarta ',
		'Asia/Irkutsk'                   => '(GMT+8:00) Irkutsk, Ulaanbaatar ',
		'Asia/Shanghai'                  => '(GMT+8:00) Beijing, Shanghai ',
		'Asia/Hong_Kong'                 => '(GMT+8:00) Hong Kong ',
		'Asia/Taipei'                    => '(GMT+8:00) Taipei ',
		'Asia/Kuala_Lumpur'              => '(GMT+8:00) Kuala Lumpur ',
		'Asia/Singapore'                 => '(GMT+8:00) Singapore ',
		'Australia/Perth'                => '(GMT+8:00) Perth ',
		'Asia/Yakutsk'                   => '(GMT+9:00) Yakutsk ',
		'Asia/Seoul'                     => '(GMT+9:00) Seoul ',
		'Asia/Tokyo'                     => '(GMT+9:00) Osaka, Sapporo, Tokyo ',
		'Australia/Darwin'               => '(GMT+9:30) Darwin ',
		'Australia/Adelaide'             => '(GMT+9:30) Adelaide ',
		'Asia/Vladivostok'               => '(GMT+10:00) Vladivostok ',
		'Pacific/Port_Moresby'           => '(GMT+10:00) Guam, Port Moresby ',
		'Australia/Brisbane'             => '(GMT+10:00) Brisbane ',
		'Australia/Sydney'               => '(GMT+10:00) Canberra, Melbourne, Sydney ',
		'Australia/Hobart'               => '(GMT+10:00) Hobart ',
		'Asia/Magadan'                   => '(GMT+10:00) Magadan ',
		'SST'                            => '(GMT+11:00) Solomon Islands ',
		'Pacific/Noumea'                 => '(GMT+11:00) New Caledonia ',
		'Asia/Kamchatka'                 => '(GMT+12:00) Kamchatka ',
		'Pacific/Fiji'                   => '(GMT+12:00) Fiji Islands, Marshall Islands ',
		'Pacific/Auckland'               => '(GMT+12:00) Auckland, Wellington',
	);
}

if ( ! function_exists( 'tutor_pro_email_global_footer' ) ) {
	/**
	 * Get the global email footer
	 *
	 * @since 2.1.9
	 *
	 * @return string email footer as string
	 */
	function tutor_pro_email_global_footer() {
		$string            = '';
		$email_footer_text = tutor_utils()->get_option( 'email_footer_text' );
		$email_footer_text = str_replace( '{site_name}', get_bloginfo( 'name' ), $email_footer_text );

		if ( $email_footer_text ) {
			$string .= '<div class="tutor-email-footer-content">' . wp_unslash( json_decode( $email_footer_text ) ) . '</div>';
		}
		return $string;
	}
}
