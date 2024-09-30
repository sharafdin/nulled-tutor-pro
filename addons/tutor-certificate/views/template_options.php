<?php
if ( ! defined( 'ABSPATH' ) )
	exit;
?>


<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for=""><?php _e('Select Certificate Template', 'tutor-pro'); ?></label>
    </div>
    <div class="tutor-option-field">
        <div class="tutor-certificate-templates-fields">
			<?php
			if (tutor_utils()->count($templates)){
				foreach ($templates as $template_key => $template){
				    if ( $template['orientation'] !== 'landscape')
				        continue;
					?>
                    <label class="tutor-certificate-template <?php echo ($template_key === $selected_template) ? 'selected-template' : '' ?> ">
                        <img src="<?php echo $template['preview_src']; ?>" />
                        <input type="radio" name="<?php echo $template_field_name; ?>" value="<?php echo $template_key; ?>" <?php checked($template_key,
                            $selected_template) ?> style="display: none;" >
                    </label>
					<?php
				}
			}
			?>
        </div>

        <div class="tutor-certificate-templates-fields">
		    <?php if (tutor_utils()->count($templates)) {
			    foreach ($templates as $template_key => $template) {
				    if ( $template['orientation'] !== 'portrait')
					    continue;
				    ?>
                    <label class="tutor-certificate-template <?php echo ($template_key === $selected_template) ? 'selected-template' : '' ?> ">
                        <img src="<?php echo $template['preview_src']; ?>" />
                        <input type="radio" name="<?php echo $template_field_name; ?>" value="<?php echo $template_key; ?>" <?php checked($template_key,
						    $selected_template) ?> style="display: none;" >
                    </label>
				    <?php
			    }
		    }
		    ?>
        </div>
    </div>
</div>