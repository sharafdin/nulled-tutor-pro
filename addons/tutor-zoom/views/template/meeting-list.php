<?php
	$page_key             = 'meeting-table';
	is_admin() ? $context = 'backend-dashboard' : '';

	$table_columns = include __DIR__ . '/contexts.php';
	$zoom_object   = new \TUTOR_ZOOM\Zoom( false );
?>
<div class="tutor-table-responsive tutor-mb-24">
	<table class="tutor-table tutor-table-zoom">
		<thead>
			<tr>
				<?php foreach ( $table_columns as $key => $column ) : ?>
					<th class="<?php echo $key == 'start_time' ? 'tutor-table-rows-sorting' : ''; ?>">
						<?php echo $column; ?>
						<?php if ( $key == 'start_time' ) : ?>
							<span class="a-to-z-sort-icon tutor-icon-ordering-z-a"></span>
						<?php endif; ?>
					</th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
		<?php
		foreach ( $meetings as $key => $meeting ) {
			$tzm_start    = get_post_meta( $meeting->ID, '_tutor_zm_start_datetime', true );
			$meeting_data = get_post_meta( $meeting->ID, $this->zoom_meeting_post_meta, true );
			$meeting_data = json_decode( $meeting_data, true );
			$input_date   = \DateTime::createFromFormat( 'Y-m-d H:i:s', $tzm_start );
			$start_date   = $input_date->format( 'j M, Y, h:i A' );
			$course_id    = get_post_meta( $meeting->ID, '_tutor_zm_for_course', true );
			$topic_id     = get_post_meta( $meeting->ID, '_tutor_zm_for_topic', true );

			$row_id              = 'tutor-zoom-meeting-' . $meeting->ID;
			$id_string_delete    = 'zoom-delete-' . $meeting->ID;
			$id_string_edit      = 'tutor-zoom-edit-modal-' . $meeting->ID;
			$popup_action_string = 'tutor-zoom-popup-action-' . $meeting->ID;
			$popover_id          = 'tutor-zoom-popupover-action-' . $meeting->ID;
			$copy_target_id      = 'tutor-zoom-popupover-copy-' . $meeting->ID;

			if ( ! is_null( $meeting_data ) ) {

				// Set default values in case it was deleted. So user can delete it at least
				!is_array($meeting_data) ? $meeting_data=array() : 0;
				empty($meeting_data['id']) ? $meeting_data['id']='' : 0;
				empty($meeting_data['password']) ? $meeting_data['password']='' : 0;
				empty($meeting_data['host_email']) ? $meeting_data['host_email']='' : 0;
				empty($meeting_data['start_url']) ? $meeting_data['start_url']='#' : 0;

				?>
				<tr id="<?php echo $row_id; ?>" class="tutor-zoom-meeting-item">
					<?php
					foreach ( $table_columns as $column_key => $column_name ) {
						switch ( $column_key ) {
							case 'start_time':
								?>
									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $tzm_start ) ) ); ?>,
											<div class="tutor-fw-normal tutor-color-muted"><?php echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $tzm_start ) ) ); ?></div>
										</div>
									</td>
								<?php
								break;

							case 'meeting_name':
								?>
									<td>
										<?php esc_html_e( $meeting->post_title ); ?>
										<div class="tutor-meta tutor-mt-8">
											<div>
												<?php _e( 'Course', 'tutor-pro' ); ?>: 
												<span class="tutor-meta-value"><?php echo get_the_title( $course_id ); ?></span>
											</div>
										</div>
									</td>
								<?php
								break;

							case 'meeting_token':
								?>
									<td>
										<?php echo $meeting_data['id']; ?>
									</td>
								<?php
								break;

							case 'password':
								?>
									<td>
										<?php echo $meeting_data['password']; ?>
									</td>
								<?php
								break;

							case 'hostmail':
								?>
									<td>
										<?php echo $meeting_data['host_email']; ?>
									</td>
								<?php
								break;

							case 'action_frontend':
								$button_text  = __('Start Meeting', 'tutor-pro');
								$button_class = 'tutor-btn tutor-btn-primary tutor-btn-md tutor-mr-12';
								if ( $meeting->is_expired ) {
									$button_text  = __('Expired', 'tutor-pro');
									$button_class = 'tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mr-12';
								} elseif ( $meeting->is_running ) {
									$button_text  = __('Join Now', 'tutor-pro');
									$button_class = 'tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mr-12';
								}
								?>
								<td>
									<div class="tutor-d-flex tutor- tutor-align-center tutor-justify-end">
										<div class="tutor-d-inline-flex tutor-align-center td-action-btns">
											<a href="<?php echo ! $meeting->is_expired ? $meeting_data['start_url'] : 'javascript:void(0)'; ?>" class="<?php esc_attr_e( $button_class ); ?>" target="<?php echo ! $meeting->is_expired ? '_blank' : ''; ?>"<?php echo $meeting->is_expired ? ' disabled="disabled"' : ''; ?>>
												<i class="tutor-icon-brand-zoom tutor-mr-8"></i> <?php echo $button_text; ?>
											</a>

											<div class="tutor-dropdown-parent">
												<button class="tutor-btn tutor-btn-outline-primary tutor-btn-md" action-tutor-dropdown="toggle">
													<span><?php _e( 'Info', 'tutor' ); ?></span>
													<span class="tutor-icon-angle-down tutor-fs-7 tutor-ml-4" area-hidden="true"></span>
												</button>

												<ul class="tutor-dropdown" style="width: 280px;">
													<div class="tutor-d-flex tutor-align-center tutor-px-24 tutor-py-12">
														<div>
															<div class="tutor-fs-7 tutor-color-muted"><?php _e( 'Meeting ID', 'tutor-pro' ); ?></div>
															<div class="tutor-fs-6 tutor-fw-medium tutor-color-black" id="<?php echo $copy_target_id; ?>">
																<?php esc_html_e( $meeting_data['id'] ); ?>
															</div>
														</div>
														<div class="tutor-ml-auto">
															<button class="tutor-iconic-btn" data-tutor-copy-target="<?php echo $copy_target_id; ?>">
																<span class="tutor-icon-copy-text" area-hidden="true"></span>
															</button>
														</div>
													</div>

													<div class="tutor-d-flex tutor-align-center tutor-px-24 tutor-py-12">
														<div>
															<div class="tutor-fs-7 tutor-color-muted"><?php _e( 'Password', 'tutor-pro' ); ?></div>
															<div class="tutor-fs-6 tutor-fw-medium tutor-color-black" id="<?php echo $copy_target_id; ?>-2">
																<?php esc_html_e( $meeting_data['password'] ); ?>
															</div>
														</div>
														<div class="tutor-ml-auto">
															<button class="tutor-iconic-btn" data-tutor-copy-target="<?php echo $copy_target_id; ?>-2">
																<span class="tutor-icon-copy-text" area-hidden="true"></span>
															</button>
														</div>
													</div>

													<div class="tutor-hr" area-hidden="true"></div>

													<div class="tutor-px-24 tutor-py-12 tutor-mt-8">
														<div class="tutor-fs-7 tutor-color-muted"><?php _e( 'Host Email', 'tutor-pro' ); ?></div>
														<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-nowrap-ellipsis" title="<?php esc_html_e( $meeting_data['host_email'] ); ?>">
															<?php esc_html_e( $meeting_data['host_email'] ); ?>
														</div>
													</div>
												</ul>
											</div>

											<div class="tutor-dropdown-parent">
												<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
													<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
												</button>
												<ul class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
													<li>
														<a href="#" class="tutor-dropdown-item" data-tutor-modal-target="tutor-zoom-meeting-modal-<?php echo $meeting->ID; ?>">
															<i class="tutor-icon-edit tutor-mr-8" area-hidden="true"></i>
															<spa><?php _e("Edit", "tutor-pro"); ?></span>
														</a>
													</li>
													<li>
														<a href="#" class="tutor-dropdown-item" data-tutor-modal-target="<?php echo $id_string_delete; ?>">
															<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
															<span><?php _e("Delete", "tutor-pro"); ?></span>
														</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
	
									<?php
									// Meeting update modal
									$zoom_object->tutor_zoom_meeting_modal_content( $meeting->ID, $topic_id, $course_id, '0' );

									// Delete confirmation modak
									tutor_load_template( 'modal.confirm', array(
										'id' => $id_string_delete,
										'image' => 'icon-trash.svg',
										'title' => __('Do You Want to Delete This Meeting?', 'tutor-pro'),
										'content' => __('Are you sure you want to delete this meeting permanently? Please confirm your choice.', 'tutor-pro'),
										'yes' => array(
											'text' => __('Yes, Delete This', 'tutor-pro'),
											'class' => 'tutor-list-ajax-action',
											'attr' => array(
												'data-request_data=\'{"meeting_id":"'. $meeting->ID .'", "action":"tutor_zoom_delete_meeting"}\'', 
												'data-delete_element_id="' . $row_id . '"'
											)
										),
									));
									?>
								</td>
								<?php
								break;

							case 'action_backend':
								$button_text  = __('Start Meeting', 'tutor-pro');
								$button_class = 'tutor-btn tutor-btn-primary tutor-btn-md tutor-mr-12';
								if ( $meeting->is_expired ) {
									$button_text  = __('Expired', 'tutor-pro');
									$button_class = 'tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mr-12';
								} elseif ( $meeting->is_running ) {
									$button_text  = __('Join Now', 'tutor-pro');
									$button_class = 'tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mr-12';
								}
								?>
								<td>
									<div class="tutor-d-flex tutor-align-center tutor-justify-end">
										<div class="tutor-flex-wrap tutor-d-inline-flex tutor-align-center td-action-btns">
											<a href="<?php echo ! $meeting->is_expired ? $meeting_data['start_url'] : 'javascript:void(0)'; ?>" class="<?php esc_attr_e( $button_class ); ?>" <?php echo ! $meeting->is_expired ? 'target="_blank"' : 'disabled="disabled"'; ?>>
												<i class="tutor-icon-brand-zoom tutor-mr-8"></i> <?php echo $button_text; ?>
											</a>
	
											<a href="#" class="tutor-btn tutor-btn-outline-primary tutor-btn-md" data-tutor-modal-target="tutor-zoom-meeting-modal-<?php echo $meeting->ID; ?>">
												<?php _e("Edit", "tutor-pro"); ?>
											</a>
	
											<a href="#" class="tutor-iconic-btn" data-tutor-modal-target="<?php echo $id_string_delete; ?>">
												<i class="tutor-icon-trash-can-line" area-hidden="true"></i>
											</a>
										</div>
									</div>
									<?php
										// Meeting update modal
										$zoom_object->tutor_zoom_meeting_modal_content( $meeting->ID, $topic_id, $course_id, '0' );

										// Delete confirmation modal
										tutor_load_template( 'modal.confirm', array(
											'id' => $id_string_delete,
											'image' => 'icon-trash.svg',
											'title' => __('Do You Want to Delete This Meeting?', 'tutor-pro'),
											'content' => __('Are you sure you want to delete this meeting permanently? Please confirm your choice.', 'tutor-pro'),
											'yes' => array(
												'text' => __('Yes, Delete This', 'tutor-pro'),
												'class' => 'tutor-list-ajax-action',
												'attr' => array(
													'data-request_data=\'{"meeting_id":"'. $meeting->ID .'", "action":"tutor_zoom_delete_meeting"}\'', 
													'data-delete_element_id="' . $row_id . '"'
												)
											),
										));
									?>
								</td>
								<?php
								break;
						}
					}
					?>
				</tr>
				<?php
			}
		}
		?>
		</tbody>
	</table>
</div>
