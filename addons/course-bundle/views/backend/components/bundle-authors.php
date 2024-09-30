<?php
/**
 * Course bundle authors component
 *
 * @since 2.2.0
 *
 * @package TutorPro\CourseBundle\Views
 */

use TutorPro\CourseBundle\Models\BundleModel;

$bundle_id = isset( $data['bundle_id'] ) ? $data['bundle_id'] : 0;
$authors   = BundleModel::get_bundle_course_authors( $bundle_id );

?>
<div class="tutor-courses-instructors tutor-courses-instructors tutor-d-flex tutor-flex-column" style="gap: 24px">
	<label class="tutor-form-label">
		<?php esc_html_e( 'Instructors', 'tutor-pro' ); ?>
	</label>
	<?php if ( is_array( $authors ) && count( $authors ) ) : ?>
		<?php foreach ( $authors as $author ) : ?>
			<div class="tutor-d-flex tutor-align-center">
				<div class="tutor-d-flex tutor-mr-16">
					<div class="tutor-avatar tutor-avatar-md">
						<div class="tutor-ratio tutor-ratio-1x1">
							<img src="<?php echo esc_url( get_avatar_url( $author->user_id ) ); ?>" alt="<?php echo esc_attr( $author->display_name ); ?>"> 
						</div>
					</div>			
				</div>

				<div>	
					<a class="tutor-fs-6 tutor-fw-bold tutor-color-black" href="" target="_blank">
						<?php echo esc_html( $author->display_name ); ?>				
					</a>                       
					<div class="tutor-instructor-designation tutor-fs-7 tutor-color-muted">
						<?php echo esc_html( $author->designation ); ?>					
					</div>				
				</div>
			</div>            
		<?php endforeach; ?>
	<?php else : ?>
		<div class="tutor-fs-7 tutor-color-muted">
			<?php esc_html_e( 'No authors found', 'tutor-pro' ); ?>
		</div>
	<?php endif; ?>
</div>
