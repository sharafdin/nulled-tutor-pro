<?php
/**
 * Manage Instructor Related Logic for PRO
 *
 * @package TutorPro
 *
 * @since 2.1.0
 */

namespace TUTOR_PRO;

use TUTOR\Input;
use Tutor\Models\WithdrawModel;
use TUTOR\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Instructor
 *
 * @since 2.2.4
 */
class Instructor {
	/**
	 * Register hooks.
	 *
	 * @since 2.2.4
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_after_instructor_list_commission_column', array( $this, 'add_account_summary_column' ) );
		add_action( 'tutor_after_instructor_list_commission_column_data', array( $this, 'add_account_summary_data' ) );
		add_filter( 'tutor_instructor_list_edit_button', array( $this, 'instructor_edit_button' ), 10, 2 );
		add_action( 'wp_ajax_tutor_update_instructor_data', array( $this, 'update_instructor_data' ) );
	}

	/**
	 * Edit instructor modal with button click.
	 *
	 * @since 2.4.0
	 *
	 * @param string   $btn_markup edit button markup.
	 * @param \WP_User $instructor instructor object.
	 *
	 * @return string markup of link button and modal.
	 */
	public function instructor_edit_button( $btn_markup, $instructor ) {
		$id       = 'instructor-edit-modal-' . $instructor->ID;
		$phone    = get_user_meta( $instructor->ID, 'phone_number', true );
		$bio      = get_user_meta( $instructor->ID, '_tutor_profile_bio', true );
		$email    = $instructor->user_email;
		$username = $instructor->user_login;
		ob_start();
		?>
		<a 	href="#"
			data-tutor-modal-target="<?php echo esc_attr( $id ); ?>"
			class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
			<?php esc_html_e( 'Edit', 'tutor-pro' ); ?>
		</a>
		<form class="tutor-modal tutor-modal-scrollable tutor-instructor-edit-modal" id="<?php echo esc_attr( $id ); ?>">
			<div class="tutor-modal-overlay"></div>
			<div class="tutor-modal-window">
				<div class="tutor-modal-content">
					<div class="tutor-modal-header">
						<div class="tutor-modal-title">
							<?php esc_html_e( 'Edit Instructor', 'tutor-pro' ); ?>
						</div>
						<button class="tutor-modal-close tutor-iconic-btn" data-tutor-modal-close role="button">
							<span class="tutor-icon-times" area-hidden="true"></span>
						</button>
					</div>

					<div class="tutor-modal-body">
					<?php tutor_nonce_field(); ?>
					<?php do_action( 'tutor_edit_instructor_form_fields_before' ); ?>
					<input type="hidden" name="user_id" value="<?php echo esc_attr( $instructor->ID ); ?>">
					<div class="tutor-rows">
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'First Name', 'tutor-pro' ); ?>
							</label>
							<div class="tutor-mb-16">
								<input
									value="<?php echo esc_attr( $instructor->first_name ); ?>" 
									type="text" name="first_name" class="tutor-form-control tutor-mb-12" placeholder="<?php esc_attr_e( 'Enter First Name', 'tutor-pro' ); ?>" title="<?php esc_attr_e( 'Only alphanumeric & space are allowed', 'tutor-pro' ); ?>" required/>
							</div>

