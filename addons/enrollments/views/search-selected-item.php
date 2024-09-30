<div class="tutor-user-item tutor-d-flex tutor-mb-8 tutor-bg-white tutor-p-8 tutor-br tutor-radius-8 tutor-align-center" data-user-id="<?php echo $user->ID; ?>">
    <?php echo tutor_utils()->get_tutor_avatar($user->ID, 'md'); ?>
    <span class=" tutor-ml-12"> 
        <div>
            <div class="tutor-text-btn-xlarge tutor-color-black"><?php echo $user->display_name; ?></div>
            <?php echo isset($authorTag) ? $authorTag : ''; ?>
        </div>
        <div class="tutor-d-block tutor-fs-7 tutor-color-muted">
            <?php echo $user->user_email; ?>
        </div>
    </span>
    <?php if( get_current_user_id() != $user->ID ): ?>
        <span class="tutor-ml-auto">
            <a href="javascript:void(0)" class="tutor-btn-remove-student tutor-action-icon tutor-iconic-btn">
                <i class="tutor-icon-times"></i>
            </a>
        </span>
    <?php endif; ?>
</div>