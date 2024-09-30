<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

    function load_template_cert( $templates, $mode, $template_field_name, $selected_template ) {
        if ( tutor_utils()->count( $templates ) ) {
            $added = 0;
            $current_user_id = get_current_user_id();

            foreach ( $templates as $template_key => $template ) {
                if ( $template['orientation'] !== $mode){
                    continue;
                }

                $added++;
                $id_key = 'tutor-certificate-' . $template_key;

                ?>
                <div class="tutor-certificate-template">
                    <label for="<?php echo $id_key; ?>">
                        <input type="radio" name="<?php echo $template_field_name; ?>" value="<?php echo $template_key; ?>" id="<?php echo $id_key; ?>" <?php checked($template_key, $selected_template) ?> />
                        <div class="tutor-certificate-template-inner">
                            <img src="<?php echo $template['preview_src']; ?>" alt="<?php echo $template_field_name; ?>">
                            <div class="tutor-certificate-template-overlay">
                                <span class="tutor-btn tutor-btn-primary tutor-btn-sm"><?php $template_key == 'none' ? _e('Disable Certificate', 'tutor') : _e('Use This', 'tutor-pro'); ?></span>
                                <?php if( $template_key != 'none' ) : ?>
                                    <a href="<?php echo $template['preview_src']; ?>" target="_blank" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
                                        <?php _e('Preview', 'tutor-pro'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </label>
                </div>
                <?php
            }
        }
    }

    $is_selected_horizontal = empty($templates[$selected_template]) || $templates[$selected_template]['orientation'] == 'landscape';
?>

<div class="tutor-course-certificates tutor-text-center">
    <ul class="tutor-nav tutor-nav-pills tutor-course-certificate-tabs">
        <li class="tutor-nav-item">
            <a href="#" class="tutor-nav-link<?php echo $is_selected_horizontal ? ' is-active' : ''; ?>" data-tutor-nav-target="tab-target-certificate-landscape">
                <span class="tutor-icon-certificate-landscape tutor-mr-8" area-hidden="true"></span>
                <span><?php _e('Landscape', 'tutor-pro'); ?></span>
            </a>
        </li>

        <li class="tutor-nav-item">
            <a href="#" class="tutor-nav-link<?php echo !$is_selected_horizontal ? ' is-active' : ''; ?>" data-tutor-nav-target="tab-target-certificate-portrait">
                <span class="tutor-icon-certificate-portrait tutor-mr-8" area-hidden="true"></span>
                <span><?php _e('Portrait', 'tutor-pro'); ?></span>
            </a>
        </li>
    </ul>

    <div class="tutor-tab tutor-mt-32">
        <div class="tutor-tab-item<?php echo $is_selected_horizontal ? ' is-active' : ''; ?>" id="tab-target-certificate-landscape">
            <div class="tutor-certificate-templates tutor-certificate-templates-landscape">
                <?php load_template_cert($templates, 'landscape', $template_field_name, $selected_template); ?>
            </div>
        </div>
        
        <div class="tutor-tab-item<?php echo !$is_selected_horizontal ? ' is-active' : ''; ?>" id="tab-target-certificate-portrait">
            <div class="tutor-certificate-templates tutor-certificate-templates-portrait">
                <?php load_template_cert($templates, 'portrait', $template_field_name, $selected_template); ?>
            </div>
        </div>
    </div>
</div>
