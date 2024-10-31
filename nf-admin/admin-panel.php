<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

include ("functions.php");

function neoforum_admin_menu(){
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
      <h1><?php esc_html( get_admin_page_title() ); ?></h1>
      <form action="options.php" method="post">
        <?php
        settings_fields("neoforum_admin");
        do_settings_sections( 'neoforum_admin' );
        submit_button( __('Save Settings', "neoforum") );
        ?>
      </form>
    </div>
<?php
}

function neoforum_forums_menu(){
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    global $wpdb;
    $forums=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums ORDER BY ord", ARRAY_A);
    wp_enqueue_style("neoforum_admim_forums", neoforum_URL."nf-admin/css/forums.css");
    ?>
    <div class="wrap">
        <div class="neoforum_forums_descr">
        </div>
        <div class="neoforum_forums_panel_contorls">
            <button class="neoforum_create_forum" onclick="neoforum_forum_option_change(event, 0, <?php echo("'".wp_create_nonce('neoforum_admin_option_change')."',") ?> 'neoforum_create_section');"><?php esc_html_e("Create new section", "neoforum"); ?></button>
            <button class="neoforum_create_forum" onclick="neoforum_forum_option_change(event, 0, <?php echo("'".wp_create_nonce('neoforum_admin_option_change')."',") ?> 'neoforum_create_forum');"><?php esc_html_e("Create new forum", "neoforum"); ?></button>
            <button class="neoforum_create_forum" onclick="neoforum_recalculate_forums(this, <?php echo("'".wp_create_nonce('neoforum_recalculate_forums')."'") ?>);"><?php esc_html_e("Recalculate all forums stats", "neoforum"); ?></button>
        </div>
        <div class="neoforum_forum_drag_place"></div>
        <?php
            foreach ($forums as $forum) {
                if($forum['parent_forum']==0){
                    neoforum_forum_display($forum, $forums);
                }
            }
        ?>
    </div>
    <div id="neoforum_options_blocked_cover"></div>
    <div class="neoforum_users_editor_cover">
        <div id="neoforum_users_editor">
            <div class="neoforum_users_editor_header"></div>
            <div class="neoforum_users_editor_wrap">
                <input type="text" oninput="neoforum_search_user(this.value, neoforum_currentforum, '<?php echo(wp_create_nonce("neoforum_search_moder")) ?>', this)" id="neoforum_users_input" placeholder="<?php esc_html_e("Enter username here","neoforum"); ?>" class="neoforum_users_input_load">
                <div class="neoforum_users_editor_list">
                </div>
            </div>
            <div class="neoforum_users_editor_users">
            </div>
            <div class="neoforum_users_editor_controls">
            <?php esc_html_e("Notice: all added users and moderators will be able to read and moderate all subforums of forum where they were added.", "neoforum"); ?>
                <br><button type="button" onclick="neoforum_users_editor.parentNode.style.display='none'"><?php esc_html_e("Close","neoforum"); ?></button>
            </div>
        </div>
    </div>
    <?php
}

function neoforum_reports_menu(){
    global $wpdb;
    wp_enqueue_style("neoforum_admim_trash", neoforum_URL."/nf-admin/css/trash.css") ?>
    <div class="neoforum_reports_wrap">
        <h1>
            <?php esc_html_e("Awaiting reports:","neoforum"); ?>
        </h1>
        <div class="neoforum_reports_content">
            <?php
                $pagintaion=neoforum_reports_render();
            ?>
        </div>
        <div class="neoforum_reports_pagination">
            <?php echo $pagintaion ?>
        </div>
    </div>
    <?php

}

