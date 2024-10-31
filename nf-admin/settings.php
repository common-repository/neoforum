<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function neoforum_settings_init(){
    neoforum_settings_update();
    add_settings_section(//our sectrion
    'neoforum_settings',
    /*translators: general settings page*/
    __('General', 'neoforum'),
    function(){},
    'neoforum_admin'
    );
    
    add_settings_field(//make field in section, forum URL
    "forum_url", 
    __("Forum page URL", "neoforum"), 
    function(){
        echo(get_site_url()."/<input type='text' id='forum_url' name='neoforum_forum_url' value='".get_option("neoforum_forum_url")."'><input type=hidden name='neoforum_old_forum_page' value='".get_option("neoforum_forum_url")."'>");
    },
    "neoforum_admin",
    'neoforum_settings' 
    );
    add_settings_field(//select forum theme
    "forum_theme", 
    __("Forum theme", "neoforum"), 
    function(){
        $current=get_option("neoforum_theme");
        echo("<select id='forum_theme' name='neoforum_theme' onchange='neoforum_change_theme_descr(event, `".wp_create_nonce('neoforum_get_theme')."`)'>");
        foreach(neoforum_get_themes_list() as $val)
            {
                if ($current!=$val)
                {
                    echo("<option value='".$val."'>".$val."</option>");
                }
                else{
                    echo("<option selected value='".$val."'>".$val."</option>");
                }
            }
        echo("</select>");
        echo ("<div class='neoforum_theme_description'>"); 
        include( neoforum_DIR . '/nf-themes/'.$current.'/description.php' );
        echo ("</div>");
    },
    "neoforum_admin",
    'neoforum_settings' 
    );
    add_settings_field(//are guests allowed to create topics
    "guests_can_topics", 
    __("Guests can create topics", "neoforum"), 
    function(){
        echo("<input type='checkbox' id='guests_can_topics' name='neoforum_guests_can_topics' ".checked( "on", get_option("neoforum_guests_can_topics"), false).">");
    },
    "neoforum_admin",
    'neoforum_settings' 
    );
    add_settings_field(//are guests allowed to create topics
    "guests_can_posts", 
    __("Guests can answer in topics", "neoforum"), 
    function(){
        echo("<input type='checkbox' id='guests_can_posts' name='neoforum_guests_can_posts' ".checked( "on", get_option("neoforum_guests_can_posts"), false).">");
    },
    "neoforum_admin",
    'neoforum_settings' 
    );
    add_settings_field(
    "topics_need_approving", 
    __("When enabled, all new topics will require premoderation before publishing", "neoforum"), 
    function(){
        echo("<input type='checkbox' id='neoforum_topics_need_approving' name='neoforum_topics_need_approving' ".checked( "on", get_option("neoforum_topics_need_approving"), false).">");
    },
    "neoforum_admin",
    'neoforum_settings' 
    );
    add_settings_field(
    "topics_need_solving", 
    __("When enabled, all new topics will be marked as not solved, until creator or moderator marks them as solved", "neoforum"), 
    function(){
        echo("<input type='checkbox' id='neoforum_topics_need_solving' name='neoforum_topics_need_solving' ".checked( "on", get_option("neoforum_topics_need_solving"), false).">");
    },
    "neoforum_admin",
    'neoforum_settings' 
    );
    add_settings_field(
    "posts_per_page", 
    __("Number of posts to be displayed on one page inside topic", "neoforum"), 
    function(){
        echo("<input type='number' id='neoforum_posts_per_page' name='neoforum_posts_per_page' value='".get_option("neoforum_posts_per_page")."'>");
    },
    "neoforum_admin",
    'neoforum_settings' 
    );
    add_settings_field(
    "topics_per_page", 
    __("Number of topics to be displayed on single page inside forum", "neoforum"), 
    function(){
        echo("<input type='number' id='neoforum_topics_per_page' name='neoforum_topics_per_page' value='".get_option("neoforum_topics_per_page")."'>");
    },
    "neoforum_admin",
    'neoforum_settings' 
    );
    add_settings_field(
    "allow_links", 
    __("Users can include links in their replies", "neoforum"), 
    function(){
        echo("<input type='checkbox' id='neoforum_allow_links' name='neoforum_allow_links' ".checked( "on", get_option("neoforum_allow_links"), false).">");
    },
    "neoforum_admin",
    'neoforum_settings' 
    );
    add_settings_field(
    "users_files", 
    __("Users uploads settings", "neoforum"), 
    function(){
        ?>
        <label><input type='checkbox' id='neoforum_can_upload' name='neoforum_can_upload' <?php echo checked( "on", get_option("neoforum_can_upload"), false) ?>>
            <?php esc_html_e("Users can upload files","neoforum"); ?>
        </label><br>
        <label><input type='number' id='neoforum_max_file_size' name='neoforum_max_file_size' value="<?php echo get_option("neoforum_max_file_size") ?>">
            <?php esc_html_e("Max file size (bytes)","neoforum"); ?>
        </label><br>
        <label><input type='number' id='neoforum_max_folder_size' name='neoforum_max_folder_size' value="<?php echo get_option("neoforum_max_folder_size") ?>">
            <?php esc_html_e("Max file folder size for each user (bytes)","neoforum"); ?>
        </label><br>
        <?php
    },
    "neoforum_admin",
    'neoforum_settings' 
    );


}
function neoforum_settings_update(){ //registering settings here
    register_setting(
            "neoforum_admin",
            "neoforum_forum_url"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_old_forum_page"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_theme"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_guests_can_topics"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_guests_can_posts"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_topics_need_approving"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_topics_need_solving"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_posts_per_page"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_topics_per_page"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_allow_links"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_can_upload"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_max_file_size"
    );
    register_setting(
            "neoforum_admin",
            "neoforum_max_folder_size"
    );
}
add_action('admin_init', 'neoforum_settings_init');//set admin menu
add_action('admin_init', 'neoforum_set_forum_page');//create page for forum to display
?>
