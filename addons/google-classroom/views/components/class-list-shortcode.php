<div class="tutor-wrap tutor-container tutor-gc-courses">
    <?php if ( count( $google_classes ) ) : ?>
        <div class="tutor-row">
            <?php foreach ( $google_classes as $class ) : ?>
                <div class="<?php echo esc_attr( $column_class ); ?>">
                    <div id="<?php echo $row_id; ?>" class="tutor-card tutor-course-card tutor-gc-course-card">
                        <a href="<?php echo esc_url( get_permalink( $class->ID ) ); ?>" class="tutor-d-block">
                            <div class="tutor-gc-course-thumbnail">
                                <div class="tutor-ratio tutor-ratio-16x9">
                                    <img class="tutor-card-image-top" src="<?php echo esc_url( $class->post_thumbnail_url ); ?>" alt="<?php echo $class->post_title; ?>" loading="lazy">
                                </div>
        
                                <div class="tutor-avatar tutor-avatar-md">
                                    <div class="tutor-ratio tutor-ratio-1x1">
                                        <img src="<?php echo esc_url( $class->remote_class_owner->photoUrl ); ?>" />
                                    </div>
                                </div>
                            </div>
                        </a>

                        <div class="tutor-card-body">
                            <div class="tutor-meta tutor-mt-16 tutor-mb-16">
                                <span>
                                    <?php echo esc_html_e( "By", "tutor-pro" ); ?> <span class="tutor-meta-value"><?php echo esc_html( $class->remote_class_owner->name->fullName ); ?></span>
                                </span>
                            </div>

                            <div class="tutor-course-name tutor-fs-6 tutor-fw-bold tutor-mb-16">
                                <a href="<?php echo esc_url( get_permalink( $class->ID ) ); ?>">
                                    <?php echo esc_html( $class->post_title ); ?>
                                </a>
                            </div>

                            <div class="tutor-meta tutor-mb-24">
                                <div>
                                    <?php echo esc_html( $class->remote_class->room_and_section ); ?>
                                </div>
                            </div>

                            <?php if ( $is_class_restricted && ! tutor_utils()->is_enrolled( $class->ID, get_current_user_id() ) ) : ?>
                                <div class='tutor-alert tutor-primary tutor-mt-auto'>
                                    <div class='tutor-alert-text tutor-d-flex tutor-align-start'>
                                        <span class='tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12' area-hidden="true"></span>
                                        <span><?php esc_html_e( 'Only logged in students in a specific Classroom can join.', 'tutor-pro' ); ?></span>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="tutor-gc-code tutor-d-flex tutor-align-center tutor-justify-between tutor-mt-auto">
                                    <div>
                                        <span><?php esc_html_e( 'Code', 'tutor-pro' ); ?>: </span>
                                        <span><?php echo esc_html( $class->remote_class->enrollmentCode ); ?></span>
                                    </div>
                                    <div>
                                        <span class="tutor-iconic-btn tutor-copy-text tutor-mr-n8" data-text="<?php echo esc_attr( $class->remote_class->enrollmentCode ); ?>" role="button"><span class="tutor-icon-copy" area-hidden="true"></span></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
            $page = isset( $_GET['class_page'] ) ? $_GET['class_page'] : 1;
            ( ! is_numeric( $page ) || $page < 1 ) ? $page = 1 : 0;
        ?>
        <div class="tutor-pagination-wrap">
            <?php if ( $page > 1 ) : ?>
            <a class="next previous page-numbers" href="?class_page=<?php echo $page - 1; ?>">
                <span class="tutor-icon-angle-double-left tutor-mr-8" area-hidden="true"></span> <?php esc_html_e( 'Previous', 'tutor-pro' ); ?>
            </a>
            <?php endif; ?>
            <a class="previous previous page-numbers" href="?class_page=<?php echo $page + 1; ?>">
                <?php esc_html_e( 'Next', 'tutor-pro' ); ?> <span class="tutor-icon-angle-double-right tutor-ml-8" area-hidden="true"></span>
            </a>
        </div>
    <?php else : ?>
        <?php tutor_utils()->tutor_empty_state( esc_html( "No Class Found", "tutor-pro" ) ); ?>
    <?php endif; ?>
</div>
