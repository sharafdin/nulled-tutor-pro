<?php
/**
 * Zoom template as per tutor_dashboard_sub_page
 * 
 * if not subpage then load zoom main
 * 
 * @since 1.9.4
 */
    $user           = wp_get_current_user();
    $allowed_roles  = array( 'administrator', tutor()->instructor_role ); 
    if ( array_intersect( $allowed_roles, $user->roles ) ) {
        global $wp_query;
        $query_vars     = $wp_query->query_vars;
        if( $wp_query->is_page ) {
            if( isset($query_vars['tutor_dashboard_sub_page']) ||  isset($query_vars['tutor_dashboard_page']) ) {
                tutor_zoom_instance()->zoom->tutor_zoom();
            }      
        }
    }

?>