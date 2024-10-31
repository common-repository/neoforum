<?php
//Functrions for topics here

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function neoforum_create_topic($title, $descr, $message, $forumid, $userid, $creator=""){
    global $wpdb;
    //$topic_id=$wpdb->get_var("SELECT MAX(topicid) FROM ".$wpdb->prefix."neoforum_topics")+1;
    //$sql="INSERT INTO ".$wpdb->prefix."neoforum_topics
    //(topicid, topic_title, slug, topic_descr, forumid, authorid, creation_date, authorname)
    //VALUES($topic_id, '$title', '".neoforum_topic_slug_gen($title)."', '$descr', $forumid, $userid, NOW(), '$creator')";
    $approved=get_option('neoforum_topics_need_approving')=="on" and !neoforum_is_user_moder($forumid) ? 0 : 1;
    $solved=get_option('neoforum_topics_need_solving')=="on" and !neoforum_is_user_moder($forumid) ? 0 : 1;
    $wpdb->insert($wpdb->prefix."neoforum_topics", array("topic_title"=>$title,"slug"=>neoforum_topic_slug_gen($title),"topic_descr"=>$descr,"forumid"=>$forumid,"authorid"=>$userid,"creation_date"=>current_time('mysql'),"authorname"=>$creator, "is_approved"=>$approved, "is_solved"=>$solved));
    $increase_topic_num="UPDATE ".$wpdb->prefix."neoforum_forums SET topics_num=topics_num+1 WHERE forumid=$forumid";
    $wpdb->query($increase_topic_num);
    $topic_id=$wpdb->insert_id;
    if(strlen(wp_kses($message, array('img' => array())))>0)
        neoforum_create_post($message, $topic_id, $userid, $creator, 1);
    return $topic_id;
}

function neoforum_render_topic_item($topic){
    if(!neoforum_is_user_can_topic('read', $topic)){
        return;
    }
    ?>
        <div class="neoforum_topic <?php echo neoforum_topic_attrubutes($topic); ?>">
            <a class="neoforum_topic_link" href="<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/".get_query_var("forum")."/".$topic['slug']); ?>">
                <div class="neoforum_topic_icon"></div>
                <div class="neoforum_topic_capt">
                    <div class="neoforum_topic_title"><?php echo esc_html( $topic['topic_title']); ?></div>
                    <div class="neoforum_topic_descr"><?php echo esc_html($topic['topic_descr']); ?></div>
                </div>
            </a>
            <div class="neoforum_topic_author"><div><?php esc_html_e("Author", "neoforum"); ?></div>
            <?php if ($topic['authorid']==0){ ?>
                <span class="neoforum_topic_author_link" ><?php echo esc_html($topic['authorname']) ?></span>
            <?php } else
            { ?>
            <a class="neoforum_topic_author_link" href=""><?php echo(neoforum_themes::get_user_name($topic)); ?></a>
            <?php } ?>
            </div>
            <div class="neoforum_topic_posts_num"><div><?php esc_html_e("Answers", "neoforum"); ?></div><?php echo esc_html($topic['posts_num']); ?></div>
            <div class="neoforum_topic_views_num"><div><?php esc_html_e("Views", "neoforum"); ?></div><?php echo esc_html($topic['views_num']); ?></div>
            <div class="neoforum_topic_lastpost">
                <div><?php esc_html_e("Last post", "neoforum"); ?></div>
                <?php
                    $info=neoforum_themes::get_topic_last_post($topic['topicid']);
                    /*translators: by which user current last post was made*/
                    echo("<a class='neoforum_topic_lastpost_date' href='".get_site_url()."/".get_option('neoforum_forum_url')."/".get_query_var("forum")."/".$topic['slug']."/pg=last'>".$info['creation_date']."</a> ".esc_html__("by", "neoforum")." ");
                    if ($topic['authorid']==0)
                        echo("<span class='neoforum_topic_lastpost_author'>".esc_html($info['authorname'])."</a>");
                    else
                        echo("<a class='neoforum_topic_lastpost_author' href='".get_site_url()."/".get_option('neoforum_forum_url')."/?user=".$info['authorid']."'>".esc_html(get_user_option('display_name', $info['authorid']))."</a>");

                ?>

        </div>
            
        </div>
    <?php
}

function neoforum_pagination($slug, $page_num, $type){ //type - topics or posts
    if ($page_num==null)
        $page_num=1;
    global $wpdb;
    if ($type=="posts")
    $items_num=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_posts WHERE topicid=".neoforum_get_topic_id_by_slug($slug)." AND in_trash=0");
    if ($type=="topics")
    $items_num=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_topics WHERE forumid=".neoforum_get_forum_id_by_slug($slug)." AND in_trash=0");
    $pagelen=get_option("neoforum_".$type."_per_page");
    if($items_num%$pagelen==0)
        $pages=intdiv($items_num, $pagelen);
    else
        $pages=intdiv($items_num, $pagelen)+1;

    if ($pages==1){
        return;
    }
    if ($page_num=="last")
        $page_num=$pages;
    echo "<div class='neoforum_pagination'>";
    for ($i=1; $i <= 3; $i++) {
        if ($i>$pages)
            continue;
        if ($i!=$page_num)
        {
            ?>
            <a class="neoforum_pagination_item" href="<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/".get_query_var("forum")."/".get_query_var("topic")."/pg=".$i); ?>"><?php echo $i; ?></a>
            <?php
        }
        else{
            ?>
            <span class="neoforum_pagination_current"><?php echo $i; ?></span>
            <?php
        }
    }
    if($page_num-4>0 and $pages>6){
        echo("<span class='neoforum_pagination_divider'>...</span>");
    }
    for ($i=$page_num-1; $i <= $page_num+1; $i++){
        if ($i<=3 or $i>=$pages-2)
            continue;
        if ($i!=$page_num)
        {
            ?>
            <a class="neoforum_pagination_item" href="<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/".get_query_var("forum")."/".get_query_var("topic")."/pg=".$i); ?>"><?php echo $i; ?></a>
            <?php
        }
        else{
            ?>
            <span class="neoforum_pagination_current"><?php echo $i; ?></span>
            <?php
        }
    }
    if($pages-$page_num>4 and $pages>6){
        echo("<span class='neoforum_pagination_divider'>...</span>");
    }
    for ($i=$pages-2; $i <= $pages; $i++) {
        if ($i<=3)
            continue;
        if ($i!=$page_num)
        {
            ?>
            <a class="neoforum_pagination_item" href="<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/".get_query_var("forum")."/".get_query_var("topic")."/pg=".$i); ?>"><?php echo $i; ?></a>
            <?php
        }
        else{
            ?>
            <span class="neoforum_pagination_current"><?php echo $i; ?></span>
            <?php
        }
    }
    echo "</div>";

}

