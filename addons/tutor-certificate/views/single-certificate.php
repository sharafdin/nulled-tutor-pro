<?php
/**
 * Template for displaying certificate
 *
 * @since 1.5.1
 *
 * @author Themeum
 * @link https://themeum.com
 * @package TutorPro/Addons
 * @subpackage Certificate
 */

use TUTOR\Input;
use TUTOR_CERT\Certificate;

tutor_utils()->tutor_custom_header();

$cert_obj = new Certificate( true );

$cert_hash  = Input::get( 'cert_hash' );
$completed  = $cert_obj->completed_course( $cert_hash );
$course     = get_post( $completed->course_id );
$upload_dir = wp_upload_dir();

$certificate_dir_url  = $upload_dir['baseurl'] . '/' . $cert_obj->certificates_dir_name;
$certificate_dir_path = $upload_dir['basedir'] . '/' . $cert_obj->certificates_dir_name;
$rand_string          = get_comment_meta( $completed->certificate_id, $cert_obj->certificate_stored_key, true );

$cert_path = '/' . $rand_string . '-' . $cert_hash . '.jpg';
$cert_file = $certificate_dir_path . $cert_path;

if ( ! file_exists( $cert_file ) ) {
	$cert_file = null;
}

$generate_cert = ! $cert_file || ( isset( $_GET['regenerate'] ) && 1 == $_GET['regenerate'] );
if ( $generate_cert ) {
	$cert_file = null;
}

$cert_img = $generate_cert ? get_admin_url() . 'images/loading.gif' : $certificate_dir_url . $cert_path;
$cert_url = $cert_obj->tutor_certificate_public_url( $cert_hash );

// Similar to compact('course', 'cert_file', 'cert_img', 'cert_hash', 'completed'), true).

/**
 * Check who are generated certificate
 *
 * @since 2.4.0
 */
if ( $generate_cert ) {
	update_user_meta( get_current_user_id(), 'tutor_certificate_generated', $completed->course_id );
}

$issued_by = tutor_utils()->get_option( 'tutor_cert_authorised_name' );

$share_config = array(
	'title' => __( 'Course Completion Certificate', 'tutor-pro' ),
	'text'  => __( 'My course completion certificate for', 'tutor-pro' ) . ' ' . $course->post_title,
	'image' => $cert_img,
);
?>
<?php //phpcs:ignore ?>
<link rel="stylesheet" href="<?php echo esc_url( TUTOR_CERT()->url . 'assets/css/certificate-page.css' ); ?>">

