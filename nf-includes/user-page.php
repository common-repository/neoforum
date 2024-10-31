<?php
//Functrions for topics here

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users LEFT JOIN ".$wpdb->prefix."users ON userid=".$wpdb->prefix."users.ID WHERE userid=".esc_sql($_GET['user'])." LIMIT 1", ARRAY_A)[0];
$current=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users LEFT JOIN ".$wpdb->prefix."users ON userid=".$wpdb->prefix."users.ID WHERE userid=".get_current_user_id()." LIMIT 1", ARRAY_A)[0];
$topics=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_topics WHERE authorid=".esc_sql($_GET['user']));
$avatar=neoforum_themes::get_user_avatar($user['ID']);
$avatar_exists=neoforum_themes::get_user_avatar($user['ID'], true);
if ($user['ID']==$current['ID'] or user_can($current['ID'], 'administrator') or $current['user_caps']=='administrator'){
    ?>
        <div class="neoforum_userpage">
        <div class="neoforum_userdata-container">
            <div class="neoforum_username-wrap">
                <div class="neoforum_username <?php echo (((strtotime(current_time("mysql"))-(strtotime($user['last_visit']))))/60>15 ? "neoforum_user_offline" : "neoforum_user_online"); ?>"><?php echo $user['display_name'] ?></div>
                <?php echo neoforum_themes::get_user_caps($user) ?>
                <div class="neoforum_user_avatar">
                    <img src="<?php echo $avatar ?>" alt="User avatar" class="neoforum_avatar"><br>
                    <button class="neoforum_avatar_delete" <?php if (!$avatar_exists) echo " style='display:none;' " ?> type="button" onclick="neoforum_avatar_delete(this, <?php echo $user['ID'] ?>, '<?php echo wp_create_nonce('neoforum_delete_avatar'); ?>');"><?php esc_html_e("Delete avatar", "neoforum") ?></button>
                    <input type="hidden" name="MAX_FILE_SIZE" value="512000">
                    <input type="file" name="neoforum_avatar" onchange="neoforum_avatar_upload(this, <?php echo $user['ID'] ?>, '<?php echo wp_create_nonce('neoforum_save_avatar'); ?>');"><br>
                </div>
            </div>
            <div class="neoforum_userdata-wrap">
                <div class="neoforum_user_posts"><?php echo esc_html__("Posts", "neoforum").": ".$user['posts_num'] ?></div>
                <div class="neoforum_user_topics"><?php echo esc_html__("Topics", "neoforum").": ".$topics ?></div>
                <div class="neoforum_contacts">
                    <label class="neoforum_facebook">
                    <input type="text" value="<?php echo $user['facebook'] ?>" oninput="neoforum_save_contact_trottle(this, <?php echo $user['ID'] ?>, 'facebook', this.value, '<?php echo wp_create_nonce('neoforum_save_contact'); ?>')"></label>
                    <label class="neoforum_twitter">
                    <input type="text" value="<?php echo $user['twitter'] ?>" oninput="neoforum_save_contact_trottle(this, <?php echo $user['ID'] ?>, 'twitter', this.value, '<?php echo wp_create_nonce('neoforum_save_contact'); ?>')"></label>
                    <label class="neoforum_instagram">
                    <input type="text" value="<?php echo $user['instagram'] ?>" oninput="neoforum_save_contact_trottle(this, <?php echo $user['ID'] ?>, 'instagram', this.value, '<?php echo wp_create_nonce('neoforum_save_contact'); ?>')"></label>
                </div>
            </div>
        </div>
        <div class="neoforum_usercaption">
            <div class="neoforum_usercaption_wrapper">
                <button class="neoforum_edit_usercaption" onclick="this.parentNode.style.display='none'; document.querySelector('.neoforum_user_caption_editor').style.display='block';"><?php esc_html_e("Edit", "neoforum") ?></button>
                <?php esc_html_e("User caption", "neoforum") ?>:
                <div class="neoforum_usercaption_content">
                    <?php echo $user['user_caption'] ?>
                </div>
            </div>
            <div class="neoforum_user_caption_editor" style="display:none;">
                <?php neoforum_themes::place_text_editor($user['user_caption']); ?>
                <button class="neoforum_save_usercaption" onclick="neoforum_save_usercaption(this, <?php echo $user['ID'] ?>, document.querySelector(`[name='ne_text_field']`).value, '<?php echo wp_create_nonce('neoforum_save_usercaption'); ?>');"><?php esc_html_e("Save user caption", "neoforum") ?></button>
                <button class="neoforum_cancel_usercaption" onclick="neoforum_cancel_usercaption(this)"><?php esc_html_e("Cancel", "neoforum") ?></button>
            </div>
        </div>
        <div class="neoforum_userfiles">
            <div class='neoforum_attachments_header'><?php esc_html_e("Attachments", "neoforum")?>:</div>
            <?php global $wpdb;
            $files=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_attachments WHERE userid=".$user['ID'], ARRAY_A);
            foreach ($files as $f) {
                ?>
                    <div class="neoforum_remove_attach" onclick="neoforum_remove_attach(this, <?php echo $f['userid'] ?>, '<?php echo $f['filename'] ?>', <?php echo $f['postid'] ?>, '<?php echo(wp_create_nonce('neoforum_delete_attach')) ?>');"><?php echo $f['filename'] ?></div>
                <?php
            } ?>
        </div>
    </div>
    <?php
}
else{
    ?>
    <div class="neoforum_userpage">
        <div class="neoforum_username-wrap">
            <div class="neoforum_username <?php echo (((strtotime(current_time("mysql"))-(strtotime($user['last_visit']))))/60>15 ? "neoforum_user_offline" : "neoforum_user_online"); ?>"><?php echo $user['display_name'] ?></div>
            <div class="neoforum_userstatus <?php 
                if (user_can($user['ID'], 'administrator'))
                    $userstat="administrator";
                else
                    $userstat=$user['user_caps'];
                echo("neoforum_".$userstat);
             ?>"><?php echo esc_html($userstat) ?></div> 
            <div class="neoforum_user_avatar">
                <img src="<?php echo $avatar ?>" alt="User avatar" class="neoforum_avatar"><br>
            </div>
        </div>
        <div class="neoforum_userdata-wrap">
            <div class="neoforum_user_posts"><?php echo esc_html__("Posts", "neoforum").": ".$user['posts_num'] ?></div>
            <div class="neoforum_user_topics"><?php echo esc_html__("Topics", "neoforum").": ".$topics ?></div>
            <div class="neoforum_contacts">
                <?php echo neoforum_themes::get_user_contacts($user, true); ?>
            </div>
        </div>
        <div class="neoforum_usercaption">
            <?php esc_html_e("User caption", "neoforum") ?>:
            <div class="neoforum_usercaption_content">
                <?php echo $user['user_caption'] ?>
            </div>
        </div>
    </div>
    <?php
}
    wp_register_script(
        'neoforum_user-page_js',
        plugins_url( 'js/user-page.js', __FILE__ ),
        array( 'wp-i18n' ),
        '0.0.1'
    );
    wp_set_script_translations( 'neoforum_user-page_js', 'neoforum' );
    wp_enqueue_script( "neoforum_user-page_js");
    global $neoforum_globals;
    wp_localize_script('neoforum_user-pagejs', 'neoforum_globals', $neoforum_globals);
?>