function neoforum_is_topic_not_read($topic, $user=null){
    global $wpdb;
    if ($user==null){
        $user=neoforum_get_user_by_id(get_current_user_id());
    }
    $lastpost=strtotime($topic['lastpost_date']);
    $usertime=strtotime($user['new_post_border']);

    if ($usertime>$lastpost or preg_match('/(?<=^|;)'.$user['userid'].'(?=;)/',$topic['read_by'])){
        return "";
    }
    return " neoforum_topic_new_posts ";
}

function neoforum_topic_attrubutes($topic){
    $res="";
    $topic['is_closed'] ? $res.=" neoforum_topic_closed " : null;
    $topic['is_pinned'] ? $res.=" neoforum_topic_pinned " : null;
    !$topic['is_approved'] and get_option('neoforum_topics_need_approving')=="on" ? $res.=" neoforum_topic_not_approved " : null;
    !$topic['is_solved'] and ($topic['is_approved'] or get_option('neoforum_topics_need_approving')!="on") and get_option('neoforum_topics_need_solving')=="on" ? $res.=" neoforum_topic_not_solved " : null;
    $topic['is_solved'] and get_option('neoforum_topics_need_solving')=="on" ? $res.=" neoforum_topic_solved " : null;
    $res.=neoforum_is_topic_not_read($topic);
    return $res;
}

function neoforum_edit_topic($title, $descr, $message, $topicid, $userid){
    global $wpdb;
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET slug='".neoforum_topic_slug_gen($title)."', topic_title='".esc_sql($title)."', topic_descr='".esc_sql($descr)."' WHERE topicid=".esc_sql($topicid));
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET edited=content, edit_date=NOW(), editorid=".esc_sql($userid).", content='".esc_sql(neoforum_clean_post_content($message))."' WHERE postid=".neoforum_get_topic_first_post($topicid));
}

function neoforum_get_topic_first_post($topicid){
    global $wpdb;
    return $wpdb->get_results("SELECT MAX(creation_date), postid FROM ".$wpdb->prefix."neoforum_posts WHERE topicid=".esc_sql($topicid)." LIMIT 1", ARRAY_A)[0]['postid'];
}

function neoforum_get_topic_by_id($id){
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($id)." LIMIT 1", ARRAY_A)[0];
}

function neoforum_get_topic_id_by_slug($slug){
    global $wpdb;
    return $wpdb->get_results("SELECT topicid FROM ".$wpdb->prefix."neoforum_topics WHERE slug='".esc_sql($slug)."' LIMIT 1", ARRAY_A)[0]['topicid'];
}

function neoforum_topic_slug_gen($title){
    global $wpdb;
    $counter=0;
    $sanitized=sanitize_title($title);
    $res=$sanitized;
    while(count($wpdb->get_results("SELECT forumid FROM ".$wpdb->prefix."neoforum_topics WHERE slug='".esc_sql($res)."' LIMIT 1"))>0){
        $counter++;
        $res=$sanitized."-".$counter;
    }
    return $res;
}

function neoforum_show_post_reply_fields(){
    if(!neoforum_is_user_can_post('post')){
        return;
    }
    ?>
    <form class="neoforum_topic_answer" method="post" action="<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/?action=newpost") ?>" enctype="multipart/form-data"> 
        <div class="neoforum_reply_topic"><?php esc_html_e("Leave a reply", "neoforum"); ?></div>
        <?php if(get_current_user_id()==0){ ?>
        <input type="text" maxlength="256" name="guestname" placeholder="<?php esc_html_e("Enter your nickname", "neoforum"); ?>" required>
        <?php }
        neoforum_themes::place_text_editor(); 
        if (get_option("neoforum_can_upload")=='on' and get_current_user_id()!=0){
        ?>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo get_option("neoforum_max_file_size") ?>">
            <input type="file" name="neoforum_file[]"  multiple><br>
        <?php } ?>
        <input type="hidden" name="topic" value="<?php echo(neoforum_get_topic_id_by_slug(get_query_var('topic'))); ?>">
        <input type="hidden" name="topicname" value="<?php echo(neoforum_themes::get_topic_name(neoforum_get_topic_id_by_slug(get_query_var('topic')))); ?>">
        <?php wp_nonce_field('neoforum_reply_topic', 'nonce' ); ?>
        <input type="submit" value="<?php _e("Leave a reply", "neoforum"); ?>">
    </form>
    <?php
}
function neoforum_show_post_editor(){
    ?>
    <template id="neoforum_post_editor">
        <div class="neoforum_post_editor">
            <div class="neoforum_edit_post"><?php esc_html_e("Edit post", "neoforum"); ?></div>
            <?php neoforum_themes::place_text_editor(); ?>
            <div class="neoforum_edit_attachments">
            </div>
            <?php
                if (get_option("neoforum_can_upload")=='on' and get_current_user_id()!=0){
            ?>
                <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo get_option("neoforum_max_file_size") ?>">
                <input type="file" name="neoforum_file[]"  multiple><br>
            <?php } ?>
            <input type="hidden" name="postid" value="0" class="neoforum_post_id">
            <input type="button" class="neoforum_post_editor_commit" value="<?php _e("Commit changes", "neoforum"); ?>" onclick="neoforum_commit_edit_post(event, editor.innerHTML, document.querySelector('.neoforum_post_id').value, document.querySelector('#post_edit_nonce').value);">
            <?php wp_nonce_field('neoforum_edit_post', 'post_edit_nonce' ); ?>
            <input type="button" value="<?php _e("Cancel", "neoforum"); ?>" onclick="neoforum_cancel_edit_post(event);">
        </div>
    </template>
    <?php
}

