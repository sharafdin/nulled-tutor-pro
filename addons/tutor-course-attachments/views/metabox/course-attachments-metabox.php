<div class="tutor-attachments-metabox">
    <?php
        $course_id   = ! is_admin() && isset( $_GET['course_ID'] ) ? $_GET['course_ID'] : get_the_ID();
        $attachments = tutor_utils()->get_attachments( $course_id );
        tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/attachments.php', array(
            'name' => 'tutor_attachments[]',
            'attachments' => $attachments,
            'add_button' => true,
            'size_below' => true,
            'is_responsive' => is_admin() ? true : false
        ), false);
    ?>
    <input type="hidden" name="_tutor_attachments_main_edit" value="true" />  
</div>