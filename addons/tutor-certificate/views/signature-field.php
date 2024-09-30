<div class="tutor-row tutor-mb-60">
    <div class="tutor-col-12">
        <div class="tutor-form-group">
            <label class="tutor-form-label tutor-color-black"><?php _e('Certificate Signature', 'tutor-pro'); ?></label>
            <?php 
                tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/thumbnail-uploader.php', array(
                    'media_id' => isset($signature['id']) ? $signature['id'] : null,
                    'input_name' => $this->file_id_string,
                    'borderless' => true
                ), false);
            ?>
        </div>
    </div>
</div>