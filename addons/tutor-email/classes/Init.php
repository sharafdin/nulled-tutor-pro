<?php
/**
 * E-mail addon init.
 *
 * @package TutorPro\Addon
 * @subpackage Email
 *
 * @since 2.0.0
 */

namespace TUTOR_EMAIL;

use TUTOR\Input;
use TUTOR\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 *
 * @since 2.0.0
 */
class Init {
	/**
	 * Version of the addon.
	 *
	 * @var string
	 */
	public $version = TUTOR_EMAIL_VERSION;

	/**
	 * Addon path.
	 *
	 * @var string
	 */
	public $path;

	/**
	 * Url
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Basename
	 *
	 * @var string
	 */
	public $basename;

	/**
	 * Email notification
	 *
	 * @var mixed
	 */
	private $email_notification;

	/**
	 * Addon enable check and register hooks.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		$addon_config = tutor_utils()->get_addon_config( TUTOR_EMAIL()->basename );
		$is_enable    = (bool) tutor_utils()->avalue_dot( 'is_enable', $addon_config );
		if ( ! $is_enable ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_EMAIL_FILE );
		$this->url      = plugin_dir_url( TUTOR_EMAIL_FILE );
		$this->basename = plugin_basename( TUTOR_EMAIL_FILE );

		$this->load_tutor_email();

		add_action( 'admin_init', array( $this, 'tutor_image_size_register' ) );
		add_action( 'template_redirect', array( $this, 'load_email_preview' ) );
	}

	/**
	 * Preview email template on an URL
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function load_email_preview() {
		if ( is_user_logged_in() && User::is_admin() && 'tutor-email-preview' === Input::get( 'page' ) ) {
			$template = Input::get( 'template' );
			$file     = tutor_get_template( 'email.' . $template, true );
			if ( file_exists( $file ) ) {
				ob_start();
				include $file;
				$footer_text = '<div class="tutor-email-footer-content" data-source="email_footer_text">' . json_decode( tutor_utils()->get_option( 'email_footer_text' ) ) . '</div>';
				$content     = ob_get_clean();
				$content     = $content . $footer_text;

				// already sanitized inside template file.
				echo $content; //phpcs:ignore.
				exit;
			}
		}
	}

	/**
	 * Register email logo size
	 *
	 * @return void
	 */
	public function tutor_image_size_register() {
		add_image_size( 'tutor-email-logo-size', 220, 50 );
	}

	/**
	 * Load tutor email
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function load_tutor_email() {
		/**
		 * Loading Autoloader
		 */
		spl_autoload_register( array( $this, 'loader' ) );
		$this->email_notification = new EmailNotification();

		/**
		 * Handle email settings
		 *
		 * @since 2.5.0
		 */
		new EmailSettings();

		/**
		 * Handle manual email.
		 *
		 * @since 2.5.0
		 */
		new ManualEmail();

		/**
		 * Handle email queue with cron
		 *
		 * @since 1.8.7
		 */
		new EmailCron();

