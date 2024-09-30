<?php
/**
 * Google meet settings page
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TutorPro\GoogleMeet\Settings\Settings;

$default_settings = Settings::default_settings();
$user_settings    = maybe_unserialize( get_user_meta( get_current_user_id(), Settings::META_KEY, true ) );

?>
<div class="tutor-google-meet-settings-content">
	<div class="tutor-admin-container tutor-admin-container-sm">
		<div>
			<div class="tutor-fs-4 tutor-fw-medium tutor-mb-20">Settings</div>
			<form id="tutor-google-meet-settings">
				<input type="hidden" name="action" value="tutor_update_google_meet_settings">
                <?php tutor_nonce_field(); ?>
				<?php foreach ( $default_settings as $settings ) : ?>
					<?php
					$name = $settings['name'];
					?>
				<div class="tutor-card tutor-p-24 tutor-mb-12">
					<div class="card-content">
						<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-4">
							<?php echo esc_html( $settings['label'] ); ?>
						</div>
						<div class="tutor-fs-7 tutor-color-muted">
							<?php echo esc_html( $settings['help_text'] ); ?>
						</div>
						<div class="tutor-d-flex tutor-mt-24">
							<?php if ( 'radio' === $settings['type'] ) : ?>
								<?php
								$i = 1;
								foreach ( $settings['options'] as $option ) :
									$i++;
								?>
									<div class="tutor-form-check tutor-mr-16 tutor-align-center">
										<input type="radio" id="<?php echo esc_attr( $name . $i ); ?>" class="tutor-form-check-input tutor-flex-shrink-0" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $option['value'] ); ?>"
										<?php
											echo isset( $user_settings[ $name ] ) && $user_settings[ $name ] == $option['value'] ?
												esc_attr( 'checked = checked' )
												: '';
										?>
										>
										<label for="<?php echo esc_attr( $settings['name'] . $i ); ?>">
											<?php echo esc_html( $option['label'] ); ?>
										</label>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
			
							<?php if ( 'dropdown' === $settings['type'] ) : ?>
								<div class="tutor-col-md-8 tutor-mb-md-0 tutor-mb-16">
									<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $settings['name'] ); ?>" class="tutor-form-select" data-value="<?php echo esc_attr( isset( $user_settings[ $name ] ) ? $user_settings[ $name ] : '' );  ?>">
										<?php foreach ( $settings['options'] as $key => $value ) : ?>
											<option value="<?php echo esc_attr( $key ); ?>"
												<?php
													$selected = isset( $user_settings[ $name ] ) ? $user_settings[ $name ] : '';
													selected( $selected, $key );
												?>
											>
												<?php echo esc_html( $value ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</form>
		</div>
	</div>
</div>
