<div class="neoforum_single_forum_container">
    <?php neoforum_themes::breadcrumbs(); 
    $forums=neoforum_themes::get_forums_list(get_query_var("forum"));
    if (count($forums)>0){
    ?>
    <div class="neoforum_child_forums_block">
        <div class="forum_section_title" href='<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/".$forum['slug']); ?>'>
            <?php esc_html_e("Subforums","neoforum"); ?>
        </div>
        <?php
        //display subforums
            foreach ($forums as $f) {
                if (neoforum_get_forum_id_by_slug(get_query_var("forum"))==$f['parent_forum']){
                    neoforum_forum_item_render($forums, $f);
                }
            }

        ?>
    </div>
    <?php } 
    neoforum_themes::create_topic_button(get_query_var("forum")); 
    echo neoforum_themes::mark_as_read_button(); ?>
    <div class="neoforum_forum_topic_list">
        <?php
        //show topic list of current forum
        $list=neoforum_themes::get_topics_of_forum(get_query_var("forum"), get_query_var("pg"));
        foreach ($list as $topic){
            neoforum_render_topic_item($topic);
        }
        if (count($list)==0){
            esc_html_e("There is no topics yet","neoforum");
        }
        neoforum_pagination(get_query_var("forum"), get_query_var("pg"), "topics");
        ?>
    </div>
    <div class="neoforum_online_users">
        <div class="neoforum_online_header">
            <?php
                esc_html_e("Browsing the forum","neoforum");
            ?>:
        </div>
    <?php echo(neoforum_themes::get_online_on_forum(get_query_var("forum"))) ?>
    </div>
</div>
<?php return; ?>
