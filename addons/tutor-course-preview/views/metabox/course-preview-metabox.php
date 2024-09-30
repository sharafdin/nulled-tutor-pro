<?php
$post_id = get_the_ID();
if ( ! empty($_POST['lesson_id'])){
	$post_id = sanitize_text_field($_POST['lesson_id']);
}

$_is_preview = get_post_meta($post_id, '_is_preview', true);
?>


<div class="tutor-mb-8">
    <div class="tutor-input-group">
        <div class="tutor-form-check tutor- tutor-align-center">
            <input id="_enable_preview_course" type="checkbox" class="tutor-form-check-input" name="_is_preview" value="1"  <?php checked(1, $_is_preview); ?>/>
            <label for="_enable_preview_course">
                <?php _e('Enable Course Preview', 'tutor-pro'); ?>
            </label>
        </div>
    </div>
    <div class="tutor-form-feedback">
        <i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
        <div>
            <?php _e('If checked, any users/guest can view this lesson without enroll course', 'tutor-pro'); ?>
        </div>
    </div>
</div>