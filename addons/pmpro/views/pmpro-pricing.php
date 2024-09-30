<?php
/**
 * PM PRO pricing view
 *
 * @package TutorPro\Addons
 * @subpackage PmPro\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.3.5
 */

?>
<form class="tutor-pmpro-single-course-pricing">
	<h3 class="tutor-fs-5 tutor-fw-bold tutor-mb-16"><?php esc_html_e( 'Pick a plan', 'tutor-pro' ); ?></h3>

	<?php
		// Tutor Setting for PM Pro.
		$no_commitment = tutor_utils()->get_option( 'pmpro_no_commitment_message' );
		$money_back    = tutor_utils()->get_option( 'pmpro_moneyback_day' );
		$money_back    = ( is_numeric( $money_back ) && $money_back > 0 ) ? $money_back : false;

		$level_page_id  = apply_filters( 'tutor_pmpro_checkout_page_id', pmpro_getOption( 'checkout_page_id' ) );
		$level_page_url = get_the_permalink( $level_page_id );

	if ( $no_commitment ) {
		?>
			<small><?php esc_html_e( $no_commitment, 'tutor-pro' );//phpcs:ignore ?></small>
			<?php
	}

		$level_count = count( $required_levels );
	?>


	<?php foreach ( $required_levels as $level ) : ?>
		<?php
			$level_id  = 'tutor_pmpro_level_radio_' . $level->id;
			$highlight = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_level_highlight', true );
		?>
		<input type="radio" name="tutor_pmpro_level_radio" id="<?php echo esc_attr( $level_id ); ?>" <?php echo ( $highlight || 1 === $level_count ) ? 'checked="checked"' : ''; ?>/>
		<label for="<?php echo esc_attr( $level_id ); ?>" class="<?php echo $highlight ? 'tutor-pmpro-level-highlight' : ''; ?>">
			<div class="tutor-pmpro-level-header tutor-d-flex tutor-align-center tutor-justify-between">
				<div class="tutor-d-flex tutor-align-center">
					<span class="tutor-form-check-input tutor-form-check-input-radio" area-hidden="true"></span>
					<span class="tutor-fs-5 tutor-fw-medium tutor-ml-12"><?php echo esc_html( $level->name ); ?></span>
				</div>

				<div class="tutor-fs-4">
					<?php
						$billing_amount  = round( $level->billing_amount );
						$initial_payment = round( $level->initial_payment );

						$billing_text                                       = '<span class="tutor-fw-bold">';
							'left' === $currency_position ? $billing_text  .= $currency_symbol : 0;
								$billing_text                              .= ( $level->cycle_period ? $billing_amount : $initial_payment );
							'right' === $currency_position ? $billing_text .= $currency_symbol : 0;
						$billing_text                                      .= '</span>';

						$billing_text .= ( $level->cycle_period ? '<span class="tutor-fs-7 tutor-color-muted">/' . substr( $level->cycle_period, 0, 2 ) . '</span>' : '' );

						echo $billing_text;//phpcs:ignore
					?>

				</div>
			</div>

			<div class="tutor-pmpro-level-desc tutor-mt-20">
				<div class="tutor-fs-6 tutor-color-muted tutor-mb-20"><?php echo wp_kses_post( $level->description ); ?></div>

				<a href="<?php echo esc_url( $level_page_url ) . '?level=' . esc_attr( $level->id ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block">
					<?php esc_html_e( 'Buy Now', 'tutor-pro' ); ?>
				</a>

				<?php if ( $money_back ) : ?>
					<div class="tutor-fs-6 tutor-color-muted tutor-mt-16 tutor-text-center"><?php echo sprintf( esc_html__( '%d-day money-back guarantee', 'tutor-pro' ), $money_back ); //phpcs:ignore?></div>
				<?php endif; ?>
			</div>
		</label>
	<?php endforeach; ?>
</form>
