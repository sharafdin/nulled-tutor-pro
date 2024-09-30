<div class="consent-screen google-consent-screen-redirect">
    <div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php esc_html_e( 'Please complete the authorization process', 'tutor-pro' ); ?></div>
    <div class="tutor-fs-6 tutor-color-muted tutor-mb-52"><?php esc_html_e( 'Press the button to grant permissions to your Google Classroom. Please allow all required permissions.', 'tutor-pro' ); ?></div>
    
    <div class="tutor-mb-52">
        <img src="<?php echo esc_url( TUTOR_GC()->url . '/assets/images/classroom.svg' ); ?>"/>
    </div>

    <div class="tutor-mb-24">
        <a class="tutor-btn tutor-btn-primary" href="<?php echo esc_url( $classroom->get_consent_screen_url() ); ?>">
            <?php esc_html_e( 'Allow Permissions', 'tutor-pro' ); ?>
        </a>
    </div>

    <div>
        <a href="#" id="tutor_gc_credential_upgrade" class="tutor-btn tutor-btn-ghost">
            <?php esc_html_e( 'Change Credential', 'tutor-pro' ); ?>
        </a>
    </div>
</div>