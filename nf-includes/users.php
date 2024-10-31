<?php
//Functrions for topics here

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function neoforum_add_user($userid){ //add or update existing WP-user in forum user table
    global $wpdb;
    $user_exists=$wpdb->get_results("SELECT userid FROM ".$wpdb->prefix."neoforum_users WHERE userid=".esc_sql($userid)." LIMIT 1");
    get_query_var('topic')!=null ? $place="forum=".get_query_var('forum').";topic=".get_query_var('topic') : $place="forum=".get_query_var('forum');
    if (!$user_exists){
        $wpdb->query("INSERT INTO ".$wpdb->prefix."neoforum_users 
            (userid, last_visit, new_post_border, last_place)
            VALUES($userid, '".current_time("mysql")."', '".current_time("mysql")."', '".$place."')");
    }
    else{
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET last_visit='".current_time("mysql")."', last_place='".$place."' WHERE userid=$userid");
    }
}

function neoforum_get_user_by_id($userid){ //
    global $wpdb;
    if ($userid==0) return "guest";
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users FULL JOIN ".$wpdb->prefix."users ON userid=".$wpdb->prefix."users.ID WHERE userid=".esc_sql($userid)." LIMIT 1", ARRAY_A)[0];
}

function neoforum_is_user_exists($userid){
    global $wpdb;
    if (count($wpdb->get_results("SELECT ID FROM ".$wpdb->prefix."users WHERE ID=".esc_sql($userid)." LIMIT 1"))==0 and $userid!=0){
        return false;
    }
    else{
        return true;
    }
}

function neoforum_user_posts_increase($userid){
    global $wpdb;
    neoforum_add_user($userid);
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET posts_num=posts_num+1 WHERE userid=".esc_sql($userid));
}

function neoforum_is_user_can_forum($ability, $forum_row=null, $user_row=null) {
    global $wpdb;
    switch ($ability) {
        case 'read':
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".esc_sql(get_query_var('forum'))."' LIMIT 1", ARRAY_A)[0];
            }
            if ($user_row==null){
                $user_row=neoforum_get_user_by_id(get_current_user_id());
            }
            if ($user_row=="guest" and neoforum_is_guest_can_read($forum_row)){
                return true;
            } 

            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                or $forum_row['is_restricted']==0 or neoforum_is_user_can_read($forum_row, $user_row)) {
                return true;
            }
            else{
                return false;
            }
        break;
        case 'edit':
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".esc_sql(get_query_var('forum'))."' LIMIT 1", ARRAY_A)[0];
            }
            if ($user_row==null){
                $user_row=neoforum_get_user_by_id(get_current_user_id());
            }
            if ($forum_row['is_section']){
                return false;
            }
            if ($user_row=="guest" and neoforum_is_guest_can_read($forum_row) and $forum_row['is_closed']==0){
                return true;
            } 

            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                or $forum_row['is_closed']==0) {
                return true;
            }
            else{
                return false;
            }
        break;
    }
}

function neoforum_is_user_can_topic($ability, $topic_row=null, $forum_row=null, $user_row=null) {
global $wpdb;
    if($user_row==null)
    $user_row=neoforum_get_user_by_id(get_current_user_id());
    if ($topic_row==null){
        $topic_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE slug='".esc_sql(get_query_var('topic'))."' LIMIT 1", ARRAY_A)[0];
    }
    switch ($ability) {
        case 'start':
            if (neoforum_is_user_banned($user_row['userid'])){
                return false;
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".esc_sql(get_query_var('forum'))."' LIMIT 1", ARRAY_A)[0];
            }
            if($forum_row['is_section']==true){
                return false;
            }
            if ($user_row=="guest" and get_option("neoforum_guests_can_topics")){
                return true;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                or ($user_row['user_caps']=='registered' 
                    and $forum_row['is_closed']==false
                    and ($forum_row['is_restricted']==false 
                        or neoforum_is_user_can_read($forum_row, $user_row))))
                {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'edit':
            if (neoforum_is_user_banned($user_row['userid'])){
                return false;
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']."' LIMIT 1", ARRAY_A)[0];
            }
            if ($user_row=="guest"){
                return false;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                or ($user_row['userid']==$topic_row['authorid'] 
                    and $topic_row['is_closed']==false
                    and $forum_row['is_closed']==false
                    and ($forum_row['is_restricted']==false 
                        or neoforum_is_user_can_read($forum_row, $user_row)))) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'delete':
            if (neoforum_is_user_banned($user_row['userid'])){
                return false;
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
            }

            if ($user_row=="guest"){
                return false;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                ) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'close':
            if (neoforum_is_user_banned($user_row['userid'])){
                return false;
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
            }

            if ($user_row=="guest"){
                return false;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                ) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'pin':
            if (neoforum_is_user_banned($user_row['userid'])){
                return false;
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
            }

            if ($user_row=="guest"){
                return false;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                ) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'approve':
            if (neoforum_is_user_banned($user_row['userid'])){
                return false;
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
            }

            if ($user_row=="guest"){
                return false;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                ) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'solved':
            if (neoforum_is_user_banned($user_row['userid'])){
                return false;
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
            }

            if ($user_row=="guest"){
                return false;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                or ($user_row['userid']==$topic_row['authorid'] 
                    and $topic_row['is_closed']==0
                    and $forum_row['is_closed']==0
                    and ($forum_row['is_restricted']==0
                        or neoforum_is_user_can_read($forum_row, $user_row)))) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'read':
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid'], ARRAY_A)[0];
            }
            if($topic_row['in_trash']){
                return false;
            }
            if ($user_row=="guest" and neoforum_is_guest_can_read($forum_row)){
                return true;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                or ($forum_row['is_restricted']==0 or neoforum_is_user_can_read($forum_row, $user_row)) and ($topic_row['is_approved'] or get_option('neoforum_topics_need_approving')!="on" or $topic_row['authorid']==$user_row['userid'])
                ) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'read trash':
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid'], ARRAY_A)[0];
            }
            if ($user_row=="guest" and neoforum_is_guest_can_read($forum_row)){
                return true;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                or $forum_row['is_restricted']==0 or neoforum_is_user_can_read($forum_row, $user_row)) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'subscribe':
            if ($user_row=="guest"){
                return false;
            }
            if($topic_row['in_trash']){
                return false;
            }
            if (neoforum_is_user_can_read($forum_row, $user_row)){
                return true;
            }
            else{
                return false;
            }
            break;

    }
}

