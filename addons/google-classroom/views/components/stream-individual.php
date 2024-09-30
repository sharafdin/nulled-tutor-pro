<?php
    foreach ( $classroom_stream as $stream ) :
            
    $photo_url = $stream->creator_user_object->photoUrl;
    strpos( $photo_url, '//') === 0 ? $photo_url = 'https://' . $photo_url : 0;
    $user_name = $stream->creator_user_object->name->fullName;
?>
    <div class="tutor-gc-stream-single tutor-card tutor-mt-24">
        <div class="tutor-card-body">
            <div class="tutor-position-relative">
                <div class="tutor-d-flex tutor-mb-20">
                    <div class="tutor-mr-16">
                        <div class="tutor-avatar tutor-avatar-md">
                            <div class="tutor-ratio tutor-ratio-1x1">
                                <img src="<?php echo esc_url( $photo_url ); ?>" />
                            </div>
                        </div>
                    </div>
    
                    <div>
                        <h3 class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-4"><?php echo esc_html( $user_name ); ?></h3>
                        <div class="tutor-fs-7 tutor-color-muted"><?php echo esc_html( date( "j F, Y", strtotime( $stream->creationTime ) ) ); ?></div>
                    </div>
                </div>
    
                <div class="tutor-gc-stream-single-content tutor-fs-7 tutor-color-secondary">
                    <?php echo esc_html( $stream->text ); ?>
                </div>
                <a href="<?php echo esc_url( $stream->alternateLink ); ?>" class="tutor-stretched-link"></a>
            </div>
            <?php 
                if ( $show_stream_files ) {
                    $materials_array = $stream->materials ? $stream->materials : array();
                    include 'materials.php';
                }
            ?>
        </div>
    </div>
<?php endforeach; ?>