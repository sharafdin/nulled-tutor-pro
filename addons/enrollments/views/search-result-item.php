<div class="tutor-result-row tutor-d-flex tutor-mb-16 tutor-bg-white tutor-p-8 tutor-br tutor-radius-8 tutor-align-center" data-user_id="<?php echo $row->ID ?>">
    <?php echo tutor_utils()->get_tutor_avatar($row->ID, 'md') ?>
    
    <div class="tutor-nowrap-ellipsis tutor-ml-12">
        <div class="tutor-fs-6 tutor-color-black tutor-fw-medium"><?php echo $row->display_name; ?></div>
        <div class="tutor-fs-7 tutor-color-muted"><?php echo $row->user_email; ?></div>
    </div>

    <div class="tutor-ml-auto">
        <span class="tutor-iconic-btn tutor-add-student" data-user_id="<?php echo $row->ID ?>"><i class="tutor-icon-plus-o"></i></span>
    </div>
</div>