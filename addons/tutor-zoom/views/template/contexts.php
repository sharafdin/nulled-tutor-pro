<?php

$contexts =  array(
    'meeting-table' => array(
        'columns' => array(
            'start_time' => __('Start Time', 'tutor-pro'),
            'meeting_name' => __('Meeting Name', 'tutor-pro'),
            'meeting_token' => __('Meeting Token', 'tutor-pro'),
            'password' => __('Password', 'tutor-pro'),
            'hostmail' => __('Host Mail', 'tutor-pro'),
            'action_frontend' => '',
            'action_backend' => '',
        ),
        'contexts' => array(
            'backend-dashboard' => array(
                'start_time',
                'meeting_name',
                'meeting_token',
                'password',
                'hostmail',
                'action_backend',
            ),
            'frontend-active' => array(
                'start_time',
                'meeting_name',
                'action_frontend',
            ),
            'frontend-expired' => 'frontend-active',
        )
    ),
);

return tutor_utils()->get_table_columns_from_context($page_key, $context, $contexts, 'tutor/zoom/meeting/table/column');