function neoforum_display_topic_contols($topic_slug){
    global $wpdb;
    $topic_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE slug='".$topic_slug."' LIMIT 1", ARRAY_A)[0];
    $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
    if (!neoforum_is_user_moder($forum_row)){
        return;
    }
    ?>
    <div class="neoforum_topic_controls_wrap">
        <select class="neoforum_topic_contols" data-nonce="<?php echo wp_create_nonce("neoforum_topic_controls"); ?>" data-forum="<?php echo($topic_row['forumid']);?>" data-id="<?php echo($topic_row['topicid']); ?>" onchange="neoforum_controls_change(event);">
            <option selected="true" value="def"><?php esc_html_e("Choose option...", "neoforum"); ?></option>
            <option value="move_topic"><?php esc_html_e("Move topic", "neoforum"); ?></option>
            <option value="delete_topic" data-nonce="<?php echo wp_create_nonce("neoforum_delete_topic"); ?>"><?php esc_html_e("Delete topic", "neoforum"); ?></option>
            <option value="move_posts"><?php esc_html_e("Move selected posts to existing topic", "neoforum"); ?></option>
            <option value="new_topic"><?php esc_html_e("Start new topic with selected posts", "neoforum"); ?></option>
            <option value="delete_posts"><?php esc_html_e("Delete selected posts", "neoforum"); ?></option>
        </select>
    </div>
    <?php
}


