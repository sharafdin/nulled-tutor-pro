<div class="tutor-mt-80 tutor-pb-80" style="margin-left: 110px">
    <div>
        <img src="<?php echo TUTOR_CONTENT_DRIP()->url; ?>/assets/images/restrict.jpg" style=" position: relative; left: -15px; margin-bottom: 50px; max-width: 300px"/>
        <div style="font-weight: 500; font-size: 24px; color: #212327;" class="tutor-mb-20">
            <?php echo wp_kses_post( $this->unlock_message ); ?>
        </div>
        
        <?php if ( ! $this->quiz_pass_req ): ?>
        <div style="font-weight: 500; font-size: 20px; color: #212327;">
            <?php echo esc_html( $previous_title ); ?>
        </div>
        <?php endif; ?>

        <?php if ( $this->quiz_pass_req && $this->quiz_manual_review_required ): ?>
        <div class="tutor-app-process-alert" style="width: 95%;margin-bottom:45px;margin-top:40px">
            <div style="border:1px solid var(--tutor-color-warning);" class="tutor-primary tutor-py-12 tutor-px-20 tutor-radius-6">
                <div class="tutor-alert-text tutor-d-flex tutor-align-start">
                <span class="tutor-icon-circle-info tutor-fs-5 tutor-color-warning tutor-mr-12"></span>
                <span class="tutor-fs-7">
                    <?php esc_html_e( 'For any open ended question (short/broad description) which requires instructor’s review, the next course content will be available only when the instructor reviews the quiz.', 'tutor-pro' ) ?>
                </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div>
            <a href="<?php echo esc_url( $previous_permalink ); ?>" class="tutor-btn tutor-btn-primary tutor-mt-36">
                <?php echo esc_html( sprintf( __( 'Back to %s', 'tutor-pro' ), $previous_content_type ) ); ?>
            </a>
        </div>

    </div>
</div>