<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


function neoforum_set_forum_page(){//creates and updates page to display forum
    $new_url=get_option("neoforum_forum_url");
    $old_url=get_option("neoforum_old_forum_page");
    if (!neoforum_the_slug_exists($new_url)){
        if(neoforum_the_slug_exists($old_url)){
            $menu_order=get_page_by_path( $old_url, OBJECT, 'page' ) -> menu_order;
         }
         else{
            $menu_order=0;
         }
        wp_insert_post(array(
            'post_name' => $new_url,
            'post_content' => '[neoforum]',
            'post_title' => 'Forum',
            'post_type' => 'page',
            'comment_status' => 'closed',
            'post_status' => 'publish',
            'menu_order' => $menu_order

        ));
  
        if(neoforum_the_slug_exists($old_url) and ($new_url!=$old_url)){
            wp_delete_post(get_page_by_path( $old_url, OBJECT, 'page' ) -> ID, True);
            flush_rewrite_rules();
        }
        update_option("neoforum_old_forum_page", $new_url);
    }
}

function neoforum_the_slug_exists($slug) { //check is the slug exists
    $post = get_page_by_path( $slug, OBJECT, 'page' );
    if($post!=null) {
        return true;
    } else {
        return false;
    }

}
function neoforum_get_themes_list(){//scanning theme directory and returns child ditectories list
    $scanned=scandir(neoforum_DIR."/nf-themes");
    $result = array();
    foreach ($scanned as $value) {
        if (is_dir(neoforum_DIR."/nf-themes/".$value) and $value!="." and $value!=".."){
            $result[]=$value;
        }
    }
    return $result;
}