function neoforum_trash_menu(){
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    global $wpdb;
    $forums=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE is_section=0 ORDER BY ord", ARRAY_A);
    wp_enqueue_style("neoforum_admim_trash", neoforum_URL."/nf-admin/css/trash.css") ?>
    <h1>
        <?php esc_html_e("Trash", "neoforum"); ?>
    </h1>
    <form class="neoforum_trash_filters_wrap" name="trashSearch">
        <div class="neoforum_trash_controls">
            <button type="button" onclick="neoforum_delete_all_trash('<?php echo wp_create_nonce("neoforum_delete_all_trash"); ?>')"><?php esc_html_e("Delete ALL trash", "neoforum"); ?></button>
        </div>
        <label><input class="neoforum_trash_type_topics" type="radio" name="searchtype" value="topics" checked>
            <?php esc_html_e("Search in topics", "neoforum"); ?>
        </label>
        <label><input class="neoforum_trash_type_posts" type="radio" name="searchtype" value="posts">
            <?php esc_html_e("Search in posts", "neoforum"); ?>
        </label>
        <div class="neoforum_trash_filters">
            <label> <?php esc_html_e("After", "neoforum") ?>:
                <input type="date" name="date_after">
            </label>
            <label> <?php esc_html_e("Before", "neoforum") ?>:
                <input type="date" name="date_before">
            </label>
            <br>
            <label> <?php esc_html_e("Username", "neoforum") ?>:
                <input type="text" name="username">
            </label>
            <div class="neoforum_username_list"></div>
            <label> <?php esc_html_e("Forum", "neoforum") ?>:
                <select class="neoforum_trash_forum_select" name="forum">
                    <option value="NULL"><?php esc_html_e("All forums", "neoforum") ?>...</option>
                    <?php
                        foreach ($forums as $f) {
                            echo "<option value='".$f['forumid']."'>".$f['forum_name']."</option>";
                        }
                    ?>
                </select>
            </label>
            <br>
            <label> <?php esc_html_e("Including words", "neoforum") ?>:
                    <input type="text" name="word_filter">
            </label>
            <label> <?php esc_html_e("Words in topic title", "neoforum") ?>:
                    <input type="text" name="title_filter">
            </label>
        </div>
        <button type="submit" class="neoforum_trash_submit" onclick="neoforum_trash_search(event, this, '<?php echo wp_create_nonce('neoforum_search') ?>')"><?php esc_html_e("Search!", "neoforum") ?></button>
    </form>
    <select class="neoforum_trash_actions" onchange="neoforum_controls_change(event)">
        <option value="NULL"><?php esc_html_e("Choose action", "neoforum") ?>...</option>
        <option value="restore" data-nonce="<?php echo wp_create_nonce('neoforum_restore_items'); ?>"><?php esc_html_e("Restore selected", "neoforum") ?></option>
        <option value="eradicate" data-nonce="<?php echo wp_create_nonce('neoforum_eradicate_items'); ?>"><?php esc_html_e("Delete selected", "neoforum") ?></option>
    </select>
    <div class="neoforum_trash_content">
        
    </div>
    <div id="neoforum_options_blocked_cover"></div>
    <?php
}

