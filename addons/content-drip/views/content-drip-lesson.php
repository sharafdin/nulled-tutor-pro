<?php

$post_id = get_the_ID();

/**
 * define vars first
 * prevent undefined error
 * @since 1.8.9
*/
$lesson_id		= 0;
$quiz_id		= 0;
$assignment_id	= 0;

if( count($_POST) > 0 ) {
	$lesson_id = tutor_utils()->array_get('lesson_id', $_POST);
	$quiz_id = tutor_utils()->array_get('quiz_id', $_POST);
	$assignment_id = tutor_utils()->array_get('assignment_id', $_POST);
} else {
	
	/**
	 * retrieve post 
	 * if not null set lesson id
	 * @since 1.8.9
	*/
	$post = get_post($post_id);
	if( !is_null($post) ) {
		if( $post->post_type == tutor()->lesson_post_type ) {
			$lesson_id = $post->ID;
		}
	}
}



$course_item_id = 0;
if ($lesson_id){
	$course_item_id = $lesson_id;
}elseif ($quiz_id){
	$course_item_id = $quiz_id;
}elseif ($assignment_id){
	$course_item_id = $assignment_id;
}

if ( $course_item_id){
	$post_id = (int) sanitize_text_field($course_item_id);
}

/**
 * check for $_POST
 * if not set item then get course id from utils
 * by lesson id
 * @since 1.8.9
*/
$course_id = 0;
if( count($_POST) > 0 ) {
	$course_id = (int) sanitize_text_field(tutor_utils()->array_get('course_id', $_POST));
} else {
	$course_id = tutor_utils()->get_course_id_by( 'lesson', $lesson_id );
}

$enable_content_drip = get_tutor_course_settings($course_id, 'enable_content_drip');
if ( ! $enable_content_drip){
	return;
}
$content_drip_type = get_tutor_course_settings($course_id, 'content_drip_type', 'unlock_by_date');
if ($content_drip_type === 'unlock_sequentially'){
	return;
}
?>

<div class="lqa-content-drip-wrap">
	<span><?php _e('Content Drip Settings', 'tutor-pro'); ?></span>
	<?php
	if ($content_drip_type === 'unlock_by_date'){
		$unlock_date = get_item_content_drip_settings($course_item_id, 'unlock_date');
		?>
        <div class="">
            <label>
				<?php _e('Unlocking date', 'tutor-pro'); ?>
            </label>
            <div class="tutor-mb-4 tutor-d-block" style="width: 218px;">
				<div class="tutor-v2-date-picker" data-is_clearable="true" data-prevent_redirect="1" data-input_name="content_drip_settings[unlock_date]" data-input_value="<?php echo $unlock_date ? tutor_get_formated_date( 'd-m-Y', $unlock_date ) : ''; ?>">

				</div>
            </div>
        </div>
		<?php
	}elseif ($content_drip_type === 'specific_days'){
		$days = get_item_content_drip_settings($course_item_id, 'after_xdays_of_enroll', 7);
		?>
        <div class="">
            <label class="tutor-form-label">
                <?php _e('Days', 'tutor-pro'); ?>
            </label>
            <div class="tutor-mb-4 tutor-d-block">
				<input class="tutor-form-control" type="number" min="0" step="1" onkeypress='return event.charCode >= 48 && event.charCode <= 57' value="<?php echo $days; ?>" name="content_drip_settings[after_xdays_of_enroll]">
                <div class="tutor-form-feedback">
                	<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
					<div>
						<?php _e('This lesson will be available after the given number of days.', 'tutor-pro'); ?>
					</div>
				</div>
            </div>
        </div>
		<?php

	}elseif($content_drip_type === 'after_finishing_prerequisites'){
		$prerequisites = (array) get_item_content_drip_settings($course_item_id, 'prerequisites');
		$query_topics = tutor_utils()->get_topics($course_id);

		if (tutor_utils()->count($query_topics->posts)){
			?>
            <div class="">
                <label class="tutor-form-label">
                    <?php _e('Prerequisites', 'tutor-pro'); ?>
                </label>
                <div class="tutor-input-group tutor-mb-4 tutor-d-block">
                    <select name="content_drip_settings[prerequisites][]" multiple="multiple" class="select2_multiselect">
                        <option value=""><?php _e('Select prerequisites item', 'tutor-pro'); ?></option>
						<?php
						foreach ($query_topics->posts as $topic){
							echo "<optgroup label='{$topic->post_title}'>";
							$topic_items = tutor_utils()->get_course_contents_by_topic($topic->ID, -1);
							foreach ($topic_items->posts as $topic_item){
							    if ($topic_item->ID != $course_item_id){

							        $isSelected = '';
							        if (in_array($topic_item->ID, $prerequisites)){
								        $isSelected = 'selected="selected"';
                                    }

								    echo "<option value='{$topic_item->ID}' {$isSelected} >{$topic_item->post_title}</option>";
							    }
							}
							echo "</optgroup>";
						}
						?>
                    </select>
					<div class="tutor-form-feedback">
                		<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
						<div>
							<?php _e('Select items that should be complete before this item', 'tutor-pro'); ?>
						</div>
					</div>
                </div>
            </div>
			<?php
		}
	}
	?>
</div>