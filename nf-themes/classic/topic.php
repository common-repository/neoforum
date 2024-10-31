<?php 
    
?>
<div class="neoforum_topic_container <?php echo(neoforum_is_user_moder($current_user) ? 'neoforum_topic_moderator' : '') ?>">
    <?php neoforum_themes::breadcrumbs(); ?>
    <div class="neoforum_topic_posts">
    <h1 class="neoforum_topic_title">
        <?php
            echo(esc_html(neoforum_themes::get_topic_name(get_query_var("topic"))));
        ?>
    </h1>
        <?php
        neoforum_display_topic_contols(get_query_var("topic"));
        //show topic list of current forum
        $forum=neoforum_get_forum_by_slug(get_query_var("forum"));
        $list=neoforum_themes::get_posts_of_topic(get_query_var("topic"), get_query_var("pg"));
        foreach ($list as $post){
            neoforum_template::render_post($post, $forum, $current_user);
        }
        ?>
    </div>
    <?php 
    neoforum_pagination(get_query_var("topic"), get_query_var("pg"), "posts");
    neoforum_show_post_reply_fields(); 
    ?>
    <div class="neoforum_online_users">
        <div class="neoforum_online_header">
            <?php
                esc_html_e("Currently reading topic","neoforum");
            ?>:
        </div>
    <?php echo(neoforum_themes::get_online_on_topic(get_query_var("topic"))) ?>
    </div>
</div>

<?php 

return; ?>
