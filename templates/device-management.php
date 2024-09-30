<?php
/**
 * User's logged in device management
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\DeviceManagement
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.10
 */

use TUTOR_PRO\DeviceManagement;

// User's logged-in devices.
$user_id = get_current_user_id();
$devices = DeviceManagement::get_logged_in_devices( $user_id );

?>

<h3><?php esc_html_e( 'Settings', 'tutor-pro' ); ?></h3>

<div class="tutor-dashboard-setting-social tutor-dashboard-content-inner">

	<div class="tutor-mb-32">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'manage-login-sessions' ) ); ?>
	</div>

	<!-- device-management -->
	<div class="tutor-users-devices-wrapper">
		<div class="tutor-row">
			<?php if ( is_array( $devices ) && count( $devices ) ) : ?>

				<?php
				$current_fingerprint = DeviceManagement::get_current_device_fingerprint();

				/**
				 * Show logged in user session first in the list.
				 */
				$current_device   = null;
				$filtered_devices = array();
				foreach ( $devices as $device ) {
					$fingerprint = DeviceManagement::get_fingerprint( $device->meta_key );
					if ( $current_fingerprint === $fingerprint ) {
						$current_device = $device;
					} else {
						$filtered_devices[] = $device;
					}
				}

				if ( ! is_null( $current_device ) ) {
					array_unshift( $filtered_devices, $current_device );
				}

				foreach ( $filtered_devices as $device ) :
					$info        = json_decode( $device->meta_value );
					$last_active = tutor_i18n_get_formated_date( date( 'Y-m-d H:i:s', $info->login_time ) );
					$location    = trim( "{$info->city}, {$info->country}", ',' );
					$fingerprint = DeviceManagement::get_fingerprint( $device->meta_key );
					if ( ' ' === $location ) {
						$location = __( 'Unknown Location', 'tutor-pro' );
					}

					$device_icon = 'Laptop' === $info->device ? 'tutor-icon-laptop' : ( 'Tablet' === $info->device ? 'tutor-icon-tablet' : 'tutor-icon-mobile' );
					?>
					<div class="tutor-col-md-6">
						<div class="tutor-card">
							<div class="tutor-card-header">
								<div class="tutor-d-flex tutor-align-center" style="gap: 10px">
									<i class="<?php echo esc_attr( $device_icon ); ?>"></i>
									<span class="tutor-fw-medium tutor-color-black">
										<?php echo esc_html( "{$info->device}, {$info->os}, {$info->browser}" ); ?>
									</span>
									<?php if ( $current_fingerprint === $fingerprint ) : ?>
										<i class="tutor-icon-circle-mark-o tutor-color-primary"></i>
									<?php endif; ?>
								</div>
								<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-device-sign-out" data-umeta-id="<?php echo esc_attr( $device->umeta_id ); ?>">
									<?php esc_html_e( 'Sign out', 'tutor-pro' ); ?>
								</button>
							</div>
							<div class="tutor-card-body">
								<p class="tutor-d-flex tutor-align-center tutor-color-subdued tutor-mb-8" style="gap: 10px">
									<i class="tutor-icon-clock-line-o"></i>
									<?php echo esc_html__( 'Last Active', 'tutor-pro' ) . ' ' . esc_html( $last_active ); ?>
								</p>
								<p class="tutor-d-flex tutor-align-center tutor-color-subdued" style="gap: 10px">
									<i class="tutor-icon-map-pin"></i>
									<?php echo esc_html( $location ); ?>
								</p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>

			<?php else : ?>
				<?php tutor_utils()->tutor_empty_state(); ?>
			<?php endif; ?>
		</div>
	</div>
	<!-- device-management end-->

</div>
