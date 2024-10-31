<?php
//installation and activation

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function neoforum_activate() {
    global $wpdb;
    $sql=array("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."neoforum_forums`(
    `forumid` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `forum_name` VARCHAR(256) NOT NULL,
    `slug` VARCHAR(256) NOT NULL,
    `forum_descr` VARCHAR(1024) NOT NULL,
    `ord` INT NOT NULL,
    `topics_num` INT NOT NULL,
    `posts_num` INT NOT NULL,
    `lastpost_authorid` INT DEFAULT 0,
    `lastpost_topicid` INT DEFAULT 0,
    `lastpost_topicname` VARCHAR(256),
    `lastpost_authorname` VARCHAR(64),
    `lastpost_date` DATETIME,
    `parent_forum` INT NOT NULL DEFAULT 0,
    `moderators` VARCHAR(256) NOT NULL DEFAULT '',
    `can_read` VARCHAR(256) NOT NULL DEFAULT '',
    `is_section` BOOLEAN NOT NULL DEFAULT FALSE,
    `is_restricted` BOOLEAN NOT NULL DEFAULT FALSE,
    `is_closed` BOOLEAN NOT NULL DEFAULT FALSE
    );",
    "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."neoforum_topics`(
    `topicid` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `forumid` INT NOT NULL,
    `authorid` INT NOT NULL,
    `authorname` VARCHAR(64),
    `topic_title` VARCHAR(256) NOT NULL,
    `topic_descr` VARCHAR(256) NOT NULL,
    `slug` VARCHAR(256) NOT NULL,
    `creation_date` DATETIME,
    `lastpost_authorid` INT DEFAULT 0,
    `lastpost_authorname` VARCHAR(64),
    `lastpost_date` DATETIME,
    `read_by` TEXT NOT NULL DEFAULT '',
    `posts_num` INT NOT NULL DEFAULT 0,
    `views_num` INT NOT NULL DEFAULT 0,
    `is_approved` BOOLEAN NOT NULL DEFAULT TRUE,
    `is_closed` BOOLEAN NOT NULL DEFAULT FALSE,
    `in_trash` BOOLEAN NOT NULL DEFAULT FALSE,
    `deleted` DATETIME,
    `is_solved` BOOLEAN NOT NULL DEFAULT FALSE,
    `is_pinned` BOOLEAN NOT NULL DEFAULT FALSE
    );",
    "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."neoforum_posts`(
    `postid` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `topicid` INT NOT NULL,
    `forumid` INT NOT NULL,
    `authorid` INT NOT NULL,
    `authorname` VARCHAR(256),
    `deleted` DATETIME,
    `in_trash` BOOLEAN NOT NULL DEFAULT FALSE,
    `is_first` BOOLEAN NOT NULL DEFAULT FALSE,
    `content` TEXT NOT NULL,
    `edit_date` DATETIME,
    `editorid` INT,
    `edited` TEXT,
    `creation_date` DATETIME
    );",
    "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."neoforum_users`(
    `userid` INT PRIMARY KEY NOT NULL UNIQUE,
    `posts_num` INT NOT NULL DEFAULT 0,
    `user_caps` VARCHAR(20) NOT NULL DEFAULT 'registered' CHECK (user_caps IN('registered', 'supermoderator', 'administrator')),
    `user_caption` VARCHAR(512),
    `facebook` VARCHAR(512) DEFAULT '',
    `twitter` VARCHAR(512) DEFAULT '',
    `instagram` VARCHAR(512) DEFAULT '',
    `last_visit` DATETIME,
    `new_post_border` DATETIME,
    `last_place` VARCHAR(1024),
    `ban` DATETIME,
    `banned_by` INT,
    `ban_comment` VARCHAR(1024),
    `reports_num` INT DEFAULT 0
    );",
    "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."neoforum_reports`(
    `reportid` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `userid` INT NOT NULL,
    `postid` INT NOT NULL DEFAULT 0,
    `topicid` INT NOT NULL DEFAULT 0,
    `forumid` INT NOT NULL DEFAULT 0,
    `comment` VARCHAR(1024),
    `date` DATETIME,
    `solved` BOOLEAN NOT NULL DEFAULT FALSE
    );",
    "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."neoforum_subscribes`(
    `topicid` INT NOT NULL,
    `userid` INT NOT NULL,
    `sent` BOOLEAN NOT NULL DEFAULT 1
    );",
    "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."neoforum_attachments`(
    `postid` INT NOT NULL,
    `userid` INT NOT NULL,
    `filename` VARCHAR(512) NOT NULL
    );");
    foreach ($sql as $v) {
        $wpdb->query($v);
    }
    $wpdb->query("INSERT INTO ".$wpdb->prefix."neoforum_users (userid, last_visit, new_post_border) SELECT ID, '".current_time("mysql")."', '".current_time("mysql")."' FROM ".$wpdb->prefix."users WHERE ID NOT IN (SELECT userid FROM ".$wpdb->prefix."neoforum_users)" );
    //default settings here
    neoforum_settings_update();
    if (get_option("neoforum_theme")==null){
        update_option("neoforum_theme", "classic");
    }
    if (get_option("neoforum_forum_url")==null){
        update_option("neoforum_forum_url", "forum");
    }
    if (get_option("neoforum_guests_can_topics")==null){
        update_option("neoforum_guests_can_topics", 0);
    }
    if (get_option("neoforum_guests_can_posts")==null){
        update_option("neoforum_guests_can_posts", 0);
    }
    if (get_option("neoforum_guests_can_posts")==null){
        update_option("neoforum_guests_can_posts", 0);
    }
    if (get_option("neoforum_topics_need_approving")==null){
        update_option("neoforum_topics_need_approving", 0);
    }
    if (get_option("neoforum_topics_need_solving")==null){
        update_option("neoforum_topics_need_solving", 0);
    }
    if (get_option("neoforum_posts_per_page")==null){
        update_option("neoforum_posts_per_page", 15);
    }
    if (get_option("neoforum_topics_per_page")==null){
        update_option("neoforum_topics_per_page", 30);
    }
    if (get_option("neoforum_can_upload")==null){
        update_option("neoforum_can_upload", 'on');
    }
    if (get_option("neoforum_allow_links")==null){
        update_option("neoforum_allow_links", 0);
    }
    if (get_option("neoforum_max_file_size")==null){
        update_option("neoforum_max_file_size", 1000000);
    }
    if (get_option("neoforum_max_folder_size")==null){
        update_option("neoforum_max_folder_size", 20000000);
    }
    flush_rewrite_rules();
    //end of default settings
    if ($wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."neoforum_forums")==0){
        neoforum_create_forum("Section 1", "First section", TRUE);
        neoforum_create_forum("Forum", "First forum", FALSE, 1);
    }
}

?>