<div class="tutor-download-certificate tutor-pb-48 tutor-p-12">
	<?php do_action( 'tutor_certificate/before_content' ); ?>
	<div class="tutor-dc-title tutor-pb-36">
		<div class="tutor-certificate-course-title">
		<span class="tutor-dc-course-title tutor-fs-5 tutor-fw-bold tutor-color-black">
			<?php echo esc_html( $course->post_title ); ?>
		</span>
		</div>
	</div>
	<div class="tutor-certificate-demo tutor-pb-44">
		<span class="tutor-dc-demo-img">
			<img
				id="tutor-pro-certificate-preview"
				src="<?php echo esc_url( $cert_img ); ?>"
				alt="<?php echo esc_attr( $course->post_title ); ?>"
				style="<?php echo ! $cert_file ? 'width:auto;height:auto;' : ''; ?>"
				data-is_generated="<?php echo esc_attr( $cert_file ? 'yes' : 'no' ); ?>"
				data-certificate_url="<?php echo remove_query_arg( 'regenerate', tutor()->current_url );//phpcs:ignore ?>"
			/>
		</span>
	</div>
	<!--Printable area-->
	<div class="tutor-certificate-demo tutor-pb-44" id="div-to-print" style="display:none;max-width:730px;height:auto;overflow:hidden;">
		<span class="tutor-dc-demo-img">
			<img
				style="width: 100%;"
				src="<?php echo esc_url( $cert_img ); ?>"
				alt="<?php echo esc_attr( $course->post_title ); ?>"
				data-is_generated="<?php echo esc_attr( $cert_file ? 'yes' : 'no' ); ?>"
			/>
		</span>
	</div>
	<!--End printable area-->
	<div class="tutor-dc-certificate-details">
		<div class="tutor-certificate-info">
			<div class="tutor-info-id">
				<div class="tutor-info-id-name tutor-fs-7 tutor-color-secondary tutor-pb-4">
					<?php esc_html_e( 'Credential ID', 'tutor-pro' ); ?>
				</div>
				<div class="tutor-info-id-details tutor-fs-6 tutor-fw-medium tutor-color-black">
					#<?php echo esc_html( $cert_hash ); ?>
				</div>
			</div>
			<div class="tutor-info-issued">
				<?php if ( '' !== $issued_by ) : ?>
				<div class="tutor-info-issued-name tutor-fs-7 tutor-color-secondary tutor-pb-4">
					<?php esc_html_e( 'Issued By', 'tutor-pro' ); ?>
				</div>
				<div class="tutor-info-issued-value tutor-fs-6 tutor-fw-medium tutor-color-black">
					<?php echo esc_html( $issued_by ); ?>
				</div>
				<?php endif; ?>
			</div>
			<div class="tutor-info-issued-date">
				<div class="tutor-info-date-name tutor-fs-7 tutor-color-secondary tutor-pb-4">
					<?php esc_html_e( 'Issued Date', 'tutor-pro' ); ?>
				</div>
				<div class="tutor-info-date-details tutor-fs-6 tutor-fw-medium tutor-color-black">
					<?php echo esc_html( tutor_i18n_get_formated_date( $completed->completion_date, get_option( 'date_format' ) ) ); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="tutor-dc-button-group tutor-mt-72">
		<div class="tutor-dc-download-button tutor-py-16">
			<button class="tutor-iconic-btn tutor-iconic-btn-outline tutor-iconic-btn-lg tooltip-wrap">
				<span class="tutor-icon-import-o"></span>
				<span style="top:10px" class="tooltip-txt tooltip-left tutor-d-flex">
					<a class="tutor-certificate-pdf tutor-cert-view-page tutor-mr-8 tutor-d-flex tutor- tutor-align-center" style="text-decoration:none;color:#ffffff;" data-cert_hash="<?php echo esc_attr( $cert_hash ); ?>" data-course_id="<?php echo esc_attr( $course->ID ); ?>">
						<span class="tutor-icon-pdf-file tutor-fs-6"></span> <span><?php esc_html_e( 'PDF', 'tutor-pro' ); ?></span>
					</a>
					<a href="#" class="tutor-d-flex tutor- tutor-align-center" id="tutor-pro-certificate-download-image" style="text-decoration:none;color:#ffffff;">
						<span class="tutor-icon-jpg-file tutor-fs-6"></span> <span><?php esc_html_e( 'JPG', 'tutor-pro' ); ?></span>
					</a>
				</span>
			</button>
		</div>
		<div class="tutor-dc-copy-button tutor-copy-text tutor-py-16" data-text="<?php echo esc_url( $cert_url ); ?>">
			<button class="tutor-iconic-btn tutor-iconic-btn-outline tutor-iconic-btn-lg tooltip-wrap">
				<span class="tutor-icon-copy"></span>
				<span class="tooltip-txt tooltip-left"><?php esc_html_e( 'Copy Credential URL', 'tutor-pro' ); ?></span>
			</button>
		</div>
		<div class="tutor-dc-print-button tutor-py-16" onClick="PrintDiv()">
			<button class="tutor-iconic-btn tutor-iconic-btn-outline tutor-iconic-btn-lg tooltip-wrap">
				<span class="tutor-icon-print"></span>
				<span class="tooltip-txt tooltip-left"><?php esc_html_e( 'Print Now', 'tutor-pro' ); ?></span>
			</button>
		</div>
		<div class="tutor-dc-share-button tutor-py-16">
			<button class="tutor-iconic-btn tutor-iconic-btn-outline tutor-iconic-btn-lg tooltip-wrap">
				<span class="tutor-icon-share"></span>
				<span style="top:10px" class="tooltip-txt tooltip-left tutor-d-flex tutor-social-share-wrap" data-social-share-config="<?php echo esc_attr( json_encode( $share_config ) ); ?>">
					<a class="tutor-d-flex tutor-align-center tutor-mr-8 tutor_share s_facebook" style="text-decoration:none;color:#ffffff;">
						<span><?php esc_html_e( 'Facebook', 'tutor-pro' ); ?></span>
					</a>
					<a class="tutor-d-flex tutor-align-center tutor-mr-8 tutor_share s_twitter" style="text-decoration:none;color:#ffffff;">
						<span><?php esc_html_e( 'Twitter', 'tutor-pro' ); ?></span>
					</a>
					<a class="tutor-d-flex tutor-align-center tutor_share s_linkedin" style="text-decoration:none;color:#ffffff;">
						<span><?php esc_html_e( 'LinkedIn', 'tutor-pro' ); ?></span>
					</a>
				</span>
			</button>
		</div>
	</div>
</div>
<?php
tutor_utils()->tutor_custom_footer();
