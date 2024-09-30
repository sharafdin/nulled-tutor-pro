<?php
namespace TUTOR_GC;

use \Google_Service_Classroom;
use \Google_Client;

use \Google_Service_Script;
use \Google_Service_Script_CreateProjectRequest;
use \Google_Service_Script_ScriptFile;
use \Google_Service_Script_Content;
use \Google_Service_Script_ExecutionRequest;

if ( ! defined( 'ABSPATH' ) )
	exit;

require_once tutor_pro()->path . '/vendor/autoload.php';

class Classroom{

    private $upload_dir;
    private $credential_path;
    private $token_path;
    private $google_callback_url;
    private $client;

    private $service_classroom;
    private $current_credential;
    private $credential_owner;

    private $gc_user_identifier = 'tutor_gc_user_from_class';
    private $gc_post_time = 'tutor_gc_post_time';
    private $remote_class = 'tutor_gc_remote_class_cache';
    private $remote_class_owner = 'tutor_gc_remote_class_owner';
    private $course_credential = 'tutor_gc_engaged_credential_serial';
    private $credential_serial = 'tutor_gc_user_current_credential_serial';
    private $classroom_key = 'tutor_gc_classroom_id';
    private $temporary_password = 'tutor_gc_temp_raw_password';
    public static $password_reset_base = 'tutor-student-password-reset';

    private $required_scopes=array(    
        Google_Service_Classroom::CLASSROOM_COURSES_READONLY,
        Google_Service_Classroom::CLASSROOM_ROSTERS_READONLY,
        Google_Service_Classroom::CLASSROOM_PROFILE_PHOTOS,
        Google_Service_Classroom::CLASSROOM_PROFILE_EMAILS,
        Google_Service_Classroom::CLASSROOM_TOPICS_READONLY,
        Google_Service_Classroom::CLASSROOM_ANNOUNCEMENTS_READONLY,
        Google_Service_Classroom::CLASSROOM_COURSEWORK_ME_READONLY,
        Google_Service_Classroom::CLASSROOM_COURSEWORK_STUDENTS_READONLY
    );

    // Initialize necessary rsource
    function __construct($owner_id=null, $post_id=null, $ret=false){
        
        if($ret){
            return;
        }

        // Set credential owner
        $this->credential_owner = $owner_id ? $owner_id : ($post_id ? get_post_field('post_author', $post_id) : get_current_user_id());
        
        // Set credential serial
        $credential_serial = get_user_meta($this->credential_owner, $this->credential_serial, true);
        $post_id ? $credential_serial = get_post_meta($post_id, $this->course_credential, true) : 0;

        $this->current_credential = ($credential_serial && is_numeric($credential_serial)) ? $credential_serial : 0;

        $this->upload_dir = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'tutor-gc' . DIRECTORY_SEPARATOR . $this->credential_owner;
        $this->credential_path = $this->get_credential_path();
        $this->token_path = $this->get_token_path();
        $this->google_callback_url = get_home_url().'/'.init::$google_callback_string.'/';
        
        // Init client if credential loaded
        $this->is_credential_loaded() ? $this->init_client() : 0;

        // Init services 
        ($this->is_credential_loaded() && $this->is_app_permitted()) ? $this->init_service() : 0;
    }

    private function get_credential_path($serial=null){
        return $this->upload_dir . DIRECTORY_SEPARATOR . 'credential-' . ($serial ? $serial : $this->current_credential) . '.json';
    }

    private function get_token_path($serial=null){
        return $this->upload_dir . DIRECTORY_SEPARATOR . 'token-' . ($serial ? $serial : $this->current_credential) . '.json';
    }

    // Set up google client
    private function init_client(){

        $this->client = new Google_Client();
        $this->client->setApplicationName(get_bloginfo('name'));
        $this->client->setAuthConfig($this->credential_path);
        $this->client->setRedirectUri($this->google_callback_url);
        $this->client->addScope($this->required_scopes);
        $this->client->setAccessType("offline");
        $this->client->setApprovalPrompt('force');

        $this->assign_token();
    }

    private function init_service(){
        $this->service_classroom = new Google_Service_Classroom($this->client);
    }

    public function get_who_logged_in(){
        return $this->service_classroom->userProfiles->get('me');
    }

    public function upgrade_credential_serial(){
        $serial = $this->current_credential+1;
        update_user_meta($this->credential_owner, $this->credential_serial, $serial);
    }

    // Check if credential file uploaded
    public function is_credential_loaded(){
        return file_exists($this->credential_path);
    }