function neoforum_forums_order_handler(){
    global $wpdb;
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_admin_edit_forums')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $ans=json_decode(stripslashes($_POST['data']));
    foreach ($ans->neoforum_orders as $id => $value) {
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET ord=$value WHERE forumid=$id");
    }
    foreach ($ans->neoforum_parents as $id => $value) {
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET parent_forum=$value WHERE forumid=$id");
    }
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_order_commit", "neoforum_forums_order_handler");


function neoforum_forum_close_handler(){
    global $wpdb;
    $id=$_POST['forumid'];

    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_admin_option_change')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if (!is_numeric($id))
    {
        $res=array("result"=>false, "message"=>__("Wrong forum id!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    if (!neoforum_is_forum_exists($id))
    {
        $res=array("result"=>false, "message"=>__("Forum does not exists!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $forum=$wpdb->get_results("SELECT is_closed FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=$id", ARRAY_A)[0];
    if($forum['is_closed']){
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET is_closed=false WHERE forumid=$id");
        $res=array("result"=>true, "locked"=>false);
        echo json_encode($res);
    }
    else{
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET is_closed=true WHERE forumid=$id");
        $res=array("result"=>true, "locked"=>true);
        echo json_encode($res);
    }
    wp_die();
}

add_action("wp_ajax_neoforum_close_forum", "neoforum_forum_close_handler");

function neoforum_forum_restrict_handler(){
    global $wpdb;
    $id=$_POST['forumid'];

    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_admin_option_change'))  {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if (!is_numeric($id))
    {
        $res=array("result"=>false, "message"=>__("Wrong forum id!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    if (!neoforum_is_forum_exists($id))
    {
        $res=array("result"=>false, "message"=>__("Forum does not exists!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $forum=$wpdb->get_results("SELECT is_restricted FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=$id", ARRAY_A)[0];
    if($forum['is_restricted']){
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET is_restricted=false WHERE forumid=$id");
        $res=array("result"=>true, "restricted"=>false);
        echo json_encode($res);
    }
    else{
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET is_restricted=true WHERE forumid=$id");
        $res=array("result"=>true, "restricted"=>true);
        echo json_encode($res);
    }
    wp_die();
}

add_action("wp_ajax_neoforum_restrict_forum", "neoforum_forum_restrict_handler");

function neoforum_forum_delete_handler(){
    global $wpdb;
    $id=$_POST['forumid'];

    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_admin_option_change')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if (!is_numeric($id))
    {
        $res=array("result"=>false, "message"=>__("Wrong forum id!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    if (!neoforum_is_forum_exists($id))
    {
        $res=array("result"=>false, "message"=>__("Forum does not exists!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $forum=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=$id", ARRAY_A)[0];
    $topics=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE forumid=$id", ARRAY_A);
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET parent_forum=".$forum['parent_forum']." WHERE parent_forum=$id");
    foreach ($topics as $topic) {
        $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_posts WHERE topicid=".$topic['topicid']);
    }
    $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_topics WHERE forumid=$id");
    $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=$id");
    $res=array("result"=>true, "restricted"=>false);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_delete_forum", "neoforum_forum_delete_handler");

function neoforum_create_forum_handler(){
    global $wpdb;
    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_admin_option_change')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    neoforum_create_forum(esc_html__("New forum", "neoforum"), esc_html__("Description", "neoforum"), false);
    
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_create_forum", "neoforum_create_forum_handler");

function neoforum_recalculate_forums_handler(){
    global $wpdb;
    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_recalculate_forums')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums AS f SET topics_num=(SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_topics WHERE forumid=f.forumid AND in_trash=0), posts_num=(SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_posts WHERE forumid=f.forumid AND in_trash=0)");
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics AS t SET posts_num=(SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_posts WHERE topicid=t.topicid AND in_trash=0)");

    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_recalculate_forums", "neoforum_recalculate_forums_handler");

function neoforum_recalculate_users_handler(){
    global $wpdb;
    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_recalculate_users')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users AS f SET posts_num=(SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_posts WHERE authorid=f.userid AND in_trash=0)");

    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_recalculate_users", "neoforum_recalculate_users_handler");

function neoforum_create_section_handler(){
    global $wpdb;
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_admin_option_change') ) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    neoforum_create_forum(esc_html__("New section", "neoforum"), "", true);
    
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_create_section", "neoforum_create_section_handler");

function neoforum_edit_title_handler(){
    global $wpdb;
    $title=sanitize_text_field($_POST['data']);
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_admin_option_change')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if (strlen($title)<1)  {
        $res=array("result"=>false, "message"=>__("Title is empty!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    if (strlen($title)>256)  {
        $res=array("result"=>false, "message"=>__("Title is too long, max length is 256 characters!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET forum_name='".neoforum_topic_slug_gen($title)."' WHERE forumid=".esc_sql($_POST['forumid']));
    $res=array("result"=>true, "data"=>$title);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_edit_forum_title", "neoforum_edit_title_handler");

function neoforum_edit_descr_handler(){
    global $wpdb;
    $title=sanitize_textarea_field($_POST['data']);
    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_admin_option_change')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if (strlen($title)>1024)  {
        $res=array("result"=>false, "message"=>__("Description is too long, max length is 1024 characters!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET forum_descr='$title' WHERE forumid=".esc_sql($_POST['forumid']));
    $res=array("result"=>true, "data"=>$title);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_edit_forum_descr", "neoforum_edit_descr_handler");

function neoforum_get_users_handler(){
    global $wpdb;
    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_admin_get_moders')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if(!is_numeric($_POST['id'])){
        $res=array("result"=>false, "message"=>__("Wrong forum id!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if(!neoforum_is_forum_exists($_POST['id'])){
        $res=array("result"=>false, "message"=>__("Forum does not exists!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $moders=neoforum_return_users_list($_POST['id'], $_POST['type']);
    $data="";
    foreach ($moders as $val) {
        $user=get_user_by( "id", $val);
        $data.="<div class='neoforum_user_item neoforum_clearfix' data-username='".$user->display_name."'>".$user->display_name."<button class='neoforum_delete_moder_button' title='".esc_html__("Remove this user from moderators","neoforum")."' onclick='neoforum_delete_user(".$_POST['id'].", ".$val.", \"".$_POST['type']."\", \"".wp_create_nonce("neoforum_delete_user")."\", this)'></button></div>";
    }
    
    $res=array("result"=>true, "data"=>$data);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_get_moderators", "neoforum_get_users_handler");
add_action("wp_ajax_neoforum_get_can_read", "neoforum_get_users_handler");

function neoforum_delete_user_handler(){
    global $wpdb;
    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_delete_user')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if(!is_numeric($_POST['forumid'])){
        $res=array("result"=>false, "message"=>__("Wrong forum id!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if(!is_numeric($_POST['userid'])){
        $res=array("result"=>false, "message"=>__("Wrong user id!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if(!neoforum_is_forum_exists($_POST['forumid'])){
        $res=array("result"=>false, "message"=>__("Forum does not exists!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $moders=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$_POST['forumid'], ARRAY_A)[0][$_POST['type']];
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET ".$_POST['type']."='".str_replace($_POST['userid'].';', '', $moders)."' WHERE forumid=".$_POST['forumid']);
    
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_delete_moderators", "neoforum_delete_user_handler");
add_action("wp_ajax_neoforum_delete_can_read", "neoforum_delete_user_handler");

function neoforum_search_user($type){
    if (empty($_POST['data'])){
        $res=array("result"=>true, "data"=>__("No users found","neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_search_moder')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $moders=neoforum_return_users_list($_POST['forumid'], $type);
    $users=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."users WHERE display_name LIKE '%".esc_sql($_POST['data'])."%'", ARRAY_A);
    $data="";
    foreach ($users as $user) {
        //echo($user->ID." ".$moders[0]);
        if (in_array($user['ID'], $moders)) continue;
        $data.="<button class='neoforum_search_users_item' onclick='neoforum_add_user(".$user['ID'].", neoforum_currentforum, \"".$type."\", \"".wp_create_nonce("neoforum_add_user")."\", \"".wp_create_nonce('neoforum_admin_get_moders')."\", this);'>".str_ireplace($_POST['data'], '<b>'.$_POST['data'].'</b>', $user['display_name'])."</button>";
    }
    $res=array("result"=>true, "data"=>$data);
    echo json_encode($res);
    wp_die();
}
function neoforum_search_moder_handler(){
    neoforum_search_user('moderators');
}

function neoforum_search_can_read_handler(){
    neoforum_search_user('can_read');
}

add_action("wp_ajax_neoforum_search_moderators", "neoforum_search_moder_handler");
add_action("wp_ajax_neoforum_search_can_read", "neoforum_search_can_read_handler");

function neoforum_return_users_list($id, $column_name){
    global $wpdb;
    if (gettype($id)=="integer" or gettype($id)=="string"){
        $list=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=$id", ARRAY_A)[0][$column_name];
    } else{
        $list=$id[$column_name];
    }
    preg_match_all('/(?<=^|;)[^;]*?(?=;)/', $list, $res);
    return $res[0];
}

function neoforum_display_users_of_forum($forumid, $column_name){
    $ids=neoforum_return_users_list($forumid, $column_name);
    $res="";
    foreach ($ids as $id){
        $res.="<span class='neoforum_user_of_forum_item'>".get_userdata($id)->display_name."</span>, ";
    }
    echo( substr($res, 0, -2));
}

function neoforum_add_user_to_list($type){
    global $wpdb;
    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_add_user')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if(!is_numeric($_POST['forumid'])){
        $res=array("result"=>false, "message"=>__("Wrong forum id!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if(!is_numeric($_POST['userid'])){
        $res=array("result"=>false, "message"=>__("Wrong user id!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if(!neoforum_is_forum_exists($_POST['forumid'])){
        $res=array("result"=>false, "message"=>__("Forum does not exists!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }

    if(!neoforum_is_user_exists($_POST['userid'])){
        $res=array("result"=>false, "message"=>__("User does not exists!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $moders=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$_POST['forumid'], ARRAY_A)[0][$type];
    if(preg_match('/(?<=^|;)'.$_POST['userid'].'(?=;)/',$moders)){
        $res=array("result"=>false, "message"=>__("User already in list!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    neoforum_add_user($_POST['userid']);
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET ".$type."='".$moders.$_POST['userid'].";' WHERE forumid=".$_POST['forumid']);
    
    $res=array("result"=>true);
    neoforum_calculate_reports_for_user(esc_sql($_POST['userid']));
    echo json_encode($res);
    wp_die();
}

function neoforum_add_moder_handler(){
    neoforum_add_user_to_list("moderators");
}

add_action("wp_ajax_neoforum_add_moderators", "neoforum_add_moder_handler");

function neoforum_add_can_read_handler(){
    neoforum_add_user_to_list("can_read");
}

add_action("wp_ajax_neoforum_add_can_read", "neoforum_add_can_read_handler");

function neoforum_forum_display($forum, $forums){
    if ($forum['is_section']){
        ?> <div class="neoforum_section" data-id="<?php echo($forum['forumid']); ?>" data-nonce="<?php echo(wp_create_nonce("neoforum_admin_edit_forums")); ?>"> 
            <div class="neoforum_forum_title_wrap">
                <div class="neoforum_section_title">
                    <button class="neoforum_forum_edit_button" title="<?php esc_html_e("Edit section title", "neoforum"); ?>" onclick="neoforum_forum_text_editor(event)"></button>
                    <button class="neoforum_forum_edit_button_save" title="<?php esc_html_e("Edit section title", "neoforum"); ?>" onclick="neoforum_forum_option_change(event, <?php echo(esc_html($forum['forumid']).", '".wp_create_nonce('neoforum_admin_option_change')."'") ?>, 'neoforum_edit_forum_title', this.nextElementSibling.value)" style="display: none;"></button>
                    <input type="text" maxlength="256" class="neoforum_forum_editor" data-id="<?php echo($forum['forumid']); ?>">
                    <span><?php echo(esc_html($forum['forum_name'])); ?></span>
                </div>
            </div>
            <div class="neoforum_forum_controls">
                <button class="neoforum_forum_control_close <?php if($forum['is_closed']) echo('neoforum_forum_control_close_locked'); ?>" onclick="neoforum_forum_option_change(event, <?php echo(esc_html($forum['forumid']).", '".wp_create_nonce('neoforum_admin_option_change')."'") ?>, 'neoforum_close_forum')">
                </button>
                <button class="neoforum_forum_control_restrict <?php if($forum['is_restricted']) echo('neoforum_forum_control_restrict_true'); ?>" onclick="neoforum_forum_option_change(event, <?php echo(esc_html($forum['forumid']).", '".wp_create_nonce('neoforum_admin_option_change')."'") ?>, 'neoforum_restrict_forum')">
                </button>
                <button class="neoforum_forum_control_delete" onclick="neoforum_forum_option_change(event, <?php echo(esc_html($forum['forumid']).", '".wp_create_nonce('neoforum_admin_option_change')."'") ?>, 'neoforum_delete_forum')">
                </button>
            </div>
             <div class="neoforum_forum_moders">
                <div class="neoforum_moderators" onclick="neoforum_users_change(<?php echo($forum['forumid']) ?>, 'moderators', '<?php echo(wp_create_nonce('neoforum_admin_get_moders')); ?>');">
                    <?php esc_html_e("Moderators", "neoforum"); ?>: 
                    <span class="neoforum_moderators_list">
                    <?php neoforum_display_users_of_forum($forum['forumid'], 'moderators'); ?>
                    </span>
                </div>
                <div class="neoforum_can_read" onclick="neoforum_users_change(<?php echo($forum['forumid']) ?>, 'can_read', '<?php echo(wp_create_nonce('neoforum_admin_get_moders')); ?>');">
                    <?php esc_html_e("Users who can read restricted forum", "neoforum"); ?>: 
                    <span class="neoforum_can_read_list">
                    <?php neoforum_display_users_of_forum($forum['forumid'], 'can_read'); ?>
                    </span>
                </div>
            </div>
            <div class="neoforum_subforums">
                <?php _e("Subforums", "neoforum"); ?>:
                <div class='neoforum_forum_drag_place'></div>
        <?php
    } else{
        ?> <div class="neoforum_forum" data-id="<?php echo($forum['forumid']); ?>" data-nonce="<?php echo(wp_create_nonce("neoforum_admin_edit_forums")); ?>">
            <div class="neoforum_forum_title_wrap">
                <div class="neoforum_section_title">
                    <button class="neoforum_forum_edit_button" title="<?php esc_html_e("Edit section title", "neoforum"); ?>" onclick="neoforum_forum_text_editor(event)"></button>
                    <button class="neoforum_forum_edit_button_save" title="<?php esc_html_e("Edit section title", "neoforum"); ?>" onclick="neoforum_forum_option_change(event, <?php echo(esc_html($forum['forumid']).", '".wp_create_nonce('neoforum_admin_option_change')."'") ?>, 'neoforum_edit_forum_title', this.nextElementSibling.value)" style="display: none;"></button>
                    <input type="text" maxlength="256" class="neoforum_forum_editor" data-id="<?php echo($forum['forumid']); ?>">
                    <span><?php echo(esc_html($forum['forum_name'])); ?></span>
                </div>
                <div class="neoforum_section_descr">
                    <button class="neoforum_forum_edit_button" title="<?php esc_html_e("Edit section title", "neoforum"); ?>" onclick="neoforum_forum_text_editor(event)"></button>
                    <button class="neoforum_forum_edit_button_save" title="<?php esc_html_e("Edit section title", "neoforum"); ?>" onclick="neoforum_forum_option_change(event, <?php echo(esc_html($forum['forumid']).", '".wp_create_nonce('neoforum_admin_option_change')."'") ?>, 'neoforum_edit_forum_descr', this.nextElementSibling.value)" style="display: none;"></button>
                    <textarea type="text" maxlength="1024" class="neoforum_forum_editor" data-id="<?php echo($forum['forumid']); ?>"></textarea>
                    <span><?php echo(esc_html($forum['forum_descr'])); ?></span>
                </div>
            </div>
            <div class="neoforum_forum_topics">
                <?php 
                    esc_html_e("Topics", "neoforum");
                    echo("<br>".esc_html($forum['topics_num'])); 
                ?>
            </div>
            <div class="neoforum_forum_posts">
                <?php 
                    esc_html_e("Posts", "neoforum");
                    echo("<br>".esc_html($forum['posts_num'])); 
                ?>
            </div>
            <div class="neoforum_forum_controls">
                <button class="neoforum_forum_control_close <?php if($forum['is_closed']) echo('neoforum_forum_control_close_locked'); ?>" onclick="neoforum_forum_option_change(event, <?php echo(esc_html($forum['forumid']).", '".wp_create_nonce('neoforum_admin_option_change')."'") ?>, 'neoforum_close_forum')">
                </button>
                <button class="neoforum_forum_control_restrict <?php if($forum['is_restricted']) echo('neoforum_forum_control_restrict_true'); ?>" onclick="neoforum_forum_option_change(event, <?php echo(esc_html($forum['forumid']).", '".wp_create_nonce('neoforum_admin_option_change')."'") ?>, 'neoforum_restrict_forum')">
                </button>
                <button class="neoforum_forum_control_delete" onclick="neoforum_forum_option_change(event, <?php echo(esc_html($forum['forumid']).", '".wp_create_nonce('neoforum_admin_option_change')."'") ?>, 'neoforum_delete_forum')">
                </button>
            </div>
            <div class="neoforum_forum_moders">
                <div class="neoforum_moderators" onclick="neoforum_users_change(<?php echo($forum['forumid']) ?>, 'moderators', '<?php echo(wp_create_nonce('neoforum_admin_get_moders')); ?>');">
                    <?php esc_html_e("Moderators", "neoforum"); ?>:
                    <span class="neoforum_moderators_list">
                    <?php neoforum_display_users_of_forum($forum['forumid'], 'moderators'); ?>
                    </span>
                </div>
                <div class="neoforum_can_read" onclick="neoforum_users_change(<?php echo($forum['forumid']) ?>, 'can_read', '<?php echo(wp_create_nonce('neoforum_admin_get_moders')); ?>');">
                    <?php esc_html_e("Users who can read restricted forum", "neoforum"); ?>: 
                    <span class="neoforum_can_read_list">
                    <?php neoforum_display_users_of_forum($forum['forumid'], 'can_read'); ?>
                    </span>
                </div>
            </div>
            <?php _e("Subforums", "neoforum"); ?>:
            <div class="neoforum_subforums">
                <div class='neoforum_forum_drag_place'></div>
         <?php
    }
    $subforums=array();
    foreach ($forums as $value) { //form list of subforums
        if ($value['parent_forum']==$forum['forumid']){
            $subforums[]=$value;
        }
    }
    foreach ($subforums as $value) {
        //echo("<div class='neoforum_forum_drag_place'></div>");
        neoforum_forum_display($value, $forums);
    }
    echo("</div></div>
        <div class='neoforum_forum_drag_place'></div>
        ");
}
function neoforum_topic_restore_handler(){
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_trash_topic_restore')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $topic=$wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET in_trash=0 WHERE topicid=".esc_sql($_POST['topicid'])." LIMIT 1");
    $fid=$wpdb->get_results("SELECT forumid, posts_num FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($_POST['topicid'])." LIMIT 1", ARRAY_A)[0];
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num+".$fid['posts_num'].", topics_num=topics_num+1 WHERE forumid=".esc_sql($fid['forumid'])." LIMIT 1");
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_topic_restore", "neoforum_topic_restore_handler");

function neoforum_topic_eradicate_handler(){
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_trash_topic_eradicate')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_posts WHERE topicid=".esc_sql($_POST['topicid']));
    $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($_POST['topicid']));
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_topic_eradicate", "neoforum_topic_eradicate_handler");

function neoforum_post_restore_handler(){
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_trash_post_restore')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $topic=$wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET in_trash=0 WHERE postid=".esc_sql($_POST['postid'])." LIMIT 1");
    $post=get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($_POST['postid'])." LIMIT 1");
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num+1 WHERE topicid=".$post['topicid']." LIMIT 1");
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num+1 WHERE forumid=".$post['forumid']." LIMIT 1");
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_post_restore", "neoforum_post_restore_handler");

function neoforum_post_eradicate_handler(){
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_trash_post_eradicate')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($_POST['postid'])." LIMIT 1");
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_post_eradicate", "neoforum_post_eradicate_handler");

function neoforum_items_restore_handler(){
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_restore_items')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $data=json_decode(stripslashes($_POST['items']), true);
    $type=$data[0]['type'];
    $ans=array();
    foreach ($data as $v) {
        if ($type!=$v['type'])
        {
            $res=array("result"=>false, "message"=>__("Items has diffetent types!", "neoforum"));
            echo json_encode($res);
            wp_die();
        }
    }
    if ($type=="topic"){
        foreach ($data as $v) {
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET in_trash=0 WHERE topicid=".esc_sql($v['id'])." AND in_trash=1 LIMIT 1");
            $fid=$wpdb->get_results("SELECT forumid, posts_num FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($v['id'])."LIMIT 1", ARRAY_A)[0];
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num+".$fid['posts_num'].", topics_num=topics_num+1 WHERE forumid=".esc_sql($fid['forumid'])." LIMIT 1");
            $ans[]=$v['id'];
        }
    }
    if ($type=="post"){
        foreach ($data as $v) {
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET in_trash=0 WHERE postid=".esc_sql($v['id'])." AND in_trash=1 LIMIT 1");
            $post=get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($v['id'])." LIMIT 1", ARRAY_A)[0];
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num+1 WHERE topicid=".$post['topicid']." LIMIT 1");
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num+1 WHERE forumid=".$post['forumid']." LIMIT 1");
            $ans[]=$v['id'];
        }
    }
    $res=array("result"=>true, "data"=>$ans);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_items_restore", "neoforum_items_restore_handler");

function neoforum_items_eradicate_handler(){
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_eradicate_items')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $data=json_decode(stripslashes($_POST['items']), true);
    $type=$data[0]['type'];
    $ans=array();
    foreach ($data as $v) {
        if ($type!=$v['type'])
        {
            $res=array("result"=>false, "message"=>__("Items has diffetent types!", "neoforum"));
            echo json_encode($res);
            wp_die();
        }
    }
    if ($type=="topic"){
        foreach ($data as $v) {
            $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($v['id'])." AND in_trash=1 LIMIT 1");
            $ans[]=$v['id'];
        }
    }
    if ($type=="post"){
        foreach ($data as $v) {
            $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($v['id'])." AND in_trash=1 LIMIT 1");
            $ans[]=$v['id'];
        }
    }
    $res=array("result"=>true, "data"=>$ans);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_items_eradicate", "neoforum_items_eradicate_handler");

function neoforum_delete_all_trash_handler(){
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_delete_all_trash')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_topics WHERE in_trash=1");
    $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_posts WHERE in_trash=1");
    $res=array("result"=>true, "message"=>__("Done", "neoforum"));
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_delete_all_trash", "neoforum_delete_all_trash_handler");

function neoforum_reports_render($page=1){
    global $wpdb;
    $pagelen=15;
    $user=neoforum_get_user_by_id(get_current_user_id());
    $pagintaion="";

    $cond="(SELECT COUNT(user_id) FROM ".$wpdb->prefix."usermeta WHERE meta_value LIKE '%administrator%' AND user_id=".$user['userid'].")>0 OR (SELECT COUNT(userid) FROM(SELECT userid FROM ".$wpdb->prefix."neoforum_users WHERE user_caps='administrator' OR user_caps='supermoderator')tmp)>0";
    $if_admin="(SELECT *, @rows:=@rows+1 FROM ".$wpdb->prefix."neoforum_reports WHERE solved=0)";
    $if_moder="(SELECT *, @rows:=@rows+1 FROM ".$wpdb->prefix."neoforum_reports WHERE solved=0 AND forumid IN (SELECT forumid FROM ".$wpdb->prefix."neoforum_forums WHERE moderators LIKE '%;".$user['userid'].";%' OR moderators LIKE '".$user['userid'].";%'))";

    $wpdb->query("SET @rows=0");

    $reports=$wpdb->get_results("SELECT *, @rows:=@rows+1 FROM ".$wpdb->prefix."neoforum_reports WHERE solved=0 AND IF(".$cond.", TRUE, forumid IN (SELECT forumid FROM ".$wpdb->prefix."neoforum_forums WHERE moderators LIKE '%;".$user['userid'].";%' OR moderators LIKE '".$user['userid'].";%'))", ARRAY_A);
    $items_num=count($reports);
    if($items_num%$pagelen==0)
        $pages=intdiv($items_num, $pagelen);
    else
        $pages=intdiv($items_num, $pagelen)+1;
    $first=$pagelen*$page-$pagelen+1;
    foreach ($reports as $r) {
        if ($r['num']<$first AND $r['num']>=$first+$pagelen){
            continue;
        }
        $post=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".$r['postid']." LIMIT 1", ARRAY_A)[0];
        if (is_null($post) or $post['in_trash']==1){
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_reports SET solved=1 WHERE postid=".$r['postid']);
            continue;
        }
        $forum=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$post['forumid']." LIMIT 1", ARRAY_A)[0];
        $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$r['topicid']." LIMIT 1", ARRAY_A)[0];
        $reporter=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."users WHERE ID=".$r['userid']." LIMIT 1", ARRAY_A)[0];
        if (neoforum_is_user_moder($forum, $user)){
            ?>
                <?php
                $data.="<div class='neoforum_trash_post' data-id='".$r['reportid']."'><div class='neoforum_trash_post_controls'><button class='neoforum_repost_leave' onclick='neoforum_report_leave_post(this, ".$r['reportid'].", \"".wp_create_nonce('neoforum_report_leave_post')."\");' title='".esc_html__("Leave post","neoforum")."'></button><button class='neoforum_trash_delete' onclick='neoforum_report_delete_post(this, ".$r['reportid'].", \"".wp_create_nonce('neoforum_report_delete_post')."\");' title='".esc_html__("Delete post","neoforum")."'></button></div><div class='neoforum_trash_post_topic_title'>".esc_html__("In topic","neoforum").": <a href=".get_site_url()."/".get_option('neoforum_forum_url')."/".$forum['slug']."/".$topic['slug'].">".$topic['topic_title']."</a></div>";
                $data.="<div class='neoforum_trash_post_forum'>".esc_html__("In forum","neoforum").": <a class='neoforum_trash_forum' href=".get_site_url()."/".get_option('neoforum_forum_url')."/".$forum['slug'].">".$forum['forum_name']."</a></div><div class='neoforum_trash_post_author'>".esc_html__("Author","neoforum").": ".neoforum_themes::get_user_name($post)."</div><div class='neoforum_trash_post_author'>".esc_html__("Reported by","neoforum").": ".$reporter['display_name']."</div><div class='neoforum_trash_post_dates'><i>".esc_html__("Created","neoforum").": ".$post['creation_date']."; ".esc_html__("Reported","neoforum").": ".$r['date']."</i></br>".esc_html__("Reporter's comment","neoforum").":<div class='neoforum_report_comment'>".$r['comment']."</div>".esc_html__("Post content","neoforum").":<div class='neoforum_trash_post_content'>".$post['content']."</div></div>";
                $data.="</div>";
                ?>
            <?php
        }
        echo $data;
    }
    if ($pages==1)
        return;
    for ($i=1; $i <=$pages ; $i++) { 
        $pagination.="<a href='#' onclick='neoforum_get_reports_page(event, this, $i)'> $i </a>";
    }
    return $pagination;
}

function neoforum_update_reports_count($report_forumid, $num){
    global $wpdb;
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET reports_num=reports_num+".$num." WHERE user_caps='administrator' OR user_caps='supermoderator' OR userid IN (SELECT user_id FROM ".$wpdb->prefix."usermeta WHERE meta_value LIKE '%administrator%') OR LOCATE(userid, (SELECT moderators FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$report_forumid." LIMIT 1))>0");
}

function neoforum_calculate_reports_for_user($userid){
    global $wpdb;
    $cond="(SELECT COUNT(user_id) FROM ".$wpdb->prefix."usermeta WHERE meta_value LIKE '%administrator%' AND user_id=".$userid.")>0 OR (SELECT COUNT(userid) FROM(SELECT userid FROM ".$wpdb->prefix."neoforum_users WHERE user_caps='administrator' OR user_caps='supermoderator')tmp)>0";
    $if_admin="(SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_reports WHERE solved=0)";
    $if_moder="(SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_reports WHERE solved=0 AND forumid IN (SELECT forumid FROM ".$wpdb->prefix."neoforum_forums WHERE moderators LIKE '%;".$userid.";%' OR moderators LIKE '".$userid.";%'))";
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET reports_num=IF(".$cond.", ".$if_admin.", ".$if_moder.") WHERE userid=".$userid." LIMIT 1");
}

function neoforum_report_leave_handler(){
    global $wpdb;
    if (!is_numeric($_POST['reportid'])){
        $res=array("result"=>false, "message"=>"NaN");
        echo json_encode($res);
        wp_die();
    }
    $report=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_reports WHERE reportid=".$_POST['reportid']." LIMIT 1", ARRAY_A)[0];
    $post=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".$report['postid']." LIMIT 1", ARRAY_A)[0];
    $forum=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$post['forumid']." LIMIT 1", ARRAY_A)[0];
    if ( !neoforum_is_user_moder($forum) or !wp_verify_nonce($_POST['nonce'], 'neoforum_report_leave_post')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_reports SET solved=1 WHERE reportid=".esc_sql($_POST['reportid']));
    $res=array("result"=>true);
    neoforum_update_reports_count($post['forumid'], -1);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_report_leave_post", "neoforum_report_leave_handler");

function neoforum_report_delete_handler(){
    global $wpdb;
    if (!is_numeric($_POST['reportid'])){
        $res=array("result"=>false, "message"=>"NaN");
        echo json_encode($res);
        wp_die();
    }
    $report=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_reports WHERE reportid=".$_POST['reportid']." LIMIT 1", ARRAY_A)[0];
    $post=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".$report['postid']." LIMIT 1", ARRAY_A)[0];
    $forum=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forum WHERE forumid=".$post['forumid']." LIMIT 1", ARRAY_A)[0];
    if ( !neoforum_is_user_moder($forum) or !wp_verify_nonce($_POST['nonce'], 'neoforum_report_delete_post')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_reports SET solved=1 WHERE reportid=".esc_sql($_POST['reportid']));
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET in_trash=1, deleted=NOW() WHERE postid=".esc_sql($post['postid']));
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num-1 WHERE topicid=".esc_sql($post['topicid']));
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num-1 WHERE forumid=".esc_sql($post['forumid']));
    $res=array("result"=>true);
    neoforum_update_reports_count($post['forumid'], -1);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_report_delete_post", "neoforum_report_delete_handler");

function neoforum_get_ban_menu_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_get_ban_menu' )){
        $res='<div class="neoforum_choose_ban" data-id="'.$_POST['id'].'">'.esc_html__("Choose date when ban will expire", "neoforum").':<br>
                <input type="date" required><br>
                <textarea placeholder="'.esc_html__("Leave comment", "neoforum").'"></textarea><br>
                <button type="button" onclick="neoforum_ban_user(this, this.parentNode.dataset.id, `'.wp_create_nonce("neoforum_ban_user").'`)">'.esc_html__("Ban user", "neoforum").'</button>
            </div>';
        $reply=array(
        'result' => true,
        'data'=>$res);
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

add_action('wp_ajax_neoforum_get_ban_menu', 'neoforum_get_ban_menu_handler');

function neoforum_ban_user_handler(){
    global $wpdb;
    $point_user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users WHERE userid=".esc_sql($_POST['id'])." LIMIT 1", ARRAY_A)[0];
    if (user_can($_POST['id'], 'administrator') or $point_user['user_caps']=='administrator' or $point_user['user_caps']=='supermoderator'){
        $res=array("result"=>false, "message"=>__("You can't ban administrator or supermoderator!", "neoforum"));
        echo json_encode($res);
        wp_die();}
    if (!neoforum_is_user_can_user('ban', $point_user) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_ban_user')){
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    if ($_POST['ban']==""){
        $res=array("result"=>false, "message"=>__("You must choose date!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET ban=CONVERT('".esc_sql($_POST['ban'])."', DATETIME), banned_by=".get_current_user_id().", ban_comment='".esc_sql(esc_html($_POST['comment']))."' WHERE userid=".esc_sql($_POST['id']));
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_ban_user", "neoforum_ban_user_handler");

function neoforum_unban_user_handler(){
    global $wpdb;
    $point_user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users WHERE userid=".esc_sql($_POST['id'])." LIMIT 1", ARRAY_A)[0];
    if (!user_can($_POST['id'], 'administrator') and !$point_user['user_caps']=='administrator' and $point_user['banned_by']!=get_current_user_id()) {
        $res=array("result"=>false, "message"=>__("You have no rights to unban users banned by other moderators", "neoforum"));
        echo json_encode($res);
        wp_die();}
    if (!neoforum_is_user_can_user('unban', $point_user) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_unban_user')){
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET ban=NULL WHERE userid=".esc_sql($_POST['id']));
    $res=array("result"=>true);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_unban_user", "neoforum_unban_user_handler");

function neoforum_make_admin_handler(){
    global $wpdb;
    $point_user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users WHERE userid=".esc_sql($_POST['id'])." LIMIT 1", ARRAY_A)[0];
    if (!neoforum_is_user_can_user('make moder', $point_user) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_make_admin')){
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    if ($_POST['type']=="admin"){
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET user_caps='administrator', ban=NULL WHERE userid=".esc_sql($_POST['id']));
    }
    else{
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET user_caps='supermoderator', ban=NULL WHERE userid=".esc_sql($_POST['id']));
    }
    $res=array("result"=>true);
    neoforum_calculate_reports_for_user(esc_sql($_POST['id']));
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_make_admin", "neoforum_make_admin_handler");

function neoforum_remove_admin_handler(){
    global $wpdb;
    $point_user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users WHERE userid=".esc_sql($_POST['id'])." LIMIT 1", ARRAY_A)[0];
    if (user_can($_POST['id'], 'administrator')){
        $res=array("result"=>false, "message"=>__("This user is site administrator, he must be removed from site admins to take off his admin capabilities on forum", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    if (!neoforum_is_user_can_user('make moder', $point_user) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_remove_admin')){
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET user_caps='registered' WHERE userid=".esc_sql($_POST['id']));
    $res=array("result"=>true);
    neoforum_calculate_reports_for_user(esc_sql($_POST['id']));
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_remove_admin", "neoforum_remove_admin_handler");

function neoforum_search($paramsarray){
    global $wpdb;
    $res=array();
    if ($paramsarray['searchtype']=="topics"){
        $username="SELECT ID FROM ".$wpdb->prefix."users WHERE display_name LIKE '%".esc_sql($paramsarray['username'])."%'";
        $topic_post="SELECT topicid FROM ".$wpdb->prefix."neoforum_posts WHERE is_first=1 AND forumid=IFNULL(".esc_sql($paramsarray['forum']).", forumid) AND content LIKE '%".esc_sql($paramsarray['word_filter'])."%'";
        $topic="SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE in_trash=".esc_sql($paramsarray['trash'])." AND topic_title LIKE '%".esc_sql($paramsarray['title_filter'])."%' AND forumid=IFNULL(".esc_sql($paramsarray['forum']).", forumid) AND (authorid IN (".$username.") OR (authorname<>'' AND authorname LIKE '%".esc_sql($paramsarray['username'])."%')) AND creation_date>=IF('".esc_sql($paramsarray['date_after'])."'='', creation_date, CONVERT('".esc_sql($paramsarray['date_after'])."', DATETIME)) AND creation_date<=IF('".esc_sql($paramsarray['date_before'])."'='', creation_date, CONVERT('".esc_sql($paramsarray['date_before'])."', DATETIME)) AND topicid IN (".$topic_post.") ORDER BY deleted ASC, creation_date ASC";
        $topics=$wpdb->get_results($topic, ARRAY_A);
        $counter=0;
        $user=neoforum_get_user_by_id(get_current_user_id());
        $moder=neoforum_is_user_moder($forum, $user);
        foreach ($topics as $t) {
            $forum=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$t['forumid']." LIMIT 1", ARRAY_A)[0];
            if (neoforum_is_user_can_topic('read', $t, $forum, $user) or ($paramsarray['trash'] and $moder)){
                $counter+=1;
                $t['forum_slug']=$forum['slug'];
                $t['forum_name']=$forum['forum_name'];
                $t['moder']=$moder;
                $res[]=$t;
                if ($counter>=20){
                    break;
                }
            }
        }
    }
    if ($paramsarray['searchtype']=="posts"){
        $username="SELECT ID FROM ".$wpdb->prefix."users WHERE display_name LIKE '%".esc_sql($paramsarray['username'])."%'";
        $post="SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE in_trash=".esc_sql($paramsarray['trash'])." AND forumid=IFNULL(".esc_sql($paramsarray['forum']).", forumid) AND content LIKE '%".esc_sql($paramsarray['word_filter'])."%' AND  (authorid IN (".$username.") OR (authorname<>'' AND authorname LIKE '%".esc_sql($paramsarray['username'])."%')) AND creation_date>=IF('".esc_sql($paramsarray['date_after'])."'='', creation_date, CONVERT('".esc_sql($paramsarray['date_after'])."', DATETIME)) AND creation_date<=IF('".esc_sql($paramsarray['date_before'])."'='', creation_date, CONVERT('".esc_sql($paramsarray['date_before'])."', DATETIME)) ORDER BY deleted ASC, creation_date ASC";
        $posts=$wpdb->get_results($post, ARRAY_A);
        $counter=0;
        $user=neoforum_get_user_by_id(get_current_user_id());
        foreach ($posts as $p) {
            $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$p['topicid']." LIMIT 1", ARRAY_A)[0];
            $forum=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$p['forumid']." LIMIT 1", ARRAY_A)[0];
            $moder=neoforum_is_user_moder($forum, $user);
            if (neoforum_is_user_can_topic('read', $topic, $forum, $user) or ($paramsarray['trash'] and $moder)){
                $counter+=1;
                $p['topic_title']=$topic['topic_title'];
                $p['topic_slug']=$topic['slug'];
                $p['forum_name']=$forum['forum_name'];
                $p['forum_slug']=$forum['slug'];
                $p['moder']=$moder;
                $res[]=$p;
                if ($counter>=30){
                    break;
                }
            }
        }
    }
    return $res;
}

function neoforum_search_handler(){
        if (!wp_verify_nonce($_POST['nonce'], 'neoforum_search')){
            $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
            echo json_encode($res);
            wp_die();
        }
        global $wpdb;
        $cont=neoforum_search($_POST);
        $data="";
        if ($_POST['trash']){
            if (!current_user_can( 'manage_options' ) and !neoforum_get_user_by_id(get_current_user_id())['user_caps']!='administrator'){
                $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
                echo json_encode($res);
                wp_die();
            }
            if ($_POST['searchtype']=="topics"){
                foreach ($cont as $value) {
                    $user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users WHERE userid=".$value['authorid']." LIMIT 1", ARRAY_A)[0];
                    $data.="<div class='neoforum_trash_topic' data-type='topic' data-id='".$value['topicid']."'><input type='checkbox' data-type='topic' value='".$value['topicid']."'><div class='neoforum_trash_topic_title'>".$value['topic_title']."<div class='neoforum_trash_topic_dates'><i>".esc_html__("Created","neoforum").": ".$value['creation_date']."; ".esc_html__("Deleted","neoforum").": ".$value['deleted']."</i></div></div>";
                    $data.="<div class='neoforum_trash_author'>".esc_html__("Author","neoforum").":<br>".neoforum_themes::get_user_name($value)."</div><div class='neoforum_trash_posts_num'>".esc_html__("Posts","neoforum").":<br>".$value['posts_num']."</div>";
                    $data.="<div class='neoforum_trash_forum'>".esc_html__("Was in forum","neoforum").":<br><a class='neoforum_trash_forum' href=".get_site_url()."/".get_option('neoforum_forum_url')."/".$value['forum_slug'].">".$value['forum_name']."</a></div><div class='neoforum_trash_topic_controls'><button onclick='neoforum_trash_topic_restore(this, ".$value['topicid'].", \"".wp_create_nonce('neoforum_trash_topic_restore')."\");'  class='neoforum_trash_restore' title='".esc_html__("Restore topic","neoforum")."'></button><button class='neoforum_trash_delete' onclick='neoforum_trash_topic_eradicate(this, ".$value['topicid'].", \"".wp_create_nonce('neoforum_trash_topic_eradicate')."\");' title='".esc_html__("Delete topic completely","neoforum")."'></button></div></div>";
                    $data.="</div>";
                }
            }
            if ($_POST['searchtype']=="posts"){
                foreach ($cont as $value) {
                    $user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users WHERE userid=".$value['authorid']." LIMIT 1", ARRAY_A)[0];
                    $data.="<div class='neoforum_trash_post' data-id='".$value['postid']."'><input type='checkbox' data-type='post' value='".$value['postid']."'><div class='neoforum_trash_post_controls'><button class='neoforum_trash_restore' onclick='neoforum_trash_post_restore(this, ".$value['postid'].", \"".wp_create_nonce('neoforum_trash_post_restore')."\");' title='".esc_html__("Restore post","neoforum")."'></button><button class='neoforum_trash_delete' onclick='neoforum_trash_post_eradicate(this, ".$value['postid'].", \"".wp_create_nonce('neoforum_trash_post_eradicate')."\");' title='".esc_html__("Delete post completely","neoforum")."'></button></div><div class='neoforum_trash_post_topic_title'>".esc_html__("Was in topic","neoforum").": <a href=".get_site_url()."/".get_option('neoforum_forum_url')."/".$value['forum_slug']."/".$value['topic_slug'].">".$value['topic_title']."</a></div>";
                    $data.="<div class='neoforum_trash_post_forum'>".esc_html__("Was in forum","neoforum").": <a class='neoforum_trash_forum' href=".get_site_url()."/".get_option('neoforum_forum_url')."/".$value['forum_slug'].">".$value['forum_name']."</a></div><div class='neoforum_trash_post_author'>".esc_html__("Author","neoforum").": ".neoforum_themes::get_user_name($value)."</div><div class='neoforum_trash_post_dates'><i>".esc_html__("Created","neoforum").": ".$value['creation_date']."; ".esc_html__("Deleted","neoforum").": ".$value['deleted']."</i></div><div class='neoforum_trash_post_content'>".str_replace($_POST['word_filter'], "<strong>".$_POST['word_filter']."</strong>", $value['content'])."</div></div>";
                    $data.="</div>";
                }
            }
        }
        else{
            if (get_current_user_id()==0){
                $res=array("result"=>false, "message"=>__("You must be logged in to search!", "neoforum"));
                echo json_encode($res);
                wp_die();
            }
             if ($_POST['searchtype']=="topics"){
                foreach ($cont as $value) {
                    $user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users WHERE userid=".$value['authorid']." LIMIT 1", ARRAY_A)[0];
                    $data.="<div class='neoforum_search_topic' data-type='topic' data-id='".$value['topicid']."'>";
                    $data.=$value['moder'] ? "<input type='checkbox' data-type='topic' value='".$value['topicid']."'>" : "";
                    $data.="<div class='neoforum_search_topic_title'>".$value['topic_title']."<div class='neoforum_search_topic_dates'><i>".esc_html__("Created","neoforum").": ".$value['creation_date']."</i></div></div>";
                    $data.="<div class='neoforum_search_author'>".esc_html__("Author","neoforum").":<br>".neoforum_themes::get_user_name($value)."</div><div class='neoforum_search_posts_num'>".esc_html__("Posts","neoforum").":<br>".$value['posts_num']."</div>";
                    $data.="<div class='neoforum_search_forum'>".esc_html__("In forum","neoforum").":<br><a class='neoforum_search_forum' href=".get_site_url()."/".get_option('neoforum_forum_url')."/".$value['forum_slug'].">".$value['forum_name']."</a></div>";
                    $data.= $value['moder'] ? "<div class='neoforum_search_topic_controls'><button class='neoforum_search_delete' onclick='neoforum_search_topic_delete(this, ".$value['topicid'].", \"".wp_create_nonce('neoforum_search_topic_delete')."\");' title='".esc_html__("Delete topic completely","neoforum")."'></button></div>" : "";
                    $data.="</div>";
                    $data.="</div>";
                }
            }
            if ($_POST['searchtype']=="posts"){
                foreach ($cont as $value) {
                    $user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users WHERE userid=".$value['authorid']." LIMIT 1", ARRAY_A)[0];
                    $data.="<div class='neoforum_search_post' data-id='".$value['postid']."'>";
                    $data.= $value['moder'] ? "<input type='checkbox' data-type='post' value='".$value['postid']."'>" : "";
                    $data.= $value['moder'] ? "<div class='neoforum_search_post_controls'><button class='neoforum_search_delete' onclick='neoforum_search_post_delete(this, ".$value['postid'].", \"".wp_create_nonce('neoforum_search_post_delete')."\");' title='".esc_html__("Delete post","neoforum")."'></button></div>" : "";
                    $data.="<div class='neoforum_search_post_topic_title'>".esc_html__("In topic","neoforum").": <a href=".get_site_url()."/".get_option('neoforum_forum_url')."/".$value['forum_slug']."/".$value['topic_slug'].">".$value['topic_title']."</a></div>";
                    $data.="<div class='neoforum_search_post_forum'>".esc_html__("In forum","neoforum").": <a class='neoforum_search_forum' href=".get_site_url()."/".get_option('neoforum_forum_url')."/".$value['forum_slug'].">".$value['forum_name']."</a></div><div class='neoforum_search_post_author'>".esc_html__("Author","neoforum").": ".neoforum_themes::get_user_name($value)."</div><div class='neoforum_search_post_dates'><i>".esc_html__("Created","neoforum").": ".$value['creation_date']."; ".esc_html__("Deleted","neoforum").": ".$value['deleted']."</i></div><div class='neoforum_search_post_content'>".str_replace($_POST['word_filter'], "<strong>".$_POST['word_filter']."</strong>", $value['content'])."</div></div>";
                    $data.="</div>";
                }
            }
        }
    
    $res=array('result'=>true, 'data'=>$data);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_search", "neoforum_search_handler");

function neoforum_users_search_user_handler(){
    if (empty($_POST['str']) and $_POST['type']==null){
        $res=array("result"=>true, "data"=>__("No users found","neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    if ( ! current_user_can( 'manage_options' )  or ! wp_verify_nonce($_POST['nonce'], 'neoforum_search_users')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    if($_POST['type']==null){
        $users=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."users FULL JOIN ".$wpdb->prefix."neoforum_users ON ID=".$wpdb->prefix."neoforum_users.userid WHERE display_name LIKE '%".esc_sql($_POST['str'])."%'", ARRAY_A);
    }
    if($_POST['type']=='admins'){
        $users=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."users FULL JOIN ".$wpdb->prefix."neoforum_users ON ID=".$wpdb->prefix."neoforum_users.userid WHERE ".$wpdb->prefix."neoforum_users.user_caps='administrator' OR ID IN (SELECT user_id FROM ".$wpdb->prefix."usermeta WHERE meta_value LIKE '%administrator%')", ARRAY_A);
    }
    if($_POST['type']=='supers'){
        $users=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."users FULL JOIN ".$wpdb->prefix."neoforum_users ON ID=".$wpdb->prefix."neoforum_users.userid WHERE ".$wpdb->prefix."neoforum_users.user_caps='supermoderator'", ARRAY_A);
    }
    if($_POST['type']=='banned'){
        $users=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."users FULL JOIN ".$wpdb->prefix."neoforum_users ON ID=".$wpdb->prefix."neoforum_users.userid WHERE ".$wpdb->prefix."neoforum_users.ban>CONVERT('".current_time('mysql', 1)."', DATETIME)", ARRAY_A);
    }
    $data="";
    foreach ($users as $user) {
        $admin=($user["user_caps"]=="administrator" or user_can($user['ID'],'manage_options'));
        $data.="<div class='neoforum_search_users_item'>".str_ireplace($_POST['str'], '<b>'.$_POST['str'].'</b>', $user['display_name']);
        $data.= ($admin) ? " <span class='neoforum_user_admin'>".esc_html__("Administrator", "neoforum")."</span>" : "";
        $data.= ($user["user_caps"]=="supermoderator") ? " <span class='neoforum_user_supermod'>".esc_html__("Supermoderator", "neoforum")."</span>" : "";
        $data.= ($user["ban"]>current_time("mysql")) ? " <span class='neoforum_user_banned'>".esc_html__("Banned by", "neoforum")." <a href='".$user['banned_by']."'>".neoforum_themes::get_user_data($user['banned_by'], 'display_name')."</a> ".esc_html__("Expires", "neoforum")." ".$user['ban']." ".esc_html__("Comment", "neoforum").": ".esc_html($user['ban_comment'])."</span>" : "";
        $data.= ($user["user_caps"]=="supermoderator" or $admin) ? "<button class='neoforum_remove_admin' title='".esc_html__("Remove admin","neoforum")."' onclick='neoforum_remove_admin(this, ".$user['ID'].", \"".wp_create_nonce('neoforum_remove_admin')."\")'></button>" : "<button class='neoforum_add_admin' title='".esc_html__("Make admin or supermoderator","neoforum")."' onclick='neoforum_add_admin(".$user['ID'].")'></button>";
        $data.= ($user["ban"]>current_time("mysql")) ? "<button class='neoforum_unban_user' title='".esc_html__("Unban user","neoforum")."' onclick='neoforum_unban_user(this, ".$user['ID'].", \"".wp_create_nonce('neoforum_unban_user')."\")'></button>" : "<button class='neoforum_ban_user' title='".esc_html__("Ban user","neoforum")."' onclick='neoforum_ban_user(".$user['ID'].")'></button>";
        $data.="</div>";

    }
    $res=array("result"=>true, "data"=>$data);
    echo json_encode($res);
    wp_die();
}

add_action("wp_ajax_neoforum_search_users", "neoforum_users_search_user_handler");

function neoforum_theme_descr_handler(){
    if (!current_user_can( 'manage_options' ) or !wp_verify_nonce($_POST['neoforum_get_theme'])){
        esc_html_e("You can't do this action!", "neoforum");
        exit;
    }
    $theme=str_replace("/", "", $_POST['theme']);
    $theme=str_replace("\\", "", $theme);
    include( neoforum_DIR . '/nf-themes/'.$theme.'/description.php' );
    exit;
}

add_action("wp_ajax_neoforum_theme_descr", "neoforum_theme_descr_handler");

function neoforum_message($message){
    ?>
        <div class="neoforum_message_wrapper">
            <div class="neoforum_message_container">
                <div class="neoforum_message_denied">
                    <?php esc_html_e("Oops, some problems occured", "neoforum") ?>
                </div>
                <div class="neoforum_message_text">
                    <?php echo($message) ?>
                </div>
                <a class="neoforum_message_button" href="#" onclick="history.go(-1);">
                    <?php 
                    /*translators: go on the previous page from message page*/
                    esc_html_e("Back", "neoforum") ?>
                </a>
            </div>
        </div>
    <?php
}
function neoforum_banmessage(){
    global $wpdb;
    $ban=$wpdb->get_var("SELECT ban FROM ".$wpdb->prefix."neoforum_users WHERE userid=".get_current_user_id()." LIMIT 1");
    ?>
        <div class="neoforum_message_wrapper">
            <div class="neoforum_message_container">
                <div class="neoforum_message_denied">
                    <?php esc_html_e("You were banned. Ban expires ", "neoforum");
                    echo $ban; ?>
                </div>
            </div>
        </div>
    <?php
}
?>