						</div>
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Last Name', 'tutor-pro' ); ?>
							</label>
							<div class="tutor-mb-16">
								<input 
									value="<?php echo esc_attr( $instructor->last_name ); ?>"
									type="text" name="last_name" class="tutor-form-control tutor-mb-12" placeholder="<?php esc_attr_e( 'Enter Last Name', 'tutor-pro' ); ?>" title="<?php esc_attr_e( 'Only alphanumeric & space are allowed', 'tutor-pro' ); ?>" required/>
							</div>
						</div>
					</div>
					<div class="tutor-row">
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Username', 'tutor-pro' ); ?>
							</label>
							<div class="tutor-mb-16">
								<input 
									readonly
									value="<?php echo esc_attr( $username ); ?>"
									type="text" 
									name="user_login" class="tutor-form-control tutor-mb-12" autocomplete="off" placeholder="<?php esc_attr_e( 'Enter Username', 'tutor-pro' ); ?>" pattern="^[a-zA-Z0-9_]*$" title="<?php esc_attr_e( 'Only alphanumeric and underscore are allowed', 'tutor-pro' ); ?>" required/>
							</div>
						</div>
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Phone Number', 'tutor-pro' ); ?>
								<span class="tutor-fs-7 tutor-fw-medium tutor-color-muted">
									<?php esc_html_e( '(Optional)', 'tutor-pro' ); ?>
								</span>
							</label>
							<div class="tutor-mb-16">
								<input 
									value="<?php echo esc_attr( $phone ); ?>"
									type="text" name="phone_number"  class="tutor-form-control tutor-mb-12" placeholder="<?php esc_attr_e( 'Enter Phone Number', 'tutor-pro' ); ?>" minlength="8" maxlength="16" pattern="[0-9]+" title="<?php esc_attr_e( 'Only number is allowed', 'tutor-pro' ); ?>"/>
							</div>
						</div>
					</div>

					<div class="tutor-row">
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Email Address', 'tutor-pro' ); ?>
							</label>
							<div class="tutor-mb-16">
								<input 
									readonly
									value="<?php echo esc_attr( $email ); ?>"
									type="email" name="email"  class="tutor-form-control tutor-mb-12" autocomplete="off" placeholder="<?php esc_attr_e( 'Enter Your Email', 'tutor-pro' ); ?>" required/>
							</div>
						</div>
					</div>

					<?php do_action( 'tutor_edit_instructor_form_fields_after', $instructor->ID ); ?>

					<div class="tutor-row">
						<div class="tutor-col">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Bio', 'tutor-pro' ); ?>
								<span class="tutor-fs-7 tutor-fw-medium tutor-color-muted">
									<?php esc_html_e( '(Optional)', 'tutor-pro' ); ?>
								</span>
							</label>
							<div class="tutor-mb-16">
								<?php wp_editor( $bio, 'tutor_profile_bio_' . $instructor->ID, tutor_utils()->get_profile_bio_editor_config( 'tutor_profile_bio' ) ); ?>
							</div>
						</div>
					</div>

					<div class="tutor-row tutor-form-response"></div>

					</div>

