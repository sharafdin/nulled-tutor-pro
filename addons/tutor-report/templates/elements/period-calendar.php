<div class="analytics-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-my-24">
    <?php _e( 'Earnings Graph', 'tutor-pro' ); ?>
</div>
<div class="tutor-analytics-filter-tabs tutor-d-flex tutor-flex-xl-nowrap tutor-flex-wrap tutor-align-center tutor-justify-between tutor-pb-40">
    <?php 
        $active     = isset( $_GET['period'] ) ? sanitize_text_field( $_GET['period'] ) : '';
        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
        $end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
    ?>
    <?php if( count( $data['filter_period'] ) ): ?>
        <div class="tutor-d-flex tutor-align-center tutor-justify-between">
            <?php foreach( $data['filter_period'] as $key => $value ): ?>
                <?php $active_class = $active === $value['type'] ? ' is-active' : ''; ?>
                <a href="<?php echo $value['url']; ?>" class="tutor-btn tutor-btn-outline-primary <?php esc_attr_e($value['class'].' '.$active_class); ?> tutor-mr-16">
                    <?php esc_html_e( $value['title'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ( $data['filter_calendar'] ): ?>
        <div class="tutor-v2-date-range-picker" style="flex-basis: 40%;"></div>
    <?php endif; ?>
</div>