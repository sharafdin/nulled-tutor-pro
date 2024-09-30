<?php
/**
 * Handle PM PRO logics
 *
 * @package TutorPro\Addons
 * @subpackage PmPro
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.3.5
 */

namespace TUTOR_PMPRO;

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PaidMembershipsPro
 *
 * @since 1.3.5
 */
class PaidMembershipsPro {
	/**
	 * Membership types constants.
	 *
	 * @since 2.5.0
	 */
	const FULL_WEBSITE_MEMBERSHIP  = 'full_website_membership';
	const CATEGORY_WISE_MEMBERSHIP = 'category_wise_membership';

	/**
	 * Register hooks
	 */
	public function __construct() {
		add_action( 'pmpro_membership_level_after_other_settings', array( $this, 'display_courses_categories' ) );
		add_action( 'pmpro_save_membership_level', array( $this, 'pmpro_settings' ) );
		add_filter( 'tutor_course/single/add-to-cart', array( $this, 'tutor_course_add_to_cart' ) );
		add_filter( 'tutor_course_price', array( $this, 'tutor_course_price' ) );
		add_filter( 'tutor-loop-default-price', array( $this, 'add_membership_required' ) );

		add_filter( 'tutor/course/single/entry-box/free', array( $this, 'pmpro_pricing' ), 10, 2 );
		add_filter( 'tutor/course/single/entry-box/is_enrolled', array( $this, 'pmpro_pricing' ), 10, 2 );
		add_action( 'tutor/course/single/content/before/all', array( $this, 'pmpro_pricing_single_course' ), 100, 2 );
		add_filter( 'tutor/options/attr', array( $this, 'add_options' ) );

		if ( tutor_utils()->has_pmpro( true ) ) {
			// Remove price column if PM pro used.
			add_filter( 'manage_' . tutor()->course_post_type . '_posts_columns', array( $this, 'remove_price_column' ), 11, 1 );

			// Add categories column to pm pro level table.
			add_action( 'pmpro_membership_levels_table_extra_cols_header', array( $this, 'level_category_list' ) );
			add_action( 'pmpro_membership_levels_table_extra_cols_body', array( $this, 'level_category_list_body' ) );
			add_filter( 'pmpro_membership_levels_table', array( $this, 'outstanding_cat_notice' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'pricing_style' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );

			add_filter( 'tutor_course_expire_validity', array( $this, 'filter_expire_time' ), 99, 2 );
			add_action( 'pmpro_subscription_expired', array( $this, 'remove_course_access' ) );
		}
	}

	/**
	 * On PM Pro subscription expired, remove course access
	 *
	 * @see https://www.paidmembershipspro.com/hook/pmpro_subscription_expired
	 *
	 * @since 2.5.0
	 *
	 * @param \MemberOrder $old_order old order data.
	 *
	 * @return void
	 */
	public function remove_course_access( \MemberOrder $old_order ) {
		$user_id = $old_order->user_id;
		$level   = pmpro_getMembershipLevelForUser( $user_id );
		$model   = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_membership_model', true );

		$all_models = array( self::FULL_WEBSITE_MEMBERSHIP, self::CATEGORY_WISE_MEMBERSHIP );
		if ( ! in_array( $model, $all_models, true ) ) {
			return;
		}

		$enrolled_courses = array();

		if ( self::FULL_WEBSITE_MEMBERSHIP === $model ) {
			$enrolled_courses = tutor_utils()->get_enrolled_courses_by_user( $user_id );
		}

		if ( self::CATEGORY_WISE_MEMBERSHIP === $model ) {
			$lbl_obj    = new \PMPro_Membership_Level();
			$categories = (array) $lbl_obj->get_membership_level_categories( $level->id );
			if ( count( $categories ) ) {
				$enrolled_courses = tutor_utils()->get_enrolled_courses_by_user( $user_id, 'publish', 0, -1, array( 'category__in' => $categories ) );
			}
		}

		// Now cancel the course enrollment.
		if ( isset( $enrolled_courses->posts ) && is_array( $enrolled_courses->posts ) && count( $enrolled_courses->posts ) ) {
			foreach ( $enrolled_courses->posts as $course ) {
				tutor_utils()->cancel_course_enrol( $course->ID, $user_id );
			}
		}

	}

	/**
	 * Remove price column
	 *
	 * @param array $columns columns.
	 *
	 * @return array
	 */
	public function remove_price_column( $columns = array() ) {

		if ( isset( $columns['price'] ) ) {
			unset( $columns['price'] );
		}

		return $columns;
	}

	/**
	 * Display courses categories
	 *
	 * @return void
	 */
	public function display_courses_categories() {
		global $wpdb;

		if ( Input::has( 'edit' ) ) {
			$edit = intval( $_REQUEST['edit'] ); //phpcs:ignore
		} else {
			$edit = false;
		}

		// get the level...
		if ( ! empty( $edit ) && $edit > 0 ) {
			$level   = $wpdb->get_row(
				$wpdb->prepare(
					"
							SELECT * FROM $wpdb->pmpro_membership_levels
							WHERE id = %d LIMIT 1",
					$edit
				),
				OBJECT
			);
			$temp_id = $level->id;
		} elseif ( ! empty( $copy ) && $copy > 0 ) {
			$level     = $wpdb->get_row(
				$wpdb->prepare(
					"
							SELECT * FROM $wpdb->pmpro_membership_levels
							WHERE id = %d LIMIT 1",
					$copy
				),
				OBJECT
			);
			$temp_id   = $level->id;
			$level->id = null;
		} elseif ( empty( $level ) ) {
			// didn't find a membership level, let's add a new one...
			$level                    = new \stdClass();
			$level->id                = null;
			$level->name              = null;
			$level->description       = null;
			$level->confirmation      = null;
			$level->billing_amount    = null;
			$level->trial_amount      = null;
			$level->initial_payment   = null;
			$level->billing_limit     = null;
			$level->trial_limit       = null;
			$level->expiration_number = null;
			$level->expiration_period = null;
			$edit                     = -1;
		}

		// defaults for new levels.
		if ( empty( $copy ) && -1 == $edit ) {
			$level->cycle_number = 1;
			$level->cycle_period = 'Month';
		}

		// grab the categories for the given level...
		if ( ! empty( $temp_id ) ) {
			$level->categories = $wpdb->get_col(
				$wpdb->prepare(
					"
							SELECT c.category_id
							FROM $wpdb->pmpro_memberships_categories c
							WHERE c.membership_id = %d",
					$temp_id
				)
			);
		}

		if ( empty( $level->categories ) ) {
			$level->categories = array();
		}

		$level_categories = $level->categories;
		$highlight        = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_level_highlight', true );

		include_once TUTOR_PMPRO()->path . 'views/pmpro-content-settings.php';
	}

	/**
	 * PM Pro save tutor settings
	 *
	 * @param int $level_id level id.
	 *
	 * @return void
	 */
	public function pmpro_settings( $level_id ) {

		if ( 'pmpro_settings' !== Input::post( 'tutor_action' ) ) {
			return;
		}

		$tutor_pmpro_membership_model = Input::post( 'tutor_pmpro_membership_model' );
		$highlight_level              = Input::post( 'tutor_pmpro_level_highlight' );

		if ( $tutor_pmpro_membership_model ) {
			update_pmpro_membership_level_meta( $level_id, 'tutor_pmpro_membership_model', $tutor_pmpro_membership_model );
		}

		if ( $highlight_level && 1 == $highlight_level ) {
			update_pmpro_membership_level_meta( $level_id, 'tutor_pmpro_level_highlight', 1 );
		} else {
			delete_pmpro_membership_level_meta( $level_id, 'tutor_pmpro_level_highlight' );
		}
	}

	/**
	 * Add options.
	 *
	 * @param array $attr attr.
	 *
	 * @return array
	 */
	public function add_options( $attr ) {
		$attr['tutor_pmpro'] = array(
			'label'    => __( 'PM Pro', 'tutor-pro' ),
			'slug'     => 'pm-pro',
			'desc'     => __( 'Paid Membership', 'tutor-pro' ),
			'template' => 'basic',
			'icon'     => 'tutor-icon-brand-paid-membersip-pro',
			'blocks'   => array(
				array(
					'label'      => '',
					'slug'       => 'pm_pro',
					'block_type' => 'uniform',
					'fields'     => array(
						array(
							'key'     => 'pmpro_moneyback_day',
							'type'    => 'number',
							'label'   => __( 'Moneyback gurantee in', 'tutor-pro' ),
							'default' => '0',
							'desc'    => __( 'Days in you gurantee moneyback. Set 0 for no moneyback.', 'tutor-pro' ),
						),
						array(
							'key'     => 'pmpro_no_commitment_message',
							'type'    => 'text',
							'label'   => 'No commitment message',
							'default' => '',
							'desc'    => __( 'Keep empty to hide', 'tutor-pro' ),
						),
					),
				),
			),
		);

		return $attr;
	}

	/**
	 * Required levels
	 *
	 * @param mixed   $term_ids term ids.
	 * @param boolean $check_full check full.
	 *
	 * @return mixed
	 */
	private function required_levels( $term_ids, $check_full = false ) {

		global $wpdb;
		$cat_clause = count( $term_ids ) ? ( $check_full ? ' OR ' : '' ) . " (meta.meta_value='category_wise_membership' AND cat_table.category_id IN (" . implode( ',', $term_ids ) . '))' : '';

		$query_last = ( $check_full ? " meta.meta_value='full_website_membership' " : '' ) . $cat_clause;
		$query_last = ( ! $query_last || ctype_space( $query_last ) ) ? '' : ' AND (' . $query_last . ')';

		//phpcs:disable
		return $wpdb->get_results(
			"SELECT DISTINCT level_table.*
            FROM {$wpdb->pmpro_membership_levels} level_table 
                LEFT JOIN {$wpdb->pmpro_memberships_categories} cat_table ON level_table.id=cat_table.membership_id
                LEFT JOIN {$wpdb->pmpro_membership_levelmeta} meta ON level_table.id=meta.pmpro_membership_level_id 
            WHERE 
                meta.meta_key='tutor_pmpro_membership_model' " . $query_last
		);
		//phpcs:enable
	}

	/**
	 * Check has any full site level.
	 *
	 * @return boolean
	 */
	private function has_any_full_site_level() {
		global $wpdb;

		$count = $wpdb->get_var(
			"SELECT level_table.id
            FROM {$wpdb->pmpro_membership_levels} level_table 
                INNER JOIN {$wpdb->pmpro_membership_levelmeta} meta ON level_table.id=meta.pmpro_membership_level_id 
            WHERE 
                meta.meta_key='tutor_pmpro_membership_model' AND 
                meta.meta_value='full_website_membership'"
		);

		return (int) $count;
	}

	/**
	 * Just check if has membership access
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return boolean|mixed
	 */
	private function has_course_access( $course_id, $user_id = null ) {
		global $wpdb;

		if ( ! tutor_utils()->has_pmpro( true ) ) {
			// Check if monetization is pmpro and the plugin exists.
			return true;
		}

		// Prepare data.
		$user_id           = null === $user_id ? get_current_user_id() : $user_id;
		$has_course_access = false;

		// Get all membership levels of this user.
		$levels                         = $user_id ? pmpro_getMembershipLevelsForUser( $user_id ) : array();
		! is_array( $levels ) ? $levels = array() : 0;

		// Get course categories by id.
		$terms    = get_the_terms( $course_id, 'course-category' );
		$term_ids = array_map(
			function( $term ) {
				return $term->term_id;
			},
			( is_array( $terms ) ? $terms : array() )
		);

		$required_cats = $this->required_levels( $term_ids );
		if ( is_array( $required_cats ) && ! count( $required_cats ) && ! $this->has_any_full_site_level() ) {
			// Has access if no full site level and the course has no category.
			return true;
		}

		// Check if any level has access to the course.
		foreach ( $levels as $level ) {

			// Remove enrolment of expired levels.
			$endtime = (int) $level->enddate;
			if ( 0 < $endtime && $endtime < tutor_time() ) {
				// Remove here.
				continue;
			}

			if ( $has_course_access ) {
				// No need further check if any level has access to the course.
				continue;
			}

			$model = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_membership_model', true );

			if ( self::FULL_WEBSITE_MEMBERSHIP === $model ) {
				// If any model of the user is full site then the user has membership access.
				$has_course_access = true;

			} elseif ( self::CATEGORY_WISE_MEMBERSHIP === $model ) {
				// Check this course if attached to any category that is linked with this membership.
				$member_cats = pmpro_getMembershipCategories( $level->id );
				$member_cats = array_map(
					function( $member ) {
						return (int) $member;
					},
					( is_array( $member_cats ) ? $member_cats : array() )
				);

				// Check if the course id in the level category.
				foreach ( $term_ids as $term_id ) {
					if ( in_array( $term_id, $member_cats ) ) {
						$has_course_access = true;
						break;
					}
				}
			}
		}

		return $has_course_access ? true : $this->required_levels( $term_ids, true );
	}

	/**
	 * Add membership required.
	 *
	 * @param mixed $price price.
	 *
	 * @return mixed
	 */
	public function add_membership_required( $price ) {
		return ! ( $this->has_course_access( get_the_ID() ) === true ) ? '' : __( 'Free', 'tutor-pro' );
	}

	/**
	 * Tutor course add to cart
	 *
	 * @param mixed $html html.
	 *
	 * @return mixed
	 */
	public function tutor_course_add_to_cart( $html ) {

		$access_require = $this->has_course_access( get_the_ID() );
		if ( true === $access_require ) {
			// If has membership access, then no need membership require message.
			return $html;
		}

		return apply_filters( 'tutor_enrol_no_membership_msg', '' );
	}

	/**
	 * Check if user has access to the current content
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id  current course id.
	 * @param int $content_id course content like lesson, quiz etc.
	 *
	 * @return void
	 */
	public function pmpro_pricing_single_course( $course_id, $content_id ) {
		$course_id  = (int) $course_id;
		$content_id = (int) $content_id;

		$require = $this->pmpro_pricing( null, $course_id );
		// @since v2.0.7 If user has no access to the content then get back to the course.
		$has_course_access  = tutor_utils()->has_user_course_content_access();
		$is_enrolled        = tutor_utils()->is_enrolled( $course_id, get_current_user_id() );
		$is_preview_enabled = tutor()->lesson_post_type === get_post_type( $content_id ) ? (bool) get_post_meta( $content_id, '_is_preview', true ) : false;

		if ( $has_course_access || $is_enrolled || $is_preview_enabled ) {
			return;
		}

		if ( null !== $require ) {
			wp_safe_redirect( get_permalink( $course_id ) );
			exit;
		}
	}

	/**
	 * Alter tutor enroll box to show PMPRO pricing
	 *
	 * @param string $html  content to filter.
	 * @param string $course_id  current course id.
	 *
	 * @return string  html content to show on the enrollment section
	 */
	public function pmpro_pricing( $html, $course_id ) {
		$is_enrolled       = tutor_utils()->is_enrolled();
		$has_course_access = tutor_utils()->has_user_course_content_access();

		/**
		 * If current user has course access then no need to show price
		 * plan.
		 *
		 * @since v2.0.7
		 */
		if ( $is_enrolled || $has_course_access ) {
			return $html;
		}

		$required_levels = $this->has_course_access( $course_id );

		if ( true === $required_levels || ! count( $required_levels ) ) {
			// If has membership access, then no need membership pricing.
			return $html;
		}

		$level_page_id  = apply_filters( 'tutor_pmpro_level_page_id', pmpro_getOption( 'levels_page_id' ) );
		$level_page_url = get_the_permalink( $level_page_id );

		//phpcs:ignore
		extract( $this->get_pmpro_currency() ); // $currency_symbol, $currency_position.

		ob_start();
		include dirname( __DIR__ ) . '/views/pmpro-pricing.php';
		return ob_get_clean();
	}

	/**
	 * Remove the price if Membership Plan activated
	 *
	 * @param string $html html.
	 *
	 * @return mixed
	 */
	public function tutor_course_price( $html ) {
		return get_tutor_option( 'monetize_by' ) == 'pmpro' ? '' : $html;
	}

	/**
	 * Level category list
	 *
	 * @param mixed $reordered_levels reordered levels.
	 *
	 * @return void
	 */
	public function level_category_list( $reordered_levels ) {
		echo '<th>' . esc_html__( 'Recommended', 'tutor-pro' ) . '</th>';
		echo '<th>' . esc_html__( 'Type', 'tutor-pro' ) . '</th>';
	}

	/**
	 * Level category list body
	 *
	 * @param object $level level object.
	 *
	 * @return void
	 */
	public function level_category_list_body( $level ) {
		$model     = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_membership_model', true );
		$highlight = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_level_highlight', true );

		//phpcs:disable
		echo '<td>' . ( $highlight ? '<img src="' . TUTOR_PMPRO()->url . 'assets/images/star.svg"/>' : '' ) . '</td>';

		echo '<td>';

		if ( $model == 'full_website_membership' ) {
			echo '<b>' . __( 'Full Site Membership', 'tutor-pro' ) . '</b>';
		} elseif ( $model == 'category_wise_membership' ) {
			echo '<b>' . __( 'Category Wise Membership', 'tutor-pro' ) . '</b><br/>';

			$cats = pmpro_getMembershipCategories( $level->id );

			if ( is_array( $cats ) && count( $cats ) ) {
				global $wpdb;
				$terms      = $wpdb->get_results( "SELECT * FROM {$wpdb->terms} WHERE term_id IN (" . implode( ',', $cats ) . ')' );
				$term_links = array_map(
					function( $term ) {
						return '<small>' . $term->name . '</small>';
					},
					$terms
				);

				echo implode( ', ', $term_links );
			}
		}
		//phpcs:enable

		echo '</td>';
	}

	/**
	 * Get PM pro currency
	 *
	 * @return mixed
	 */
	private function get_pmpro_currency() {

		global $pmpro_currencies, $pmpro_currency;
		$current_currency = $pmpro_currency ? $pmpro_currency : '';
		$currency         = 'USD' == $current_currency ?
								array( 'symbol' => '$' ) :
								( isset( $pmpro_currencies[ $current_currency ] ) ? $pmpro_currencies[ $current_currency ] : null );

		$currency_symbol   = ( is_array( $currency ) && isset( $currency['symbol'] ) ) ? $currency['symbol'] : '';
		$currency_position = ( is_array( $currency ) && isset( $currency['position'] ) ) ? strtolower( $currency['position'] ) : 'left';

		return compact( 'currency_symbol', 'currency_position' );
	}

	/**
	 * Outstanding cat notice.
	 *
	 * @param string $html html.
	 *
	 * @return string
	 */
	public function outstanding_cat_notice( $html ) {
		global $wpdb;

		// Get all categories from all levels.
		$level_cats                             = $wpdb->get_col(
			"SELECT cat.category_id 
            FROM {$wpdb->pmpro_memberships_categories} cat 
                INNER JOIN {$wpdb->pmpro_membership_levels} lvl ON lvl.id=cat.membership_id"
		);
		! is_array( $level_cats ) ? $level_cats = array() : 0;

		// Get all categories and check if exist in any level.
		$outstanding = array();
		$course_cats = get_terms( 'course-category', array( 'hide_empty' => false ) );
		foreach ( $course_cats as $cat ) {
			! in_array( $cat->term_id, $level_cats ) ? $outstanding[] = $cat : 0;
		}

		ob_start();

		//phpcs:ignore
		extract( $this->get_pmpro_currency() ); // $currency_symbol, $currency_position
		include dirname( __DIR__ ) . '/views/outstanding-catagory-notice.php';

		return $html . ob_get_clean();
	}

	/**
	 * Style enqueue
	 *
	 * @return void
	 */
	public function pricing_style() {
		if ( is_single_course() ) {
			wp_enqueue_style( 'tutor-pmpro-pricing', TUTOR_PMPRO()->url . 'assets/css/pricing.css', array(), TUTOR_VERSION );
		}
	}

	/**
	 * Admin style enqueue
	 *
	 * @return void
	 */
	public function admin_script() {
		$screen = get_current_screen();
		if ( 'memberships_page_pmpro-membershiplevels' === $screen->id ) {
			wp_enqueue_style( 'tutor-pmpro', TUTOR_PMPRO()->url . 'assets/css/pm-pro.css', array(), TUTOR_VERSION );
		}
	}

	/**
	 * Filter course expire time
	 *
	 * @since 1.0.0
	 *
	 * @param string $validity course validity.
	 * @param int    $course_id course id.
	 *
	 * @return string validity time
	 */
	public function filter_expire_time( $validity, $course_id ) {
		$monetize_by = tutor_utils()->get_option( 'monetize_by' );
		if ( 'pmpro' !== $monetize_by ) {
			return $validity;
		}
		$user_id = get_current_user_id();

		/**
		 * The has_course_access method returns true if user has course
		 * access, if not then returns array of  required levels
		 */
		$has_access      = $this->has_course_access( $course_id );
		$term_ids        = $this->get_term_ids( $course_id );
		$required_levels = $this->required_levels( $term_ids );
		$user_levels     = pmpro_getMembershipLevelsForUser( $user_id );
		$is_enrolled     = tutor_utils()->is_enrolled( $course_id, $user_id );

		if ( false === $is_enrolled ) {
			// If course has levels.
			if ( is_array( $has_access ) && count( $has_access ) ) {
				$validity = __( 'Membership Wise', 'tutor-pro' );
			}
			// User not enrolled but just paid and will enroll.
			if ( true === $has_access ) {
				$validity = __( 'Membership Wise', 'tutor-pro' );
			}
		} else {
			// Check if user has level for the current course.
			$user_has_level = null;

			if ( is_array( $required_levels ) && count( $required_levels ) ) {
				foreach ( $required_levels as $key => $req_level ) {
					$level_id = $req_level->id ?? 0;
					if ( is_array( $user_levels ) && count( $user_levels ) && isset( $user_levels[ $key ] ) && $user_levels[ $key ]->id === $level_id ) {
						$user_has_level = $user_levels[ $key ];
					}
				}
			}

			if ( ! is_null( $user_has_level ) && is_object( $user_has_level ) ) {
				if ( $user_has_level->expiration_number ) {
					$validity = $user_has_level->expiration_number . ' ' . $user_has_level->expiration_period;
				} else {
					$validity = $user_has_level->cycle_number . ' ' . $user_has_level->cycle_period;
				}
			}

			/**
			 * If user don't have category wise membership then
			 * look into full-site membership
			 */
			if ( is_array( $user_levels ) && is_null( $user_has_level ) ) {
				$level = isset( $user_levels[0] ) ? $user_levels[0] : null;
				if ( is_object( $level ) ) {
					if ( isset( $level->expiration_period ) && $level->expiration_period ) {
						$validity = $level->expiration_number . ' ' . $level->expiration_period;
					} else {
						$validity = $level->cycle_number . ' ' . $level->cycle_period;
					}
				}
			}
		}
		// If membership has no validity then set lifetime.
		if ( 0 == $validity || '' === $validity ) {
			$validity = __( 'Lifetime', 'tutor-pro' );
		}
		return $validity;
	}

	/**
	 * Get terms ids by course id
	 *
	 * @since 2.1.4
	 *
	 * @param int $course_id course id.
	 *
	 * @return array
	 */
	public function get_term_ids( $course_id ) {
		$terms    = get_the_terms( $course_id, 'course-category' );
		$term_ids = array_map(
			function( $term ) {
				return $term->term_id;
			},
			( is_array( $terms ) ? $terms : array() )
		);
		return $term_ids;
	}
}
