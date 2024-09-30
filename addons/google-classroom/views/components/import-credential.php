<div class="consent-screen oauth-redirect-url">
    <?php
        echo sprintf( __( 'Create OAuth access data and upload Credentials JSON from %s Google Console %s. As a redirect URI set %s', 'tutor-pro' ), '<a href="https://console.developers.google.com/" target="_blank"><b>', '</b></a>', '<b>' . get_home_url() . '/'.\TUTOR_GC\init::$google_callback_string . '/</b>' );
    ?>
</div>

<div class="consent-screen" id="tutor_gc_credential_upload">
    <div class="tutor-upload-area">
        <div class="tutor-mb-12">
            <span class="tutor-fs-1 tutor-fw-bold tutor-color-primary tutor-icon-upload"></span>
        </div>

        <div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
            <?php esc_html_e( 'Drag & Drop your JSON File here', 'tutor-pro' ); ?>
        </div>

        <div class="tutor-fs-8 tutor-color-muted tutor-mb-12">
            <?php esc_html_e( 'or', 'tutor-pro' ); ?></small>
        </div>

        <button class="tutor-btn tutor-btn-primary tutor-btn-md"><?php esc_html_e( 'Browse File', 'tutor-pro' ); ?></button>
        <input type="file" name="credential" accept=".json"/>
    </div>
    <button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-lg" disabled="disabled">
        <?php esc_html_e( 'Load Credentials', 'tutor-pro' ); ?> 
    </button>
</div>