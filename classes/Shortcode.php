<?php
/**
 * Class Shortcode
 *
 * @package TutorPro
 *
 * @since 2.1.0
 */

namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode Register Class
 * @since 2.1.0
 */
class Shortcode {
    public function __construct() {
        add_shortcode( 'tutor_login', array( $this, 'tutor_login' ) );
    }

    /**
	 * Callback for tutor_login shortcode
	 * 
	 * @since 2.1.0
	 * @return mixed
	 */
	public function tutor_login(){
		ob_start();
		tutor_load_template( 'shortcode.tutor-login', array(), true );
		return ob_get_clean();
	}
}