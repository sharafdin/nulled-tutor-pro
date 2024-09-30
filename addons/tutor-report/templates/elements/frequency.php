<!-- new dropdown start -->
<?php
/**
 * Frequency template
 *
 * @package Tutor Report
 * @since v2.0.0
 */

	$time_period = $active = isset( $_GET['period'] ) ? $_GET['period'] : '';
	$start_date  = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
	$end_date    = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
if ( '' !== $start_date ) {
	$start_date = tutor_get_formated_date( 'Y-m-d', $start_date );
}
if ( '' !== $end_date ) {
	$end_date = tutor_get_formated_date( 'Y-m-d', $end_date );
}
	$add_30_days  = tutor_utils()->sub_days_with_today( '30 days' );
	$add_90_days  = tutor_utils()->sub_days_with_today( '90 days' );
	$add_365_days = tutor_utils()->sub_days_with_today( '365 days' );

	$current_frequency = isset( $_GET['period'] ) ? $_GET['period'] : 'last30days';
	$frequencies       = tutor_utils()->report_frequencies();
?>
<div class="tutor-dropdown-select">
	<div class="tutor-dropdown-select-options-container">
		<div class="tutor-frequencies">
			<?php foreach ( $frequencies as $key => $frequency ) : ?>
				<div class="tutor-dropdown-select-option" data-key="<?php echo esc_attr( $key ); ?>">
					<input
						type="radio"
						class="radio"
						name="category"
						value="<?php echo esc_attr( $key ); ?>"
					/>
					<label for="select-item-1">
						<div class="tutor-fs-7 tutor-color-secondary tutor-admin-report-frequency" data-key="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $frequency ); ?>
						</div>
					</label>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	
	<div class="tutor-dropdown-select-selected">
		<div>
			<?php if ( isset( $start_date ) && ( $end_date ) ) : ?>
				<div class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
					<?php echo esc_html( $frequencies['custom'] ); ?>
				</div>
				<div class="tutor-fs-7 tutor-color-muted">
					<?php echo esc_html( tutor_get_formated_date( 'M d', $start_date ) ); ?> - 
					<?php echo esc_html( tutor_get_formated_date( 'M d, Y', $end_date ) ); ?>
				</div>
			<?php elseif ( 'last30days' === $current_frequency ) : ?>
				<div class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
					<?php echo esc_html( $frequencies[ $current_frequency ] ); ?>
				</div>
				<div class="tutor-fs-7 tutor-color-muted">
					<?php echo esc_html( date_i18n( 'M d, Y', strtotime( date_format( $add_30_days, 'Y-m-d' ) ) ) . ' - ' . date_i18n( 'M d', strtotime( gmdate( 'Y-m-d' ) ) ) ); ?>
				</div>
			<?php elseif ( 'last90days' === $current_frequency ) : ?>
				<div class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
					<?php echo esc_html( $frequencies[ $current_frequency ] ); ?>
				</div>
				<div class="tutor-fs-7 tutor-color-muted">
					<?php echo esc_html( date_i18n( 'M d, Y', strtotime( date_format( $add_90_days, 'Y-m-d' ) ) ) . ' - ' . date_i18n( 'M d', strtotime( gmdate( 'Y-m-d' ) ) ) ); ?>
				</div>
			<?php elseif ( 'last365days' === $current_frequency ) : ?>
				<div class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
					<?php echo esc_html( $frequencies[ $current_frequency ] ); ?>
				</div>
				<div class="tutor-fs-7 tutor-color-muted">
					<?php echo esc_html( date_i18n( 'M d, Y', strtotime( date_format( $add_365_days, 'Y-m-d' ) ) ) . ' - ' . date_i18n( 'M d', strtotime( gmdate( 'Y-m-d' ) ) ) ); ?>
				</div>
			<?php else : ?>
				<div class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
					<?php echo esc_html( $frequencies[ $current_frequency ] ); ?>
				</div>
			<?php endif; ?>
			
		</div>
	</div>
</div>