function neoforum_is_user_can_post($ability, $post_row=null, $topic_row=null, $forum_row=null, $user_row=null) {
    global $wpdb;
    if($user_row==null)
    $user_row=neoforum_get_user_by_id(get_current_user_id());
    if (neoforum_is_user_banned($user_row['userid'])){
        return false;
    }
    switch ($ability) {
        case 'post':
            if ($topic_row==null){
                $topic_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE slug='".get_query_var('topic')."' LIMIT 1", ARRAY_A)[0];
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid'], ARRAY_A)[0];
            }

            if ($topic_row['in_trash'] or  $user_row=="guest" and !get_option("neoforum_guests_can_posts")){
                return false;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                or ($topic_row['is_closed']==false
                    and ($topic_row['is_approved']==true or get_option("neoforum_topics_need_approving")!="on")
                    and $forum_row['is_closed']==false
                    and ($forum_row['is_restricted']==false 
                        or neoforum_is_user_can_read($forum_row, $user_row)))) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'edit':
            if ($topic_row==null){
                $topic_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$post_row['topicid']." LIMIT 1", ARRAY_A)[0];
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
            }

            if ($user_row=="guest"){
                return false;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                or ($user_row['userid']==$post_row['authorid'] 
                    and $topic_row['is_closed']==false
                    and $forum_row['is_closed']==false
                    and $topic_row['is_approved']==true
                    and ($forum_row['is_restricted']==false 
                        or neoforum_is_user_can_read($forum_row, $user_row)))) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'delete':
            if ($topic_row==null){
                $topic_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$post_row['topicid']." LIMIT 1", ARRAY_A)[0];
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
            }

            if ($user_row=="guest"){
                return false;
            }
            if (current_user_can('administrator')
                or $user_row['user_caps']=='administrator' 
                or $user_row['user_caps']=='supermoderator'
                or neoforum_is_user_moder($forum_row, $user_row)
                ) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'report':
            if ($topic_row==null){
                $topic_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$post_row['topicid']." LIMIT 1", ARRAY_A)[0];
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
            }

            if ($user_row=="guest"){
                return false;
            }
            if (!current_user_can('administrator')
                and $user_row['user_caps']!='administrator' 
                and $user_row['user_caps']!='supermoderator'
                and !neoforum_is_user_moder($forum_row, $user_row)
                and $post_row['authorid']!=$user_row['userid']
                ) {
                return true;
            }
            else{
                return false;
            }
            break;
        case 'upload':
            if ($topic_row==null){
                $topic_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$post_row['topicid']." LIMIT 1", ARRAY_A)[0];
            }
            if ($forum_row==null){
                $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
            }

            if ($user_row=="guest"){
                return false;
            }
            if (get_option("neoforum_can_upload")=="on" or current_user_can('administrator')
                or $user_row['user_caps']!='administrator' 
                ) {
                return true;
            }
            else{
                return false;
            }
            break;

    }
}

