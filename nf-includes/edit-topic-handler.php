<?php
//Functrions for topics here

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$userid=get_current_user_id();
$title=sanitize_text_field($_POST['topic_title']);
global $wpdb;

if (!is_numeric($_POST['topic']))
    {
        neoforum_message(__("Wrong topic id!", "neoforum"));
    }
if (!neoforum_is_topic_exists($_POST['topic']))
    {
        neoforum_message(__("This topic doesn't exists!", "neoforum"));
    }

if(strlen($title)<1){
    neoforum_message(__("Topic title is empty!", "neoforum"));
    return;
}
if(strlen(wp_kses($_POST['ne_text_field'], array('img' => array())))<1){
    neoforum_message(__("Your post is empty!", "neoforum"));
    return;
}

if(!is_numeric($userid)){
    neoforum_message(__("Wrong user id value", "neoforum"));
    return;
}

if(!neoforum_is_user_exists($userid)){
    neoforum_message(__("User does not exists!", "neoforum"));
    return;
}

if(!wp_verify_nonce( $_POST['nonce'], 'neoforum_edit_topic' ) or !neoforum_is_user_can_topic("edit", neoforum_get_topic_by_id($_POST['topic']))){
    neoforum_message(__("You can't do this action!", "neoforum"));
    return;
}

neoforum_edit_topic($title, sanitize_text_field($_POST['topic_descr']), $_POST['ne_text_field'], $_POST['topic'], $userid);
global $wpdb;
esc_html_e("Topic successfully edited","neoforum");
$topic=$wpdb->get_results("SELECT slug, forumid FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$id." LIMIT 1", ARRAY_A)[0];
$forum=$wpdb->get_var("SELECT slug FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".esc_sql($_POST['forum'])." LIMIT 1");
$redir=get_site_url()."/".get_option("neoforum_forum_url")."/".$forum."/".$topic['slug'];
?>
<script type="text/javascript">
    location.href="<?php echo $redir ?>";
</script>
return;
?>
