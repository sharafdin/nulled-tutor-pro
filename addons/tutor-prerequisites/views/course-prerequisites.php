<div id="tutor_prereq" class="<?php echo ! is_single_course() || ! is_single() ? 'tutor-quiz-wrapper tutor-d-flex tutor-justify-center tutor-mt-32 tutor-pb-80' : ''; ?>">
    <div class="course-prerequisites-lists-wrap">
        <h3 class="tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-24"><?php _e('Course Prerequisite(s)', 'tutor-pro'); ?></h3>
        <ul class="prerequisites-course-lists">
            <li class="prerequisites-warning">
                <span>
                    <i class="tutor-icon-warning tutor-color-warning"></i>
                </span>
                <?php _e('Please note that this course has the following prerequisites which must be completed before it can be accessed', 'tutor-pro'); ?>
            </li>
            <?php
            $savedPrerequisitesIDS = maybe_unserialize(get_post_meta(get_the_ID(), '_tutor_course_prerequisites_ids', true));
            if (is_array($savedPrerequisitesIDS) && count($savedPrerequisitesIDS)){
                foreach ($savedPrerequisitesIDS as $courseID){
                    ?>
                    <li>
                        <a href="<?php echo get_the_permalink($courseID); ?>" class="prerequisites-course-item">
                            <span class="prerequisites-course-feature-image">
                                <?php echo get_the_post_thumbnail($courseID); ?>
                            </span>

                            <span class="prerequisites-course-title">
                                <?php echo get_the_title($courseID); ?>
                            </span>

                            <?php if (tutor_utils()->is_completed_course($courseID)){
                                ?>
                                <div class="is-complete-prerequisites-course"><i class="tutor-icon-mark"></i></div>
                                <?php
                            } ?>
                        </a>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
    </div>
</div>