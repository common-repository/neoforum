<?php 
    
?>
<div class="neoforum_post <?php echo ($post['is_first']==1 ? 'neoforum_firstpost' : ''); ?>" id="<?php echo("p".$post['postid']); ?>">
        <div class="neoforum_post_user_info">
            <div class="neoforum_post_user_name">
                <?php echo(neoforum_themes::get_user_name($post)." ".neoforum_themes::ban_button($user, $current_user)); ?>
            </div>
            <div class="neoforum_post_user_caps">
                <?php echo neoforum_themes::get_user_caps($user, $forum); ?>
            </div>
            <div class="neoforum_post_user_avatar">
                <img src="<?php echo neoforum_themes::get_user_avatar($user['userid']); ?>" alt="Avatar">
            </div>
            <div class="neoforum_post_user_stats">
                <?php echo esc_html__("Posts","neoforum").": ".$user['posts_num'] ?>
            </div>
            <div class="neoforum_user_contacts">
                <?php echo neoforum_themes::get_user_contacts($user); ?>
            </div>
        </div>
        <div class="neoforum_post_content">
            <div class="neoforum_post_date">
                <a href="#<?php echo("p".$post['postid']); ?>"><?php esc_html_e("Post link", "neoforum"); ?></a>
                <?php echo($post['creation_date']); ?>
            </div>
            <div class="neoforum_post_text">
                <?php echo(neoforum_themes::get_post_content($post)); ?>
            </div>
            <?php echo neoforum_themes::get_post_attachments($post); ?>
            <?php echo neoforum_themes::get_post_edit_date($post); ?>
            <div class="neoforum_user_caption">
                <?php echo(neoforum_themes::get_usercaption($user)); ?>
            </div>
            <div class="neoforum_post_control_buttons">
                <?php
                    neoforum_display_post_controls($post); 
                ?>
            </div>
        </div>
    </div>
<?php 

return; ?>
