<?php
//Here all to handle themes and shortcode display

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define("neoforum_THEME_DIR", neoforum_DIR."\\nf-themes\\".get_option("neoforum_theme"));
define("neoforum_THEME_URL", neoforum_URL."nf-themes/".get_option("neoforum_theme"));

function neoforum_shortcode_handler(){
    global $wpdb;
    if (neoforum_is_user_banned(get_current_user_id())){
        neoforum_banmessage();
    }
    $user=wp_get_current_user();
    if($user!=0 and $user->ID!=0){
        neoforum_add_user($user->ID);
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET last_visit='".current_time("mysql")."' WHERE userid=".$user->ID." LIMIT 1");  
    } 
    ?><div class='neoforum'><?php
    wp_enqueue_style("neoforum_neoforum", neoforum_URL."/nf-includes/css/neoforum.css");
    echo(neoforum_template::include_all_css());
    echo neoforum_themes::report_message();
    if (!is_null($_GET['user'])) {
        neoforum_template::display_mainmenu();
        neoforum_template::user_page($_GET['user']);
    }
    if (get_query_var("forum")==null and $_GET['action']==null and $_GET['user']==null) {
        neoforum_template::display_mainmenu();
        echo(neoforum_template::display_mainboard());
    }
    if (get_query_var("forum")!=null and get_query_var("topic")==null and $_GET['action']==null and $_GET['user']==null) {
        global $wpdb;
        if(count($wpdb->get_results("SELECT forumid FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".esc_sql(get_query_var("forum"))."' LIMIT 1"))==0){
            neoforum_message(__("Forum does not exists!", "neoforum"));
            return;
        } 
        neoforum_template::display_mainmenu();
        echo(neoforum_template::display_forum());
    }
    if (get_query_var("topic")!=null and $_GET['action']==null and $_GET['user']==null) {
        global $wpdb;
        if(count($wpdb->get_results("SELECT topicid FROM ".$wpdb->prefix."neoforum_topics WHERE slug='".esc_sql(get_query_var("topic"))."' LIMIT 1"))==0){
            neoforum_message(__("Topic does not exists!", "neoforum"));
            return;
        } 
        neoforum_template::display_mainmenu();
        echo(neoforum_template::display_topic());
    }
    if (!is_null($_GET['action'])){
        if ($_GET['action']=="newpost") {
            echo(neoforum_template::new_post());
        }
        if ($_GET['action']=="startnewtopic") {
            neoforum_template::display_mainmenu();
            echo(neoforum_template::new_topic());
        }
        if ($_GET['action']=="newtopic") {
            echo(neoforum_template::new_topic_handler());
        }
        if ($_GET['action']=="edittopic") {
            neoforum_template::display_mainmenu();
            echo(neoforum_template::edit_topic());
        }
        if ($_GET['action']=="edittopichandler") {
            echo(neoforum_template::edit_topic_handler());
        }
        if ($_GET['action']=="search") {
            neoforum_template::display_mainmenu();
            echo(neoforum_template::search());
        }
    }
    ?>
    </div><?php
    wp_register_script(
    'neoforum_neoforum_js',
    plugins_url( 'js/neoforum.js', __FILE__ ),
    array( 'wp-i18n' ),
    '0.0.1'
    );
    wp_set_script_translations( 'neoforum_neoforum_js', 'neoforum' );
    wp_enqueue_script( "neoforum_neoforum_js");
    global $neoforum_globals;
    wp_localize_script('neoforum_neoforum_js', 'neoforum_globals', $neoforum_globals);
}

