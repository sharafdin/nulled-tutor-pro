<?php
/**
 * License Form
 *
 * @package TutorPro\Updater
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<div class="themeum-license-window">
	<div class="themeum-license-card">
		<div class="themeum-license-brand">
			<?php
				echo strpos( $header_content, 'http' ) === 0 ? '<img src="' . $header_content . '"/>' : $header_content; //phpcs:ignore --it is tutor svg icon.
			?>
		</div>

		<?php if ( null !== $license ) : ?>
			<?php if ( true === (bool) $license['activated'] ) : ?>
				<div class="themeum-license-alert-success">
					<div class="themeum-license-alert-icon">
						<svg width="48" height="48" fill="none" xmlns="http://www.w3.org/2000/svg"><defs/><path fill-rule="evenodd" clip-rule="evenodd" d="M24 41c9.389 0 17-7.611 17-17S33.389 7 24 7 7 14.611 7 24s7.611 17 17 17zm-8.434-16.145a.928.928 0 00.19.29l6.023 6c.08.093.178.168.288.22a.97.97 0 00.74 0 .852.852 0 00.29-.22l10.666-10.61a.928.928 0 00.189-.289 1.066 1.066 0 000-.74.887.887 0 00-.19-.289l-1.34-1.303a.842.842 0 00-.629-.289.906.906 0 00-.37.074.975.975 0 00-.3.215l-8.678 8.678-4.043-4.05a.985.985 0 00-.307-.215.878.878 0 00-.71 0 .806.806 0 00-.29.215l-1.34 1.284a.89.89 0 00-.189.29 1.067 1.067 0 000 .74z" fill="#24A148"/></svg>
					</div>
					<div class="themeum-license-alert-title">
						<?php esc_html_e( 'Congratulation', 'tutor-pro' ); ?>
					</div>
					<div class="themeum-license-alert-message">
						<?php
						/* translators: %s: product title */
						echo esc_html( sprintf( __( 'Your %s is connected to the Tutor LMS license system and will now receive automatic updates', 'tutor-pro' ), $product_title ) );
						?>

					</div>
				</div>
			<?php else : ?>
				<div class="themeum-license-alert-error">
					<div class="themeum-license-alert-icon">
						<svg width="48" height="48" fill="none" xmlns="http://www.w3.org/2000/svg"><defs/><path fill-rule="evenodd" clip-rule="evenodd" d="M24 41c9.389 0 17-7.611 17-17S33.389 7 24 7 7 14.611 7 24s7.611 17 17 17zm8.465-11.118c.002-.2-.032-.4-.1-.588a1.475 1.475 0 00-.324-.484l-4.819-4.812 4.837-4.808a1.492 1.492 0 00.44-1.072 1.607 1.607 0 00-.44-1.12l-1.07-1.073c-.15-.14-.326-.25-.518-.324a1.735 1.735 0 00-1.17 0 1.44 1.44 0 00-.484.324l-4.801 4.829-4.82-4.83a1.39 1.39 0 00-.49-.323 1.735 1.735 0 00-1.17 0 1.619 1.619 0 00-.51.324l-1.067 1.072c-.144.15-.254.33-.323.526-.067.191-.101.392-.101.595-.002.2.032.398.1.585.073.184.183.35.324.487l4.784 4.808-4.802 4.812a1.494 1.494 0 00-.441 1.072c0 .201.038.4.111.588.075.198.187.379.33.533l1.07 1.072c.149.14.324.25.515.324.188.067.387.101.587.1.199.003.397-.031.583-.1.183-.074.348-.184.487-.324l4.798-4.846 4.819 4.84c.14.14.308.251.493.323.185.067.38.102.577.1.202.002.403-.032.594-.1.188-.074.36-.184.507-.324l1.07-1.072c.142-.152.252-.33.323-.526.066-.19.1-.388.101-.588z" fill="#F44337"/></svg>
					</div>
					<div class="themeum-license-alert-title">
						<?php esc_html_e( 'Valid Key Required', 'tutor-pro' ); ?>
					</div>
					<div class="themeum-license-alert-message">
						<?php
						/* translators: %s: product title */
						echo esc_html( sprintf( __( 'You have entered an invalid license key. Please insert a valid one if you have purchased %s from our website.', 'tutor-pro' ), $product_title ) );
						?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( null !== $license && $license['activated'] ) : ?>
				<div class="themeum-license-fieldset">
					<div class="themeum-license-fieldset-label">
						<?php esc_html_e( 'Licensed To:', 'tutor-pro' ); ?>
					</div>
					<div class="themeum-license-fieldset-content">
						<?php echo esc_html( $license['license_to'] ); ?>
					</div>
				</div>

				<?php if ( $license['license_type'] ) : ?>
					<div class="themeum-license-fieldset">
						<div class="themeum-license-fieldset-label">
							<?php esc_html_e( 'License Type:', 'tutor-pro' ); ?>
						</div>
						<div class="themeum-license-fieldset-content">
							<?php echo esc_html( ucwords( $license['license_type'] ) ); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="themeum-license-fieldset">
					<div class="themeum-license-fieldset-label">
						<?php esc_html_e( 'Expires on:', 'tutor-pro' ); ?>
					</div>
					<div class="themeum-license-fieldset-content">
						<?php echo ! $license['expires_at'] ? 'Never' : esc_html( $license['expires_at'] ); ?>
					</div>
				</div>

			<?php endif; ?>
		<?php endif; ?>


		<?php
		$class_name = $license ? ( $license['activated'] ? 'themeum-license-is-valid' : 'themeum-license-is-invalid' ) : '';
		$value      = '';

		if ( $license ) {
			if ( $license['activated'] ) {
				$license_key     = preg_replace( '/[^\-]/i', '*', $license['license_key'] );
				$last_dash_index = strrpos( $license_key, '-' );
				$value           = substr( $license_key, 0, $last_dash_index ) . substr( $license['license_key'], $last_dash_index );
			} else {
				$value = $license['license_key'];
			}
		}
		?>
		<form method="post" id="themeum-license-key-form">
			<div class="themeum-license-fieldset">
				<div class="themeum-license-fieldset-content">
					<?php wp_nonce_field( $nonce_field_name ); ?>
					<input name="<?php echo esc_attr( $field_name ); ?>" type="text" placeholder="Enter your license key here" value="<?php echo esc_attr( $value ); ?>" class="<?php echo esc_attr( $class_name ); ?>">
					<div class="themeum-license-help-text">
						<?php
						/* translators: %s: product title */
						echo esc_html( sprintf( __( 'If you have already purchased a %s license, please paste your code here.', 'tutor-pro' ), $product_title ) );
						?>

						<a href="https://tutorlms.com/account/subscriptions/licenses"><?php esc_html_e( 'Get the license key', 'tutor-pro' ); ?></a>
					</div>
				</div>
			</div>

			<div class="themeum-license-actions">
				<button type class="button button-primary"><?php esc_html_e( 'Connect With License Key', 'tutor-pro' ); ?></button>
			</div>
		</form>
	</div>
</div>

<script>
	window.jQuery(document).ready(function($) {
		$('#themeum-license-key-form').submit(function(e) {

			var val = $(this).find('input[type="text"]').val();

			if(!val || !val.trim().length || val.indexOf('*')>-1) {
				alert('Please enter valid license key');
				e.preventDefault();
			}
		});
	});
</script>
