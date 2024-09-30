<?php if ( is_array( $data ) && count( $data ) ): ?>
    <div class="tutor-analytics-info-cards">
        <div class="tutor-row tutor-gx-lg-4">
            <?php foreach( $data as $key => $value ): ?>
                <div class="tutor-col-lg-6 tutor-col-xl-4 tutor-mb-16 tutor-mb-lg-32">
                    <div class="tutor-card">
                        <div class="tutor-d-flex tutor-flex-lg-column tutor-align-center tutor-text-lg-center tutor-px-12 tutor-px-lg-24 tutor-py-8 tutor-py-lg-32">
                            <span class="tutor-round-box tutor-mr-12 tutor-mr-lg-0 tutor-mb-lg-12">
                                <i class="<?php echo $value['icon']; ?>" area-hidden="true"></i>
                            </span>
                            <div class="tutor-fs-3 tutor-fw-bold tutor-d-none tutor-d-lg-block">
                                <?php if ( $value['price'] ): ?>
                                    <?php echo $value['title'] ? wp_kses_post(tutor_utils()->tutor_price( $value['title'] )) : '-'; ?>
                                <?php else: ?>
                                    <?php echo $value['title'] ? esc_html($value['title']) : '-'; ?>
                                <?php endif; ?>
                            </div>
                            <div class="tutor-fs-7 tutor-color-secondary"><?php echo esc_html($value['sub_title']); ?></div>
                            <div class="tutor-fs-4 tutor-fw-bold tutor-d-block tutor-d-lg-none tutor-ml-auto">
                                <?php if ( $value['price'] ): ?>
                                    <?php echo $value['title'] ? wp_kses_post(tutor_utils()->tutor_price( $value['title'] )) : '-'; ?>
                                <?php else: ?>
                                    <?php echo $value['title'] ? esc_html($value['title']) : '-'; ?>
                                <?php endif; ?> 
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>       