class neoforum_template{ //here functions to include files from theme
    public static function include_all_css(){//attach all css
        $scanned=scandir(neoforum_THEME_DIR."\\css");
        $res="";
        foreach ($scanned as $value) {
            if (!is_dir(neoforum_THEME_DIR."\\css\\".$value) and $value!="." and $value!=".."){
                wp_enqueue_style("neoforum_theme_style_".$value, neoforum_THEME_URL."/css/".$value);
            }
        }
    }
    public static function display_mainboard(){
        return include(neoforum_THEME_DIR."\\forums-list.php");
    }
    public static function display_mainmenu(){
        $userid=get_current_user_id();
        ?>
            <ul class="neoforum_mainmenu">
                <li><a href="<?php echo get_site_url()."/".get_option('neoforum_forum_url') ?>"><?php esc_html_e("Main page","neoforum") ?></a></li>
                <?php if ($userid!=0){ ?>
                <li><a href="<?php echo get_site_url()."/".get_option('neoforum_forum_url')."/?user=".$userid ?>"><?php esc_html_e("Profile","neoforum") ?></a></li>
                <?php } ?>
                <li><a href="<?php echo get_site_url()."/".get_option('neoforum_forum_url')."/?action=search" ?>"><?php esc_html_e("Search","neoforum") ?></a></li>
            </ul>
        <?php
    }
    public static function display_forum(){
        if(!neoforum_is_user_can_forum('read')){
            neoforum_message(esc_html__("You can't do this action!","neoforum"));
            return;
        }
        return include(neoforum_THEME_DIR."\\single-forum.php");
    }
    public static function display_topic(){
        if(!neoforum_is_user_can_topic('read')){
            neoforum_message(esc_html__("You can't do this action!","neoforum"));
            wp_die();
        }
        ?>
        <?php
        wp_enqueue_style("neoforum_style_topic", get_site_url()."/wp-content/plugins/neoforum/nf-includes/css/topic.css");
        global $wpdb;
        $id=get_current_user_id();
        $current_user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."users FULL JOIN ".$wpdb->prefix."neoforum_users ON ID=".$wpdb->prefix."neoforum_users.userid WHERE ID=$id LIMIT 1", ARRAY_A)[0];
        include(neoforum_THEME_DIR."\\topic.php");
        neoforum_themes::place_post_editor_template();
        ?>
        <div class="neoforum_forum_selector_cover" onclick="this==event.target ? this.style.display='none' : null">
            <div class="neoforum_forum_selector">
                <div class="neoforum_forum_selector_header">
                </div>
                <div class="neoforum_forum_selector_content">
                </div>
            </div>
        </div>
        <?php
        wp_register_script(
            'neoforum_topic_js',
            plugins_url( 'neoforum/nf-includes/js/topic.js'),
            array( 'wp-i18n' ),
            '0.0.1'
        );
        wp_set_script_translations( 'neoforum_topic_js', 'neoforum' );
        wp_enqueue_script( "neoforum_topic_js", "", array( 'wp-i18n' ));
        //views increase
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_subscribes SET sent=0 WHERE topicid=".neoforum_get_topic_id_by_slug(get_query_var('topic'))." AND userid=".get_current_user_id());
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET views_num=views_num+1 WHERE slug='".get_query_var('topic')."'");        
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET read_by=IF(read_by NOT LIKE '%;".$id.";%' AND read_by NOT LIKE '".$id.";%', CONCAT(read_by, '".$id.";'), read_by) WHERE slug='".get_query_var('topic')."'");
    }
    public static function new_post(){
        return include( neoforum_DIR . '/nf-includes/new-post-handler.php' );
    }
    public static function render_post($post, $forum=null, $current_user=null){
            if($post['in_trash'])
            return;
            global $wpdb;
            $user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users FULL JOIN ".$wpdb->prefix."users ON userid=".$wpdb->prefix."users.ID WHERE userid=".$post['authorid']." LIMIT 1", ARRAY_A)[0];
            return include(neoforum_THEME_DIR."\\post.php");
    }
    public static function new_topic(){
        wp_enqueue_style("neoforum_style_topic", get_site_url()."/wp-content/plugins/neoforum/nf-includes/css/topic.css");
        wp_register_script(
            'neoforum_topic_js',
            plugins_url( 'neoforum/nf-includes/js/topic.js'),
            array( 'wp-i18n' ),
            '0.0.1'
        );
        wp_set_script_translations( 'neoforum_topic_js', 'neoforum' );
        wp_enqueue_script( "neoforum_topic_js", "", array( 'wp-i18n' ));
        return include( neoforum_DIR . '/nf-includes/new-topic.php' );
    }
    public static function new_topic_handler(){
        return include( neoforum_DIR . '/nf-includes/new-topic-handler.php' );
    }
    public static function edit_topic(){
        wp_enqueue_style("neoforum_style_topic", get_site_url()."/wp-content/plugins/neoforum/nf-includes/css/topic.css");
        wp_register_script(
            'neoforum_topic_js',
            plugins_url( 'neoforum/nf-includes/js/topic.js'),
            array( 'wp-i18n' ),
            '0.0.1'
        );
        wp_set_script_translations( 'neoforum_topic_js', 'neoforum' );
        wp_enqueue_script( "neoforum_topic_js", "", array( 'wp-i18n' ));
        return include( neoforum_DIR . '/nf-includes/edit-topic.php' );
    }
    public static function edit_topic_handler(){
        return include( neoforum_DIR . '/nf-includes/edit-topic-handler.php' );
    }
    public static function user_page($userid){
        return include( neoforum_DIR . '/nf-includes/user-page.php' );
    }
    public static function search($text=null){
        return include( neoforum_DIR . '/nf-includes/search.php' );
    }
}

