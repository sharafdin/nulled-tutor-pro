<?php

// Check if api key connected, and set first sub page

use TUTOR\Input;

$check_api      = tutor_zoom_check_api_connection();
$currentSubPage = $check_api ? 'meetings' : 'set_api';
$dashboard_url  = tutor_utils()->tutor_dashboard_url();

// Prepare sub page list
$subPages = array(
    'meetings' => array(
        'key' => 'meetings',
        'title' => __('Active Meetings', 'tutor-pro'),
        'frontend_url' => esc_url( $dashboard_url.'zoom' ),
    ),
    'expired' => array(
        'key' => 'expired',
        'title' => __('Expired', 'tutor-pro'),
        'frontend_url' => esc_url( $dashboard_url.'zoom/expired' ),
    ),
    'set_api' => array(
        'key' => 'set_api',
        'title' => __('Set API', 'tutor-pro'),
        'frontend_url' => esc_url( $dashboard_url.'zoom/set-api' ),
    ),
    'settings' => array(
        'key' => 'settings',
        'title' => __('Settings', 'tutor-pro'),
        'frontend_url' => esc_url( $dashboard_url.'zoom/settings' ),
    ),
    'help' => array(
        'key' => 'help',
        'title' => __('Help', 'tutor-pro'),
        'frontend_url' => esc_url( $dashboard_url.'zoom/help' ),
    ),
);

// Assign backend dashboard URLs
foreach($subPages as $key=>$page) {
    $subPages[$key]['url'] = add_query_arg( array( 'page' => 'tutor_zoom', 'sub_page' => $key ), admin_url( 'admin.php' ) );
}

// Remove meeting list page if api key not connected
if(!$check_api) {
    unset($subPages['meetings']);
    unset($subPages['expired']);
}

// Prepare query information
global $wp_query, $wp;
$paged = Input::get( 'paged', 1, Input::TYPE_INT );
$paged = max( 1, $paged );

$error_msg = '';
if (!empty($_GET['sub_page'])) {
    $currentSubPage = sanitize_text_field($_GET['sub_page']);
    if(!$check_api && ($currentSubPage == 'meetings' || $currentSubPage == 'settings')) {
        $error_msg = __('Please set your API Credentials. Without valid credentials, Zoom integration will not work', 'tutor-pro');
        $currentSubPage = 'set_api';
    }
}
?>

<div class="<?php echo is_admin() ? 'tutor-admin-wrap' : ''; ?>">
    <?php if(is_admin()) : ?>
        <?php
            $navbar_data = array(
                'page_title' => __('Zoom', 'tutor'),
                'tabs'       => $subPages,
                'active'     => $currentSubPage
            );
            $navbar_template  = tutor()->path . 'views/elements/navbar.php';
            tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
        ?>
    <?php else : ?>
        <div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16"><?php esc_html_e( 'Zoom', 'tutor-pro' ); ?></div>
        <div class="tutor-mb-32">
            <ul class="tutor-nav" tutor-priority-nav>
                <?php
                    global $wp_query;
                    $query_vars = $wp_query->query_vars;

                    foreach ( $subPages as $key => $sub_page ) {
                        if ( isset( $query_vars['tutor_dashboard_sub_page'] ) ) {
                            if ( $query_vars['tutor_dashboard_sub_page'] == 'set-api' ) {
                                $active_query_vars = 'set_api';
                            } elseif (
                                $query_vars['tutor_dashboard_sub_page'] == 'settings' ||
                                $query_vars['tutor_dashboard_sub_page'] == 'help' ||
                                $query_vars['tutor_dashboard_sub_page'] == 'expired'
                                ) {
                                $active_query_vars = $query_vars['tutor_dashboard_sub_page'];
                            } elseif ( $query_vars['tutor_dashboard_sub_page'] == "expired/page/$paged" ) {
                                $active_query_vars = 'expired';
                            } else {
                                $active_query_vars = 'meetings';
                            }
                        } else {
                            if ( ! $check_api ) {
                                $active_query_vars = 'set_api';
                            } else {
                                $active_query_vars = 'meetings';
                            }
                        }
                        ?>
                        <li class="tutor-nav-item">
                            <a class="tutor-nav-link<?php echo $active_query_vars == $key ? ' is-active' : '' ?>" href="<?php echo esc_url( $sub_page['frontend_url'] ); ?>">
                                <?php echo esc_html( $sub_page['title'] ); ?>
                            </a>
                        </li>
                        <?php
                    }
                ?>
                <li class="tutor-nav-item tutor-nav-more tutor-d-none">
                    <a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4"><?php _e("More", "tutor-pro"); ?></span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
                    <ul class="tutor-nav-more-list tutor-dropdown"></ul>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <div class="<?php echo is_admin() ? 'tutor-admin-body' : ''; ?>">
        <?php
            $frontend_class = ! is_admin() ? 'tutor-zoom-frontend' : '';
            if ( $error_msg ) : ?>
            <div class="tutor-app-process-alert tutor-mb-16">
                <div style="border:1px solid #1973aa;" class="tutor-primary tutor-py-12 tutor-px-4 tutor-radius-6">
                    <div class="tutor-alert-text tutor-d-flex tutor- tutor-align-center">
                        <span class="tutor-icon-circle-info tutor-fs-4 tutor-color-primary tutor-mr-12"></span>
                        <span class="tutor-fs-6">
                            <?php echo esc_html( $error_msg ); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="tutor-zoom-content tutor-mt-24 <?php echo $frontend_class?>">
            <?php
            $page = sanitize_text_field($currentSubPage);
            $view_page = TUTOR_ZOOM()->path . 'views/pages/';

            /**
             * If only frontend check query vars & set page name
             *
             * @since 1.9.3
             */

            $sub_page = isset($query_vars['tutor_dashboard_sub_page'])?$query_vars['tutor_dashboard_sub_page']:'';
            $sub_page = ('expired'!==explode('/',$sub_page)[0])?$sub_page:explode('/',$sub_page)[0];
            if( !is_admin() ) {
                global $wp_query;
                $query_vars = $wp_query->query_vars;

                if( !isset($query_vars['tutor_dashboard_sub_page']) ) {

                } elseif ( isset($query_vars['tutor_dashboard_sub_page']) && $query_vars['tutor_dashboard_sub_page'] == 'set-api' ) {
                    $page = 'set_api';
                } else if ( $query_vars['tutor_dashboard_sub_page'] == 'expired' || $query_vars['tutor_dashboard_sub_page'] == 'meetings' || $query_vars['tutor_dashboard_sub_page'] == 'settings' || $query_vars['tutor_dashboard_sub_page'] == 'help'  ) {
                    $page = $query_vars['tutor_dashboard_sub_page'];
                } /* else {
                    // $page = "expired";
                    pr($query_vars['tutor_dashboard_sub_page']);
                } */
                if ( $page == 'meetings' ) {
                    $page = 'frontend-meetings' ;
                }
                if ( !$check_api && $page === 'settings' ) {
                    $page = 'set_api';
                }

            } else if ( $page == 'expired' ) {
                $page = 'meetings';
            }
            // pr($query_vars['tutor_dashboard_sub_page']);
            if(isset($sub_page) && 'expired'===$sub_page){
                $page = $sub_page;
            }
            /**
             * Change zoom /all mettings page for the frontend
             *
             * as design style changed
             *
             * @since 1.9.4
             */
            if (file_exists($view_page . "/{$page}.php")) {
                include_once $view_page . "/{$page}.php";
            } else {
                if ( !is_admin() ) {
                    include_once $view_page . "/frontend-meetings.php";
                }
            }
            ?>
        </div>
    </div>
</div>