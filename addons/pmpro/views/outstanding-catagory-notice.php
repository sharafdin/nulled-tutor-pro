<?php
/**
 * Outstanding Category Notice
 *
 * @package TutorPro\Addons
 * @subpackage PmPro\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.3.5
 */

if ( ! count( $outstanding ) ) {
	return;
}
?>

<div id="tutor-pmpro-outstanding-categories">
	<div>
		<div>
			<img src="<?php echo esc_url( TUTOR_PMPRO()->url ); ?>assets/images/info.svg"/>
		</div>
		<div>
			<h3><?php esc_html_e( 'Tutor course categories not used in any level', 'tutor-pro' ); ?></h3>
			<p><?php esc_html_e( 'Some course categories from Tutor LMS are not in any category. Make sure you have them in a category if you want to monetize them. Otherwise, they will be free to access.', 'tutor-pro' ); ?></p>

			<div class="tutor-outstanding-cat-holder">
				<?php
				foreach ( $outstanding as $item ) {
					echo '<span>' . esc_html( $item->name ) . '</span>';
				}
				?>
			</div>
		</div>
	</div>
</div>