class neoforum_themes{ //here functions to include content in theme files
    public static function get_forums_list($forum=null){
        global $wpdb;
        if ($forum==null)
            $list=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums ORDER BY ord", ARRAY_A);
        if (gettype($forum)=='integer')
            $list=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE parent_forum=".$forum." ORDER BY ord", ARRAY_A);
        if (gettype($forum)=='string')
            $list=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE parent_forum=(SELECT forumid FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".$forum."' LIMIT 1) ORDER BY ord", ARRAY_A);
        return $list;
    }
    public static function breadcrumbs(){
        global $wpdb;
        ?>
        <div class="neoforum_breadcrumbs">
        <a class="neoforum_breadcrumbs_item" href="<?php echo get_site_url()."/".get_option("neoforum_forum_url"); ?>"><?php echo get_the_title(get_page_by_path(get_option("neoforum_forum_url"))); ?></a>
        <?php
        $forum=get_query_var("forum");
        if ($forum!=null){
            ?>
                <span class="neoforum_breadcrumbs_divider">>>></span>
                <a class="neoforum_breadcrumbs_item" href="<?php echo get_site_url()."/".get_option("neoforum_forum_url")."/".$forum; ?>"><?php echo $wpdb->get_var("SELECT forum_name FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".$forum."' LIMIT 1", 0, 0); ?></a>
            <?php
        }
        $topic=get_query_var("topic");
        if ($topic!=null){
            ?>
                <span class="neoforum_breadcrumbs_divider">>>></span>
                <span class="neoforum_breadcrumbs_item"><?php echo $wpdb->get_var("SELECT topic_title FROM ".$wpdb->prefix."neoforum_topics WHERE slug='".esc_sql($topic)."' LIMIT 1", 0, 0); ?></a>
            <?php
        }
        echo "</div>";
    }
    public static function get_topics_of_forum($slug, $page=1){
        global $wpdb;
        $wpdb->query("SET @rows=0");
        $list=$wpdb->get_results("SELECT *, (@rows:=@rows+1) AS num FROM ".$wpdb->prefix."neoforum_topics WHERE forumid=".neoforum_get_forum_id_by_slug($slug)." AND in_trash=0 ORDER BY is_pinned DESC, is_approved ASC, lastpost_date DESC", ARRAY_A);
        $pagelen=get_option("neoforum_topics_per_page");
        $res=Array();
        if($page==null){
            $page=1;
        }
        if($page=="last"){
            $page=intdiv(count($list), $pagelen)+1;

        }
        $first=$pagelen*$page-$pagelen+1;
        foreach ($list as $item) {
            if ($item['num']>=$first AND $item['num']<$first+$pagelen)
            {
                $res[]=$item;
            }
        }
        return $res;
    }
    public static function get_moders_of_forum($forum){
        global $wpdb;
        if (gettype($forum)=="integer"){
            $forum=$wpdb->get_results("SELECT moderators FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=$forum LIMIT 1");
        }
        if (gettype($forum)=="string"){
            $forum=$wpdb->get_results("SELECT moderators FROM ".$wpdb->prefix."neoforum_forums WHERE slug='".$forum."' LIMIT 1");
        }
        $m=neoforum_return_users_list($forum, "moderators");
        $res="";
        foreach ($m as $id) {
            $moder=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."users WHERE ID=".$id[0]." LIMIT 1", ARRAY_A)[0];
            if (strlen($moder['display_name'])>0){
                $res.="<a href='".get_site_url()."/".get_option('neoforum_forum_url')."/?user=".$id[0]."' class='neoforum_moder'>".$moder['display_name']."</a>, ";
            }
        }
        return substr($res, 0, -2);
    }

