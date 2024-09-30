<?php
/**
 * PM PRO content settings view
 *
 * @package TutorPro\Addons
 * @subpackage PmPro\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.3.5
 */

?>
<div id="tutor-pmpro-setting-wrapper" style="background: white; padding: 10px 20px;">
	<h3><?php esc_html_e( 'Tutor LMS Content Settings', 'tutor-pro' ); ?></h3>

	<?php
	/**
	 * Generate category for PM Pro
	 *
	 * @param array $cats cats.
	 * @param array $level_categories level categories.
	 *
	 * @return void
	 */
	function generate_categories_for_pmpro( $cats, $level_categories = array() ) {

		if ( ! count( $cats ) ) {
			return;
		}

		echo '<ul>';
		foreach ( $cats as $cat ) {
			$name = 'membershipcategory_' . $cat->term_id;
			if ( ! empty( $level_categories ) ) {
				$checked = checked( in_array( $cat->term_id, $level_categories ), true, false );
			} else {
				$checked = '';
			}

			//phpcs:ignore
			echo "<li class=membershipcategory>
						<label><input type=checkbox name='{$name}' value='yes' {$checked}/> {$cat->name}</label>";
				generate_categories_for_pmpro( $cat->children, $level_categories );
			echo '</li>';
		}
			echo '</ul>';
	}
	?>


	<input type="hidden" value="pmpro_settings" name="tutor_action"/>

	<table class="form-table">
		<tbody>
			<tr class="membership_model">
				<th width="200"><label for="tutor_pmpro_membership_model_select"><?php esc_html_e( 'Membership Model', 'tutor-pro' ); ?>:</label></th>
				<td>
					<?php
					$membership_model = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_membership_model', true );
					?>
					<select name="tutor_pmpro_membership_model" id="tutor_pmpro_membership_model_select" class="tutor_select2">
						<option value=""><?php esc_html_e( 'Select a membership model', 'tutor-pro' ); ?></option>
						<option value="full_website_membership" <?php selected( 'full_website_membership', $membership_model ); ?> ><?php esc_html_e( 'Full website membership', 'tutor-pro' ); ?></option>
						<option value="category_wise_membership" <?php selected( 'category_wise_membership', $membership_model ); ?>><?php esc_html_e( 'Category wise membership', 'tutor-pro' ); ?></option>
					</select>
				</td>
			</tr>

			<tr class="membership_categories membership_course_categories" style="display: <?php echo esc_attr( 'category_wise_membership' === $membership_model ? '' : 'none' ); ?>;">
				<th width="200"><label><?php esc_html_e( 'Course Categories', 'tutor-pro' ); ?>:</label></th>
				<td>
					<?php generate_categories_for_pmpro( tutor_utils()->get_course_categories(), $level_categories ); ?>
				</td>
			</tr>

			<tr class="">
				<th width="200"><label><?php esc_html_e( 'Add Recommend badge', 'tutor-pro' ); ?>:</label></th>
				<td>
					<label class="tutor-switch">
						<input type="checkbox"  value="1" name="tutor_pmpro_level_highlight" <?php echo $highlight ? 'checked="checked"' : ''; ?>/>
						<span class="slider round tutor-switch-blue"></span>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
</div>
