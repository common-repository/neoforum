<?php
//Functrions for forums

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function neoforum_forum_item_render($forums, $forum){ //render single forum item
    if(!neoforum_is_user_can_forum('read', $forum)){
        return;
    }
    global $wpdb;
    ?>
        <div class="neoforum_forum_item <?php echo neoforum_forum_attributes($forum); ?>">
            <a class="neoforum_forum_item_link" href="<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/".$forum['slug']) ?>">
                <div class="neoforum_forum_item_icon"></div>
                <div class="neoforum_forum_capt">
                    <div class="neoforum_forum_item_title">
                        <?php echo(esc_html($forum['forum_name'])); ?>
                    </div>
                    <div class="neoforum_forum_item_description">
                        <?php echo(esc_html($forum['forum_descr'])); ?>
                    </div>
                </div>
                <div class="neoforum_forum_posts_num">
                    <div><?php esc_html_e("Posts", "neoforum"); ?>:</div>
                    <?php echo(esc_html($forum['posts_num'])); ?>
                </div>
                <div class="neoforum_forum_topics_num">
                    <div><?php esc_html_e("Topics", "neoforum"); ?>:</div>
                    <?php echo(esc_html($forum['topics_num'])); ?>
                </div>
            </a>
            <div class="neoforum_forum_last_post">
                    <div><?php esc_html_e("Last post", "neoforum"); ?></div>
                    <?php
                        $info=neoforum_themes::get_forum_last_post($forum['forumid']);
                        if (!is_null($info)){
                            $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$info['topicid']." LIMIT 1");
                        }

                        if ($info==null){
                            esc_html_e("There is no posts yet", "neoforum");
                        }
                        else{
                            echo(esc_html($info['creation_date'])." ");
                            echo(">> <a class='neoforum_lastpost_topic_link' href='".get_site_url()."/".get_option('neoforum_forum_url')."/".esc_html($forum['slug'])."/".esc_html($topic['slug'])."'>".esc_html($topic['topic_name'])."</a>");
                            /*translators: from which user is last post in current forum*/
                            if ($info['authorid']==0)
                            {
                                echo(" ".esc_html__("From", "neoforum")." <span class='neoforum_lastpost_topic_link' href=''>".esc_html($info['authorname'])."</span>");
                            }else
                            {
                                echo(" ".esc_html__("From", "neoforum")." <a class='neoforum_lastpost_topic_link' href='".get_site_url()."/".get_option('neoforum_forum_url')."/?user=".$info['authorid']."'>".esc_html(get_user_option('display_name', $info['authorid']))."</a>");
                            }
                        }
                    ?>
                </div>
            <?php $moders=neoforum_themes::get_moders_of_forum($forum);
            if (strlen($moders)>1){ ?>
                <div class="neoforum_forum_moderators">
                    <div class="neoforum_forum_moderators_title"><?php esc_html_e("Moderators", "neoforum") ?>:</div>
                    <?php echo $moders ?>
                </div>
            <?php } 
            $subforums=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE parent_forum=".$forum['forumid'], ARRAY_A);
            if (count($subforums)>0){
            ?>
            <div class="neoforum_included_forums">
                <div class="neoforum_subforums_title">
                    <?php esc_html_e("Subforums", "neoforum") ?>:
                </div>
                <?php
                foreach ($subforums as $forum_in) {
                    echo("<a class='neoforum_subforum_item ".neoforum_forum_attributes($forum_in)."' href='".get_site_url()."/".get_option('neoforum_forum_url')."/?forum=".esc_html($forum_in['forumid'])."'><span class='neoforum_subforum_item_icon'></span>".esc_html($forum_in['forum_name'])."</a>");
                }
                ?>
            </div>
            <?php } ?>
        </div>
    <?php
}

function neoforum_is_forum_not_read($forum, $user=null){
    global $wpdb;
    if ($user==null){
        $user=neoforum_get_user_by_id(get_current_user_id());
    }
    $newtopic=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_topics WHERE forumid=".$forum['forumid']." AND lastpost_date>CONVERT('".$user['new_post_border']."', DATETIME) AND read_by NOT LIKE '%;".$user['userid'].";%' AND read_by NOT LIKE '".$user['userid'].";%' LIMIT 1");
    if ($newtopic<1){
        return "";
    }
    return " neoforum_forum_new_posts ";
}

function neoforum_forum_attributes($forum){
    $res="";
    $forum['is_closed'] ? $res.=" neoforum_forum_closed" : null;
    $forum['is_restricted'] ? $res.=" neoforum_forum_restricted" : null;
    $res.=neoforum_is_forum_not_read($forum);
    return $res;
}

function neoforum_create_forum($title, $descr, $is_section, $parent_forum=0){
    global $wpdb;
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET ord=ord+1");
    $sql="INSERT INTO ".$wpdb->prefix."neoforum_forums (forum_name, slug, forum_descr, is_section, ord, parent_forum)
    VALUES ('".esc_sql($title)."', '".esc_sql(neoforum_forum_slug_gen($title))."', '".esc_sql($descr)."', '".esc_sql($is_section)."', '1', '".esc_sql($parent_forum)."')
    ";

    $wpdb->query($sql);
}

function neoforum_is_forum_exists($id){
    global $wpdb;
    $res=$wpdb->get_results("SELECT forumid FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".esc_sql($id)." LIMIT 1");
    if (count($res)>0){
        return true;
    }
    else{
        return false;
    }
}

function neoforum_get_forum_id_by_slug($slug){
    global $wpdb;
    return $wpdb->get_results("SELECT forumid FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".esc_sql($slug)."' LIMIT 1", ARRAY_A)[0]['forumid'];
}

function neoforum_get_forum_by_slug($slug){
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".esc_sql($slug)."' LIMIT 1", ARRAY_A)[0];
}

function neoforum_forum_slug_gen($title){
    global $wpdb;
    $counter=0;
    $sanitized=sanitize_title($title);
    $res=$sanitized;
    while(count($wpdb->get_results("SELECT forumid FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".esc_sql($res)."' LIMIT 1"))>0){
        $counter++;
        $res=$sanitized."-".$counter;
    }
    return $res;
}

function neoforum_update_forum_last_post($forumid){
    global $wpdb;
    $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE forumid=".esc_sql($forumid)." AND in_trash=0 AND is_approved=1 ORDER BY lastpost_date DESC LIMIT 1", ARRAY_A)[0];
    error_log($topic."z");
    if ($topic==""){
         $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET lastpost_date=NULL, lastpost_authorid=NULL, lastpost_topicid=NULL, lastpost_topicname=NULL, lastpost_authorname=NULL WHERE forumid=".esc_sql($forumid));
         return;
    }
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET lastpost_date='".$topic['lastpost_date']."', lastpost_authorid=".$topic['lastpost_authorid'].", lastpost_topicid=".$topic['topicid'].", lastpost_topicname='".$topic['topic_title']."', lastpost_authorname='".$topic['lastpost_authorname']."' WHERE forumid=$forumid");
}


function neoforum_mark_as_read_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_mark_as_read' ) and $_POST['id']==get_current_user_id()){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET new_post_border='".current_time('mysql')."' WHERE userid=".esc_sql($_POST['id']));
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

add_action('wp_ajax_neoforum_mark_as_read', 'neoforum_mark_as_read_handler');
?>