    public static function mark_as_read_button($id=null){
        if ($id==null)
            $id=get_current_user_id();
        if ($id!=0)
            return "<button class='neoforum_mark_as_read' onclick='neoforum_mark_as_read(this, `".wp_create_nonce("neoforum_mark_as_read")."`, ".$id.")'>".esc_html__("Mark all as read","neoforum")."</button>";
    }
    public static function report_message($user=null){
        global $wpdb;
        if ($user==null)
            $user=$wpdb->get_results("SELECT reports_num FROM ".$wpdb->prefix."neoforum_users WHERE userid=".get_current_user_id()." LIMIT 1", ARRAY_A)[0];
        if ($user['reports_num']>0){
            return "<div class='neoforum_reports_message_wrap'><a class='neoforum_reports_message' href='".get_site_url()."/wp-admin/admin.php?page=neoforum_reports'>".esc_html__("New reports").": ".$user['reports_num']."</a></div>";
        }
    }
    public static function get_online_users(){
        global $wpdb;
        $users=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users FULL JOIN ".$wpdb->prefix."users ON userid=".$wpdb->prefix."users.ID WHERE TIME_TO_SEC(TIMEDIFF('".current_time("mysql")."', last_visit))/60<15 ORDER BY last_visit DESC", ARRAY_A);
        $res="";
        foreach ($users as $u) {
            $status="";
            $u["user_caps"]=="administrator" ? $status="neoforum_admin" : null;
            $u["user_caps"]=="supermoderator" ? $status="neoforum_super" : null;
            $res.="<a class='neoforum_username ".$status."' href='".get_site_url()."/".get_option('neoforum_forum_url')."/?user=".$u['userid']."'>".esc_html($u['display_name'])."</a>, ";
        }
        return substr($res, 0, -2);
    }
    public static function get_online_on_forum($forum_slug){
        global $wpdb;
        $users=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users FULL JOIN ".$wpdb->prefix."users ON userid=".$wpdb->prefix."users.ID WHERE TIME_TO_SEC(TIMEDIFF('".current_time("mysql")."', last_visit))/60<15 AND (last_place LIKE '%forum=".$forum_slug.";%' OR last_place LIKE '%forum=".$forum_slug."') ORDER BY last_visit DESC", ARRAY_A);
        $res="";
        foreach ($users as $u) {
            $status="";
            $u["user_caps"]=="administrator" ? $status="neoforum_admin" : null;
            $u["user_caps"]=="supermoderator" ? $status="neoforum_super" : null;
            $res.="<a class='neoforum_username ".$status."' href='".get_site_url()."/".get_option('neoforum_forum_url')."/?user=".$u['userid']."'>".esc_html($u['display_name'])."</a>, ";
        }
        return substr($res, 0, -2);
    }
    public static function get_online_on_topic($topic_slug){
        global $wpdb;
        $users=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users FULL JOIN ".$wpdb->prefix."users ON userid=".$wpdb->prefix."users.ID WHERE TIME_TO_SEC(TIMEDIFF('".current_time("mysql")."', last_visit))/60<15 AND last_place LIKE '%topic=".$topic_slug."%' ORDER BY last_visit DESC", ARRAY_A);
        $res="";
        foreach ($users as $u) {
            $status="";
            $u["user_caps"]=="administrator" ? $status="neoforum_admin" : null;
            $u["user_caps"]=="supermoderator" ? $status="neoforum_super" : null;
            $res.="<a class='neoforum_username ".$status."' href='".get_site_url()."/".get_option('neoforum_forum_url')."/?user=".$u['userid']."'>".esc_html($u['display_name'])."</a>, ";
        }
        return substr($res, 0, -2);
    }

