<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="tutor-admin-wrap">
    <div class="tutor-admin-body">
        <div class="tutor-admin-container tutor-admin-container-sm">
            <div class="tutor-zoom-settings">
                <?php if ( is_admin() ): ?>
                    <div class="tutor-zoom-page-title tutor-mb-16">
                        <div class="tutor-fs-4 tutor-fw-medium tutor-color-black"><?php _e('FAQ', 'tutor-pro')?></div>
                    </div>
                <?php endif;?>

                <div class="tutor-zoom-accordion-item tutor-card tutor-p-16 tutor-mb-16">
                    <div class="tutor-zoom-accordion-panel">
                        <span class="tutor-zoom-accordion-panel-handler tutor-d-flex tutor-align-center tutor-cursor-pointer">
                            <span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
                            <span class="tutor-accordion-panel-handler-label tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24"><?php _e('How Do I Connect Zoom With my LMS Website?', 'tutor-pro'); ?></span>
                        </span>
                    </div>

                    <div class="tutor-zoom-accordion-body tutor-pt-16" style="display: none;">
                        <div class="tutor-fs-7 tutor-color-secondary">
                            <?php _e('To connect Zoom with your eLearning website powered by Tutor LMS, you need to first create an app on Zoom by following this link. Then create a JWT, copy the API Credentials, and paste it to the Tutor LMS backend by navigating to <strong>WP Admin > Tutor LMS Pro > Zoom > Set API</strong>.', 'tutor-pro'); ?>
                        </div>
                    </div>
                </div>

                <div class="tutor-zoom-accordion-item tutor-card tutor-p-16 tutor-mb-16">
                    <div class="tutor-zoom-accordion-panel">
                        <span class="tutor-zoom-accordion-panel-handler tutor-d-flex tutor-align-center tutor-cursor-pointer">
                            <span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
                            <span class="tutor-accordion-panel-handler-label tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24"><?php _e('How Do I Create a Live Lesson on Tutor LMS?', 'tutor-pro'); ?></span>
                        </span>
                    </div>

                    <div class="tutor-zoom-accordion-body tutor-pt-16" style="display: none;">
                        <div class="tutor-fs-7 tutor-color-secondary">
                            <?php _e('You can create a live lesson by going into any Tutor LMS course editor. There, you will see a section called Zoom Meeting from where you can schedule a General Zoom meeting. You can also add lesson-specific meetings by navigating into any topic and then selecting the <strong>Zoom Live Lesson</strong> option.', 'tutor-pro'); ?>
                        </div>
                    </div>
                </div>

                <div class="tutor-zoom-accordion-item tutor-card tutor-p-16 tutor-mb-16">
                    <div class="tutor-zoom-accordion-panel">
                        <span class="tutor-zoom-accordion-panel-handler tutor-d-flex tutor-align-center tutor-cursor-pointer">
                            <span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
                            <span class="tutor-accordion-panel-handler-label tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24"><?php _e('How Do I Notify Students about Live Lessons?', 'tutor-pro'); ?></span>
                        </span>
                    </div>

                    <div class="tutor-zoom-accordion-body tutor-pt-16" style="display: none;">
                        <div class="tutor-fs-7 tutor-color-secondary">
                            <?php _e('You can notify students about live lessons using Email Notifications and Announcements. Docs for Email Notifications can be found ', 'tutor-pro'); ?><a href="https://docs.themeum.com/tutor-lms/addons/email-notifications/" target="_blank"><?php _e(' here', 'tutor-pro'); ?></a>.
                        </div>
                    </div>
                </div>
                
                <div class="tutor-zoom-accordion-item tutor-card tutor-p-16 tutor-mb-16">
                    <div class="tutor-zoom-accordion-panel">
                        <span class="tutor-zoom-accordion-panel-handler tutor-d-flex tutor-align-center tutor-cursor-pointer">
                            <span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
                            <span class="tutor-accordion-panel-handler-label tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24"><?php _e('Is Zoom Free to Use?', 'tutor-pro'); ?></span>
                        </span>
                    </div>

                    <div class="tutor-zoom-accordion-body tutor-pt-16" style="display: none;">
                        <div class="tutor-fs-7 tutor-color-secondary">
                            <?php _e('Zoom follows a freemium monetization plan. Therefore, for smaller-scaled and limited operations, Zoom is free. However, for medium to larger websites, it\'s best to upgrade to a premium plan to get the most out of this platform.', 'tutor-pro'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="tutor-zoom-accordion-item tutor-card tutor-p-16 tutor-mb-16">
                    <div class="tutor-zoom-accordion-panel">
                        <span class="tutor-zoom-accordion-panel-handler tutor-d-flex tutor-align-center tutor-cursor-pointer">
                            <span class="tutor-iconic-btn tutor-iconic-btn-secondary"><i class="tutor-icon-angle-down"></i></span>
                            <span class="tutor-accordion-panel-handler-label tutor-fs-6 tutor-fw-medium tutor-color-black tutor-ml-24"><?php _e('What Equipment Do I Need To Hold a Live Class?', 'tutor-pro'); ?></span>
                        </span>
                    </div>

                    <div class="tutor-zoom-accordion-body tutor-pt-16" style="display: none;">
                        <div class="tutor-fs-7 tutor-color-secondary">
                            <?php _e('You will need a Microphone, a PC running Windows or Mac OS, and preferably a Webcam to effectively hold a live class.', 'tutor-pro'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>