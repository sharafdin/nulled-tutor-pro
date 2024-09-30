<?php
/**
 * My Bundles Page
 *
 * @package TutorPro\CourseBundle
 * @subpackage Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

use TUTOR\Input;
use Tutor\Models\CourseModel;
use TutorPro\CourseBundle\Frontend\BundleBuilder;
use TutorPro\CourseBundle\Models\BundleModel;

if ( ! isset( $active_tab ) ) {
	$active_tab = 'my-bundles';
}

//phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
$status_map = array(
	'my-bundles'                 => CourseModel::STATUS_PUBLISH,
	'my-bundles/draft-bundles'   => CourseModel::STATUS_DRAFT,
	'my-bundles/pending-bundles' => CourseModel::STATUS_PENDING,
);

// Set currently required course status fo rcurrent tab.
$current_user_id = get_current_user_id();
$status          = isset( $status_map[ $active_tab ] ) ? $status_map[ $active_tab ] : CourseModel::STATUS_PUBLISH;

// Get counts for course tabs.
$count_map = array(
	'publish' => BundleModel::get_bundles_by_instructor( $current_user_id, CourseModel::STATUS_PUBLISH, 0, 0, true ),
	'pending' => BundleModel::get_bundles_by_instructor( $current_user_id, CourseModel::STATUS_PENDING, 0, 0, true ),
	'draft'   => BundleModel::get_bundles_by_instructor( $current_user_id, CourseModel::STATUS_DRAFT, 0, 0, true ),
);

$course_archive_arg = isset( $GLOBALS['tutor_course_archive_arg'] ) ? $GLOBALS['tutor_course_archive_arg']['column_per_row'] : null;
$courseCols         = null === $course_archive_arg ? tutor_utils()->get_option( 'courses_col_per_row', 4 ) : $course_archive_arg; //phpcs:ignore
$per_page           = tutor_utils()->get_option( 'courses_per_page', 10 );
$paged              = Input::get( 'current_page', 1, Input::TYPE_INT );
$offset             = $per_page * ( $paged - 1 );

$results                = BundleModel::get_bundles_by_instructor( $current_user_id, $status, $offset, $per_page );
$show_delete_bundle_btn = true;
if ( ! current_user_can( 'administrator' ) && ! tutor_utils()->get_option( 'instructor_can_delete_course' ) ) {
	$show_delete_bundle_btn = false;
}
?>

<div class="tutor-dashboard-my-courses">
	<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
		<?php esc_html_e( 'My Bundles', 'tutor-pro' ); ?>
	</div>

	<div class="tutor-dashboard-content-inner">
		<div class="tutor-mb-32">
			<ul class="tutor-nav">
				<li class="tutor-nav-item">
					<a class="tutor-nav-link<?php echo esc_attr( 'my-bundles' === $active_tab ? ' is-active' : '' ); ?>" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'my-bundles' ) ); ?>">
						<?php esc_html_e( 'Publish', 'tutor-pro' ); ?> <?php echo esc_html( '(' . $count_map['publish'] . ')' ); ?>
					</a>
				</li>
				<li class="tutor-nav-item">
					<a class="tutor-nav-link<?php echo esc_attr( 'my-bundles/pending-bundles' === $active_tab ? ' is-active' : '' ); ?>" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'my-bundles/pending-bundles' ) ); ?>">
						<?php esc_html_e( 'Pending', 'tutor-pro' ); ?> <?php echo esc_html( '(' . $count_map['pending'] . ')' ); ?>
					</a>
				</li>
				<li class="tutor-nav-item">
					<a class="tutor-nav-link<?php echo esc_attr( 'my-bundles/draft-bundles' === $active_tab ? ' is-active' : '' ); ?>" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'my-bundles/draft-bundles' ) ); ?>">
						<?php esc_html_e( 'Draft', 'tutor-pro' ); ?> <?php echo esc_html( '(' . $count_map['draft'] . ')' ); ?>
					</a>
				</li>
			</ul>
		</div>

		<!-- Bundle list -->
		<?php
		if ( ! is_array( $results ) || ( ! count( $results ) && 1 == $paged ) ) {
			tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() );
		} else {
			?>
			<div class="tutor-grid tutor-grid-3">
				<?php
				global $post;
				$tutor_nonce_value = wp_create_nonce( tutor()->nonce_action );
				foreach ( $results as $post ) :
					setup_postdata( $post );

					$tutor_course_img = get_tutor_course_thumbnail_src();
					$id_string_delete = 'tutor_my_courses_delete_' . $post->ID;
					$row_id           = 'tutor-dashboard-my-course-' . $post->ID;
					$course_duration  = get_tutor_course_duration_context( $post->ID, true );
					$total_enrolled   = BundleModel::get_total_bundle_sold( $post->ID );
					?>

					<div id="<?php echo esc_attr( $row_id ); ?>" class="tutor-card tutor-course-card tutor-mycourse-<?php the_ID(); ?>">
						<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="tutor-d-block">
							<div class="tutor-ratio tutor-ratio-16x9">
								<img class="tutor-card-image-top" src="<?php echo esc_url( $tutor_course_img ); ?>" alt="<?php the_title(); ?>" loading="lazy">
							</div>
						</a>

						<div class="tutor-card-body">
							<div class="tutor-meta tutor-mb-8">
								<span>
									<?php echo esc_html( get_the_date() ); ?> <?php echo esc_html( get_the_time() ); ?>
								</span>
							</div>

							<div class="tutor-course-name tutor-fs-6 tutor-fw-bold tutor-mb-16">
								<a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a>
							</div>

							<?php if ( ! empty( $course_duration ) || ! empty( $total_enrolled ) ) : ?>
							<div class="tutor-meta tutor-mt-16">
								<?php if ( ! empty( $course_duration ) ) : ?>
									<div>
										<span class="tutor-icon-clock-line tutor-meta-icon" area-hidden="true"></span>
										<span class="tutor-meta-value">
										<?php
											echo wp_kses(
												stripslashes( $course_duration ),
												array(
													'span' => array( 'class' => true ),
												)
											);
										?>
										</span>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $total_enrolled ) ) : ?>
									<div>
										<span class="tutor-icon-user-line tutor-meta-icon" area-hidden="true"></span>
										<span class="tutor-meta-value">
										<?php
										echo wp_kses(
											stripslashes( $total_enrolled ),
											array(
												'span' => array( 'class' => true ),
											)
										);
										?>
										</span>
									</div>
								<?php endif; ?>
							</div>
							<?php endif; ?>
						</div>

						<div class="tutor-card-footer">
							<div class="tutor-d-flex tutor-align-center tutor-justify-between">
								<div class="tutor-d-flex tutor-align-center">
									<span class="tutor-fs-7 tutor-fw-medium tutor-color-muted tutor-mr-4">
										<?php esc_html_e( 'Price:', 'tutor-pro' ); ?>
									</span>
									<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
										<?php
										$price = tutor_utils()->get_course_price();
										if ( null === $price ) {
											esc_html_e( 'Free', 'tutor-pro' );
										} else {
											echo wp_kses_post( tutor_utils()->get_course_price() );
										}
										?>
									</span>
								</div>
								<div class="tutor-iconic-btn-group tutor-mr-n8">
									<a href="<?php echo esc_url( BundleBuilder::get_edit_link( $post->ID ) ); ?>" class="tutor-iconic-btn tutor-my-course-edit">
										<i class="tutor-icon-edit" area-hidden="true"></i>
									</a>
									<div class="tutor-dropdown-parent">
										<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
											<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
										</button>
										<div id="table-dashboard-course-list-<?php echo esc_attr( $post->ID ); ?>" class="tutor-dropdown tutor-dropdown-dark tutor-text-left">

											<!-- Submit Action -->
											<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_DRAFT ) ) ) : ?>
												<?php
												$params = http_build_query(
													array(
														'tutor_action' => 'update_bundle_status',
														'status' => CourseModel::STATUS_PENDING,
														'bundle_id' => $post->ID,
														tutor()->nonce => $tutor_nonce_value,
													)
												);
												?>
											<a class="tutor-dropdown-item" href="?<?php echo esc_attr( $params ); ?>">
												<i class="tutor-icon-share tutor-mr-8" area-hidden="true"></i>
												<span>
													<?php
													$can_publish_course = current_user_can( 'administrator' ) || (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );
													if ( $can_publish_course ) {
														esc_html_e( 'Publish', 'tutor-pro' );
													} else {
														esc_html_e( 'Submit', 'tutor-pro' );
													}
													?>
												</span>
											</a>
											<?php endif; ?>
											<!-- # Submit Action -->

											<!-- Move to Draf Action -->
											<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_PUBLISH ) ) ) : ?>
												<?php
												$params = http_build_query(
													array(
														'tutor_action' => 'update_bundle_status',
														'status' => CourseModel::STATUS_DRAFT,
														'bundle_id' => $post->ID,
														tutor()->nonce => $tutor_nonce_value,
													)
												);
												?>
											<a class="tutor-dropdown-item" href="?<?php echo esc_attr( $params ); ?>">
												<i class="tutor-icon-archive tutor-mr-8" area-hidden="true"></i>
												<span><?php esc_html_e( 'Move to Draft', 'tutor-pro' ); ?></span>
											</a>
											<?php endif; ?>
											<!-- # Move to Draft Action -->

											<!-- Cancel Submission -->
											<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_PENDING ) ) ) : ?>
												<?php
												$params = http_build_query(
													array(
														'tutor_action' => 'update_bundle_status',
														'status' => CourseModel::STATUS_DRAFT,
														'bundle_id' => $post->ID,
														tutor()->nonce => $tutor_nonce_value,
													)
												);
												?>
											<a href="?<?php echo esc_attr( $params ); ?>" class="tutor-dropdown-item">
												<i class="tutor-icon-times tutor-mr-8" area-hidden="true"></i>
												<span><?php esc_html_e( 'Cancel Submission', 'tutor-pro' ); ?></span>
											</a>
											<?php endif; ?>
											<!-- # Cancel Submission -->

											<!-- Delete Action -->
											<?php if ( in_array( $post->post_status, array( CourseModel::STATUS_PUBLISH, CourseModel::STATUS_DRAFT ) ) ) : ?>
												<?php if ( $show_delete_bundle_btn ) : ?>
													<a href="#" data-tutor-modal-target="<?php echo esc_attr( $id_string_delete ); ?>" class="tutor-dropdown-item tutor-admin-course-delete">
														<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
														<span><?php esc_html_e( 'Delete', 'tutor-pro' ); ?></span>
													</a>
												<?php endif; ?>
											<?php endif; ?>
											<!-- # Delete Action -->

										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Delete prompt modal -->
						<div id="<?php echo esc_attr( $id_string_delete ); ?>" class="tutor-modal">
							<div class="tutor-modal-overlay"></div>
							<div class="tutor-modal-window">
								<div class="tutor-modal-content tutor-modal-content-white">
									<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
										<span class="tutor-icon-times" area-hidden="true"></span>
									</button>

									<div class="tutor-modal-body tutor-text-center">
										<div class="tutor-mt-48">
											<img class="tutor-d-inline-block" src="<?php echo esc_attr( tutor()->url ); ?>assets/images/icon-trash.svg" />
										</div>

										<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Delete This Bundle?', 'tutor-pro' ); ?></div>
										<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Are you sure you want to delete this bundle permanently from the site? Please confirm your choice.', 'tutor-pro' ); ?></div>

										<div class="tutor-d-flex tutor-justify-center tutor-my-48">
											<button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
												<?php esc_html_e( 'Cancel', 'tutor-pro' ); ?>
											</button>
											<button class="tutor-btn tutor-btn-primary tutor-list-ajax-action tutor-ml-20" data-request_data='{"bundle_id":<?php echo esc_attr( $post->ID ); ?>,"action":"tutor_delete_dashboard_bundle","redirect_to":"<?php echo esc_url( tutor_utils()->get_current_url() ); ?>"}' data-delete_element_id="<?php echo esc_attr( $row_id ); ?>">
												<?php esc_html_e( 'Yes, Delete This', 'tutor-pro' ); ?>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
				endforeach;
				wp_reset_postdata();
				?>
			</div>
			<div class="tutor-mt-20">
				<?php
				if ( $count_map[ $status ] > $per_page ) {
					$pagination_data = array(
						'total_items' => $count_map[ $status ],
						'per_page'    => $per_page,
						'paged'       => $paged,
					);

					tutor_load_template_from_custom_path(
						tutor()->path . 'templates/dashboard/elements/pagination.php',
						$pagination_data
					);
				}
				?>

			</div>
			<?php
		}
		?>
	</div>
</div>
<?php //phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited ?>
