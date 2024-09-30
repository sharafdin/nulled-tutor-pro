<?php if ( count ($materials_array) ) : ?>
<div class="tutor-gc-stream-materials tutor-row tutor-mt-16">
    <?php
        foreach ( $materials_array as $attachment ) :
            $public_resource = $attachment->youtubeVideo ? $attachment->youtubeVideo : $attachment->link;
            $drive_file = $attachment->driveFile ? $attachment->driveFile : null;
            ( $drive_file && $drive_file->driveFile ) ? $drive_file = $drive_file->driveFile : 0;

            $content = $drive_file ? $drive_file : $public_resource;

            if ( ! $content ) {
                continue;
            }
    ?>
        <div class="tutor-col-md-6 tutor-mt-12">
            <div class="tutor-gc-stream-material tutor-card">
                <div class="tutor-row tutor-gx-0">
                    <div class="tutor-col-4">
                        <div class="tutor-ratio tutor-ratio-16x9">
                            <img class="tutor-card-image-left" src="<?php echo esc_url( $content->thumbnailUrl ?? '' ); ?>" alt="<?php echo $content->post_title; ?>" loading="lazy">
                        </div>
                    </div>

                    <div class="tutor-col-8 tutor-align-self-center">
                        <div class="tutor-text-ellipsis tutor-px-24">
                            <?php echo esc_html( $content->title ); ?>
                        </div>
                        <a target="_blank" href="<?php echo esc_url( ($content->alternateLink ? $content->alternateLink : $content->url) ); ?>" class="tutor-stretched-link"></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>