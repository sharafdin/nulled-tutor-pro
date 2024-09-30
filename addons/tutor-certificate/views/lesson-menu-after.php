<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

$course_id = get_the_ID();
$disable_certificate = get_post_meta($course_id, '_tutor_disable_certificate', true); // This setting is no more. But used here in favour of backward compatibillity
$certificate_template = get_post_meta($course_id, 'tutor_course_certificate_template', true);

if($certificate_template=='none' || (!$certificate_template && $disable_certificate == 'yes')) {
	/* 
		Conditions when not to show certificate section in course
		-------
		1. If certificate template explicitly set as off (After certificate builder release)
		2. No certificate template is set for the course and old setting is off
	*/
	return;
}

?>

<a href="<?php echo add_query_arg( array('regenerate'=>1), $certificate_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-block tutor-mb-20 tutor-btn-view-certificate">
	<?php _e('View Certificate', 'tutor-pro'); ?>
</a>