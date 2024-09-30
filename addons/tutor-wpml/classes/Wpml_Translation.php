<?php
/**
 * Manage WPML translations
 *
 * @package Tutor\WPMLTranslation
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR_WPML;

use TUTOR_PRO\Course_Duplicator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage WPML integration
 */
class Wpml_Translation {

	private static $wpml_current_lang;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scrips' ) );
		add_action( 'save_post_' . tutor()->course_post_type, array( $this, 'copy_content_from_source' ), 999, 1 );
		add_filter( 'tutor_dashboard_page_id_filter', array( $this, 'tutor_dashboard_page_id_filter' ) );
		add_action( 'tutor_utils/get_pages/before', array( $this, 'enable_wpml_filter_dashboard_page' ) );
		add_action( 'tutor_utils/get_pages/after', array( $this, 'disable_wpml_filter_dashboard_page' ) );

		/**
		 * Apply filters to fix 404 not found issue of frontend
		 * dashboard sub pages
		 *
		 * @since 2.1.7
		 */
		add_filter( 'tutor_determine_is_page', __CLASS__ . '::is_tutor_page' );
		add_filter( 'tutor_determine_is_dashboard_page', __CLASS__ . '::is_tutor_page' );
		add_filter( 'tutor_dashboard_sub_page_template', __CLASS__ . '::filter_sub_page_template' );
		add_filter( 'tutor_should_load_dashboard_styles', __CLASS__ . '::is_tutor_page' );
		add_action( 'wp', __CLASS__ . '::manage_tutor_query_vars' );
		add_filter( 'tutor_dashboard_menu_link', __CLASS__ . '::filter_menu_link' );
		add_filter( 'template_include', __CLASS__ . '::load_assignment_template' );
		add_filter( 'body_class', __CLASS__ . '::add_course_builder_classes' );
		add_filter( 'tutor_load_course_builder_scripts', __CLASS__ . '::load_course_builder_scripts' );
	}

	public function load_scrips() {
		wp_enqueue_script( 'tutor-wpml-js', TUTOR_WPML()->url . 'assets/js/duplicate.js' );
	}

	public function copy_content_from_source( $target_course_id ) {

		if (
			! isset( $_POST['icl_trid'] ) ||
			! isset( $_POST['tutor_wpml_copy_source'] ) ||
			$_POST['tutor_wpml_copy_source'] != 1 ||
			get_post_meta( $target_course_id, 'tutor_wpml_source_copied', false ) ) {
			return;
		}
		update_post_meta( $target_course_id, 'tutor_wpml_source_copied', true );

		global $wpdb;
		$source_course_id = isset( $_POST['icl_translation_of'] ) ?
			$_POST['icl_translation_of'] :
			$wpdb->get_var(
				$wpdb->prepare(
					"SELECT element_id
                FROM {$wpdb->prefix}icl_translations
                WHERE trid=%d AND source_language_code IS NULL",
					$_POST['icl_trid']
				)
			);

		( new Course_Duplicator( false ) )->duplicate_post( $source_course_id, null, 0, $target_course_id );
	}

	public function tutor_dashboard_page_id_filter( $student_dashboard_page_id ) {

		global $sitepress;
		if ( isset( $sitepress ) ) {
			$trid                      = apply_filters( 'wpml_element_trid', null, $student_dashboard_page_id, 'post_page' );
			$translations              = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_page' );
			$current_lang              = apply_filters( 'wpml_current_language', null );
			$student_dashboard_page_id = (int) isset( $translations[ $current_lang ] ) ? $translations[ $current_lang ]->element_id : null;
		}

		return $student_dashboard_page_id;
	}

	public function enable_wpml_filter_dashboard_page() {
		global $sitepress;
		if ( isset( $sitepress ) ) {
			self::$wpml_current_lang = apply_filters( 'wpml_current_language', null );
			$wpml_default_lang       = apply_filters( 'wpml_default_language', null );
			$sitepress->switch_lang( $wpml_default_lang );
		}
	}

	public function disable_wpml_filter_dashboard_page() {
		global $sitepress;
		if ( isset( $sitepress ) ) {
			$sitepress->switch_lang( self::$wpml_current_lang );
		}
	}

	/**
	 * Fallback filter method if Tutor could not determine
	 * it is a sub page of dashboard
	 *
	 * @since 2.1.7
	 *
	 * @param bool $is_page default value of filter.
	 *
	 * @return boolean
	 */
	public static function is_tutor_page( $is_page ): bool {
		$current_url = self::current_url_arr();

		// Ignore course contents check only for frontend dashboard pages.
		if ( ! in_array( tutor()->course_post_type, $current_url ) ) {
			if ( ! $is_page ) {
				$attachment = get_query_var( 'attachment' );
				if ( '' !== $attachment ) {
					$is_page = self::is_tutor_sub_page( $attachment );
				} else {
					$is_page = self::is_tutor_sub_page( self::get_sub_page() );
				}
			}
		}
		return (bool) $is_page;
	}

	/**
	 * Filter dashboard sub page template
	 *
	 * @since 2.1.7
	 *
	 * @param string $template default template.
	 *
	 * @return string template file name
	 */
	public static function filter_sub_page_template( $template ) {
		$sub_page = get_query_var( 'attachment' );
		if ( ! $template && self::is_tutor_sub_page( $sub_page ) ) {
			$template = $sub_page;
		}
		return $template;
	}

	/**
	 * Check if current attachment is Tutor's dashboard
	 * sub page
	 *
	 * @since 2.1.7
	 *
	 * @param string $sub_page sub page name.
	 *
	 * @return boolean
	 */
	private static function is_tutor_sub_page( $sub_page ): bool {
		$dashboard_pages = tutor_utils()->tutor_dashboard_permalinks();
		return (bool) tutor_utils()->array_get( $sub_page, $dashboard_pages );
	}

	/**
	 * Setup tutor query vars
	 *
	 * Tutor's frontend pages are dependent on query
	 * vars. These are : pagename, tutor_dashboard_page,
	 * tutor_dashboard_sub_page
	 *
	 * @since 2.1.7
	 *
	 * @return void
	 */
	public static function manage_tutor_query_vars() {
		global $wp_query;
		if ( $wp_query->is_main_query() && ! is_admin() ) {
			$sub_page = self::get_sub_page();
			if ( self::is_tutor_sub_page( $sub_page ) ) {
				self::setup_query_vars();
			}
		}
	}

	/**
	 * Setup missing query vars based on current URL
	 *
	 * @since 2.1.7
	 *
	 * @return void
	 */
	private static function setup_query_vars() {
		global $wp_query;
		$url_arr = self::current_url_arr();
		$length  = count( $url_arr );
		if ( 6 === $length ) {
			$wp_query->set( 'pagename', 'dashboard' );
			$wp_query->set( 'tutor_dashboard_page', $url_arr[ $length - 1 ] );
		}
		if ( 7 === $length ) {
			$wp_query->set( 'pagename', 'dashboard' );
			$wp_query->set( 'tutor_dashboard_page', $url_arr[ $length - 2 ] );
			$wp_query->set( 'tutor_dashboard_sub_page', $url_arr[ $length - 1 ] );
		}
	}

	/**
	 * Extract sub page from current URL
	 *
	 * @since 2.1.7
	 *
	 * @param string $url URL to extract sub page. If empty
	 * then it will use current URL.
	 *
	 * @return string
	 */
	public static function get_sub_page( string $url = '' ): string {
		$sub_page = '';
		$url_arr  = self::current_url_arr( $url );
		$length   = count( $url_arr );
		if ( 7 === $length ) {
			$sub_page = $url_arr[ $length - 2 ];
		}
		if ( 6 === $length ) {
			$sub_page = $url_arr[ $length - 1 ];
		}
		return $sub_page;
	}

	/**
	 * Convert current URL in array
	 *
	 * @since 2.1.7
	 *
	 * @param string $url URL string, if empty then it will
	 * use current url.
	 *
	 * @return array of URL portions
	 */
	public static function current_url_arr( string $url = '' ) {
		if ( '' === $url ) {
			$current_url = trim( tutor_utils()->get_current_url(), '/' );
		} else {
			$current_url = trim( $url, '/' );
		}

		return array_values( array_filter( explode( '/', $current_url ) ) );
	}

	/**
	 * Filter assignment menu link
	 *
	 * @since 2.1.7
	 *
	 * @param string $link link string.
	 *
	 * @return string
	 */
	public static function filter_menu_link( $link ) {
		$sub_page         = self::get_sub_page( $link );
		$current_language = apply_filters( 'wpml_current_language', null );
		if ( 'assignments' === $sub_page && 'en' !== $current_language ) {
			$link = tutor_utils()->tutor_dashboard_url( 'tutor-assignments' );
		}
		return trailingslashit( $link );
	}

	/**
	 * Explicitly Load assignment template
	 *
	 * @since 2.1.7
	 *
	 * @param string $template default template.
	 *
	 * @return string template path
	 */
	public static function load_assignment_template( $template ) {
		$attachment = get_query_var( 'attachment' );
		if ( 'tutor-assignments' === $attachment ) {
			add_filter(
				'tutor_dashboard_sub_page_template',
				function() {
					return 'assignments';
				}
			);
			add_filter(
				'tutor_should_load_dashboard_styles',
				function() {
					return true;
				}
			);
			self::setup_query_vars();
			$template = tutor()->path . 'templates/dashboard.php';
		}
		return $template;
	}

	/**
	 * Handle filter to load course builder scripts
	 *
	 * @since 2.1.7
	 *
	 * @return bool
	 */
	public static function load_course_builder_scripts( $load ): bool {
		if ( $load ) {
			return $load;
		}
		return self::is_frontend_course_builder();
	}

	/**
	 * Add course builder style classes to the body tag
	 *
	 * @since 2.1.7
	 *
	 * @param array $classes array of classes.
	 *
	 * @return array
	 */
	public static function add_course_builder_classes( array $classes ): array {
		if ( self::is_frontend_course_builder() ) {
			$classes = array_merge( $classes, array( 'tutor-screen-course-builder', 'tutor-screen-course-builder-frontend' ) );
		}
		return $classes;
	}

	/**
	 * Check if course builder scripts required
	 *
	 * @since 2.1.7
	 *
	 * @return boolean
	 */
	private static function is_frontend_course_builder(): bool {
		$attachment = get_query_var( 'attachment' );
		return ! is_admin() && 'create-course' === $attachment;
	}

}