function neoforum_is_user_can_user($ability, $point_user_row, $user_row=null) {
    global $wpdb;
    if($user_row==null)
        $user_row=neoforum_get_user_by_id(get_current_user_id());
    switch ($ability) {
        case 'ban':
            if (user_can($point_user_row['userid'], 'administrator') or $point_user_row['userid']==$user_row['userid'] or $point_user_row['user_caps']=='administrator' or $point_user_row['user_caps']=='supermoderator' or neoforum_is_user_banned($point_user_row['userid']))
                return false;
            if (current_user_can('administrator') or $user_row['user_caps']=='administrator' or $user_row['user_caps']=='supermoderator'){
            return true;
            }
            $forums=$wpdb->get_results("SELECT moderators FROM ".$wpdb->prefix."neoforum_forums", ARRAY_A);
            foreach ($forums as $f) {
                if (preg_match('/(^|;)'.$user_row['userid'].';/', $f['moderators'])==1){
                    return true;
                }
            } 
            return false;     
            break;
        case 'unban':
            if (current_user_can('administrator') or $user_row['user_caps']=='administrator'){
            return true;
            }
            if ($point_user_row['banned_by']!=$user_row['userid']) 
                return false;     
            break;
        
        case 'make moder':
            if (current_user_can('administrator') or $user_row['user_caps']=='administrator'){
                return true;
            }
            return false;     
            break;
        }
}

function neoforum_is_user_moder($forum_row, $user_row=null){
    global $wpdb;
    if (gettype($forum_row)=='integer'){
        $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$forum_row." LIMIT 1", ARRAY_A);
    }
    if (gettype($forum_row)=='string'){
        $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".$forum_row."'' LIMIT 1", ARRAY_A);
    }
    if ($user_row==null){
        $user_row=neoforum_get_user_by_id(get_current_user_id());
    }
    if (user_can($user_row['userid'], 'administrator') or $user_row['user_caps']=='administrator' or $user_row['user_caps']=='supermoderator'){
        return true;
    }
    do{
        if(preg_match('/(^|;)'.$user_row['userid'].';/', $forum_row['moderators'])==1){
            return true;
        }
        $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$forum_row['parent_forum']." LIMIT 1", ARRAY_A)[0];
    }while($forum_row!=null);
    return false;

}

function neoforum_is_user_can_read($forum_row, $user_row=null){
    global $wpdb;
    if ($user_row==null){
        $user_row=neoforum_get_user_by_id(get_current_user_id());
    }
    do{
        if(preg_match('/(^|;)'.$user_row['userid'].';/', $forum_row['can_read'])!=1 and $forum_row['is_restricted']){
            return false;
        }
        $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$forum_row['parent_forum']." LIMIT 1", ARRAY_A)[0];
    }while($forum_row!=null);
    return true;

}

function neoforum_is_guest_can_read($forum_row){
    global $wpdb;
    do{
        if($forum_row['is_restricted']){
            return false;
        }
        $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$forum_row['parent_forum']." LIMIT 1", ARRAY_A)[0];
    }while($forum_row!=null);
    return true;

}

function neoforum_is_user_banned($userid){
    if ($userid==0){
        return false;
    }
    global $wpdb;
    $res=$wpdb->get_var("SELECT IFNULL(ban>CONVERT('".current_time("mysql")."', DATETIME), 0) FROM ".$wpdb->prefix."neoforum_users WHERE userid=".esc_sql($userid)." LIMIT 1");
    if ($res){
        return true;
    }
    else
        return false;
}


