<?php
$user_id = get_current_user_id();
if ( current_user_can( 'administrator' ) ) {
    $post = get_post( $course_id );
    $user_id = $post->post_author; 
}

$zoom_object = new \TUTOR_ZOOM\Zoom( false );

$zoom_meetings = $zoom_object->get_meetings( null, null, null, array(
    'author'    =>  $user_id,
    'course_id' => $course_id
), false );

?>
<?php if ( ! count( $zoom_meetings ) ): ?>
    <div class="tutor-d-lg-flex tutor-align-center tutor-justify-sm-between tutor-zoom-no-meetings">
        <div class="tutor-d-flex tutor-align-center tutor-mb-lg-0 tutor-mb-16">
            <span class="tutor-icon-brand-zoom tutor-fs-3 tutor-mr-8" style="color: #2e8cff" area-hidden="true"></span>
            <div class="tutor-fs-5 tutor-fw-medium tutor-color-secondary tutor-ml-8">
                <?php _e('Connect with your students using Zoom', 'tutor-pro'); ?>
            </div>
        </div>
        <div class="tutor-ml-lg-12 tutor-ml-0">
            <button class="tutor-btn tutor-btn-primary create-zoom-meeting-btn" data-tutor-modal-target="tutor-zoom-new-meeting">
                <span class="tutor-icon-brand-zoom tutor-mr-8" area-hidden="true"></span>
                <span><?php _e('Create a Zoom Meeting', 'tutor-pro'); ?></span>
            </button>
        </div>
    </div>
<?php else: ?>
    <div class="tutor-course-builder-zoom-meeting-list">
        <?php
        foreach ($zoom_meetings as $meeting) :
            $tzm_start      = get_post_meta($meeting->ID, '_tutor_zm_start_datetime', true);
            $meeting_data   = get_post_meta($meeting->ID, $this->zoom_meeting_post_meta, true);
            $meeting_data   = json_decode($meeting_data, true);

            if ( !$tzm_start ) {
                continue;
            }

            $input_date     = \DateTime::createFromFormat('Y-m-d H:i:s', $tzm_start);
            $start_date     = $input_date->format('j M, Y');
            $start_time     = $input_date->format('h:i A');

            $row_id         = 'tutor-zoom-meeting-row-' . $meeting->ID;
            $id_string_delete = 'tutor-zoom-meeting-del-' . $meeting->ID;
        ?>
        <div class="tutor-course-builder-zoom-meeting-list-item" id="<?php echo $row_id; ?>"> <!-- Row id is mandatory -->
            <div class="tutor-row">
                <div class="tutor-col-6 tutor-col-lg-3 tutor-mb-16 tutor-mb-lg-0">
                    <div class="tutor-fs-7 tutor-color-secondary tutor-mb-8">
                        <?php _e('Start Time', 'tutor-pro'); ?>
                    </div>
                    <div class="tutor-fs-6 tutor-color-black">
                        <?php echo $start_date; ?> <?php echo $start_time; ?>
                    </div>
                </div>

                <div class="tutor-col-6 tutor-col-lg-3 tutor-mb-16 tutor-mb-lg-0">
                    <div class="tutor-fs-7 tutor-color-secondary tutor-mb-8">
                        <?php _e('Meeting Name', 'tutor-pro'); ?>
                    </div>
                    <div class="tutor-fs-6 tutor-color-black">
                        <?php echo $meeting->post_title; ?>
                    </div>
                </div>

                <div class="tutor-col-6 tutor-col-lg-3 tutor-mb-16 tutor-mb-lg-0">
                    <div class="tutor-fs-7 tutor-color-secondary tutor-mb-8">
                        <?php _e('Meeting Token', 'tutor-pro'); ?>
                    </div>
                    <div class="tutor-fs-6 tutor-color-black">
                        <?php echo !empty($meeting_data['id']) ? $meeting_data['id'] : ''; ?>
                    </div>
                </div>

                <div class="tutor-col-6 tutor-col-lg-3">
                    <div class="tutor-fs-7 tutor-color-secondary tutor-mb-8">
                        <?php _e('Password', 'tutor-pro'); ?>
                    </div>
                    <div class="tutor-fs-6 tutor-color-black">
                        <?php echo !empty($meeting_data['password']) ? $meeting_data['password'] : ''; ?>
                    </div>
                </div>
            </div>

            <div class="tutor-d-flex tutor-align-center tutor-mt-16">
                <?php if(isset($meeting_data['start_url']) ): ?>
                    <a href="<?php echo $meeting_data['start_url']; ?>" target="_blank" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mr-16">
                        <span class="tutor-icon-brand-zoom tutor-mr-8" area-hidden="true"></span>
                        <span><?php _e('Start Meeting', 'tutor-pro'); ?></span>
                    </a>
                <?php endif; ?>
                <button class="tutor-iconic-btn tutor-mr-8" data-tutor-modal-target="tutor-zoom-meeting-modal-<?php echo $meeting->ID; ?>">
                    <span class="tutor-icon-pencil" area-hidden="true"></span>
                </button>
                <button class="tutor-iconic-btn" data-tutor-modal-target="<?php echo $id_string_delete; ?>">
                    <span class="tutor-icon-trash-can" area-hidden="true"></span>
                </button>
            </div>
            <?php $zoom_object->tutor_zoom_meeting_modal_content($meeting->ID, $topic_id, $course_id, 'metabox'); ?>
            
            <?php
                // Meeting Delete Confirmation Modal
                tutor_load_template( 'modal.confirm', array(
                    'id' => $id_string_delete,
                    'image' => 'icon-trash.svg',
                    'title' => __('Do You Want to Delete This Meeting?', 'tutor-pro'),
                    'content' => __('Are you sure you want to delete this meeting permanently? Please confirm your choice.', 'tutor-pro'),
                    'yes' => array(
                        'text' => __('Yes, Delete This', 'tutor'),
                        'class' => 'tutor-list-ajax-action',
                        'attr' => array('data-request_data=\'{"action":"tutor_zoom_delete_meeting", "meeting_id":"' . $meeting->ID . '"}\'', 'data-delete_element_id="' . $row_id . '"')
                    ),
                ));
            ?>
        </div>
        <?php endforeach; ?>
        <div class="tutor-course-builder-zoom-meeting-list-item">
            <button class="tutor-btn tutor-btn-primary create-zoom-meeting-btn" data-tutor-modal-target="tutor-zoom-new-meeting">
                <span class="tutor-icon-brand-zoom tutor-mr-8"></span>
                <span><?php _e('Create a Zoom Meeting', 'tutor-pro'); ?></span>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php (new \TUTOR_ZOOM\Zoom(false))->tutor_zoom_meeting_modal_content(0, 0, $course_id, 'metabox', 'tutor-zoom-new-meeting'); ?>