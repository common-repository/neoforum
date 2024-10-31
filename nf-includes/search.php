<?php
//Functrions for topics here

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $wpdb;
$forums=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_forums WHERE is_section=0 ORDER BY ord", ARRAY_A);
$user=neoforum_get_user_by_id(get_current_user_id());
?>

<form class="neoforum_search_filters_wrap" name="searchSearch">
        <label><input class="neoforum_search_type_topics" type="radio" name="searchtype" value="topics" checked="">
            <?php esc_html_e("Search in topics", "neoforum") ?>        </label>
        <label><input class="neoforum_search_type_posts" type="radio" name="searchtype" value="posts">
            <?php esc_html_e("Search in posts", "neoforum") ?>        </label>
        <div class="neoforum_search_filters">
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
                <select class="neoforum_search_forum_select" name="forum">
                    <option value="NULL"><?php esc_html_e("All forums...", "neoforum") ?></option>
                    <?php
                        foreach ($forums as $f) {
                            if (neoforum_is_user_can_forum('read', $f, $user))
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
        <button type="button" class="neoforum_search_delete" onclick="neoforum_controls_change(event, '<?php echo wp_create_nonce("neoforum_delete_selected") ?>')"><?php esc_html_e("Delete selected", "neoforum") ?></button>
        <button type="submit" class="neoforum_search_submit" onclick="neoforum_search(event, this, '<?php echo wp_create_nonce('neoforum_search') ?>')"><?php esc_html_e("Search!", "neoforum") ?></button>
    </form>
<div class="neoforum_search_content"></div>
<?php

wp_register_script(
    'neoforum_search_js',
    plugins_url( 'js/search.js', __FILE__ ),
    array( 'wp-i18n' ),
    '0.0.1'
);
wp_set_script_translations( 'neoforum_search_js', 'neoforum' );
wp_enqueue_script( "neoforum_search_js");
global $neoforum_globals;
wp_localize_script('neoforum_search_js', 'neoforum_globals', $neoforum_globals);
return;
