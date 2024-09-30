<div class="tutor-analytics-graph tutor-mb-48">
	<?php if ( $data ) : ?>
		<div class="tutor-nav-tabs-container">
			<div class="tutor-nav tutor-nav-tabs">
				<?php foreach ( $data as $key => $value ) : ?>
					<?php $active = $value['active']; ?>
					<div class="tutor-nav-item">
						<div class="tutor-nav-link<?php echo esc_attr( $active ); ?>" data-tutor-nav-target="<?php echo esc_attr( $value['data_attr'] ); ?>" role="button">
							<div class="tutor-fs-7 tutor-color-secondary">
								<?php echo esc_html( $value['tab_title'] ); ?>
							</div>
							<div class="tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mt-4">
								<?php if ( $value['price'] ) : ?>
									<?php echo $value['tab_value'] ? wp_kses_post( tutor_utils()->tutor_price( $value['tab_value'] ) ) : '-'; ?>
								<?php else : ?>
									<?php esc_html_e( $value['tab_value'] ? $value['tab_value'] : '-' ); ?>
								<?php endif; ?>    
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="tutor-tab">
				<?php foreach ( $data as $key => $value ) : ?>
					<?php $active = $value['active']; ?>
					<div class="tutor-tab-item<?php echo esc_attr( $active ); ?>" id="<?php echo esc_attr( $value['data_attr'] ); ?>">
						<div class="tutor-py-24 tutor-px-32">
							<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
								<?php echo esc_html( $value['content_title'] ); ?>
							</div>
							<canvas id="<?php echo esc_attr( $value['data_attr'] . '_canvas' ); ?>"></canvas>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
