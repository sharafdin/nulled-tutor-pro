<?php
/**
 * Certificate Addon Init
 *
 * @package TutorPro/Addons
 * @subpackage Certificate
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.0.0
 */

namespace TUTOR_CERT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 */
class Init {
	//phpcs:disable
	public $version = TUTOR_CERT_VERSION;
	public $path;
	public $url;
	public $basename;
	public $certificate;
	//phpcs:enable

	/**
	 * Constructor
	 *
	 * @return mixed
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}
		$addon_config = tutor_utils()->get_addon_config( TUTOR_CERT()->basename );
		$is_enable    = (bool) tutor_utils()->avalue_dot( 'is_enable', $addon_config );
		if ( ! $is_enable ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_CERT_FILE );
		$this->url      = plugin_dir_url( TUTOR_CERT_FILE );
		$this->basename = plugin_basename( TUTOR_CERT_FILE );

		$this->load_certificate_addon();

		new Instructor_Signature();

		add_filter(
			'tutor_pages',
			function( array $pages ) {
				return $pages + array( 'tutor_certificate_page' => __( 'Tutor Certificate', 'tutor' ) );
			}
		);

		add_action(
			'init',
			function() {
				if ( ! wp_doing_ajax() ) {
					$this->generate_tutor_certificate_page();
				}
			}
		);
	}

	/**
	 * Create certificate page & update page ID to tutor option.
	 *
	 * @since 2.1.7
	 *
	 * @return void
	 */
	private function generate_tutor_certificate_page() {
		$certificate_page_id = (int) tutor_utils()->get_option( 'tutor_certificate_page' );
		if ( in_array( $certificate_page_id, array( 0, -1 ) ) ) {
			$post_details = array(
				'post_title'   => __( 'Tutor Certificate', 'tutor' ),
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id      = wp_insert_post( $post_details );
			update_tutor_option( 'tutor_certificate_page', $page_id );
		}
	}

	/**
	 * Load addon
	 *
	 * @return void
	 */
	public function load_certificate_addon() {
		spl_autoload_register( array( $this, 'loader' ) );
		$this->certificate = new Certificate();

		add_filter( 'tutor/options/extend/attr', array( $this, 'add_options' ), 10 );
	}

	/**
	 * Class autoloader
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

			$class_name = str_replace( 'TUTOR_CERT' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Add options
	 *
	 * @param array $attr attributes.
	 *
	 * @return array
	 */
	public function add_options( $attr ) {
		$pages                     = tutor_utils()->get_pages();
		$attr['tutor_certificate'] = array(
			'label'    => __( 'Certificate', 'tutor-pro' ),
			'slug'     => 'tutor_certificate',
			'desc'     => __( 'All Certificate Settings', 'tutor-pro' ),
			'template' => 'tab',
			'icon'     => 'tutor-icon-certificate-landscape',
			'blocks'   => array(
				array(
					'label'    => __( 'Certificate Settings', 'tutor-pro' ),
					'slug'     => 'certificate_settings',
					'segments' => array(
						array(
							'label'      => __( 'Legacy Certificate Settings', 'tutor-pro' ),
							'block_type' => 'uniform',
							'fields'     => array(
								array(
									'key'         => 'tutor_cert_authorised_name',
									'type'        => 'text',
									'default'     => __( 'Authorised Name', 'tutor-pro' ),
									'label'       => __( 'Authorised Name', 'tutor-pro' ),
									'desc'        => __( 'Authorised name will be printed under signature.', 'tutor-pro' ),
									'placeholder' => __( 'Enter authorised name', 'tutor-pro' ),
								),
								array(
									'key'         => 'tutor_cert_authorised_company_name',
									'type'        => 'text',
									'default'     => __( 'Company Name', 'tutor-pro' ),
									'label'       => __( 'Authorised Company Name', 'tutor-pro' ),
									'desc'        => __( 'Authorised company name will be printed under authorised name.', 'tutor-pro' ),
									'placeholder' => __( 'Enter authorised company name', 'tutor-pro' ),
								),
								array(
									'key'     => 'tutor_certificate_page',
									'type'    => 'select',
									'label'   => __( 'Certificate Page', 'tutor' ),
									'default' => '0',
									'options' => $pages,
									'desc'    => __( 'Choose the page for certificate.', 'tutor' ),
								),
								array(
									'key'         => 'show_instructor_name_on_certificate',
									'type'        => 'toggle_switch',
									'default'     => 'off',
									'label'       => __( 'Show instructor name on certificate', 'tutor-pro' ),
									'label_title' => '',
									'desc'        => __( 'Show instructor name on certificate before Authorised Name', 'tutor-pro' ),
								),
								array(
									'key'         => 'send_certificate_link_to_course_completion_email',
									'type'        => 'toggle_switch',
									'default'     => 'off',
									'label'       => __( 'Certificate link in course completion email', 'tutor-pro' ),
									'label_title' => '',
									'desc'        => __( 'Send certificate link along with the course completion email. Student must be logged in to access the certificate if public view is not enabled.', 'tutor-pro' ),
								),
								array(
									'key'     => 'tutor_cert_signature_image_id',
									'type'    => 'upload_full',
									'label'   => __( 'Upload Signature', 'tutor-pro' ),
									'default' => TUTOR_CERT()->url . 'assets/images/signature.png',
									'desc'    => __( 'Upload a signature that will be printed at certificate', 'tutor-pro' ),
								),
							),
						),
					),
				),
			),
		);

		/**
		 * Certificate Showcase Options
		 *
		 * @since 2.2.3
		 */
		$certificate_showcase_options = array(
			array(
				'key'           => 'enable_certificate_showcase',
				'type'          => 'toggle_switch',
				'label'         => __( 'Showcase Certificate', 'tutor-pro' ),
				'default'       => 'off',
				'desc'          => __( 'Enable to show certificate on course details', 'tutor-pro' ),
				'toggle_fields' => 'certificate_showcase_title,certificate_showcase_desc',
			),
			array(
				'key'         => 'certificate_showcase_title',
				'type'        => 'text',
				'label'       => __( 'Title', 'tutor-pro' ),
				'placeholder' => __( 'Insert your title here', 'tutor-pro' ),
				'desc'        => __( 'Enter a title for the certificate showcase section.', 'tutor-pro' ),
				'default'     => __( 'Earn a certificate', 'tutor-pro' ),
				'maxlength'   => 45,
			),
			array(
				'key'         => 'certificate_showcase_desc',
				'type'        => 'textarea',
				'label'       => __( 'Description', 'tutor-pro' ),
				'placeholder' => __( 'Insert your description here', 'tutor-pro' ),
				'desc'        => __( 'Enter a description for the certificate showcase section.', 'tutor-pro' ),
				'maxlength'   => 110,
				'rows'        => 4,
				'default'     => __( 'Add this certificate to your resume to demonstrate your skills & increase your chances of getting noticed.', 'tutor-pro' ),
			),
		);

		array_splice(
			$attr['design']['blocks'],
			3,
			0,
			array(
				array(
					'slug'       => 'certificate-showcase',
					'block_type' => 'uniform',
					'fields'     => $certificate_showcase_options,
				),
			)
		);

		return $attr;
	}
}