function neoforum_save_contact_handler(){
    $userid=get_current_user_id();
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_save_contact' ) and ($userid==$_POST['userid'] or current_user_can('administrator') or neoforum_get_user_by_id($userid)['user_caps']=='administrator')){
        global $wpdb;
        $http=substr($_POST['value'], 0, 4)!='http' ? 'https://' : '';
        switch ($_POST['type']) {
            case 'facebook':
                if (preg_match('/(^(https:\/\/|http:\/\/)?(www\.)?(m\.)?facebook\.com(\/|$).*?|^$)/', $_POST['value'])){
                    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET ".($_POST['type']."='".$http.esc_sql($_POST['value'])."' WHERE userid=".esc_sql($_POST['userid'])));
                    $reply=array(
                    'result' => true);
                } else{
                    $reply=array(
                    'result' => false,
                    'message' => esc_html__("Wrong contact format! Use only valid links!", "neoforum"));
                }
                break;
            case 'twitter':
                if (preg_match('/(^(https:\/\/|http:\/\/)?(www\.)?(m\.)?twitter\.com(\/|$).*?|^$)/', $_POST['value'])){
                    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET ".($_POST['type']."='".$http.esc_sql($_POST['value'])."' WHERE userid=".esc_sql($_POST['userid'])));
                    $reply=array(
                    'result' => true);
                } else{
                    $reply=array(
                    'result' => false,
                    'message' => esc_html__("Wrong contact format! Use only valid links!", "neoforum"));
                }
                break;
            case 'instagram':
                if (preg_match('/(^(https:\/\/|http:\/\/)?(www\.)?(m\.)?instagram\.com(\/|$).*?|^$)/', $_POST['value'])){
                    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET ".($_POST['type']."='".$http.esc_sql($_POST['value'])."' WHERE userid=".esc_sql($_POST['userid'])));
                    $reply=array(
                    'result' => true);
                } else{
                    $reply=array(
                    'result' => false,
                    'message' => esc_html__("Wrong contact format! Use only valid links!", "neoforum"));
                }
                break;
            
            default:
                $reply=array(
                'result' => false,
                'message' => esc_html__("Invalid contact type!", "neoforum"));
                break;
        }

        
        echo(json_encode($reply));
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_save_contact', 'neoforum_save_contact_handler');

function neoforum_save_avatar_handler(){
    $userid=get_current_user_id();
    if (!neoforum_is_user_banned($userid) and wp_verify_nonce( $_POST['nonce'], 'neoforum_save_avatar' ) and ($userid==$_POST['userid'] or current_user_can('administrator') or neoforum_get_user_by_id($userid)['user_caps']=='administrator')){
        $fullname=sanitize_file_name($_FILES['avatar']['name']);
        global $wpdb;
        $allowed_types=array(
            'jpg|jpeg|jpe'                 => 'image/jpeg',
            'gif'                          => 'image/gif',
            'png'                          => 'image/png');
        $righttype=false;
        foreach ($allowed_types as $key => $value) {
            if(preg_match('/^.*?\.('.$key.')$/i', $fullname)){
                $righttype=true;
                break;
            }
        }
        if (!$righttype){
            $reply=array(
            'result' => false,
            'data' => esc_html__("File type is forbidden!","neoforum")." ".$_FILES['avatar']['name']);
            echo(json_encode($reply));
            wp_die();
        }
        $maxsize=100000;
        if($_FILES['avatar']['size']>$maxsize){
            $reply=array(
            'result' => false,
            'message' => printf(esc_html__("File size must be less then %d bytes","neoforum"), $maxsize)." ".$_FILES['avatar']['name']);
            echo(json_encode($reply));
            wp_die();
        }
        wp_mkdir_p(neoforum_DIR."\\nf-userdata\avatars");
        wp_mkdir_p(neoforum_DIR."\\nf-userdata\avatars\\".$_POST['userid']);
        $files = glob(neoforum_DIR."\\nf-userdata\\avatars\\".$_POST['userid']."\\*");
        foreach($files as $file){ 
          if(is_file($file))
            unlink($file);
        }
        preg_match('/(^.*?)\.([a-z]{1,10}$)/i', $fullname, $m);
        $name=$m[1];
        $ext=$m[2];
        move_uploaded_file($_FILES['avatar']['tmp_name'], neoforum_DIR."\\nf-userdata\avatars\\".$userid."\\avatar.".$ext);
        $reply=array(
        'result' => true,
        'data' => neoforum_URL."/nf-userdata/avatars/".$userid."/avatar.".$ext);
        echo(json_encode($reply));
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_save_avatar', 'neoforum_save_avatar_handler');

function neoforum_delete_avatar_handler(){
    $userid=get_current_user_id();
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_delete_avatar' ) and ($userid==$_POST['userid'] or current_user_can('administrator') or neoforum_get_user_by_id($userid)['user_caps']=='administrator')){
        $files = glob(neoforum_DIR."\\nf-userdata\\avatars\\".$_POST['userid']."\\*");
        foreach($files as $file){ 
            unlink($file);
        }
        $reply=array(
        'result' => true);
        echo(json_encode($reply));
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_delete_avatar', 'neoforum_delete_avatar_handler');

function neoforum_save_usercaption_handler(){
    $userid=get_current_user_id();
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_save_usercaption' ) and ($userid==$_POST['userid'] or current_user_can('administrator') or neoforum_get_user_by_id($userid)['user_caps']=='administrator')){
        $caption=neoforum_clean_post_content($_POST['value']);
        $maxlen=512;
        $len=strlen($caption);
        if ($len>$maxlen){
            $reply=array(
            'result' => false,
            'data' => esc_html__("Your usercaption is $len characters. Max length is $maxlen characters", "neoforum"));
            echo(json_encode($reply));
            wp_die();
        }
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET user_caption='".$caption."' WHERE userid=".esc_sql($_POST['userid']));
        $reply=array(
        'result' => true,
        'data' => $caption);
        echo(json_encode($reply));
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_save_usercaption', 'neoforum_save_usercaption_handler');

?>