    // Check if the app is permitted by user via consent screen
    public function is_app_permitted(){
        return $this->assign_token()===false ? false : true;
    }

    // Upload credential file
    public function save_credential($file){
        
        // Create dir if already not
        wp_mkdir_p($this->upload_dir);
        
        // Store the file
        $serial = $this->current_credential+1;
        $path = $this->get_credential_path($serial);
        move_uploaded_file($file['tmp_name'], $path);

        // Assign the current credential serial
        update_user_meta($this->credential_owner, $this->credential_serial, $serial);
    }

    // Return consent screen url
    public function get_consent_screen_url(){
        return $this->client->createAuthUrl();
    }

    // Assign the existing token, or try to refresh if expired.
    public function assign_token(){

        if (file_exists($this->token_path)) {
            $accessToken = json_decode(file_get_contents($this->token_path), true);
            $this->client->setAccessToken($accessToken);
        }
      
        // Check if token expired
        if ($this->client->isAccessTokenExpired()) {

            $refresh_token = $this->client->getRefreshToken();
            
            if(!$refresh_token){
                return false;
            }

            $new_token = null;
            
            try {
                $new_token = $this->client->fetchAccessTokenWithRefreshToken($refresh_token);
            } catch(\Exception $e) {
                if($e) {
                    return false;
                }
            }

            $this->save_token(null, $new_token);
        }
    }

    // Save token provided by google
    public function save_token($code=null, $token=null){
            
        if(!$token){
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            $this->client->setAccessToken($token);
            $token = $this->client->getAccessToken();
        }
        
        file_put_contents($this->get_token_path(), json_encode($token));
    }

    /* ----------------------Class related helpers---------------------- */
    // fetch class list from google
    public function get_class_list()
    {
        // Get classroom list from google
        $course_list = $this->service_classroom->courses->listCourses(['courseStates'=>'ACTIVE']);
        $courses = $course_list->getCourses();
        
        // Determine if locally imported class exists for the remote classroom ID
        $posts = get_posts([
            'meta_key'=>$this->classroom_key, 
            'post_type'=>tutor()->course_post_type, 
            'post_status'=>array('publish', 'draft', 'trash'), 
            'posts_per_page'=>-1
        ]);

        if(is_array($posts)){

            // Loop through all the local post
            foreach($posts as $post){
                $connected_class_id = get_post_meta($post->ID, $this->classroom_key, true);

                // Loop through all the classroom
                foreach($courses as $index=>$course){
                    if($course->id==$connected_class_id){
                        $courses[$index]->local_class_post = $post;
                    }
                }
            }
        }

        return $courses;
    }

    public function get_imported_class_list($page=1){
        
        $per_page = 16;
        $page = $page-1;
        $offset = $page*$per_page;

        $post_args = array(
            'meta_key'=>$this->classroom_key, 
            'post_type'=>tutor()->course_post_type, 
            'post_status'=>array('publish'), 
            'posts_per_page'=>$per_page,
            'offset'=>$offset
        );

        $posts = get_posts($post_args);
        !is_array($posts) ? $posts=[] : 0;

        $src = tutor()->url . 'assets/images/placeholder.svg';

        foreach($posts as $index=>$post){
            $posts[$index]->remote_class = $this->get_remote_class($post->ID);
            $posts[$index]->remote_class_owner = get_post_meta($post->ID, $this->remote_class_owner, true);

            $post_thumbnail_id = (int) get_post_thumbnail_id($post->ID);
            $posts[$index]->post_thumbnail_url = $post_thumbnail_id ? wp_get_attachment_image_url($post_thumbnail_id, 300, false) : $src;
        }

        return $posts;
    }

    public function is_google_class($course_id, $return_classroom_url=false){
        $remote_id = get_post_meta($course_id, $this->classroom_key, true);
        $remote_post = $this->get_remote_class($course_id);

        return (is_numeric($remote_id) && $remote_id>0) ? ($return_classroom_url ? $remote_post->alternateLink : true) : false;
    }

    private function get_course_students($course){

        $course_id = is_object($course) ? $course->id : $course;
        $students = array();

        if($this->service_classroom){
            $students = $this->service_classroom->courses_students->listCoursesStudents($course_id);
            $students = $students->getStudents();
        }
        
        return is_array($students) ? $students : [];
    }
    
