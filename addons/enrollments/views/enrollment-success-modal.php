<?php
/**
 * Show success modal for paid course enrollment
 *
 * @author themeum
 * @link https://themeum.com
 * @package TutorLMS/Templates
 *
 * @since 2.1.0
 */

$transient_key = 'tutor_manual_enrollment_success';
$modal_data    = get_transient( $transient_key );
if ( false !== $modal_data ) :
	?>
<div id="modal-course-save-feedback" class="tutor-modal tutor-is-active">
	<span class="tutor-modal-overlay"></span>
	<div class="tutor-modal-window tutor-modal-window-md">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
				<span class="tutor-icon-times" area-hidden="true"></span>
			</button>

			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-py-48">
					<img class="tutor-d-inline-block" src="<?php echo esc_url( tutor()->url ); ?>assets/images/icon-cheers.svg" />
					<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Success!', 'tutor-pro' ); ?></div>
					<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'The Student Enrollment Request is submitted', 'tutor-pro' ); ?> <strong><?php echo esc_html( $modal_data->post_title ?? '' ); ?> <?php esc_html_e( '(Paid)', 'tutor-pro' ); ?></strong>.</div>
					<div class="tutor-fs-6 tutor-color-muted tutor-mt-32"><?php echo esc_html_e( 'Now, either the enrolled students need to complete the payment online, or you need to mark the Order as "Completed" manually. Only after that, the students will get access to the course.', 'tutor-pro' ); ?></div>
				</div>
			</div>

			<div class="tutor-d-flex tutor-justify-center tutor-mb-48">
				<?php
				$order_url = get_admin_url() . 'edit.php?post_type=shop_order'
				?>
				<a href="<?php echo esc_url( $order_url ); ?>" class="tutor-btn tutor-btn-primary">
					<?php esc_html_e( 'View Orders', 'tutor-pro' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>

<?php endif; ?>
<?php delete_transient( $transient_key ); ?>
