<?php
/**
 * Manage database changes.
 *
 * @package TutorPro
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TUTOR_PRO;

use Tutor\Helpers\QueryHelper;
use TUTOR\User;
use TutorPro\CourseBundle\Backend\BundleList;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;

/**
 * Class Upgrader
 *
 * @since 2.2.0
 */
class Upgrader {
	/**
	 * Register hooks
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'migrations' ) );
		add_action( 'before_tutor_version_upgrade_to_2_6_0', array( $this, 'on_upgrade_to_2_6_0' ) );
	}

	/**
	 * Migrations
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function migrations() {
		if ( ! User::is_admin() ) {
			return;
		}

		global $wpdb;
		$version = get_option( 'tutor_version' );

		/**
		 * New `answer_explanation` field added to `tutor_quiz_questions` table.
		 *
		 * @since 2.2.0
		 */
		if ( version_compare( $version, '2.2.0', '<' ) ) {
			$table_name  = $wpdb->prefix . 'tutor_quiz_questions';
			$column_name = 'answer_explanation';

			// Check if the column already exists.
			$column_exists = $wpdb->query( $wpdb->prepare( "SHOW COLUMNS FROM $table_name LIKE %s", $column_name ) ); //phpcs:ignore

			// If the column doesn't exist, add it to the table.
			if ( 0 === $column_exists ) {
				//phpcs:ignore
				$modified = $wpdb->query( "ALTER TABLE $table_name ADD $column_name LONGTEXT DEFAULT '' AFTER question_description" );
				if ( $modified ) {
					update_option( 'tutor_version', TUTOR_VERSION );
				}
			}
		}

		/**
		 * Save a backup of email-related Tutor option data in the `tutor_email_default_config` option key.
		 * The keys specified in $backup_keys will be included in `tutor_option` when saving the default mail configuration.
		 *
		 * @since 2.5.0
		 */
		$email_default_config_key = 'tutor_email_default_config';
		$email_default_config     = get_option( $email_default_config_key );
		if ( version_compare( $version, '2.5.0', '<' ) && false === $email_default_config ) {
			$backup_keys = array(
				'tutor_email_template_logo_id',
				'email_logo_height',
				'email_from_name',
				'email_from_address',
				'email_footer_text',
			);

			$tutor_option = get_option( 'tutor_option' );
			$backup_data  = array();

			foreach ( $backup_keys as $key ) {
				$backup_data[ $key ] = isset( $tutor_option[ $key ] ) ? $tutor_option[ $key ] : '';
			}

			update_option( $email_default_config_key, $backup_data );

		}

		/**
		 * New `batch` column added to tutor email queue table
		 *
		 * @since 2.5.0
		 */
		if ( version_compare( $version, '2.5.0', '<' ) ) {
			if ( ! QueryHelper::table_exists( $wpdb->tutor_email_queue ) ) {
				return;
			}

			$column_name = 'batch';
			// Check if the column already exists.
			$column_exists = $wpdb->query( $wpdb->prepare( "SHOW COLUMNS FROM {$wpdb->tutor_email_queue} LIKE %s", $column_name ) );

			// If the column doesn't exist, add it to the table.
			if ( 0 === $column_exists ) {
				//phpcs:ignore
				$modified = $wpdb->query( "ALTER TABLE {$wpdb->tutor_email_queue} ADD $column_name VARCHAR(50) DEFAULT NULL AFTER headers" );
				if ( $modified ) {
					update_option( 'tutor_version', TUTOR_VERSION );
				}
			}
		}
	}

	/**
	 * On upgrade to 2.6.0 version
	 *
	 * @since 2.6.0
	 *
	 * @param string $installed_version installed tutor version.
	 *
	 * @return void
	 */
	public function on_upgrade_to_2_6_0( $installed_version ) {

		// Assign category for existing bundles.
		if ( version_compare( $installed_version, '2.6.0', '<' ) ) {
			$bundles = get_posts(
				array(
					'post_type'      => CourseBundle::POST_TYPE,
					'posts_per_page' => -1,
				)
			);
			foreach ( $bundles as $bundle ) {
				BundleList::assign_bundle_category( $bundle->ID );
			}
		}
	}
}