					<div class="tutor-modal-footer">
						<button data-tutor-modal-close type="button" data-action="back" class="tutor-btn tutor-btn-outline-primary">
							<?php esc_html_e( 'Cancel', 'tutor-pro' ); ?>
						</button>
						<button data-tutor-modal-submit type="submit" data-action="next" class="tutor-btn tutor-btn-primary">
							<?php esc_html_e( 'Save', 'tutor-pro' ); ?>
						</button>
					</div>
				</div>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Add new instructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_instructor_data() {
		tutor_utils()->checking_nonce();

		$errors = array();

		if ( ! User::is_admin() ) {
			$errors['forbidden'] = __( 'You are not allowed to do this action', 'tutor-pro' );
			wp_send_json_error( array( 'errors' => $errors ) );
		}

		$required_fields = apply_filters(
			'tutor_instructor_update_required_fields',
			array(
				'first_name' => __( 'First name field is required', 'tutor-pro' ),
				'last_name'  => __( 'Last name field is required', 'tutor-pro' ),
			)
		);

		foreach ( $required_fields as $required_key => $required_value ) {
			if ( empty( Input::post( $required_key ) ) ) {
				$errors[ $required_key ] = $required_value;
			}
		}

		if ( count( $errors ) ) {
			wp_send_json_error( array( 'errors' => $errors ) );
		}

		$user_id    = Input::post( 'user_id' );
		$first_name = Input::post( 'first_name', '' );
		$last_name  = Input::post( 'last_name', '' );

		$user_data = apply_filters(
			'tutor_instructor_update_data',
			array(
				'ID'         => $user_id,
				'first_name' => $first_name,
				'last_name'  => $last_name,
			)
		);

		do_action( 'tutor_before_instructor_update' );

		$user_id = wp_update_user( $user_data );
		if ( ! is_wp_error( $user_id ) ) {
			$phone_number      = Input::post( 'phone_number', '' );
			$tutor_profile_bio = Input::post( 'tutor_profile_bio', '', Input::TYPE_KSES_POST );

			update_user_meta( $user_id, 'phone_number', $phone_number );
			update_user_meta( $user_id, 'description', $tutor_profile_bio );
			update_user_meta( $user_id, '_tutor_profile_bio', $tutor_profile_bio );

			/**
			 * Save commission sharing data.
			 */
			if ( Input::has( 'commission_type' ) ) {
				$commission_type   = Input::post( 'commission_type', 'default' );
				$commission_amount = Input::post( 'commission_amount', 0, Input::TYPE_NUMERIC );

				if ( 'default' === $commission_type ) {
					delete_user_meta( $user_id, 'tutor_instructor_amount_type' );
					delete_user_meta( $user_id, 'tutor_instructor_amount' );
				} else {
					update_user_meta( $user_id, 'tutor_instructor_amount_type', $commission_type );
					update_user_meta( $user_id, 'tutor_instructor_amount', $commission_amount );
				}
			}

			do_action( 'tutor_after_instructor_update', $user_id );

			wp_send_json_success( array( 'msg' => __( 'Instructor has been updated successfully', 'tutor-pro' ) ) );
		}

		$errors['unknown'] = __( 'Something went wrong', 'tutor-pro' );
		wp_send_json_error( array( 'errors' => $errors ) );
	}

	/**
	 * Add account summary column in instructors table.
	 *
	 * @since 2.2.4
	 *
	 * @return void
	 */
	public function add_account_summary_column() {
		?>
		<th class="tutor-table-rows-sorting" width="20%">
			<?php esc_html_e( 'Account Summary', 'tutor-pro' ); ?>
		</th>
		<?php
	}

	/**
	 * Add account summary data.
	 *
	 * @since 2.2.4
	 *
	 * @param int $instructor_id instructor id.
	 *
	 * @return void
	 */
	public function add_account_summary_data( $instructor_id ) {
		$summary = WithdrawModel::get_withdraw_summary( $instructor_id );
		?>
		<td>
			<div class="tutor-d-flex tutor-align-center tutor-gap-1">
				<span class="tutor-fs-7 tutor-color-muted"><?php esc_html_e( 'Earnings:', 'tutor-pro' ); ?></span> 
				<?php echo wp_kses_post( tutor_utils()->tutor_price( $summary->total_income ) ); ?>
			</div>
			<div class="tutor-d-flex tutor-align-center tutor-gap-1">
				<span class="tutor-fs-7 tutor-color-muted"><?php esc_html_e( 'Withdrawal:', 'tutor-pro' ); ?></span> 
				<?php echo wp_kses_post( tutor_utils()->tutor_price( $summary->total_withdraw ) ); ?>
			</div>
			<div class="tutor-d-flex tutor-align-center tutor-gap-1">
				<span class="tutor-fs-7 tutor-color-muted"><?php esc_html_e( 'Balance:', 'tutor-pro' ); ?></span> 
				<!-- tooltip -->
				<div class="tooltip-wrap">
					<?php echo wp_kses_post( tutor_utils()->tutor_price( $summary->current_balance ) ); ?>
					<span class="tooltip-txt tooltip-top tutor-nowrap-ellipsis">
						<?php
							esc_html_e( 'Withdrawable: ', 'tutor-pro' );
							echo wp_kses_post( tutor_utils()->tutor_price( $summary->available_for_withdraw ) );
						?>
					</span>
				</div>
				<!-- end tooltip -->
			</div>
		</td>
		<?php
	}
}
