<div class="tutor-gc-stream-classroom-info">
    <h3 class="tutor-fs-5 tutor-fw-medium tutor-mb-12"><?php echo esc_html( $classroom_info->descriptionHeading ); ?></h3>
    <div class="tutor-fs-6 tutor-mb-32"><?php echo esc_html( $classroom_info->room_and_section ); ?></div>
    
    <div class="tutor-d-flex tutor-align-center">
        <span class="tutor-plain-code tutor-gc-class-code tutor-d-flex tutor-align-center">
            <div class="tutor-mr-16">
                <span class="tutor-color-muted"><?php esc_html_e( 'Code', 'tutor-pro' ); ?>: </span>
                <span><?php echo esc_html( $classroom_info->enrollmentCode ); ?></span>
            </div>
            <div>
                <span class="tutor-iconic-btn tutor-iconic-btn-light tutor-copy-text" data-text="<?php echo esc_attr( $classroom_info->enrollmentCode ); ?>" role="button"><span class="tutor-icon-copy-text" area-hidden="true"></span></span>
            </div>
        </span>

        <div class="tutor-ml-32">
            <a class="tutor-btn tutor-btn-ghost tutor-btn-ghost-light tutor-has-underline" href="<?php echo esc_url( $classroom_info->alternateLink ); ?>">
                <?php esc_html_e( 'Go to Classroom', 'tutor-pro' ); ?>
            </a>
        </div>
    </div>
</div>

<div class="tutor-gc-streams">
    <div tutor-gc-streams>
        <?php include 'stream-individual.php'; ?>
    </div>

    <?php if ( $stream_next_token ) : ?>
        <div class="tutor-text-center tutor-mt-32">
            <a href="#" class="tutor-btn tutor-btn-outline-primary" tutor-gc-stream-loader data-next_token="<?php echo esc_attr( $stream_next_token ); ?>" data-course_id="<?php echo esc_attr( $course_id ); ?>"><?php esc_html_e( "Load More", "tutor-prop" ); ?></a>
        </div>
    <?php endif; ?>
</div>