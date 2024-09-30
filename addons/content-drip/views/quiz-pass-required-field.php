<div class="tutor-mb-32 tutor-pass-required-field" style="<?php echo esc_attr( tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', 'default' ) === 'retry' ? 'display: block' : 'display: none' ); ?>">
    <label class="tutor-form-toggle tutor-pass-required-toggle">
        <input  type="checkbox" 
                class="tutor-form-toggle-input" 
                value="1" 
                name="quiz_option[pass_is_required]" 
            <?php checked( '1', tutor_utils()->get_quiz_option( $quiz_id, 'pass_is_required' ) ); ?> />

        <span class="tutor-form-toggle-control"></span> <?php _e( 'Passing is Required', 'tutor-pro' ); ?></span>
    </label>
    <div class="tutor-form-feedback">
        <?php _e( 'By enabling this option, the student must have to pass it to access the next quiz', 'tutor-pro' ); ?>
    </div>
</div>