		add_filter( 'tutor/options/attr', array( $this, 'add_options' ), 10 ); // Priority index is important. 'Content Drip' add-on uses 11.
	}

	/**
	 * Auto Load class and the files
	 *
	 * @since 2.0.0
	 *
	 * @param string $class_name class name.
	 *
	 * @return void
	 */
	private function loader( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			$class_name = preg_replace(
				array( '/([a-z])([A-Z])/', '/\\\/' ),
				array( '$1$2', DIRECTORY_SEPARATOR ),
				$class_name
			);

			$class_name = str_replace( 'TUTOR_EMAIL' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	/**
	 * Run
	 *
	 * @return void
	 */
	public function run() {
		register_activation_hook( TUTOR_EMAIL_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate() {
		$version = get_option( 'TUTOR_EMAIL_version' );
		// Save Option.
		if ( ! $version ) {
			update_option( 'TUTOR_EMAIL_version', TUTOR_EMAIL_VERSION );
		}
	}

	/**
	 * Get recipients
	 *
	 * @param mixed $key key.
	 *
	 * @return array
	 */
	private function get_recipient_array( $key = null ) {
		$recipients = ( new EmailData() )->get_recipients();

		if ( null === $key ) {
			$new_array = array();
			foreach ( $recipients as $recipient ) {
				$new_array = array_merge( $new_array, $recipient );
			}

			return $new_array;
		}

		$admin_url = admin_url( 'admin.php' );
		$array     = $recipients[ $key ];
		$fields    = array();

		foreach ( $recipients[ $key ] as $event => $mail ) {
			$tooltip        = ( isset( $mail['tooltip'] ) && ! empty( $mail['tooltip'] ) ) ? $mail['tooltip'] : null;
			$email_edit_url = add_query_arg(
				array(
					'page'     => 'tutor_settings',
					'tab_page' => 'email_notification',
					'edit'     => $event,
					'to'       => $key,
				),
				$admin_url
			);

			$fields[] = array(
				'key'      => $key,
				'event'    => $event,
				'type'     => 'toggle_switch_button',
				'label'    => $mail['label'],
				'template' => $mail['template'],
				'tooltip'  => $tooltip,
				'default'  => isset( $mail['default'] ) ? esc_attr( $mail['default'] ) : esc_attr( 'off' ),
				'buttons'  => array(
					'edit' => array(
						'type' => 'anchor',
						'text' => __( 'Edit', 'tutor-pro' ),
						'url'  => $email_edit_url,
					),
				),
			);
		}

		return $fields;
	}

	/**
	 * Email option and types
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed $attr attributes.
	 *
	 * @return array
	 */
	public function add_options( $attr ) {

		$template_path = null;
		$template_data = null;

		if ( 'settings' === Input::get( 'edit' ) ) {
			$template_path = TUTOR_EMAIL()->path . '/views/pages/settings.php';
		}

		if ( 'mailer' === Input::get( 'edit' ) ) {
			$template_path = TUTOR_EMAIL()->path . '/views/pages/mailer.php';
		}

		if ( Input::has( 'edit' ) && ! in_array( Input::get( 'edit' ), array( 'settings', 'mailer' ), true ) ) {
			$template_path = TUTOR_EMAIL()->path . '/views/pages/email-edit.php';
			$to            = Input::get( 'to' );
			$edit          = Input::get( 'edit' );

			if ( 'email_to_students' === $to ) {
				$to_readable = __( 'Email to Student', 'tutor-pro' );
			} elseif ( 'email_to_teachers' === $to ) {
				$to_readable = __( 'Email to Instructor', 'tutor-pro' );
			} elseif ( 'email_to_admin' === $to ) {
				$to_readable = __( 'Email to Admin', 'tutor-pro' );
			} else {
				$to_readable = ucwords( str_replace( '_', ' ', $to ) );
			}

			$template_data = array(
				'to'          => $to,
				'key'         => $edit,
				'edit'        => $edit,
				'to_readable' => $to_readable,
				'mail'        => $this->get_recipient_array()[ $edit ],
				'back_url'    => add_query_arg(
					array(
						'page'     => 'tutor_settings',
						'tab_page' => 'email_notification',
					),
					admin_url( 'admin.php' )
				),
			);

			$placeholders = array();
			if ( isset( $template_data['mail']['placeholders'] ) && is_array( $template_data['mail']['placeholders'] ) ) {
				$placeholders = array_values( $template_data['mail']['placeholders'] );
			}

			wp_localize_script( 'tutor-pro-email-template', '_tutorEmailPlaceholders', $placeholders );
		}

		$attr['email_notification'] = array(
			'label'           => __( 'Email', 'tutor-pro' ),
			'slug'            => 'email_notification',
			'desc'            => '',
			'template'        => 'basic',
			'icon'            => 'tutor-icon-envelope',
			'template_path'   => $template_path,
			'edit_email_data' => $template_data,
			'blocks'          => array(
				array(
					'label'         => null,
					'slug'          => 'email-settings-options',
					'block_type'    => 'custom',
					'placement'     => 'before',
					'template_path' => TUTOR_EMAIL()->path . 'views/email-settings-options.php',
				),
				array(
					'label'      => __( 'Email to Students', 'tutor-pro' ),
					'slug'       => 'email_to_students',
					'block_type' => 'uniform',
					'fields'     => $this->get_recipient_array( 'email_to_students' ),
				),
				array(
					'label'      => __( 'Email to Instructors', 'tutor-pro' ),
					'slug'       => 'email_to_teachers',
					'block_type' => 'uniform',
					'fields'     => $this->get_recipient_array( 'email_to_teachers' ),
				),
				array(
					'label'      => __( 'Email to Admin', 'tutor-pro' ),
					'slug'       => 'email_to_admin',
					'block_type' => 'uniform',
					'fields'     => $this->get_recipient_array( 'email_to_admin' ),
				),
				array(
					'label'      => __( 'Email Cron Settings', 'tutor-pro' ),
					'slug'       => 'email_sending',
					'block_type' => 'uniform',
					'fields'     => array(
						array(
							'key'     => 'tutor_email_disable_wpcron',
							'label'   => __( 'WP Cron for Bulk Mailing', 'tutor-pro' ),
							'type'    => 'toggle_switch',
							'default' => 'off',
							'desc'    => __( 'Enable this option to let Tutor LMS use WordPress native scheduler for email sending activities', 'tutor-pro' ),
						),
						array(
							'key'     => 'tutor_email_cron_frequency',
							'label'   => __( 'WP Email Cron Frequency', 'tutor-pro' ),
							'type'    => 'number',
							'min'     => 10,
							'default' => 300,
							'desc'    => __( 'Add the frequency mode in <strong>Second(s)</strong> which the Cron Setup will run', 'tutor-pro' ),
						),
						array(
							'key'         => 'tutor_bulk_email_limit',
							'label'       => __( 'Email Per Cron Execution', 'tutor-pro' ),
							'type'        => 'number',
							'number_type' => 'integer',
							'min'         => 1,
							'default'     => 10,
							'desc'        => __( 'Number of emails you\'d like to send per cron execution', 'tutor-pro' ),
						),
					),
				),
			),
		);

		return $attr;
	}
}