function neoforum_display_post_controls($sql_row){
    global $wpdb;
    $topic_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".neoforum_get_topic_id_by_slug(get_query_var('topic'))." LIMIT 1", ARRAY_A)[0];
    $forum_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic_row['forumid']." LIMIT 1", ARRAY_A)[0];
    $user_row=neoforum_get_user_by_id(get_current_user_id());
    $subscribe_row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_subscribes WHERE topicid=".$topic_row['topicid']." AND userid=".$user_row['userid']." LIMIT 1", ARRAY_A);
    $subscribe_row!=null ? $subscribe_row=$subscribe_row[0] : null;
    if ($sql_row['is_first'])
    {
        if(neoforum_is_user_can_post('post', $sql_row, $topic_row, $forum_row, $user_row)){ ?>
            <a href="#" class="neoforum_topic_quote_button neoforum_topic_button" onclick="neoforum_postquote(event, '<?php echo(get_user_option('display_name', $sql_row['authorid'])) ?>')";><?php esc_html_e("Quote", "neoforum"); ?></a>
        <?php }
        if(neoforum_is_user_can_topic('delete', $topic_row, $forum_row, $user_row)){
        ?>

            <a href="#" class="neoforum_topic_delete_button neoforum_topic_button" onclick="neoforum_posts_handler(event, 'neoforum_delete_topic', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_delete_topic")); ?>');"><?php esc_html_e("Delete topic", "neoforum");?></a>
        <?php }
        if(neoforum_is_user_can_topic('edit', $topic_row, $forum_row, $user_row)){ ?>
            <a href="#" class="neoforum_topic_move_button neoforum_topic_button" onclick="neoforum_open_move_popup(event, <?php echo(neoforum_get_topic_id_by_slug(get_query_var('topic'))) ?>);"><?php esc_html_e("Move topic", "neoforum"); ?></a>
        <?php }
        if(neoforum_is_user_can_topic('edit', $topic_row, $forum_row, $user_row)){ ?>
            <a href="#" class="neoforum_topic_edit_button neoforum_topic_button" onclick="location.href='<?php echo get_site_url()."/".get_option("neoforum_forum_url")."/"; ?>?action=edittopic&forum=<?php echo(neoforum_get_forum_id_by_slug(get_query_var('forum'))) ?>&topic=<?php echo(neoforum_get_topic_id_by_slug(get_query_var('topic'))) ?>';"><?php esc_html_e("Edit topic", "neoforum"); ?></a>
        <?php }
        if(neoforum_is_user_can_topic('close', $topic_row, $forum_row, $user_row)){ ?>
            <a href="#" class="neoforum_topic_close_button neoforum_topic_button <?php if($topic_row['is_closed']) echo('neoforum_topic_button_switcher'); ?>" onclick="neoforum_posts_handler(event, 'neoforum_close_topic', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_close_topic")); ?>');"><?php esc_html_e("Close topic", "neoforum"); ?></a>
            <a href="#" class="neoforum_topic_open_button neoforum_topic_button" onclick="neoforum_posts_handler(event, 'neoforum_open_topic', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_open_topic")); ?>');"><?php esc_html_e("Open topic", "neoforum"); ?></a>
        <?php } 
        if(neoforum_is_user_can_topic('approve', $topic_row, $forum_row, $user_row) and get_option('neoforum_topics_need_approving')=="on"){ ?>
            <a href="#" class="neoforum_topic_approve_button neoforum_topic_button <?php if($topic_row['is_approved']) echo('neoforum_topic_button_switcher'); ?>" onclick="neoforum_posts_handler(event, 'neoforum_approve_topic', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_approve_topic")); ?>');"><?php esc_html_e("Approve topic", "neoforum"); ?></a>
            <a href="#" class="neoforum_topic_unapprove_button neoforum_topic_button" onclick="neoforum_posts_handler(event, 'neoforum_unapprove_topic', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_unapprove_topic")); ?>');"><?php esc_html_e("Unapprove topic", "neoforum"); ?></a>
        <?php } 
        if(neoforum_is_user_can_topic('pin', $topic_row, $forum_row, $user_row)){ ?>
            <a href="#" class="neoforum_topic_sticky_button neoforum_topic_button <?php if($topic_row['is_pinned']) echo('neoforum_topic_button_switcher'); ?>" onclick="neoforum_posts_handler(event, 'neoforum_sticky_topic', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_sticky_topic")); ?>');"><?php esc_html_e("Sticky topic", "neoforum"); ?></a>
            <a href="#" class="neoforum_topic_unsticky_button neoforum_topic_button" onclick="neoforum_posts_handler(event, 'neoforum_unsticky_topic', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_unsticky_topic")); ?>');"><?php esc_html_e("Unsticky topic", "neoforum"); ?></a>
        <?php } 
        if(neoforum_is_user_can_topic('solved', $topic_row, $forum_row, $user_row) and get_option('neoforum_topics_need_solving')=="on"){ ?>
            <a href="#" class="neoforum_topic_solved_button neoforum_topic_button <?php if($topic_row['is_solved']) echo('neoforum_topic_button_switcher'); ?>" onclick="neoforum_posts_handler(event, 'neoforum_solved_topic', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_solved_topic")); ?>');"><?php esc_html_e("Mark topic as solved", "neoforum"); ?></a>
            <a href="#" class="neoforum_topic_unsolved_button neoforum_topic_button" onclick="neoforum_posts_handler(event, 'neoforum_notsolved_topic', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_notsolved_topic")); ?>');"><?php esc_html_e("Unmark topic as solved", "neoforum"); ?></a>
        <?php } 
        if(neoforum_is_user_can_topic('subscribe', $topic_row, $forum_row, $user_row)){ ?>
            <a href="#" class="neoforum_topic_subscribe_button neoforum_topic_button <?php if($subscribe_row!=null) echo('neoforum_topic_button_switcher'); ?>" onclick="neoforum_posts_handler(event, 'neoforum_subscribe', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_subscribe")); ?>');"><?php esc_html_e("Subscribe topic", "neoforum"); ?></a>
            <a href="#" class="neoforum_topic_unsubscribe_button neoforum_topic_button" onclick="neoforum_posts_handler(event, 'neoforum_unsubscribe', <?php echo($sql_row['topicid'].", '".wp_create_nonce("neoforum_unsubscribe")); ?>');"><?php esc_html_e("Unsubscribe topic", "neoforum"); ?></a>
        <?php } 
        
        if(neoforum_is_user_can_post('report', $sql_row, $topic_row, $forum_row, $user_row)){ ?>
            <a href="#" class="neoforum_topic_report_button neoforum_topic_button" onclick="neoforum_report_post(event, <?php echo $sql_row['postid'] ?>)"><?php esc_html_e("Report", "neoforum"); ?></a>
        <?php } 
            

        if(neoforum_is_user_moder($forum_row, $user_row)){ ?>
            <input class="neoforum_post_select" type="checkbox" data-postid="<?php echo $sql_row['postid']; ?>">
        <?php }
    }
    else{
        if(neoforum_is_user_can_post('post', $sql_row, $topic_row, $forum_row, $user_row)){ ?>
            <a href="#" class="neoforum_topic_quote_button neoforum_topic_button" onclick="neoforum_postquote(event, '<?php echo(get_user_option('display_name', $sql_row['authorid'])) ?>')";><?php esc_html_e("Quote", "neoforum"); ?></a>
        <?php }
        if(neoforum_is_user_can_post('delete', $sql_row, $topic_row, $forum_row, $user_row)){ ?>
        <a href="#" class="neoforum_post_delete_button neoforum_post_button" onclick="neoforum_posts_handler(event, 'neoforum_delete_post', <?php echo($sql_row['postid'].", '".wp_create_nonce("neoforum_delete_post")); ?>')"><?php esc_html_e("Delete post", "neoforum"); ?></a>
        <?php } 
        if(neoforum_is_user_can_post('edit', $sql_row, $topic_row, $forum_row, $user_row)){ ?>
        <a href="#" class="neoforum_post_edit_button neoforum_post_button" onclick="neoforum_edit_post(event, <?php echo($sql_row['postid']); ?>);"><?php esc_html_e("Edit post", "neoforum"); ?></a>
        <?php }
        if(neoforum_is_user_can_post('report', $sql_row, $topic_row, $forum_row, $user_row)){ ?>
            <a href="#" class="neoforum_topic_report_button neoforum_topic_button" onclick="neoforum_report_post(event, <?php echo $sql_row['postid'] ?>, '<?php echo wp_create_nonce('neoforum_report_form') ?>')"><?php esc_html_e("Report", "neoforum"); ?></a>
        <?php } 
        if(neoforum_is_user_moder($forum_row, $user_row)){ ?>
            <input class="neoforum_post_select" type="checkbox" data-postid="<?php echo $sql_row['postid']; ?>">
        <?php }
    }

}

