<div class="tutor-container">
    <div class="tutor-row tutor-justify-center">
        <div class="tutor-col-lg-6 tutor-col-xl-4">
            <div id="tutor-gc-student-password-set" class="tutor-card">
                <div class="tutor-card-body">
                    <div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php esc_html_e( 'Set Password', 'tutor-pro' ); ?></div>

                    <div class="tutor-mb-16">
                        <label class="tutor-form-label"><?php esc_html_e( 'Password', 'tutor-pro' ); ?></label>
                        <input type="password" class="tutor-form-control" name="password-1" />
                    </div>

                    <div class="tutor-mb-16">
                        <label class="tutor-form-label"><?php esc_html_e( 'Re-type Password', 'tutor-pro' ); ?></label>
                        <input type="password" class="tutor-form-control" name="password-2" />
                    </div>

                    <div>
                        <button class="tutor-btn tutor-btn-primary"> 
                            <?php esc_html_e( 'Set Password', 'tutor-pro' ); ?>
                        </button>
                    </div>
                    <input type="hidden" name="token" value="<?php echo esc_attr( $_GET['token'] ); ?>"/>
                </div>
            </div>
        </div>
    </div>
</div>