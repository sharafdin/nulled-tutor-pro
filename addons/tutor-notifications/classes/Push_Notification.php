<?php

/**
 * Tutor Push Notification
 */

namespace TUTOR_NOTIFICATIONS;

use \Minishlink\WebPush\VAPID;

defined( 'ABSPATH' ) || exit;

/**
 * Push Notification class
 */
class Push_Notification {

    /**
     * Private $browser_key
     *
     * @var string
     */
    private $browser_key = 'tutor_pn_browser_key';

    /**
     * Private $sub_key
     *
     * @var string
     */
    private $sub_key = 'tutor_pn_subscriptions';

    /**
     * Constructor
     */
    public function __construct() {

        add_action( 'init', array( $this, 'load_service_worker' ) );

        add_action( 'wp_ajax_tutor_pn_save_subscription', array( $this, 'save_subscription' ) );
        add_action( 'wp_logout', array( $this, 'purge_browser_id' ) );
        add_action( 'wp_login', array( $this, 'purge_browser_id' ) );
        add_filter( 'tutor_localize_data', array( $this, 'supply_pn_data' ) );

		add_action( 'tutor_announcement_editor/after', array( $this, 'notification_checkbox_for_announcement' ) );

        add_action( 'wp_footer', array( $this, 'permission_screen' ) );
        add_action( 'admin_footer', array( $this, 'permission_screen' ) );
    }

    /**
     * Load service worker
     */
    public function load_service_worker() {
        $uri = explode( '/', $_SERVER['REQUEST_URI'] );

        if ( end ( $uri ) == 'tutor-push-notification.js' ) {
            $file = TUTOR_NOTIFICATIONS()->path . '/assets/js/tutor-push-notification.js';
            header( 'Content-Type: text/javascript' );
            header( 'Content-Length: ' . filesize( $file ) );
            readfile( $file );
            exit;
        }
    }

    /**
     * Load web push
     */
    protected function load_web_push() {
        require_once tutor_pro()->path . '/vendor/autoload.php';
    }

    /**
     * Update Subscription
     *
     * @param int $user_id
     * @param int|string|mixed $key
     * @param mixed $subscription
     */
    private function updateSubscription( $user_id, $key, $subscription = null ) {

        $subs = get_user_meta( $user_id, $this->sub_key, true );
        ! is_array( $subs ) ? $subs = array() : 0;

        if ( ! $subscription ) {
            if ( isset( $subs[ $key ] ) ) {
                unset( $subs[ $key ] );
            }
        } else {
            $subs[ $key ] = $subscription;
        }

        update_user_meta( $user_id, $this->sub_key, $subs );
    }

    /**
     * Get current subscription
     */
    private function get_current_subscription() {
        if ( isset( $_COOKIE[ $this->browser_key ] ) ) {
            $subs = get_user_meta( get_current_user_id(), $this->sub_key, true );
            return isset( $subs[ $_COOKIE[$this->browser_key ] ] ) ? $subs[ $_COOKIE[ $this->browser_key ] ] : null;
        }
    }

    /**
     * Get subscriptions
     *
     * @param int $user_id
     */
    protected function get_subscriptions( $user_id ) {
        $subs = get_user_meta( $user_id, $this->sub_key, true );
        return is_array( $subs ) ? $subs : array();
    }

    /**
     * Purge browser id
     *
     * @param int $user_id
     */
    public function purge_browser_id( $user_id ) {
        if ( isset( $_COOKIE[ $this->browser_key ] ) ) {
            $this->updateSubscription( $user_id, $_COOKIE[ $this->browser_key ] );
            setcookie( $this->browser_key, "", time() - 3600, '/' );
        }
    }

    /**
     * Get vapid keys
     */
    protected function get_vapid_keys() {

        $vapid_keys = get_option( 'tutor_pn_vapid_keys' );
        $home_url = get_home_url();

        // Use home_url to make sure current site url is used
        // Because in some cases users move their site one domain to another
        if ( ! is_array( $vapid_keys ) || ! isset( $vapid_keys[ $home_url ] ) ) {
            $this->load_web_push();

            try {
                $vapid_keys = array( $home_url => VAPID::createVapidKeys() );
                update_option( 'tutor_pn_vapid_keys', $vapid_keys );
            }
            catch( \Exception $e ) {
                return null;
            }
        }

        return $vapid_keys[ $home_url ];
    }

    /**
     * Supply push notification data
     *
     * @param object $_tutorobject
     *
     * @return object $_tutorobject
     */
    public function supply_pn_data( $_tutorobject ) {

        $keys = $this->get_vapid_keys();
        $_tutorobject['tutor_pn_vapid_key'] = $keys ? $keys['publicKey'] : null;
        $_tutorobject['tutor_pn_client_id'] = get_current_user_id();
        $_tutorobject['tutor_pn_subscription_saved'] = $this->get_current_subscription() ? 'yes' : 'no';

        return $_tutorobject;
    }

    /**
     * Save subscription
     */
    public function save_subscription() {

        $key = isset( $_COOKIE[ $this->browser_key ] ) ? $_COOKIE[ $this->browser_key ] : 'pn_' . microtime( true );

        $subscription = @json_decode( stripslashes( $_POST['subscription'] ), true );
        $this->updateSubscription( get_current_user_id(), $key, $subscription );

        setcookie( $this->browser_key, $key,  time() + ( 5 * 365 * 24 * 60 * 60 ), '/' );
    }

    /**
     * Add checkbox for push notifications in announcements
     */
	public function notification_checkbox_for_announcement() {

		$notify_checked = tutor_utils()->get_option( 'tutor_pn_to_students.new_announcement_posted' );

		if ( $notify_checked ) : ?>
			<div class="tutor-option-field-row">
				<div class="tutor-form-check tutor-mb-4">
					<input id="tutor_announcement-notification-push" type="checkbox" class="tutor-form-check-input tutor-form-check-20" name="tutor_push_notify_students" checked="checked"/>
					<label for="tutor_announcement-notification-push">
                        <?php _e( 'Send push notification to all students of this course.', 'tutor-pro' ); ?>
					</label>
				</div>
			</div>
		<?php endif;
	}

    /**
     * Permission screen
     */
    public function permission_screen() {

        global $wp_query;

        $is_course = false;

        if ( function_exists( 'get_queried_object' ) ) {
            $object = get_queried_object();
            $is_course = is_object( $object ) && isset( $object->post_type ) && $object->post_type == tutor()->course_post_type;
        }

        if (
            tutor_utils()->is_tutor_dashboard() ||
            $is_course ||
            is_front_page() || (
                $wp_query &&
                is_object( $wp_query ) &&
                is_array( $wp_query->query_vars ) &&
                isset( $wp_query->query_vars['pagename'] ) &&
                $wp_query->query_vars['pagename'] == 'dashboard'
            )
        ) {
            ?>
            <div id="tutor-pn-permission">
                <div>
                    <p><i class="tutor-icon-bell-bold"></i><?php esc_html_e( 'Want to receive push notifications for all major on-site activities?', 'tutor-pro' ); ?></p>
                    <div>
                        <button id="tutor-pn-enable"><?php esc_html_e( 'Enable Notifications', 'tutor-pro' ); ?></button>
                        <button id="tutor-pn-dont-ask"><?php esc_html_e( 'Never', 'tutor-pro' ); ?></button>
                        <span id="tutor-pn-close"><?php esc_html_e( '✕', 'tutor-pro' ); ?></span>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}