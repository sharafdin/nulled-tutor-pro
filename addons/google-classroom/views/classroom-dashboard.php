<div class="tutor-admin-wrap">
    <div class="tutor-wp-dashboard-header tutor-px-24 tutor-mb-24">
        <div class="tutor-px-12 tutor-py-16">
            <span class="tutor-fs-5 tutor-fw-medium tutor-mr-16"><?php esc_html_e( 'Google Classroom', 'tutor-pro' ); ?></span>
        </div>
    </div>

    <div class="tutor-admin-body">
        <div id="tutor_gc_dashboard">
            <?php
                if ( ! $classroom->is_credential_loaded() ) {
                    include 'components/import-credential.php';
                } elseif ( ! $classroom->is_app_permitted() ) {
                    include 'components/consent-screen.php';
                } else {
                    include 'components/class-list.php';
                    include 'components/footer-settings.php';
                }
            ?>
        </div>
    </div>
</div>