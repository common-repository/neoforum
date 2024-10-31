<div class="neoforum_forum_mainboard">
<?php
//displays list of forums


$forum_list=neoforum_themes::get_forums_list();
$user=neoforum_get_user_by_id(get_current_user_id());

echo neoforum_themes::mark_as_read_button($user['userid']);
foreach ($forum_list as $forum){
    if (!neoforum_is_user_can_forum("read", $forum, $user)){
        continue;
    }
    if ($forum['parent_forum']==0){
        if ($forum['is_section']){
            ?>
            <div class="neoforum_forum_section">
                <a class="forum_section_title" href='<?php echo(get_site_url()."/".get_option('neoforum_forum_url')."/".$forum['slug']); ?>'>
                    <?php echo($forum['forum_name']); ?>
                </a>
                <div class="neoforum_forum_section_container">
                    <?php 
                        foreach ($forum_list as $forum_inc) {
                            if ($forum_inc['parent_forum']==$forum['forumid'])
                            {
                                neoforum_forum_item_render($forum_list, $forum_inc);
                            }
                        }
                    ?>
                </div>
            </div>
        <?php
        }
        else{ 
            neoforum_forum_item_render($forum_list, $forum);
        }
    }
}

?>
<div class="neoforum_online_users">
    <div class="neoforum_online_header">
        <?php
            esc_html_e("Online users","neoforum");
        ?>
    </div>
    <?php echo(neoforum_themes::get_online_users()) ?>
</div>
</div>
<?php return; ?>