function neoforum_moderation_menu(){
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    wp_enqueue_style("neoforum_admim_users", neoforum_URL."/nf-admin/css/users.css") ?>
    <div class="neoforum_users_wrap">
        <h1>
        <?php esc_html_e("Users", "neoforum"); ?>
        </h1>
        <button onclick="neoforum_recalculate_users(this, <?php echo("'".wp_create_nonce('neoforum_recalculate_users')."'") ?>);"><?php esc_html_e("Recalculate all users stats", "neoforum"); ?></button>
        <div class="neoforum_user_type">
            <label><input type="radio" name="usertype" value="admins" onclick="neoforum_get_users_list(this.value, '<?php echo(wp_create_nonce("neoforum_search_users")) ?>')"><?php esc_html_e("Administrators", "neoforum") ?>*</label>
            <label><input type="radio" name="usertype" value="supers" onclick="neoforum_get_users_list(this.value, '<?php echo(wp_create_nonce("neoforum_search_users")) ?>')"><?php esc_html_e("Supermoderators", "neoforum") ?></label>
            <label><input type="radio" name="usertype" value="banned" onclick="neoforum_get_users_list(this.value, '<?php echo(wp_create_nonce("neoforum_search_users")) ?>')"><?php esc_html_e("Banned users", "neoforum") ?></label>
            <div>
                <i>*<?php esc_html_e("Notice: ALL users with admin rights in site automatically has admin rights in forum", "neoforum") ?></i>
            </div>
        </div>
        <div class="neoforum_users_find">
            <h3><?php esc_html_e("Find user", "neoforum") ?>:</h3>
            <input type="text" oninput="neoforum_users_search(this, this.value, '<?php echo(wp_create_nonce("neoforum_search_users")) ?>')" id="neoforum_users_input" placeholder="<?php esc_html_e("Enter username here","neoforum"); ?>" class="neoforum_users_input_load">
            <div class="neoforum_users_editor_list">
            </div>
        </div>
        <div class="neoforum_users_content">
            
        </div>
        <div class="neoforum_users_window_wrap" onclick="event.target==this ? this.style.display='none' : null">
            <div class="neoforum_choose_admin">
                <button type='button' onclick="neoforum_make_admin(this, this.parentNode.dataset.id, 'super', '<?php echo(wp_create_nonce("neoforum_make_admin")) ?>')"><?php esc_html_e("Make supermoderator", "neoforum") ?></button><p>
                <?php esc_html_e("Supermoderators has moderator capabiblies in all forums. They can ban, delete, approve, edit posts, and they can see all restricted forums.", "neoforum") ?></p>
                <button type='button' onclick="neoforum_make_admin(this, this.parentNode.dataset.id, 'admin', '<?php echo(wp_create_nonce("neoforum_make_admin")) ?>')"><?php esc_html_e("Make administrator", "neoforum") ?></button><p>
                <?php esc_html_e("Same as supermoderators, and they can unban users banned by other admins, moderators and supermoderators. Still, managing options of Neoforum require admin capabilities in the site admin panel.", "neoforum") ?></p>
            </div>
            <div class="neoforum_choose_ban">
                <?php esc_html_e("Choose date when ban will expire", "neoforum") ?>:<br>
                <input type="date" required><br>
                <textarea placeholder="<?php esc_html_e("Leave comment", "neoforum") ?>"></textarea><br>
                <button type="button" onclick="neoforum_ban(this, this.parentNode.dataset.id, '<?php echo wp_create_nonce("neoforum_ban_user") ?>')"><?php esc_html_e("Ban user", "neoforum") ?></button>
            </div>
        </div>
    </div>
    <?php

}
function neoforum_menu_on_hook(){
    add_menu_page(
    __("NeoForum settings", "neoforum"),
    "NeoForum",
    'manage_options',
    "neoforum_admin",
    'neoforum_admin_menu'
);
    add_submenu_page( 
        "neoforum_admin", 
        __("Forums","neoforum"), 
        __("Forums","neoforum"), 
        "manage_options", 
        "neoforum_forums", 
        "neoforum_forums_menu"
);
    add_submenu_page( 
        "neoforum_admin", 
        __("Trash posts and topics","neoforum"), 
        __("Trash","neoforum"), 
        "manage_options", 
        "neoforum_trash", 
        "neoforum_trash_menu"
);
    add_submenu_page( 
        "neoforum_admin", 
        __("Users","neoforum"), 
        __("Users","neoforum"), 
        "manage_options", 
        "neoforum_moderation", 
        "neoforum_moderation_menu"
);
    add_submenu_page( 
        "neoforum_admin", 
        __("Reports","neoforum"), 
        __("Reports","neoforum"), 
        "read", 
        "neoforum_reports", 
        "neoforum_reports_menu"
);
}

add_action('admin_menu', 'neoforum_menu_on_hook');

?>
