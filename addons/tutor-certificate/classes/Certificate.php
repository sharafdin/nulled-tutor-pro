<?php
/**
 * Certificate
 *
 * @package TutorPro\Addon
 * @subpackage Certificate
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_CERT;

use TUTOR\Input;

/**
 * Class Certificate
 *
 * @since 2.0.0
 */
class Certificate {
	/**
	 * Template
	 *
	 * @var string
	 */
	private $template;
	/**
	 * Directory name
	 *
	 * @var string
	 */
	public $certificates_dir_name = 'tutor-certificates';
	/**
	 * Store Key
	 *
	 * @var string
	 */
	public $certificate_stored_key = 'tutor_certificate_has_image';
	/**
	 * Meta key
	 *
	 * @var string
	 */
	public static $template_meta_key = 'tutor_course_certificate_template';
	/**
	 * Image Url Base
	 *
	 * @var string
	 */
	public static $certificate_img_url_base = 'https://preview.tutorlms.com/certificate-templates/';

	/**
	 * Register hooks
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $reuse reuse.
	 */
	public function __construct( $reuse = false ) {
		if ( ! function_exists( 'tutor_utils' ) || true === $reuse ) {
			return;
		}

		add_action( 'tutor_course/single/actions_btn_group/before', array( $this, 'certificate_download_btn' ) );

		add_action( 'wp_loaded', array( $this, 'get_fonts' ) );

		/**
		 * Hook `template_redirect` to `template_include`
		 * for elementor custom header footer support.
		 *
		 * @since 2.4.0
		 */
		add_filter( 'template_include', array( $this, 'view_certificate' ) );

		add_action( 'wp_ajax_tutor_generate_course_certificate', array( $this, 'send_certificate_html' ) );
		add_action( 'wp_ajax_nopriv_tutor_generate_course_certificate', array( $this, 'send_certificate_html' ) );

		add_action( 'wp_ajax_tutor_store_certificate_image', array( $this, 'store_certificate_image' ) );
		add_action( 'wp_ajax_nopriv_tutor_store_certificate_image', array( $this, 'store_certificate_image' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_script' ) );
		add_action( 'wp_head', array( $this, 'certificate_header_content' ) );

		/**
		 * Certificate template metabox in course for per course template
		 *
		 * @since v1.9.0
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'load_field_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_metabox_in_course' ) );
		add_action( 'tutor/dashboard_course_builder_form_field_after', array( $this, 'frontend_course_certificate' ), 20 );
		add_action( 'tutor_save_course', array( $this, 'save_certificate_template_meta' ) );

		/**
		 * Certificate builder support
		 *
		 * @since v1.9.11
		 */
		add_filter( 'tutor_certificate_completion_data', array( $this, 'completed_course' ), 10, 2 );
		add_filter( 'tutor_certificate_public_url', array( $this, 'tutor_certificate_public_url' ), 10, 1 );
		add_filter( 'tutor_certificate_instructor_signature', array( $this, 'get_signature_url' ), 10, 2 );

		/**
		 * Show whether single course has course in course sidebar meta
		 *
		 * @ince v2.0.0
		 */
		add_filter( 'tutor/course/single/sidebar/metadata', array( $this, 'show_course_has_certificate' ), 10, 2 );

		// Download certificate button for completed courses in all kind of archive.
		add_filter( 'tutor_course/loop/start/button', array( $this, 'download_btn_in_archive' ), 99, 2 );

		add_action( 'admin_footer', array( $this, 'add_button_to_certificate_edit_page' ) );

		add_action( 'tutor_course/single/after/topics', array( $this, 'add_certificate_showcase' ) );

		// Alter yoast og tags.
		add_filter( 'wpseo_opengraph_url', array( $this, 'remove_yoast_seo_og_tags' ), 10, 1 );
		add_filter( 'wpseo_opengraph_title', array( $this, 'remove_yoast_seo_og_tags' ), 10, 1 );
		add_filter( 'wpseo_opengraph_title', array( $this, 'remove_yoast_seo_og_tags' ), 10, 1 );
		add_filter( 'wpseo_opengraph_desc', array( $this, 'remove_yoast_seo_og_tags' ), 10, 1 );
		add_filter( 'wpseo_opengraph_image', array( $this, 'remove_yoast_seo_og_tags' ), 10, 1 );
	}

	/**
	 * Certificate Showcase
	 *
	 * @since 2.2.3
	 *
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public function add_certificate_showcase( $course_id ) {
		$is_enabled = (bool) tutor_utils()->get_option( 'enable_certificate_showcase', false );
		if ( ! $is_enabled ) {
			return;
		}

		$template_key = get_post_meta( $course_id, self::$template_meta_key, true );
		if ( in_array( $template_key, array( '', 'none', 'off' ) ) ) {
			return;
		}

		$templates = $this->get_templates();
		if ( ! isset( $templates[ $template_key ] ) ) {
			return;
		}

		$template = $templates[ $template_key ];
		?>

		<div id="tutor-certificate-showcase" class="tutor-my-52">
			<div class="tutor-cs-text">
				<div>
					<h3 class="tutor-course-details-widget-title tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-8">
						<?php echo esc_html( tutor_utils()->get_option( 'certificate_showcase_title', '' ) ); ?>
					</h3>
					<p><?php echo esc_html( tutor_utils()->get_option( 'certificate_showcase_desc', '' ) ); ?></p>
				</div>
			</div>
			<div class="tutor-cs-wrapper">
				<div class="tutor-cs-image-wrapper">
					<img src="<?php echo esc_url( $template['preview_src'] ); ?>" alt="selected template">
				</div>
			</div>	
		</div>

		<?php
	}

	/**
	 * Add button to certificate page edit mode.
	 *
	 * @since 2.1.7
	 *
	 * @return void
	 */
	public function add_button_to_certificate_edit_page() {
		global $post;
		$certificate_page_id = (int) tutor_utils()->get_option( 'tutor_certificate_page' );

		if ( isset( $post ) && $certificate_page_id === $post->ID ) {
			$hash = get_transient( 'tutor_cert_hash' );
			$url  = '#';
			if ( false !== $hash ) {
				$url = home_url( $post->post_name . '?cert_hash=' . $hash );
				?>
				<script type="text/javascript">
					jQuery(document).ready( function($) {
						setTimeout(function(){
							let btn = '<a href="<?php echo esc_url( $url ); ?>" class="button" target="_blank">View Certificate</a>';
							$('div.edit-post-header__settings').prepend( btn );
						})
					});
				</script>
				<?php
			}
		}
	}

	/**
	 * Download button in archive
	 *
	 * @param string $html html.
	 * @param int    $course_id course id.
	 *
	 * @return string
	 */
	public function download_btn_in_archive( $html, $course_id ) {
		$completed_percent   = tutor_utils()->get_course_completed_percent();
		$is_completed_course = tutor_utils()->is_completed_course();
		$completed_anyway    = $is_completed_course || $completed_percent >= 100;
		// If course completed.
		if ( $is_completed_course ) {
			//phpcs:ignore
			if ( $this->has_course_certificate_template( $course_id ) && $certificate_url = $this->get_certificate( $course_id ) ) {
				$html = '<a href="' . $certificate_url . '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block">
					' . __( 'Download Certificate', 'tutor-pro' ) . '
				</a>';
			} else {
				$html = '<button disabled="disabled" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block">
					' . __( 'Download Certificate', 'tutor-pro' ) . '
				</button>';
			}
		}

		return $html;
	}

	/**
	 * Get certificate public URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $cert_hash unique hash.
	 * @return string
	 */
	public function tutor_certificate_public_url( $cert_hash ) {
		$url     = '#';
		$page_id = (int) tutor_utils()->get_option( 'tutor_certificate_page' );

		if ( ! in_array( $page_id, array( 0, -1 ) ) ) {
			$page = get_post( $page_id );
			$url  = home_url() . DIRECTORY_SEPARATOR . $page->post_name . '?cert_hash=' . $cert_hash;
		}

		return $url;
	}

	/**
	 * Register certificate template
	 *
	 * @return void
	 */
	public function register_metabox_in_course() {
		$post_type = apply_filters( 'tutor_certificate_template_post_type', tutor()->course_post_type );
		tutor_meta_box_wrapper(
			' tutor-certificate-template-selection',
			__( 'Certificate Template', 'tutor-pro' ),
			array( $this, 'render_template_selection_ui' ),
			$post_type,
			'advanced',
			'default',
			'tutor-admin-post-meta'
		);
	}

	/**
	 * Load field scripts.
	 *
	 * @return void
	 */
	public function load_field_scripts() {
		if ( isset( $_GET['page'] ) && 'tutor_settings' === $_GET['page'] ) {
			wp_enqueue_style( 'tutor-pro-certificate-field-css', TUTOR_CERT()->url . 'assets/css/certificate-field.css', array(), TUTOR_PRO_VERSION );
		}
	}

	/**
	 * Save certificate template meta.
	 *
	 * @param int $post_id post id.
	 *
	 * @return void
	 */
	public function save_certificate_template_meta( $post_id ) {
		if ( Input::has( self::$template_meta_key ) ) {
			update_post_meta( $post_id, self::$template_meta_key, Input::post( self::$template_meta_key ) );
		}
	}

	/**
	 * Frontend course certificate
	 *
	 * @param mixed $post post.
	 *
	 * @return void
	 */
	public function frontend_course_certificate( $post ) {
		?>
		<div class="tutor-course-builder-section tutor-course-builder-info">
			<div class="tutor-course-builder-section-title">
				<span class="tutor-fs-5 tutor-fw-bold tutor-color-secondary">
					<i class="tutor-icon-angle-up" area-hidden="true"></i>
					<span>
						<?php esc_html_e( 'Certificate Template', 'tutor-pro' ); ?>
					</span>
				</span>
			</div>
			<div class="tutor-course-builder-section-content">
				<div class="tutor-frontend-builder-item-scope">
					<div class="tutor-form-group">
						<?php $this->render_template_selection_ui( $post, true ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Load Script
	 *
	 * @return void
	 */
	public function load_script() {
		if ( ! empty( $_GET['cert_hash'] ) ) {
			$base = tutor_pro()->url . 'addons/tutor-certificate/assets/js/';
			wp_enqueue_script( 'html-to-image', $base . 'html-to-image.js', array( 'jquery', 'wp-i18n' ), TUTOR_PRO_VERSION, true );
		}
	}

	/**
	 * Get Fonts
	 *
	 * @return void
	 */
	public function get_fonts() {
		if ( 'get_fonts' !== Input::get( 'tutor_action' ) ) {
			return;
		}

		$url_base  = tutor_pro()->url . 'addons/tutor-certificate/assets/fonts/';
		$path_base = $this->cross_platform_path( dirname( __DIR__ ) . '/assets/css/' );

		$default_files = $path_base . 'font-loader.css';
		$default_fonts = file_get_contents( $default_files );

		$font_faces = str_replace( './fonts/', $url_base, $default_fonts );

		// Now load template fonts.
		$this->prepare_template_data( Input::get( 'course_id' ) );
		$font_css = $this->template['path'] . 'font.css';
		if ( file_exists( $font_css ) ) {
			$faces       = file_get_contents( $font_css );
			$faces       = str_replace( './fonts/', $this->template['url'] . 'fonts/', $faces );
			$font_faces .= $faces;
		}

		exit( $font_faces );//phpcs:ignore
	}

	/**
	 * Send Certificate HTML
	 *
	 * @return void
	 */
	public function send_certificate_html() {
		tutor_utils()->checking_nonce();
		$course_id = Input::post( 'course_id', '' );
		$cert_hash = Input::post( 'certificate_hash' );

		if ( $course_id && is_numeric( $course_id ) ) {

			$this->prepare_template_data( $course_id );
			$completed = $cert_hash ? $this->completed_course( $cert_hash ) : false;

			if ( strpos( $this->template['key'], 'tutor_cb_' ) === 0 ) {
				$template_id = preg_replace( '/\D/', '', $this->template['key'] );
				wp_send_json_success(
					array(
						'certificate_builder_url' => apply_filters(
							'tutor_certificate_builder_url',
							$template_id,
							array(
								'cert_hash'   => $cert_hash,
								'course_id'   => $course_id,
								'orientation' => $this->template['orientation'],
								'format'      => Input::post( 'format', 'jpg' ),
							)
						),
					)
				);
				exit;
			}

			// Get certificate html.
			$content = $this->generate_certificate( $course_id, $completed );
			wp_send_json_success( array( 'html' => $content ) );
		}

		wp_send_json_error( array( 'message' => __( 'Invalid Course ID', 'tutor' ) ) );
	}

	/**
	 * Course Platform Path
	 *
	 * @param string $path path.
	 *
	 * @return string
	 */
	private function cross_platform_path( $path ) {
		$path = str_replace( '/', DIRECTORY_SEPARATOR, $path );
		$path = str_replace( '\\', DIRECTORY_SEPARATOR, $path );

		return $path;
	}

	/**
	 * Prepare template data.
	 *
	 * @param int     $course_id course id.
	 * @param boolean $check_if_none check has or not.
	 *
	 * @return mixed
	 */
	private function prepare_template_data( $course_id, $check_if_none = false ) {
		if ( ! $this->template ) {

			// Get from settings. Set default one if not set somehow.
			$template               = tutor_utils()->get_option( 'certificate_template' );
			! $template ? $template = 'default' : 0;

			$global_template = $template;

			// Assign from course meta if custom one chosen.
			$course_template = get_post_meta( $course_id, self::$template_meta_key, true );

			// Get the selected template.
			$template_arg                      = array();
			$template_arg[]                    = $template;
			$course_template ? $template_arg[] = $course_template : 0;
			$templates                         = $this->get_templates( false, false, $template_arg );

			( $course_template && isset( $templates[ $course_template ] ) ) ? $template = $course_template : 0;

			// If explicitly set as none.
			if ( $check_if_none && in_array( $course_template, array( 'none', 'off' ) ) ) {
				return false;
			}

			// Make sure not to use templates from builder if the plugin is not active.
			if ( strpos( $template, 'tutor_cb_' ) === 0 && ! tutor_utils()->is_plugin_active( 'tutor-lms-certificate-builder/tutor-lms-certificate-builder.php' ) ) {
				// Use default if builder is not active somehow.
				$template = $global_template;
			}

			$this->template = tutor_utils()->avalue_dot( $template, $templates );
		}
	}

	/**
	 * Store certificate image
	 *
	 * @return void
	 */
	public function store_certificate_image() {
		tutor_utils()->checking_nonce();
		// Collect post data.
		$hash      = Input::post( 'cert_hash', '' );
		$completed = is_string( $hash ) ? $this->completed_course( $hash ) : null;

		// Check if the course is complete.
		if ( ! $completed ) {
			wp_send_json_error( array( 'message' => __( 'Course not yet completed', 'tutor-pro' ) ) );
			return;
		}

		// Check if valid image.
		//phpcs:ignore
		if ( ! isset( $_FILES['certificate_image'] ) || $_FILES['certificate_image']['error'] || $_FILES['certificate_image']['type'] !== 'image/jpeg' ) 
		{
			wp_send_json_error( array( 'message' => __( 'Certificate Image Error', 'tutor-pro' ) ) );
		}

		// The dir from outside of the filter hook. Otherwise infinity loop will coccur.
		$certificates_dir = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $this->certificates_dir_name;
		$rand_string      = substr( str_shuffle( str_repeat( $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( 10 / strlen( $x ) ) ) ), 1, 10 );//phpcs:ignore

		// Store new file.
		wp_mkdir_p( $certificates_dir );
		$file_dest = $certificates_dir . DIRECTORY_SEPARATOR . $rand_string . '-' . $hash . '.jpg';
		move_uploaded_file( $_FILES['certificate_image']['tmp_name'], $file_dest );//phpcs:ignore

		// Delete old one.
		$old_rand_string = get_comment_meta( $completed->certificate_id, $this->certificate_stored_key, true );
		$old_path        = $this->cross_platform_path( $certificates_dir . '/' . $old_rand_string . '-' . $hash . '.jpg' );
		file_exists( $old_path ) ? unlink( $old_path ) : 0;

		// Update new.
		update_comment_meta( $completed->certificate_id, $this->certificate_stored_key, $rand_string );

		wp_send_json_success();
	}

	/**
	 * View Certificate
	 *
	 * @since 1.5.1
	 *
	 * @param string $template template path.
	 *
	 * @return string
	 */
	public function view_certificate( $template ) {

		$cert_hash = sanitize_text_field( tutor_utils()->array_get( 'cert_hash', $_GET ) );

		if ( ! $cert_hash || ! empty( $_GET['tutor_action'] ) ) {
			return $template;
		}

		$completed = $this->completed_course( $cert_hash );
		if ( ! is_object( $completed ) || ! property_exists( $completed, 'completed_user_id' ) ) {
			return $template;
		}

		/**
		 * Load the certificate in WordPress native page.
		 *
		 * @since 2.1.7
		 */
		global $post;

		if ( isset( $post ) && 'page' === $post->post_type ) {
			$certificate_page_id = (int) tutor_utils()->get_option( 'tutor_certificate_page' );
			if ( $post->ID === $certificate_page_id ) {
				set_transient( 'tutor_cert_hash', $cert_hash );
				return TUTOR_CERT()->path . '/views/single-certificate.php';
			}
		}

		return $template;
	}

	/**
	 * Get Signature URL
	 *
	 * @param int   $instructor_id instructor id.
	 * @param mixed $use_default default.
	 *
	 * @return string
	 */
	public function get_signature_url( $instructor_id, $use_default = null ) {

		// Get personal signature first.
		$custom_signature    = ( new Instructor_Signature( false ) )->get_instructor_signature( $instructor_id );
		$signature_image_url = $custom_signature['url'];

		// Set default signature from global setting if personal one is not set.
		if ( ! $signature_image_url ) {
			// Get default ID.
			$default_sinature_id = (int) tutor_utils()->get_option( 'tutor_cert_signature_image_id' );

			if ( ! $default_sinature_id && false === $use_default ) {
				return null;
			}

			// Assign default image from plugin file system if even global one is not set yet.
			$signature_image_url = $default_sinature_id ?
										wp_get_attachment_url( $default_sinature_id ) :
										TUTOR_CERT()->url . 'assets/images/signature.png';
		}

		return $signature_image_url;
	}

	/**
	 * Generate Certificate.
	 *
	 * @param int     $course_id course id.
	 * @param boolean $completed completed.
	 *
	 * @return mixed
	 */
	public function generate_certificate( $course_id, $completed = false ) {
		$duration         = get_post_meta( $course_id, '_course_duration', true );
		$duration_hours   = (int) tutor_utils()->avalue_dot( 'hours', $duration );
		$duration_minutes = (int) tutor_utils()->avalue_dot( 'minutes', $duration );
		$course           = get_post( $course_id );
		$completed        = $completed ? $completed : tutor_utils()->is_completed_course( $course_id );
		$user             = $completed ? get_userdata( $completed->completed_user_id ) : wp_get_current_user();
		$completed_date   = '';
		if ( $completed ) {
			$wp_date_format = get_option( 'date_format' );
			$completed_date = gmdate( $wp_date_format, strtotime( $completed->completion_date ) );

			// Translate month name.
			$converter      = function ( $matches ) {
				$month = __( $matches[0] );//phpcs:ignore

				// Make first letter uppercase if it's not unicode character.
				strlen( $month ) == strlen( utf8_decode( $month ) ) ? $month = ucfirst( $month ) : 0;

				return $month;
			};
			$completed_date = preg_replace_callback( '/[a-z]+/i', $converter, $completed_date );

			// Translate day and year digits.
			$completed_date = preg_replace_callback(
				'/[0-9]/',
				function ( $m ) {
					return __( $m[0] );//phpcs:ignore
				},
				$completed_date
			);
		}

		// Prepare signature image.
		$signature_image_url = $this->get_signature_url( $course->post_author );

		// Include instructor name if enabled.
		$enabled = tutor_utils()->get_option( 'show_instructor_name_on_certificate', false );

		if ( $enabled ) {

			$user_info       = get_userdata( $course->post_author );
			$instructor_name = $user_info ? $user_info->display_name : '';

			add_filter(
				'tutor_cert_authorised_name',
				function( $authorized ) use ( $instructor_name ) {
					$suthorized = is_string( $authorized ) ? trim( $authorized ) : '';
					$authorized = $instructor_name . ( strlen( $authorized ) ? ', ' : '' ) . $authorized;

					return $authorized;
				}
			);
		}

		// Generate duration text.
		$hour_text = '';
		$min_text  = '';

		if ( $duration_hours ) {
			/* translators: %s: hour number */
			$hour_text = sprintf( _n( '%s hour', '%s hours', $duration_hours, 'tutor-pro' ), number_format_i18n( $duration_hours ) );
		}
		if ( $duration_minutes ) {
			/* translators: %s: minute number */
			$min_text = sprintf( _n( '%s minute', '%s minutes', $duration_minutes, 'tutor-pro' ), number_format_i18n( $duration_minutes ) );
		}

		$duration_text = $hour_text . ' ' . $min_text;

		ob_start();
		include $this->template['path'] . 'certificate.php';
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * PDF style
	 *
	 * @return void
	 */
	public function pdf_style() {
		$css = $this->template['path'] . 'pdf.css';

		ob_start();
		if ( file_exists( $css ) ) {
			include $css;
		}
		$css = ob_get_clean();
		$css = apply_filters( 'tutor_cer_css', $css, $this );

		echo $css;//phpcs:ignore
	}

	/**
	 * Download button for certificate
	 *
	 * @return void
	 */
	public function certificate_download_btn() {

		$course_id   = get_the_ID();
		$certificate = $this->get_certificate( $course_id, true );

		if ( ! $certificate || $this->prepare_template_data( $course_id, true ) === false ) {
			// No certificate assigned or course not completed.
			return;
		}

		// It has $certificate_hash, $certificate_url.
		extract( $certificate );//phpcs:ignore

		ob_start();
		include TUTOR_CERT()->path . 'views/lesson-menu-after.php';
		$content = ob_get_clean();

		echo $content;//phpcs:ignore
	}

	/**
	 * Generate certificate template selection UI in frontend and backend course builder
	 *
	 * @param mixed   $post post.
	 * @param boolean $course_builder course builder.
	 *
	 * @return void
	 */
	public function render_template_selection_ui( $post = null, $course_builder = false ) {
		$templates           = $this->get_templates( true, true );
		$selected_template   = tutor_utils()->get_option( 'certificate_template' );
		$template_field_name = 'tutor_option[certificate_template]';

		if ( $post && is_object( $post ) ) {

			$template_field_name = self::$template_meta_key;
			$template            = get_post_meta( $post->ID, self::$template_meta_key, true );

			( $template && isset( $templates[ $template ] ) ) ? $selected_template = $template : 0;
		}

		$template = $course_builder ? 'template_metabox' : 'template_options';
		include TUTOR_CERT()->path . 'views/' . $template . '.php';
	}

	/**
	 * Get templates
	 *
	 * @param boolean $add_none add none.
	 * @param boolean $include_admins include admins.
	 * @param array   $template_in template in.
	 *
	 * @return array
	 */
	public function get_templates( $add_none = false, $include_admins = false, $template_in = array() ) {
		$templates = array(
			'default'     => array(
				'name'        => 'Default',
				'orientation' => 'landscape',
			),
			'template_1'  => array(
				'name'        => 'Abstract Landscape',
				'orientation' => 'landscape',
			),
			'template_2'  => array(
				'name'        => 'Abstract Portrait',
				'orientation' => 'portrait',
			),
			'template_3'  => array(
				'name'        => 'Decorative Landscape',
				'orientation' => 'landscape',
			),
			'template_4'  => array(
				'name'        => 'Decorative Portrait',
				'orientation' => 'portrait',
			),
			'template_5'  => array(
				'name'        => 'Geometric Landscape',
				'orientation' => 'landscape',
			),
			'template_6'  => array(
				'name'        => 'Geometric Portrait',
				'orientation' => 'portrait',
			),
			'template_7'  => array(
				'name'        => 'Minimal Landscape',
				'orientation' => 'landscape',
			),
			'template_8'  => array(
				'name'        => 'Minimal Portrait',
				'orientation' => 'portrait',
			),
			'template_9'  => array(
				'name'        => 'Floating Landscape',
				'orientation' => 'landscape',
			),
			'template_10' => array(
				'name'        => 'Floating Portrait',
				'orientation' => 'portrait',
			),
			'template_11' => array(
				'name'        => 'Stripe Landscape',
				'orientation' => 'landscape',
			),
			'template_12' => array(
				'name'        => 'Stripe Portrait',
				'orientation' => 'portrait',
			),
		);

		foreach ( $templates as $key => $template ) {

			$path = trailingslashit( TUTOR_CERT()->path . 'templates/' . $key );
			$url  = trailingslashit( TUTOR_CERT()->url . 'templates/' . $key );

			$templates[ $key ]['path']           = $path;
			$templates[ $key ]['url']            = $url;
			$templates[ $key ]['preview_src']    = self::$certificate_img_url_base . $key . '/preview.png';
			$templates[ $key ]['background_src'] = self::$certificate_img_url_base . $key . '/background.png';
		}

		$filtered = apply_filters( 'tutor_certificate_templates', $templates, $include_admins, $template_in );

		// Customizer plugin compatibility.
		foreach ( $filtered as $index => $values ) {

			$filtered[ $index ]['key'] = $index;

			if ( ! array_key_exists( 'background_src', $values ) ) {
				$filtered[ $index ]['preview_src']    = $values['url'] . 'preview.png';
				$filtered[ $index ]['background_src'] = $values['url'] . 'preview.png';
			}
		}

		if ( $add_none ) {
			// This block is only for course editor.
			$filtered = array_merge(
				array(
					'none' => array(
						'name'           => 'none',
						'orientation'    => 'landscape',
						'path'           => '',
						'url'            => '',
						'preview_src'    => TUTOR_CERT()->url . 'assets/images/certificate-none.svg',
						'background_src' => '',
					),
					'off'  => array(
						'name'           => 'off',
						'orientation'    => 'portrait',
						'path'           => '',
						'url'            => '',
						'preview_src'    => TUTOR_CERT()->url . 'assets/images/certificate-none-portrait.svg',
						'background_src' => '',
					),
				),
				$filtered
			);
		}

		return $filtered;
	}

	/**
	 * Get completed course data
	 *
	 * @since 1.5.1
	 *
	 * @param mixed $cert_hash certificate hash.
	 * @param mixed $data data.
	 *
	 * @return mixed
	 */
	public function completed_course( $cert_hash, $data = false ) {
		global $wpdb;
		$is_completed = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT comment_ID as certificate_id,
					comment_post_ID as course_id,
					comment_author as completed_user_id,
					comment_date as completion_date,
					comment_content as completed_hash
			FROM	$wpdb->comments
			WHERE 	comment_agent = %s
					AND comment_type = %s
					AND comment_content = %s",
				'TutorLMSPlugin',
				'course_completed',
				$cert_hash
			)
		);

		return ! empty( $is_completed ) ? $is_completed : $data;
	}


	/**
	 * Add meta tags for the certificate page
	 *
	 * @since 2.4.0
	 *
	 * @return void
	 */
	public function certificate_header_content() {
		$cert_hash = sanitize_text_field( tutor_utils()->array_get( 'cert_hash', $_GET ) );

		if ( ! $cert_hash || ! empty( $_GET['tutor_action'] ) ) {
			return;
		}

		$completed = $this->completed_course( $cert_hash );
		if ( ! is_object( $completed ) || ! property_exists( $completed, 'completed_user_id' ) ) {
			return;
		}

		$course     = get_post( $completed->course_id );
		$upload_dir = wp_upload_dir();

		$certificate_dir_url  = $upload_dir['baseurl'] . '/' . $this->certificates_dir_name;
		$certificate_dir_path = $upload_dir['basedir'] . '/' . $this->certificates_dir_name;

		$rand_string = get_comment_meta( $completed->certificate_id, $this->certificate_stored_key, true );

		$cert_path = '/' . $rand_string . '-' . $cert_hash . '.jpg';
		$cert_file = $certificate_dir_path . $cert_path;

		! file_exists( $cert_file ) ? $cert_file = null : 0;

		$generate_cert              = ! $cert_file || ( isset( $_GET['regenerate'] ) && 1 == $_GET['regenerate'] );
		$generate_cert ? $cert_file = null : 0;

		$cert_img = $generate_cert ? get_admin_url() . 'images/loading.gif' : $certificate_dir_url . $cert_path;

		$title       = __( 'Course Completion Certificate', 'tutor-pro' );
		$description = __( 'My course completion certificate for', 'tutor-pro' ) . ' ' . $course->post_title;

		$og_url = get_page_link();
		if ( is_singular() && isset( $_GET['cert_hash'] ) ) {
			// Get the current page's URL.
			$og_url = trailingslashit( $og_url ) . '?cert_hash=' . $cert_hash;
		}

		echo '
		<meta property="og:url" content="' . esc_url( $og_url ) . '" />
		<meta property="og:title" content="' . esc_attr( $title ) . '"/>
		<meta property="og:description" content="' . esc_attr( $description ) . '"/>
		<meta property="og:image" content="' . esc_url( $cert_img ) . '"/>
		<meta name="twitter:title" content="' . esc_attr( $title ) . '"/>
		<meta name="twitter:description" content="' . esc_attr( $description ) . '"/>
		<meta name="twitter:image" content="' . esc_url( $cert_img ) . '"/>
		<meta name="twitter:card" content="summary_large_image">
		';
	}

	/**
	 * Remove yoast seo tags if it is certificate page
	 *
	 * @since 2.4.0
	 *
	 * @param string $str og tag.
	 *
	 * @return string
	 */
	public function remove_yoast_seo_og_tags( $str ) {
		$cert_hash = sanitize_text_field( tutor_utils()->array_get( 'cert_hash', $_GET ) );

		if ( ! $cert_hash || ! empty( $_GET['tutor_action'] ) ) {
			return $str;
		}
		return '';
	}

	/**
	 * Get certificate
	 *
	 * @param int     $course_id course id.
	 * @param boolean $full full.
	 *
	 * @return mixed
	 */
	public function get_certificate( $course_id, $full = false ) {

		$is_completed = tutor_utils()->is_completed_course( $course_id, 0, false );
		$url          = $is_completed ? apply_filters( 'tutor_certificate_public_url', $is_completed->completed_hash ) : null;

		if ( $full && $is_completed ) {
			return array(
				'certificate_url'  => $url,
				'certificate_hash' => $is_completed->completed_hash,
			);
		}

		return $url;
	}

	/**
	 * Check certificate has template.
	 *
	 * @param int $course_id course id.
	 *
	 * @return boolean
	 */
	private function has_course_certificate_template( $course_id ) {
		return ! ( $this->prepare_template_data( $course_id, true ) === false );
	}

	/**
	 * Show course certificate.
	 *
	 * @param array $meta meta.
	 * @param int   $course_id course id.
	 *
	 * @return array.
	 */
	public function show_course_has_certificate( $meta, $course_id ) {
		if ( $this->has_course_certificate_template( $course_id ) ) {
			$meta[] = array(
				'icon_class' => 'tutor-icon-ribbon-o',
				'label'      => __( 'Certificate', 'tutor-pro' ),
				'value'      => __( 'Certificate of completion', 'tutor-pro' ),
			);
		}

		return $meta;
	}
}