    private function filter_privileged_content($remote_id, $contents){
        
        $current_user = get_userdata(get_current_user_id()); 
        $current_email = $current_user->user_email;

        $remote_students = $this->get_course_students($remote_id);
        $new_contents = array();

        foreach( $contents as $content ) {
            if ( ! isset( $content->individualStudentsOptions ) ) {
                $new_contents[]=$content;
                continue;
            }

            $specific_students = $content->individualStudentsOptions->studentIds;
            !$specific_students ? $specific_students=array() : 0;

            foreach($remote_students as $student){
                $id = $student->profile->id;
                $email = $student->profile->emailAddress;

                if(in_array($id, $specific_students) && $email==$current_email){
                    $new_contents[]=$content;
                    break;
                }
            }
        }

        return $new_contents;
    }

    public function get_stream($local_id, $token=null, $only_stream=false){
        
        $remote_id = get_post_meta($local_id, $this->classroom_key, true);

        $page_arg = array('pageSize'=>10);
        $token ? $page_arg['pageToken']=$token : 0;
        
        $announcements = [];
        $next_token = '';

        try{
            if($this->service_classroom){
                $announcements_list = $this->service_classroom->courses_announcements->listCoursesAnnouncements($remote_id, $page_arg);
                $announcements = $announcements_list->getAnnouncements();
                $next_token = $announcements_list->getNextPageToken();
            }
        }
        catch(\Exception $e){

        }
        !is_array($announcements) ? $announcements=[] : 0;

        if($only_stream){
            return $announcements;
        }

        $announcements = $this->filter_privileged_content($remote_id, $announcements);
        $user_cache = [];

        // Assign bulk user object
        foreach($announcements as $index=>$announcement){
            
            $creator_id = $announcement->creatorUserId;

            if(!array_key_exists($creator_id, $user_cache)){
                // Load user data if not loaded already
                $user_cache[$creator_id] = $this->service_classroom->userProfiles->get($creator_id);
            }

            $announcements[$index]->creator_user_object = $user_cache[$creator_id];
        }

        return array('announcements'=>$announcements, 'next_token'=>$next_token);
    }

    public function get_remote_class($local_id){
        $remote = get_post_meta($local_id, $this->remote_class, true);
        return is_object($remote) ? $remote : null;
    }

    public function get_all_remote_attachments($local_id){
        $remote_class = $this->get_remote_class($local_id);
        $remote_id = (is_object($remote_class) && property_exists($remote_class, 'id')) ? $remote_class->id : null;
        $materials = array();

        if(!$remote_id){
            // It might be null somewhere
            return $materials;
        }
        
        // Get Assignment/Coursework Materials
        $course_works = array();
        try{
            if($this->service_classroom){
                $course_works = $this->service_classroom->courses_courseWork->listCoursesCourseWork($remote_id);
                $course_works = $course_works->getCourseWork();
            }
        }
        catch(\Exception $e){

        }

        $course_works = is_array($course_works) ? $this->filter_privileged_content($remote_id, $course_works) : array();
        foreach($course_works as $work){
            $materials=array_merge($materials, ($work->materials ? $work->materials : array()));
        }     
                   
        // Get stream materials
        $streams = $this->get_stream($local_id, null, true);
        foreach($streams as $stream){
            $materials=array_merge($materials, ($stream->materials ? $stream->materials : array()));
        }     
        
        return $materials;
    }

    /* ------------------Import class from Google to WP------------------ */
    public function import_class($class_id, bool $enroll_student){

        // Set execution time to 60 seconds.
        // Now there are heavy tasks like interaction with google and mail sending to auto registered students.
        set_time_limit(60);

        $course = $this->service_classroom->courses->get($class_id);
        
        // Create course at first
        $post_id = $this->import_course_post($course);
        
        // Register students
        if($enroll_student){
            $student_ids = $this->register_students($course);
            
            // Enroll students
            foreach($student_ids as $user_id){
                tutor_utils()->do_enroll($post_id, 0, $user_id);
            }
        }

        return $post_id;
    }

    private function get_local_id($remote_id){
        
        // Check if already imported
        $existing_arg = array(
            'meta_key' => $this->classroom_key, 
            'meta_value' => $remote_id,
            'post_type' => tutor()->course_post_type, 
            'post_status' => array('publish', 'draft', 'trash'), 
            'posts_per_page' => 1
        );

        $posts = get_posts($existing_arg);
        
        return (is_array($posts) && isset($posts[0])) ? $posts[0]->ID : null;
    }

