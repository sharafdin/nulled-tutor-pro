<?php
/**
 * Gradebook Addon Init
 *
 * @package TutorPro\Addons
 * @subpackage Gradebook
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_GB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Init
 */
class Init {
	//phpcs:disable
	public $version = TUTOR_GB_VERSION;
	public $path;
	public $url;
	public $basename;
	public $multi_instructors;
	//phpcs:enable

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}
		$addon_config = tutor_utils()->get_addon_config( TUTOR_GB()->basename );
		$is_enable    = (bool) tutor_utils()->array_get( 'is_enable', $addon_config );

		if ( ! $is_enable ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_GB_FILE );
		$this->url      = plugin_dir_url( TUTOR_GB_FILE );
		$this->basename = plugin_basename( TUTOR_GB_FILE );

		$this->load_gradebook();
	}

	/**
	 * Class autoload
	 *
	 * @return void
	 */
	public function load_gradebook() {
		spl_autoload_register( array( $this, 'loader' ) );
		$this->multi_instructors = new GradeBook();

		// Priority index is important. 'Content Drip' add-on uses 11.
		add_filter( 'tutor/options/attr', array( $this, 'add_options' ), 10 );
	}

	/**
	 * Auto Load class and the files
	 *
	 * @param string $class_name class name.
	 *
	 * @return void
	 */
	private function loader( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			$class_name = preg_replace(
				array( '/([a-z])([A-Z])/', '/\\\/' ),
				array( '$1$2', DIRECTORY_SEPARATOR ),
				$class_name
			);

			$class_name = str_replace( 'TUTOR_GB' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Gradebook option to tutor settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attr attributes.
	 *
	 * @return array
	 */
	public function add_options( $attr ) {
		$attr['tutor_gradebook'] = array(
			'label'    => __( 'Gradebook', 'tutor-pro' ),
			'slug'     => 'gradebook',
			'desc'     => __( 'Gradebook Settings', 'tutor-pro' ),
			'template' => 'basic',
			'icon'     => 'tutor-icon-grade-book',
			'blocks'   => array(
				array(
					'label'      => __( 'Settings', 'tutor-pro' ),
					'slug'       => 'g_settings',
					'block_type' => 'uniform',
					'fields'     => array(
						array(
							'key'     => 'gradebook_enable_grade_point',
							'type'    => 'toggle_switch',
							'label'   => __( 'Use Points Instead of Grades', 'tutor-pro' ),
							'default' => 'off',
							'desc'    => __( 'Enable this option to use numerical points instead of letter grades.', 'tutor-pro' ),
						),
						array(
							'key'     => 'gradebook_show_grade_scale',
							'type'    => 'toggle_switch',
							'label'   => __( 'Enable GPA Scale Limit', 'tutor-pro' ),
							'default' => 'off',
							'desc'    => sprintf( __( 'Turn it on if you want to show the limit of GPA scales, such as 3.8/4.0', 'tutor-pro' ), '<code>/4.0</code>' ),
						),
						array(
							'key'           => 'gradebook_scale_separator',
							'type'          => 'text',
							'classes'       => 'text',
							'field_classes' => 'tutor-w-90',
							'label'         => __( 'Separator Between Scores', 'tutor-pro' ),
							'default'       => '/',
							'desc'          => __( 'Input the separator text or symbol to display. Example: Insert <strong>/</strong> to display 3.8/4.0 or “out of” 3.8 out of 4.', 'tutor-pro' ),
						),
						array(
							'key'     => 'gradebook_scale',
							'type'    => 'number',
							'label'   => __( 'Set the GPA Scale Limit', 'tutor-pro' ),
							'default' => '4.0',
							'desc'    => __( 'Insert the highest limit of grade point out of which the final results will be calculated', 'tutor-pro' ),
						),
					),
				),
			),
		);

		return $attr;
	}
}
