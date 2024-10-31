<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if (!current_user_can('administrator')){
    die;
}

if (is_null($_POST['agree'])){
    ?>
    <div style="margin:0 auto; text-align:center; width:400px">
        <?php esc_html_e("This action will delete all forum information - topics, posts, user data etc. Are you sure? Operation cannot be undone.", "neoforum"); ?>
        <form action="<?php echo(site_url().'/wp-admin/index.php?action=nf-uninstall') ?>" method="post">
            <input type="hidden" name="agree" value="true">
            <input type="hidden" name="nonce" value="<?php echo(wp_create_nonce('neoforum_uninstall')) ?>">
            <button type="Submit"><?php esc_html_e("I'm sure. Uninstall!","neoforum"); ?></button>
        </form>
        <button type="button" onclick="location.href='<?php echo(site_url()."/wp-admin/plugins.php"); ?>';"><?php esc_html_e("No, bring me back", "neoforum") ?></button>
    </div>
    <?php
    return;
}
//uninstalling begins
if(!wp_verify_nonce($_POST['nonce'], 'neoforum_uninstall')){
    die;
}

global $wpdb;
global $wp_filesystem;
wp_filesystem();
$wp_filesystem->delete(neoforum_DIR."\\nf-userdata\attachments", true);
$wp_filesystem->delete(neoforum_DIR."\\nf-userdata\avatars", true);

$wpdb->query("DROP TABLE ".$wpdb->prefix."neoforum_forums");
$wpdb->query("DROP TABLE ".$wpdb->prefix."neoforum_topics");
$wpdb->query("DROP TABLE ".$wpdb->prefix."neoforum_posts");
$wpdb->query("DROP TABLE ".$wpdb->prefix."neoforum_users");
$wpdb->query("DROP TABLE ".$wpdb->prefix."neoforum_reports");
$wpdb->query("DROP TABLE ".$wpdb->prefix."neoforum_subscribes");
$wpdb->query("DROP TABLE ".$wpdb->prefix."neoforum_attachments");

delete_option(
            "neoforum_forum_url"
    );
    delete_option(
            "neoforum_old_forum_page"
    );
    delete_option(
            "neoforum_theme"
    );
    delete_option(
            "neoforum_guests_can_topics"
    );
    delete_option(
            "neoforum_guests_can_posts"
    );
    delete_option(
            "neoforum_topics_need_approving"
    );
    delete_option(
            "neoforum_topics_need_solving"
    );
    delete_option(
            "neoforum_posts_per_page"
    );
    delete_option(
            "neoforum_topics_per_page"
    );
    delete_option(
            "neoforum_allow_links"
    );
    delete_option(
            "neoforum_can_upload"
    );
    delete_option(
            "neoforum_max_file_size"
    );
    delete_option(
            "neoforum_max_folder_size"
    );

deactivate_plugins( plugin_basename(neoforum_DIR."\\neoforum.php"));
?>
<script type="text/javascript">
    location.href="<?php echo(site_url()."/wp-admin/plugins.php"); ?>";
</script>