    public static function get_posts_of_topic($slug, $page=1){
        global $wpdb;
        $wpdb->query("SET @rows=0");
        $list=$wpdb->get_results("SELECT *, (@rows:=@rows+1) AS num, (SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_attachments WHERE postid=".$wpdb->prefix."neoforum_posts.postid) AS filenum FROM ".$wpdb->prefix."neoforum_posts WHERE topicid=".neoforum_get_topic_id_by_slug($slug)." AND in_trash=0 ORDER BY is_first DESC, creation_date ASC", ARRAY_A);
        $list[0]['firstpost']=true;
        $pagelen=get_option("neoforum_posts_per_page");
        $res=Array();
        if($page==null){
            $page=1;
        }
        if($page=="last"){
            if(count($list)%$pagelen==0)
                $page=intdiv(count($list), $pagelen);
            else
                $page=intdiv(count($list), $pagelen)+1;
        }
        $first=$pagelen*$page-$pagelen+1;
        foreach ($list as $item) {
            if ($item['num']>=$first AND $item['num']<$first+$pagelen)
            {
                $res[]=$item;
            }
        }
        return $res;
    }
    public static function get_topic_name($slug){
        global $wpdb;
        $name=$wpdb->get_results("SELECT topic_title FROM ".$wpdb->prefix."neoforum_topics WHERE slug='".esc_sql($slug)."' LIMIT 1", ARRAY_A);
        $name!=null ? $name=$name[0]['topic_title'] : null;
        return esc_html($name);
    }
    public static function get_topic_forum($slug){
        global $wpdb;
        $forumid=$wpdb->get_results("SELECT forumid FROM ".$wpdb->prefix."neoforum_topics WHERE slug='".esc_sql($slug)."' LIMIT 1", ARRAY_A)[0]['forumid'];
        return $forumid;
    }
    public static function get_user_data($user, $data){
        global $wpdb;
        if (is_numeric($user) or gettype($user)=="string"){
            $user=$wpdb->get_results("SELECT $data FROM ".$wpdb->prefix."users FULL JOIN ".$wpdb->prefix."neoforum_users ON ID=".$wpdb->prefix."neoforum_users.userid WHERE ID=$user LIMIT 1", ARRAY_A)[0];
        }
        return $user[$data];
    }
    public static function get_user_name($sql_row){
        if ($sql_row['authorid']==0){
            return "<span class='neoforum_guest'>".$sql_row['authorname']."</span>";
        }

        else{
            $online=((strtotime(current_time("mysql")))-strtotime((neoforum_themes::get_user_data($sql_row['authorid'], 'last_visit'))))/60>15 ? "neoforum_user_offline" : "neoforum_user_online";
            return "<a class='neoforum_username ".$online."' href='".get_site_url()."/".get_option('neoforum_forum_url')."/?user=".$sql_row['authorid']."'>".esc_html(get_user_by('id', $sql_row['authorid'])->display_name)."</a>";
        }
    }
    public static function get_user_caps($user_row, $forum_row=null){
        if (user_can($user_row['userid'], 'administrator')){
            return "<div class='neoforum_usercaps neoforum_administrator'>".esc_html__("Administrator","neoforum")."</div>";
        }
        switch ($user_row['user_caps']) {
            case 'administrator':
                return "<div class='neoforum_usercaps neoforum_administrator'>".esc_html__("Administrator","neoforum")."</div>";
                break;
            case 'supermoderator':
                return "<div class='neoforum_usercaps neoforum_supermoderator'>".esc_html__("Supermoderator","neoforum")."</div>";
                break;
        }
        if (neoforum_is_user_banned($user_row['userid'])){
            return "<div class='neoforum_usercaps neoforum_banned'>".esc_html__("Banned","neoforum")."</div>";
        }
        if ($forum_row!=null and neoforum_is_user_moder($forum_row, $user_row)){
            return "<div class='neoforum_usercaps neoforum_moderator'>".esc_html__("Moderator","neoforum")."</div>";
        }
        return "";
    }
    public static function get_user_id($sql_row){
        if ($sql_row['is_guest']){
                return 0;
            }
        else{
            return get_user_by('id', $sql_row['authorid'])->ID;
        }
    }
    public static function get_user_avatar($id, $exists=false){
        $files = glob(neoforum_DIR."\\nf-userdata\\avatars\\".$id."\\avatar.*");
        if (count($files)>0 and $files){
            preg_match('/^.*?\.([a-z]{1,10})/i', $files[0], $m);
            $avatar=neoforum_URL."nf-userdata/avatars/".$id."/avatar.".$m[1];
            $avatar_exists=true;
        }
        else{
            $avatar=get_avatar_url($id);
            $avatar_exists=false;
        }
        if($exists){
            return $avatar_exists;
        } else {
            return $avatar;
        }
    }
    public static function get_user_contacts($user, $with_text=false){
        $res="";
        if ($user['facebook']!="" and $user['facebook']!=null){
            $res.="<a rel='nofollow' href='".esc_html($user['facebook'])."' class='neoforum_contact neoforum_facebook'>".($with_text ? esc_html($user['facebook']) : '')."</a>";
        }
        if ($user['twitter']!="" and $user['twitter']!=null){
            $res.="<a rel='nofollow' href='".esc_html($user['twitter'])."' class='neoforum_contact neoforum_twitter'>".($with_text ? esc_html($user['twitter']) : '')."</a>";
        }
        if ($user['instagram']!="" and $user['instagram']!=null){
            $res.="<a rel='nofollow' href='".esc_html($user['instagram'])."' class='neoforum_contact neoforum_instagram'>".($with_text ? esc_html($user['instagram']) : '')."</a>";
        }
        return $res;
    }
    public static function ban_button($point_user, $user=null){
        if (!neoforum_is_user_can_user('ban', $point_user, $user) or neoforum_is_user_banned($point_user['userid']))
            return "";
        return "<button class='neoforum_ban_button' title='".esc_html__("Ban user","neoforum")."' onclick='neoforum_ban_user_menu(".$point_user['userid'].", \"".$point_user['display_name']."\", \"".wp_create_nonce("neoforum_get_ban_menu")."\")'>".esc_html__("Ban user","neoforum")."</button>";

    }
    public static function get_topic_last_post($topicid){
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE topicid=".esc_sql($topicid)." ORDER BY creation_date DESC LIMIT 1", ARRAY_A)[0];
    }
    public static function get_forum_last_post($forumid){
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE forumid=".esc_sql($forumid)." ORDER BY creation_date DESC LIMIT 1", ARRAY_A)[0];;
    }
    public static function create_topic_button($slug){
        if (!neoforum_is_user_can_topic('start')) return;
        ?>
        <a class="neoforum_create_topic" href="<?php echo (get_site_url()."/".get_option('neoforum_forum_url')."/?forum=".neoforum_get_forum_id_by_slug($slug)."&action=startnewtopic"); ?>">
            <?php esc_html_e("Start new topic", "neoforum") ?>
        </a>
        <?php
    }
    public static function place_text_editor($text=""){
        include( neoforum_DIR . '/neo visual editor/neo-visual-editor.php' );
    }
    public static function place_post_editor_template(){
        neoforum_show_post_editor();
    }
    public static function get_post_content($post){
        return neoforum_clean_post_content($post['content']);
    }
    public static function get_usercaption($user){
        return neoforum_clean_post_content($user['user_caption']);
    }
    public static function get_post_attachments($post){
        global $wpdb;
        $res="";
        if ($post['filenum']>0) {
            $res.='<div class="neoforum_attachments">';
            $res.="<div class='neoforum_attachments_header'>".esc_html__("Attachments", "neoforum").":</div>";
            $files=$wpdb->get_results("SELECT filename FROM ".$wpdb->prefix."neoforum_attachments WHERE postid=".$post['postid'], ARRAY_A);
            foreach ($files as $f) {
                $res.='<a href="'.neoforum_URL."/attachments/userdata/".$post['authorid'].'/'.$f['filename'].'" class="neoforum_attachment" data-user="'.$post['authorid'].'" data-nonce="'.wp_create_nonce("neoforum_delete_attach").'">'.$f['filename'].'</a><br>';
            }
            $res.="</div>";
        }
        return $res;
    }
    public static function get_post_edit_date($post){
        $res="";
        if ($post['edit_date']!=null){
            $res.='<div class="neoforum_post_edited_text">';
            $res.=esc_html__("Post edited","neoforum")." ".$post['edit_date']." "./*translators: by who post was edited*/esc_html__("by","neoforum")." <a class='neoforum_post_editor' href='".$post['editorid']."'>".get_user_option("display_name", $post['editorid'])."</a>";
            $res.="</div>";
        }
            return $res;
    }
}
?>
