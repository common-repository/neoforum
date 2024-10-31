<?php
//Functrions for topics here

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$userid=get_current_user_id();
$username="";
if (!is_numeric($_POST['topic']))
    {
        neoforum_message(__("Wrong topic id!", "neoforum"));
        return;
    }

if(!wp_verify_nonce( $_POST['nonce'], 'neoforum_reply_topic' ) or !neoforum_is_user_can_post("post", null, neoforum_get_topic_by_id($_POST['topic']))){
    neoforum_message(__("You can't do this action!", "neoforum"));
    return;
}

if($userid==0){
    if (strlen($_POST['guestname'])==0){
        neoforum_message(__("You must enter your nickname!", "neoforum"));
        return;
    }
    if (strlen($_POST['guestname'])>256){
        neoforum_message(__("Your nickname must be no longer then 256 characters!", "neoforum"));
        return;
    }
    $username=$_POST['guestname'];
}

$wait=neoforum_create_post($_POST['ne_text_field'], $_POST['topic'], $userid, $username);
global $wpdb;
esc_html_e("New post successfully created","neoforum");
$topic=$wpdb->get_results("SELECT slug, forumid FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".$_POST['topic']." LIMIT 1", ARRAY_A)[0];
$forum=$wpdb->get_var("SELECT slug FROM ".$wpdb->prefix."neoforum_forums WHERE forumid=".$topic['forumid']." LIMIT 1");
$redir=get_site_url()."/".get_option("neoforum_forum_url")."/".$forum."/".$topic['slug']."/"."pg=last";
?>
<script type="text/javascript">
    setTimeout(function(){
        location.href="<?php echo $redir ?>";
    }, <?php echo $wait ?>);
</script>
<?php wp_die(); ?>