function neoforum_is_topic_exists($id){
    global $wpdb;
    $res=$wpdb->get_results("SELECT topicid FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=$id LIMIT 1");
    if (count($res)>0){
        return true;
    }
    else{
        return false;
    }
}

function neoforum_show_edit_topic_fields(){
    global $wpdb;
    $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($_GET['topic'])." LIMIT 1", ARRAY_A)[0];
    $post=$wpdb->get_results("SELECT *, (SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_attachments WHERE postid=".$wpdb->prefix."neoforum_posts.postid) AS filenum FROM ".$wpdb->prefix."neoforum_posts WHERE topicid=".esc_sql($_GET['topic'])." ORDER BY creation_date ASC LIMIT 1", ARRAY_A)[0];
    ?>
    <form class="neoforum_new_topic" method="post" action="<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/?action=edittopichandler") ?>" enctype="multipart/form-data">
        <div class="neoforum_start_topic"><?php esc_html_e("Edit topic", "neoforum");?>
        </div>
        <input type="text" <?php echo("value='".$topic['topic_title']."'");?>maxlength="256" name="topic_title" class="neoforum_topic_title_field" placeholder="Topic title" required>
        <input type="text" <?php echo("value='".$topic['topic_descr']."'");?>name="topic_descr" class="neoforum_topic_descr_field" placeholder="Topic description">
        <?php neoforum_themes::place_text_editor(neoforum_clean_post_content($post['content']));
        if ($post['filenum']>0) {
            echo '<div class="neoforum_attachments">';
            echo "<div class='neoforum_attachments_header'>".esc_html__("Attachments", "neoforum").":</div>";
            global $wpdb;
            $files=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_attachments WHERE postid=".$post['postid'], ARRAY_A);
            foreach ($files as $f) {
                ?>
                    <div class="neoforum_remove_attach" onclick="neoforum_remove_attach(this, <?php echo $f['userid'] ?>, '<?php echo $f['filename'] ?>', <?php echo $f['postid'] ?>);"><?php echo $f['filename'] ?></div>
                <?php
            }
            echo "</div>";
        }
        if (get_option("neoforum_can_upload")=='on' and get_current_user_id()!=0){
        ?>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo get_option("neoforum_max_file_size") ?>">
            <input type="file" name="neoforum_file[]" multiple><br>
        <?php } ?>
        <input type="hidden" name="forum" value="<?php echo($_GET['forum']); ?>">
        <input type="hidden" name="topic" value="<?php echo($_GET['topic']); ?>">
        <input type="hidden" name="forumname" value="<?php echo(neoforum_themes::get_topic_name(neoforum_get_topic_id_by_slug(get_query_var('topic')))); ?>">
        <?php wp_nonce_field('neoforum_edit_topic', 'nonce' ); ?>
        <input type="submit" value="<?php _e("Edit topic", "neoforum"); ?>">
    </form>
    <?php
}

function neoforum_show_new_topic_fields(){
    
    ?>
    <form class="neoforum_new_topic" method="post" action="<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/?action=newtopic") ?>" enctype="multipart/form-data">
        <div class="neoforum_start_topic"><?php esc_html_e("Start new topic", "neoforum"); ?></div>
        <?php if(get_current_user_id()==0){ ?>
        <input type="text" maxlength="256" name="guestname" placeholder="<?php esc_html_e("Enter your nickname", "neoforum"); ?>" required><br>
        <?php }
        ?>
        <input type="text" maxlength="256" name="topic_title" class="neoforum_topic_title_field" placeholder="<?php esc_html_e("Topic title", "neoforum"); ?>" required><br>
        <input type="text"name="topic_descr" class="neoforum_topic_descr_field" placeholder="<?php esc_html_e("Topic description", "neoforum"); ?>"><br>
        <?php neoforum_themes::place_text_editor(neoforum_clean_post_content($post['content'])); 
        if (get_option("neoforum_can_upload")=='on' and get_current_user_id()!=0){
        ?>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo get_option("neoforum_max_file_size") ?>">
            <input type="file" name="neoforum_file[]" multiple><br>
        <?php } ?>
        <input type="hidden" name="forum" value="<?php echo($_GET['forum']); ?>">
        <input type="hidden" name="forumname" value="<?php echo(neoforum_themes::get_topic_name(neoforum_get_topic_id_by_slug(get_query_var('topic')))); ?>">
        <?php wp_nonce_field('neoforum_create_topic', 'nonce' ); ?>
        <input type="submit" value="<?php _e("Start new topic", "neoforum"); ?>">
    </form>
    <?php
}

