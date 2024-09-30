
<div class="wrap">
	<h1 class="wp-heading-inline">Install / Active Tutor LMS</h1>
	<hr class="wp-header-end">

	<?php
	$tutor_file = WP_PLUGIN_DIR.'/tutor/tutor.php';
	if (file_exists($tutor_file) && ! is_plugin_active('tutor/tutor.php')){
		?>
		<div class="tutor-install-notice-wrap notice-warning notice" style="background: #ffffff; padding: 30px 20px; font-size: 20px;">
			You must have <a href="https://wordpress.org/plugins/tutor/" target="_blank">Tutor LMS </a> Free version installed and activated on this website in order to use Tutor LMS Pro. You <a href="<?php echo add_query_arg(array('action' => 'activate_tutor_free'), admin_url()); ?>">can activate Tutor LMS</a> .
		</div>

		<?php
	}elseif( ! file_exists($tutor_file) ){
		?>
		<div class="tutor-install-notice-wrap  notice-warning notice" style="background: #ffffff; padding: 30px 20px; font-size: 20px;">
			You must have <a href="https://wordpress.org/plugins/tutor/" target="_blank">Tutor LMS </a> Free version installed and activated on this website in order to use Tutor LMS Pro. You can <a class="install-tutor-btn" data-slug="tutor" href="<?php echo add_query_arg(array('action' => 'install_tutor_free'), admin_url()); ?>">Install Tutor LMS Now</a>
		</div>
		<div id="tutor_install_msg"></div>
		<?php
	}
	?>
</div>