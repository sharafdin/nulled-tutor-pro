<?php
/**
 * Analytics template 
 * 
 * @since 1.9.8
 */
global $wp_query;
if ( !current_user_can( tutor()->instructor_role ) ) {
    return;
}
$query_vars         = $wp_query->query_vars;
$report_instance    = tutor_report_instance();

$current_page   = isset( $query_vars['tutor_dashboard_sub_page'] ) ? $query_vars['tutor_dashboard_sub_page'] : 'overview';
$sub_pages      = $report_instance->analytics->sub_pages();
$arr = explode('/', $current_page);
if ( count( $arr ) ) {
    if (array_key_exists( $arr[0], $sub_pages) ) {
        $current_page = $arr[0];
    }
} 

?>
<div class="tutor-analytics-wrapper">
    <?php 
        /**
         * Course details page design need to display as stand alone 
         * 
         * That is why it is not included as sub page
         * 
         * @since 1.9.9
         */
        if ( 'course-details' === $current_page ) {
            include_once TUTOR_REPORT()->path.'templates/course_details.php';
            return;
        }
        if ( 'student-details' === $current_page ) {
            include_once TUTOR_REPORT()->path.'templates/student_details.php';
            return;
        }
    ?>
    <div class="tutor-report-menu tutor-mb-32">
        <div class="tutor-analytics-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
            <?php _e( 'Analytics', 'tutor-pro' ); ?>
        </div>

        <ul class="tutor-nav" tutor-priority-nav>
            <?php foreach( $sub_pages as $key => $page ): ?>
            <li class="tutor-nav-item">
                <a class="tutor-nav-link<?php echo $current_page === $key ? ' is-active' : ''; ?>" href="<?php echo $page['url'];?>">
                    <?php echo $page['title'];?>
                </a>
            </li>
            <?php endforeach; ?>
            <li class="tutor-nav-item tutor-nav-more tutor-d-none">
				<a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4"><?php _e("More", "tutor-pro"); ?></span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
				<ul class="tutor-nav-more-list tutor-dropdown"></ul>
			</li>
        </ul>
    </div>

    <div class="tutor-analytics-sub-pages">
        <?php echo $report_instance->analytics->load_sub_page($current_page); ?>
    </div>
</div>