    // Import Root course post
    private function import_course_post($course){

        $post=
        [
            'post_author' => get_current_user_id(),
            'post_title' => $course->descriptionHeading,
            'post_content' => $course->description ? $course->description : '',
            'post_status' => 'draft',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_type' => tutor()->course_post_type,
            'post_parent' => 0
        ];

        // Avoid accidental duplication
        $existing_id = $this->get_local_id($course->id);
        if($existing_id){
            $post['ID']=$existing_id;
        }
        else{
            $post_name = sanitize_title($course->descriptionHeading ? $course->descriptionHeading : '');
            $post_name = $this->get_unique_value('posts', 'post_name', $post_name);
            $post['post_name'] = $post_name;
        }

        // Insert now
        $post_id = wp_insert_post($post);
        $remote_class_owner = $this->service_classroom->userProfiles->get($course->ownerId);

        // Set single line room, section
        $room_section = [];
        $course->room ? $room_section[]=$course->room : 0;
        $course->section ? $room_section[]=$course->section : 0;
        $course->room_and_section = implode(', ', $room_section);


        // Set remote ID as identifier
        update_post_meta($post_id, $this->classroom_key, $course->id);
        update_post_meta($post_id, $this->course_credential, $this->current_credential);
        update_post_meta($post_id, $this->remote_class, $course);
        update_post_meta($post_id, $this->remote_class_owner, $remote_class_owner);
        update_post_meta($post_id, $this->gc_post_time, date("Y-m-d h:i a", tutor_time()));

        return $post_id;
    }

    public function is_reset_token_valid($token, $return_id=false){
        $segments = explode('_', $token);

        $user_id = isset($segments[0]) ? $segments[0] : '';
        $hash = implode('_', array_slice($segments, 1));

        if(is_numeric($user_id) && strlen($hash)){
            $user_data = get_userdata($user_id);

            if(is_object($user_data)){
                $user_id = $user_data->ID;
                
                $temp_pass = get_user_meta($user_id, $this->temporary_password, true);
                !$temp_pass ? $temp_pass='' : 0;
                
                $valid = wp_check_password($temp_pass, $hash);
                
                return $valid ? ($return_id ? $user_id : true) : false;
            }
        }

        return false;
    }

    private function get_unique_value(string $table, string $column, $value, $index=1){
        
        global $wpdb;
        $table_name = $wpdb->prefix.$table;

        $value = trim($value);
        empty($value) ? $value='untitled' : 0;
        $compare_value = $value.($index>1 ? '-'.$index : '');

        $duplicate = $wpdb->get_results('SELECT '.$column.' FROM '.$table_name.' WHERE '.$column.'="'.esc_sql($compare_value).'"');

        return count($duplicate)>0 ? $this->get_unique_value($table, $column, $value, $index+1) : $compare_value;
    }

    public function set_student_password($token, $password){
        $user_id = $this->is_reset_token_valid($token, true);

        if($user_id && is_numeric($user_id)){
            wp_set_password($password, $user_id);
            delete_user_meta($user_id, $this->temporary_password);
        }
    }

    private function generate_password_link($user_id, $user_pass){
        $token = $user_id.'_'.wp_hash_password($user_pass);
        return get_home_url().'/'.self::$password_reset_base.'/?token='.$token;
    }

    private function send_auto_registration_email($user_data, $user_id, $class_name){

        $password_reset_link = $this->generate_password_link($user_id, $user_data['user_pass']);

        ob_start();
        include dirname(__DIR__).'/views/email/auto-registration-verification.php';
        $html_mail = ob_get_clean();

        // Send the mail content to the user email address
        wp_mail($user_data['user_email'], __('Password Setup', 'tutor-pro'), $html_mail, array('Content-Type: text/html; charset=UTF-8'));
    }

    private function register_students($course){
        
        $students = $this->get_course_students($course);
        
        $user_ids = [];
        foreach($students as $student)
        {
            $email_address = $student->profile->emailAddress;
            $full_name = $student->profile->name->fullName;
            $password = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(8/strlen($x)) )),1,8);

            $existing = get_user_by('user_email', $email_address);

            if($existing){
                continue;
            }

            $user_login = str_replace(' ', '-', sanitize_user(strtolower($full_name)));
            $user_login = $this->get_unique_value('users', 'user_login', $user_login);

            $new_user = array(
                'user_email' => $email_address,
                'user_pass' => $password,
                'user_login' => $user_login,
                'display_name' => $full_name
            );

            $user_id = wp_insert_user($new_user);

            if(is_numeric($user_id)){
                
                $user_ids[]=$user_id;
                update_user_meta($user_id, $this->gc_user_identifier, $course->id);
                update_user_meta($user_id, $this->temporary_password, $password);

                $this->send_auto_registration_email($new_user, $user_id, $course->descriptionHeading);
            }
        }

        return $user_ids;
    }
}
    