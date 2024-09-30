<?php
	$logged_in_as = '';
    try {
        $logged_in_as = $classroom->get_who_logged_in();
    } catch ( \Exception $e ) {
        $message = $e->getMessage();
    }
?>

<div class="tutor-row tutor-gc-setting-container">
    <div class="tutor-col-lg-6 tutor-mb-24 tutor-mb-lg-0">
        <div class="tutor-card">
            <div class="tutor-card-body">
                <div class="tutor-row tutor-align-center">
                    <div class="tutor-col-12 tutor-col-md-6">
                        <div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Classroom List', 'tutor-pro' ); ?></div>
                        <div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Here is a list of Classrooms on your current Google account.', 'tutor-pro' ); ?></div>
                    </div>
                    <div class="tutor-col-12 tutor-col-md-6 tutor-d-md-flex tutor-justify-md-end">
                        <button id="tutor_gc_credential_upgrade" class="tutor-btn tutor-btn-outline-primary" data-message="<?php esc_attr_e( 'Sure to use another account?', 'tutor-pro' ); ?>">
                            <?php esc_html_e( 'Use Another Account', 'tutor-pro' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="tutor-card-footer">
                <div class="tutor-row tutor-align-center">
                    <div class="tutor-col-12 tutor-col-md-6">
                        <?php esc_html_e( 'Google Classroom Account', 'tutor-pro' ); ?>:
                        <b><?php echo esc_html( '' !== $logged_in_as ?  $logged_in_as->emailAddress : '' ); ?></b>
                    </div>
                    <div class="tutor-col-12 tutor-col-md-6" style="text-align:right">
                        <?php esc_html_e( 'Classlist Shortcode:', 'tutor-pro' ); ?> <span><b>[tutor_gc_classes]</b> <span class="tutor-iconic-btn tutor-mr-n8 tutor-copy-text" data-text="[tutor_gc_classes]" role="button"><span class="tutor-icon-copy" area-hidden="true"></span></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tutor-col-lg-6">
        <div class="tutor-card">
            <div class="tutor-card-body">
                <div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Classroom Access Settings', 'tutor-pro' ); ?></div>
                <div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Control the visibility and privacy for the Google Classroom data', 'tutor-pro' ); ?></div>
            </div>
            
            <div class="tutor-card-footer">
                <div class="tutor-d-flex tutor-align-center">
                    <label class="tutor-form-toggle">
                        <input type="checkbox" id="tutor_gc_classroom_code_privilege" class="tutor-form-toggle-input" <?php echo $is_code_for_only_logged ? 'checked="checked"' : ''; ?>>
                        <span class="tutor-form-toggle-control"></span>
                    </label>
                    <span class="tutor-ml-8"><?php esc_html_e( 'Only logged in students can see the classroom invite code', 'tutor-pro' ); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>