<?php
/**
 * Enrollment Modal
 *
 * @package TutorPro\Addons\Enrollments
 * @subpackage Views
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 2.0.0
 */

use Tutor\Models\CourseModel;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\Init as BundleAddon;

$bundle_enabled = BundleAddon::is_addon_enabled();
?>

<form action="" id="tutor-manual-enrollment-form" method="POST">
	<div id="enrollment-modal" class="tutor-modal tutor-modal-scrollable<?php echo esc_attr( is_admin() ? ' tutor-admin-design-init' : '' ); ?>">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content">

				<div class="tutor-modal-header">
					<div class="tutor-modal-title">
						<?php esc_html_e( 'Manual Enrollment', 'tutor-pro' ); ?>
					</div>

					<button class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close>
						<span class="tutor-icon-times" area-hidden="true"></span>
					</button>
				</div>

				<div class="tutor-modal-body">
					<div class="tutor-mb-32">
						<label class="tutor-form-label">
							<?php
							$_title = __( 'Course', 'tutor-pro' );
							if ( $bundle_enabled ) {
								$_title = __( 'Course/Bundle', 'tutor-pro' );
							}
							echo esc_html( $_title );
							?>
						</label>
						<?php
						global $wpdb;

						if ( $bundle_enabled ) {
							$data = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT ID,
										post_author,
										post_title,
										post_name,
										post_status,
										menu_order
								FROM 	{$wpdb->posts}
								WHERE 	post_status IN ('publish','private')
								AND 	post_type IN ( %s, %s )
								",
									CourseModel::POST_TYPE,
									CourseBundle::POST_TYPE
								)
							);
						} else {
							$data = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT ID,
										post_author,
										post_title,
										post_name,
										post_status,
										menu_order
								FROM 	{$wpdb->posts}
								WHERE 	post_status IN ('publish','private')
								AND 	post_type IN ( %s )
								",
									CourseModel::POST_TYPE
								)
							);
						}

						?>
						<select name="course_id" class="tutor-form-select tutor-mw-100" required="required">
								<option value="">
								<?php
								$select_title = __( 'Select a course', 'tutor-pro' );
								if ( $bundle_enabled ) {
									$select_title = __( 'Select a course/bundle', 'tutor-pro' );
								}
								echo esc_html( $select_title );
								?>
								</option>
								<?php
								foreach ( $data as $row ) :
									echo wp_kses(
										"<option value='{$row->ID}'>{$row->post_title}</option>",
										array(
											'option' => array( 'value' => true ),
										)
									);
								endforeach;
								?>
						</select>  
					</div>

					<div>
						<label class="tutor-form-label"><?php esc_html_e( 'Student', 'tutor' ); ?></label>

						<div class="tutor-form-wrap tutor-options-search">
							<span class="tutor-search-loader tutor-form-icon tutor-icon-search" area-hidden="true"></span>
							<input disabled="disabled" type="text" class="tutor-form-control tutor-search-input" placeholder="Search student..">
						</div>

						<!-- search result wrapper. do not remove -->
						<div class="tutor-search-result tutor-mt-12"></div>
						<!-- selected result wrapper. do not remove -->
						<div class="tutor-search-selected tutor-mt-12"></div>

					</div>

				</div>
				<!-- # modal body -->

				<div class="tutor-modal-footer">
					<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor-pro' ); ?>
					</button>

					<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-submit">
						<?php esc_html_e( 'Enroll Now', 'tutor-pro' ); ?>
					</button>
				</div>
				<!-- # modal footer -->
			</div>
		</div>
	</div>
</form>