function neoforum_delete_topic_handler(){
    if(!neoforum_is_topic_exists($_POST['id'])){
        $reply=array(
        'result' => false,
        'message' => esc_html__("Topic does not exists!", "neoforum"));
        echo(json_encode($reply));
        wp_die();
    }
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_delete_topic' ) and neoforum_is_user_can_topic("delete", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($_POST['id'])." LIMIT 1", ARRAY_A)[0];
            if($topic['in_trash']==1){
                $reply=array(
                'result' => false,
                'data' => esc_html__("Topic already in trash!", "neoforum"));
                echo(json_encode($reply));
                wp_die();
            }
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET topics_num=topics_num-1 WHERE forumid=".$topic['forumid']);
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num-".$topic['posts_num']." WHERE forumid=".$topic['forumid']);
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET in_trash=1, deleted=NOW() WHERE topicid=".esc_sql($_POST['id']));
        neoforum_update_forum_last_post($topic['forumid']);
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

add_action('wp_ajax_neoforum_delete_topic', 'neoforum_delete_topic_handler');

function neoforum_close_topic_handler(){
    
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_close_topic' ) and neoforum_is_user_can_topic("close", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET is_closed=1 WHERE topicid=".esc_sql($_POST['id']));
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

add_action('wp_ajax_neoforum_close_topic', 'neoforum_close_topic_handler');

function neoforum_open_topic_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_open_topic' ) and neoforum_is_user_can_topic("close", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET is_closed=0 WHERE topicid=".esc_sql($_POST['id']));
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

add_action('wp_ajax_neoforum_open_topic', 'neoforum_open_topic_handler');

function neoforum_approve_topic_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_approve_topic' ) and neoforum_is_user_can_topic("approve", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET is_approved=1 WHERE topicid=".esc_sql($_POST['id']));
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

add_action('wp_ajax_neoforum_approve_topic', 'neoforum_approve_topic_handler');

function neoforum_unapprove_topic_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_unapprove_topic' ) and neoforum_is_user_can_topic("approve", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET is_approved=0 WHERE topicid=".esc_sql($_POST['id']));
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

add_action('wp_ajax_neoforum_unapprove_topic', 'neoforum_unapprove_topic_handler');

function neoforum_sticky_topic_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_sticky_topic' ) and neoforum_is_user_can_topic("pin", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET is_pinned=1 WHERE topicid=".esc_sql($_POST['id']));
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

add_action('wp_ajax_neoforum_sticky_topic', 'neoforum_sticky_topic_handler');

function neoforum_unsticky_topic_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_unsticky_topic' ) and neoforum_is_user_can_topic("pin", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET is_pinned=0 WHERE topicid=".esc_sql($_POST['id']));
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

add_action('wp_ajax_neoforum_unsticky_topic', 'neoforum_unsticky_topic_handler');

function neoforum_solved_topic_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_solved_topic' ) and neoforum_is_user_can_topic("solved", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET is_solved=1 WHERE topicid=".esc_sql($_POST['id']));
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

add_action('wp_ajax_neoforum_solved_topic', 'neoforum_solved_topic_handler');

function neoforum_subscribe_handler(){
    global $wpdb;
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_subscribe' ) and neoforum_is_user_can_topic("subscribe", neoforum_get_topic_by_id($_POST['id']))){
        $userid=get_current_user_id();
        $row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_subscribes WHERE topicid=".esc_sql($_POST['id'])." AND userid=$userid LIMIT 1", ARRAY_A)[0];
        if ($row==null){
            $reply=array(
            'result' => true);
            $wpdb->query("INSERT INTO ".$wpdb->prefix."neoforum_subscribes VALUES(".esc_sql($_POST['id']).", ".$userid.", TRUE)");
            echo(json_encode($reply));
            wp_die();
        }
        else{
            $reply=array(
            'result' => false,
            'data' => esc_html__("You already subscribed on this topic", "neoforum"));
            echo(json_encode($reply));
            wp_die();
        }
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_subscribe', 'neoforum_subscribe_handler');

function neoforum_unsubscribe_handler(){
    global $wpdb;
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_unsubscribe' ) and neoforum_is_user_can_topic("subscribe", neoforum_get_topic_by_id($_POST['id']))){
        $userid=get_current_user_id();
        $row=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_subscribes WHERE topicid=".esc_sql($_POST['id'])." AND userid=$userid LIMIT 1", ARRAY_A)[0];
        if ($row!=null){
            $reply=array(
            'result' => true);
            $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_subscribes WHERE topicid=".esc_sql($_POST['id'])." AND userid=".$userid);
            echo(json_encode($reply));
            wp_die();
        }
        else{
            $reply=array(
            'result' => true);
            echo(json_encode($reply));
            wp_die();
        }
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_unsubscribe', 'neoforum_unsubscribe_handler');

function neoforum_notsolved_topic_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_notsolved_topic' ) and neoforum_is_user_can_topic("solved", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET is_solved=0 WHERE topicid=".esc_sql($_POST['id']));
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

add_action('wp_ajax_neoforum_notsolved_topic', 'neoforum_notsolved_topic_handler');

function neoforum_move_topic_handler(){
    global $wpdb;
    if(!neoforum_is_forum_exists(esc_sql($_POST['forumid']))){
        $reply=array(
        'result' => false,
        'message' => esc_html__("Forum does not exists!", "neoforum"));
        echo(json_encode($reply));
        wp_die();
    }
    $forum=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".esc_sql($_POST['forumid'])." LIMIT 1", ARRAY_A)[0];
    $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($_POST['topicid'])." LIMIT 1", ARRAY_A)[0];
    if($forum['is_section']){
        $reply=array(
        'result' => false,
        'message' => esc_html__("Section cannot contain topics!", "neoforum"));
        echo(json_encode($reply));
        wp_die();
    }
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_move_topic' ) and neoforum_is_user_can_topic("delete", neoforum_get_topic_by_id($_POST['id']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET forumid=".esc_sql($_POST['forumid'])." WHERE topicid=".esc_sql($_POST['topicid']));
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET forumid=".esc_sql($_POST['forumid'])." WHERE topicid=".esc_sql($_POST['topicid']));
        neoforum_update_forum_last_post(esc_sql($_POST['forumid']));
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET topics_num=topics_num+1, posts_num=posts_num+".$topic['posts_num']." WHERE forumid=".esc_sql($_POST['forumid']));
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET topics_num=topics_num-1, posts_num=posts_num-".$topic['posts_num']." WHERE forumid=".$topic['forumid']);
        $reply['URL']=get_site_url()."/".get_option("neoforum_forum_url")."/".$forum['slug']."/".$topic['slug'];
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

add_action('wp_ajax_neoforum_move_topic', 'neoforum_move_topic_handler');

function neoforum_new_topic_form_handler(){
    global $wpdb;
    if(!neoforum_is_forum_exists($_POST['forumid'])){
        $reply=array(
        'result' => false,
        'message' => esc_html__("Forum does not exists!", "neoforum"));
        echo(json_encode($reply));
        wp_die();
    }
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_new_topic' ) and neoforum_is_user_can_topic("delete", neoforum_get_topic_by_id($_POST['topicid']))){
        ?>
        <form class='neoforum_new_topic_form'>
        <input type='text' class='neoforum_new_topic_title' placeholder='<?php esc_html_e("Enter topic title here","neoforum") ?>' required><br>
        <input type='text' class='neoforum_new_topic_descr' placeholder='<?php esc_html_e("Enter topic desctiption here","neoforum") ?>'>
        <input type='hidden' name="neoforum_forumid" value='<?php echo(esc_sql($_POST['forumid'])) ?>'>
        <?php neoforum_themes::place_text_editor(); ?>
        <button type='submit' onclick='neoforum_new_topic(event, "<?php echo wp_create_nonce("neoforum_new_topic"); ?>");'><?php esc_html_e("Start new topic","neoforum") ?></button>
        </form>
        <?php     
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_get_new_topic_form', 'neoforum_new_topic_form_handler');

function neoforum_new_topic_handler(){
    global $wpdb;
    $forum=$wpdb->get_results("SELECT slug FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".esc_sql($_POST['forumid'])." LIMIT 1", ARRAY_A)[0];
    if(strlen(sanitize_text_field($_POST['title']))<1){
        $reply=array(
        'result' => false,
        'message' => esc_html__("Topic title is empty!", "neoforum"));
        echo(json_encode($reply));
        wp_die();
    }
    if(!neoforum_is_forum_exists(esc_sql($_POST['forumid']))){
        $reply=array(
        'result' => false,
        'message' => esc_html__("Forum does not exists!", "neoforum"));
        echo(json_encode($reply));
        wp_die();
    }
    if($forum['is_section']){
        $reply=array(
        'result' => false,
        'message' => esc_html__("Section cannot contain topics!", "neoforum"));
        echo(json_encode($reply));
        wp_die();
    }
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_new_topic' ) and neoforum_is_user_can_topic("delete", neoforum_get_topic_by_id($_POST['topicid']))){
        $topic_id=neoforum_create_topic(sanitize_text_field($_POST['title']), sanitize_text_field($_POST['descr']), $_POST['message'], esc_sql($_POST['forumid']), get_current_user_id());
        $posts=json_decode(stripslashes($_POST['posts']));
        $counter=0;
        foreach ($posts as $id) {
            $post=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".$id." LIMIT 1", ARRAY_A)[0];
            if (neoforum_is_user_can_post("delete", $post) and $_POST['oldtopic']==$post['topicid']){
                $counter+=1;
                $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET topicid=".$topic_id.", forumid=".esc_sql($_POST['forumid'])." WHERE postid=".$id);
            }
        }
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num-$counter WHERE topicid=".esc_sql($_POST['oldtopic']));
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num-$counter WHERE forumid=".$post['forumid']);
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num+$counter WHERE topicid=".$topic_id);
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num+$counter WHERE forumid=".$topic['forumid']);
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=topics_num+1 WHERE forumid=".$topic['forumid']);

        $topic=$wpdb->get_results("SELECT slug FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$topic_id." LIMIT 1", ARRAY_A)[0];
        $reply=array(
        'result' => true);
        $reply['URL']=get_site_url()."/".get_option("neoforum_forum_url")."/".$forum['slug']."/".$topic['slug'];
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

add_action('wp_ajax_neoforum_new_topic', 'neoforum_new_topic_handler');

function neoforum_get_forums_handler(){
    if (wp_verify_nonce( $_POST['nonce'], 'neoforum_topic_controls' ) and neoforum_is_user_can_topic("delete", neoforum_get_topic_by_id($_POST['topicid']))){
        $reply=array(
        'result' => true);
        $pagelen=20;
        global $wpdb;
        $user=neoforum_get_user_by_id(get_current_user_id());
        $data="";
        error_log($_POST['currentforum']);
        switch ($_POST['do']) {
            case 'move_topic': 
            case 'new_topic':
                $forums=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums ORDER BY ord DESC", ARRAY_A);
                foreach ($forums as $f) {
                    if ($f['is_section'] or $f['forumid']==$_POST['currentforum'] AND $_POST['do']=='move_topic')
                            continue;
                    if($_POST['do']=='move_topic')
                        $data.="<div class='neoforum_forum_selector_wrap' onclick=\"neoforum_move_topic(".$f['forumid'].", '".wp_create_nonce('neoforum_move_topic')."');\">";
                    else
                        $data.="<div class='neoforum_forum_selector_wrap' onclick=\"neoforum_get_new_topic_form(".$f['forumid'].", '".wp_create_nonce('neoforum_new_topic')."');\">";
                    $data.="<div class='neoforum_forum_selector_item'>".$f['forum_name']."</div>";
                    $data.="<div class='neoforum_forum_selector_topics'>".esc_html__("Topics","neoforum")."<br>".$f['topics_num']."</div><div class='neoforum_forum_selector_posts'>".esc_html__("Posts","neoforum")."<br>".$f['posts_num']."</div>";
                    $data.="</div>";
                }
                $reply['data']=$data;
                break;
            case 'move_posts':
                if ($_POST['forumid']=="null")
                    {
                    $forums=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums ORDER BY ord DESC", ARRAY_A);
                    foreach ($forums as $f) {
                        if (!neoforum_is_user_can_forum('read', $f, $user) or !neoforum_is_user_can_forum('edit', $f, $user))
                            continue;
                        $data.="<div class='neoforum_forum_selector_wrap' onclick=\"neoforum_get_forums_list(event, '".$_POST['do']."', ".$_POST['currentforum'].", ".$f['forumid'].", 1)\">";
                        $data.="<div class='neoforum_forum_selector_item'>".$f['forum_name']."</div>";
                        $data.="<div class='neoforum_forum_selector_topics'>".esc_html__("Topics","neoforum")."<br>".$f['topics_num']."</div><div class='neoforum_forum_selector_posts'>".esc_html__("Posts","neoforum")."<br>".$f['posts_num']."</div>";
                        $data.="</div>";
                    }
                    $reply['data']=$data;
                    break;
                    }
                $first=$pagelen*$_POST['page']-$pagelen+1;
                $wpdb->query("SET @rows=0");
                $topics=$wpdb->get_results("SELECT *, (@rows:=@rows+1) as num FROM ".$wpdb->prefix."neoforum_topics WHERE forumid=".esc_sql($_POST['forumid'])." ORDER BY creation_date DESC", ARRAY_A);
                $topics_num=count($topics);
                foreach ($topics as $t) {
                    if($_POST['topicid']==$t['topicid'] or !neoforum_is_user_can_post('post', null, $t, null, $user))
                        continue;
                    if ($t['num']>=$first AND $t['num']<$first+$pagelen){
                        $data.="<div class='neoforum_forum_selector_wrap' onclick=\"neoforum_move_posts(event, ".$t['topicid'].", '".wp_create_nonce('neoforum_move_posts')."');\">";
                        $data.="<div class='neoforum_forum_selector_item'>".$t['topic_title']."</div>";
                        $data.="<div class='neoforum_forum_selector_posts'>".esc_html__("Posts","neoforum")."<br>".$f['posts_num']."</div>";
                        $data.="</div>";
                    }
                }
                if($topics_num%$pagelen==0)
                    $pages=intdiv($topics_num, $pagelen);
                else
                    $pages=intdiv($topics_num, $pagelen)+1;
                if ($pages==1){
                    $data.="</div>";
                    $reply['data']=$data;
                    break;
                }
                error_log($data);
                $data.="<div class='neoforum_forum_selector_pagination'>";
                for ($i=1; $i <= $pages; $i++) { 
                    if ($i==$_POST['page'])
                        $data.="<span> $i <span>";
                    else
                        $data.="<a href='' onclick=\"neoforum_get_forums_list(event, '".$_POST['do']."', ".$_POST['forumid'].", $i)\"> $i </a>";
                }
                $data.="</div>";
                $reply['data']=$data;
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

add_action('wp_ajax_neoforum_get_forums', 'neoforum_get_forums_handler');

function neoforum_get_report_form_handler(){
    if (!is_numeric($_POST['postid']))
    {
        $res=array("result"=>false, "message"=>"NaN");
        echo json_encode($res);
        wp_die();
    }
    if ( !neoforum_is_user_can_post('report') or ! wp_verify_nonce($_POST['nonce'], 'neoforum_report_form')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $reports=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_reports WHERE postid=".esc_sql($_POST['postid'])." AND userid=".get_current_user_id()." LIMIT 1");
    if (count($reports)>0)
    {
        $res=array("result"=>false, "message"=>__("You already reported this post!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $data="<div class='neoforum_report_form_wrap'><textarea maxlength='1024' class='neoforum_report_form_field'></textarea><button onclick='neoforum_commit_report(this, ".$_POST['postid'].", \"".wp_create_nonce('neoforum_commit_report')."\")' class='neoforum_report_send'>".esc_html__("Send report","neoforum")."</button><button onclick='neoforum_forum_selector.parentNode.style.display=\"none\";' class='neoforum_report_send'>".esc_html__("Cancel","neoforum")."</button></div>";
    $res=array("result"=>true, "data"=>$data);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_report", "neoforum_get_report_form_handler");

function neoforum_commit_report_handler(){
    if ( !neoforum_is_user_can_post('report') or ! wp_verify_nonce($_POST['nonce'], 'neoforum_commit_report')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    if (!is_numeric($_POST['postid']))
    {
        $res=array("result"=>false, "message"=>"NaN");
        echo json_encode($res);
        wp_die();
    }
    if (strlen($_POST['data'])>1024)
    {
        $res=array("result"=>false, "message"=>__("Report length must be less then 1024 characters!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $reports=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_reports WHERE postid=".esc_sql($_POST['postid'])." AND userid=".get_current_user_id()." LIMIT 1");
    if (count($reports)>0)
    {
        $res=array("result"=>false, "message"=>__("You already reported this post!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    $rep=sanitize_textarea_field($_POST['data']);
    $postdata=$wpdb->get_results("SELECT topicid, forumid FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($_POST['postid'])." LIMIT 1", ARRAY_A)[0];
    $wpdb->query("INSERT INTO ".$wpdb->prefix."neoforum_reports (userid, postid, topicid, forumid, comment, date) VALUES (".get_current_user_id().", ".esc_sql($_POST['postid']).", ".$postdata['topicid'].", ".$postdata['forumid'].", '".esc_sql($rep)."', '".current_time('mysql')."')");
    $res=array("result"=>true);
    neoforum_update_reports_count($postdata['forumid'], 1);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_commit_report", "neoforum_commit_report_handler");
?>
