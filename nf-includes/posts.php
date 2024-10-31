<?php
//Functrions for topics here

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function neoforum_create_post($message, $topicid, $userid, $autor="", $is_first=0){
    global $wpdb;
    //sanitize first
    $message=neoforum_clean_post_content($message);
    if(strlen(wp_kses($message, array('img' => array())))<1){
        neoforum_message(__("Your post is empty!", "neoforum"));
        return;
    }
    if(!is_numeric($topicid)){
        neoforum_message(__("Wrong topic id value", "neoforum"));
        return;
    }
    if(!is_numeric($userid)){
        neoforum_message(__("Wrong user id value", "neoforum"));
        return;
    }
    
    if(!neoforum_is_user_exists($userid)){
        neoforum_message(__("User does not exists!", "neoforum"));
        return;
    }
    $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=$topicid LIMIT 1", ARRAY_A)[0];
    if(count($topic)==0){
        neoforum_message(__("Topic does not exists!", "neoforum"));
        return;
    }
    

    //sanitizind finished
    //$wpdb->query($sql);
    $wpdb->insert($wpdb->prefix."neoforum_posts", array("content"=>$message,"forumid"=>$topic['forumid'],"topicid"=>$topicid,"authorid"=>$userid,"authorname"=>$autor,"creation_date"=>current_time('mysql', 1),"is_first"=>$is_first));
    $wait=neoforum_save_attachment($wpdb->insert_id);
    $wait==null ? $wait=0 : null;
    $increase_post_num="UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num+1 WHERE forumid=".$topic['forumid'];
    $wpdb->query($increase_post_num);
    $increase_post_num="UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num+1 WHERE topicid=$topicid";
    $wpdb->query($increase_post_num);
    $self_id=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE authorid=".$userid." ORDER BY creation_date DESC LIMIT 1", ARRAY_A)[0];
    $subscribe=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_subscribes WHERE topicid=".$topicid." AND userid<>".$userid." AND sent=0", ARRAY_A);
    if (count($subscribe)>0){
        $send_to=array();
        foreach ($subscribe as $v) {
            $send_to[]=get_user_by('ID', $v['userid'])->user_email;
        }
        $header=esc_html__("New replies", "neoforum");
        $pagelen=get_option("neoforum_posts_per_page");
        if($topic['posts_num']%$pagelen==0)
            $page=intdiv($topic['posts_num'], $pagelen);
        else
            $page=intdiv($topic['posts_num'], $pagelen)+1;
        $message=esc_html__("There is new posts in topic", "neoforum")." <a href='".get_site_url()."/".get_option('neoforum_forum_url')."/".$topic['slug']."/pg=".$page."#".$self_id['postid']."'>".$topic['topic_title']."</a> ".esc_html__("you subscribed on.", "neoforum");
        wp_mail($send_to, $header, $message);
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_subscribes SET sent=1 WHERE topicid=".$topicid." AND userid<>".$userid." AND sent=0");

    }
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET lastpost_authorid=".$userid.", read_by='' WHERE topicid=$topicid");
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET lastpost_authorname='".get_user_by('id', $self_id['authorid'])->display_name."' WHERE topicid=$topicid");
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET lastpost_date='".current_time('mysql', 1)."' WHERE topicid=$topicid");
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET lastpost_authorid=".$self_id['authorid']." WHERE forumid=".$topic['forumid']);
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET lastpost_authorname='".get_user_by('id', $self_id['authorid'])->display_name."' WHERE forumid=".$topic['forumid']);
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET lastpost_topicid=".$self_id['topicid']." WHERE forumid=".$topic['forumid']);
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET lastpost_topicname='".neoforum_themes::get_topic_name($topicid)."' WHERE forumid=".$topic['forumid']);
    $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET lastpost_date='".current_time('mysql', 1)."' WHERE forumid=".$topic['forumid']);
    neoforum_user_posts_increase($userid);
    return $wait;
}

function neoforum_single_post_render($post, $forum=null){
    if($post['in_trash'])
        return;
    global $wpdb;
    $user=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_users WHERE userid=".$post['authorid']." LIMIT 1", ARRAY_A)[0];
?>
    <div class="neoforum_post" id="<?php echo("p".$post['postid']); ?>">
        <div class="neoforum_post_user_info">
            <div class="neoforum_post_user_name">
                <?php echo(neoforum_themes::get_user_name($post)); ?>
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
}

function neoforum_dirsize($dir)
    {
      @$dh = opendir($dir);
      $size = 0;
      while ($file = @readdir($dh))
      {
        if ($file != "." and $file != "..") 
        {
          $path = $dir."/".$file;
          if (is_dir($path))
          {
            $size += neoforum_dirsize($path); // recursive in sub-folders
          }
          elseif (is_file($path))
          {
            $size += filesize($path); // add file
          }
        }
      }
      @closedir($dh);
      return $size;
    }

function neoforum_save_attachment($postid){
    $wait=0;
    $allowed_types=array(
            // Image formats.
            'jpg|jpeg|jpe'                 => 'image/jpeg',
            'gif'                          => 'image/gif',
            'png'                          => 'image/png',
            'bmp'                          => 'image/bmp',
            'tiff|tif'                     => 'image/tiff',
            'ico'                          => 'image/x-icon',
            // Video formats.
            'asf|asx'                      => 'video/x-ms-asf',
            'wmv'                          => 'video/x-ms-wmv',
            'wmx'                          => 'video/x-ms-wmx',
            'wm'                           => 'video/x-ms-wm',
            'avi'                          => 'video/avi',
            'divx'                         => 'video/divx',
            'flv'                          => 'video/x-flv',
            'mov|qt'                       => 'video/quicktime',
            'mpeg|mpg|mpe'                 => 'video/mpeg',
            'mp4|m4v'                      => 'video/mp4',
            'ogv'                          => 'video/ogg',
            'webm'                         => 'video/webm',
            'mkv'                          => 'video/x-matroska',
            '3gp|3gpp'                     => 'video/3gpp', // Can also be audio
            '3g2|3gp2'                     => 'video/3gpp2', // Can also be audio
            // Text formats.
            'txt|asc|c|cc|h|srt'           => 'text/plain',
            'csv'                          => 'text/csv',
            'tsv'                          => 'text/tab-separated-values',
            'ics'                          => 'text/calendar',
            'rtx'                          => 'text/richtext',
            'css'                          => 'text/css',
            'vtt'                          => 'text/vtt',
            'dfxp'                         => 'application/ttaf+xml',
            // Audio formats.
            'mp3|m4a|m4b'                  => 'audio/mpeg',
            'aac'                          => 'audio/aac',
            'ra|ram'                       => 'audio/x-realaudio',
            'wav'                          => 'audio/wav',
            'ogg|oga'                      => 'audio/ogg',
            'flac'                         => 'audio/flac',
            'mid|midi'                     => 'audio/midi',
            'wma'                          => 'audio/x-ms-wma',
            'wax'                          => 'audio/x-ms-wax',
            'mka'                          => 'audio/x-matroska',
            // Misc application formats.
            'rtf'                          => 'application/rtf',
            'js'                           => 'application/javascript',
            'pdf'                          => 'application/pdf',
            'swf'                          => 'application/x-shockwave-flash',
            'class'                        => 'application/java',
            'tar'                          => 'application/x-tar',
            'zip'                          => 'application/zip',
            'gz|gzip'                      => 'application/x-gzip',
            'rar'                          => 'application/rar',
            '7z'                           => 'application/x-7z-compressed',
            'psd'                          => 'application/octet-stream',
            'xcf'                          => 'application/octet-stream',
            // MS Office formats.
            'doc'                          => 'application/msword',
            'pot|pps|ppt'                  => 'application/vnd.ms-powerpoint',
            'wri'                          => 'application/vnd.ms-write',
            'xla|xls|xlt|xlw'              => 'application/vnd.ms-excel',
            'mdb'                          => 'application/vnd.ms-access',
            'mpp'                          => 'application/vnd.ms-project',
            'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
            'oxps'                         => 'application/oxps',
            'xps'                          => 'application/vnd.ms-xpsdocument',
            // OpenOffice formats.
            'odt'                          => 'application/vnd.oasis.opendocument.text',
            'odp'                          => 'application/vnd.oasis.opendocument.presentation',
            'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg'                          => 'application/vnd.oasis.opendocument.graphics',
            'odc'                          => 'application/vnd.oasis.opendocument.chart',
            'odb'                          => 'application/vnd.oasis.opendocument.database',
            'odf'                          => 'application/vnd.oasis.opendocument.formula',
            // WordPerfect formats.
            'wp|wpd'                       => 'application/wordperfect',
            // iWork formats.
            'key'                          => 'application/vnd.apple.keynote',
            'numbers'                      => 'application/vnd.apple.numbers',
            'pages'                        => 'application/vnd.apple.pages',
        );
    $userid=get_current_user_id();
    if (strlen($_FILES['neoforum_file']['name'][0])<1 or get_option("neoforum_can_upload")!='on' or get_current_user_id()==0)
        return;
    $maxsize=get_option("neoforum_max_file_size");
    wp_mkdir_p(neoforum_DIR."\\nf-userdata\attachments");
    wp_mkdir_p(neoforum_DIR."\\nf-userdata\attachments\\".$userid);
    $maxfoldersize=get_option("neoforum_max_folder_size");
    for ($z=0; $z < count($_FILES['neoforum_file']['name']); $z++) { 
        if ($_FILES['neoforum_file']['size'][$z]>$maxsize){
            neoforum_message(printf(esc_html__("File size must be less then %d bytes","neoforum"), $maxsize)." ".$_FILES['neoforum_file']['name'][$z] );
            $wait=8000;
            continue;
        }
        $dirsize=neoforum_dirsize(neoforum_DIR."\\nf-userdata\attachments\\".$userid);
        if ($_FILES['neoforum_file']['size'][$z]+$dirsize>$maxfoldersize){
            neoforum_message(printf(esc_html__("Not enough free space to save file. You have only %d free bytes","neoforum"), $maxfoldersize-$dirsize) );
            return 8000;
        }
        $righttype=false;
        foreach ($allowed_types as $key => $value) {
            if(preg_match('/^.*?\.('.$key.')$/i', $_FILES['neoforum_file']['name'][$z])){
                $righttype=true;
                break;
            }
        }
        if (!$righttype){
            neoforum_message(esc_html__("File type is forbidden!","neoforum")." ".$_FILES['neoforum_file']['name'][$z]);
            $wait=8000;
            continue;
        }
        $fullname=sanitize_file_name($_FILES['neoforum_file']['name'][$z]);
        if (!file_exists(neoforum_DIR."\\nf-userdata\attachments\\".$userid."\\".$fullname)){
            move_uploaded_file($_FILES['neoforum_file']['tmp_name'][$z], neoforum_DIR."\\nf-userdata\attachments\\".$userid."\\".$fullname);
        }
        else{
            preg_match('/(^.*?)\.([a-z]{1,10}$)/i', $fullname, $m);
            $name=$m[1];
            $ext=$m[2];
            $counter=1;
            while(file_exists(neoforum_DIR."\\nf-userdata\attachments\\".$userid."\\".$name."-$counter.".$ext)){
                $counter+=1;
            }
            move_uploaded_file($_FILES['neoforum_file']['tmp_name'][$z], neoforum_DIR."\\nf-userdata\attachments\\".$userid."\\".$name."-$counter.".$ext);
            $fullname=$name."-$counter.".$ext;
        }
        global $wpdb;
        $wpdb->query("INSERT INTO ".$wpdb->prefix."neoforum_attachments VALUES($postid, $userid, '$fullname')");
        return $wait;
    }

}

function neoforum_clean_post_content($message){
    $message=wp_unslash($message);
    $allowed_html = array(
    'img' => array(
        'src' => true,
        'alt' => true,
    ),
    'br' => array(),
    'em' => array(),
    'b' => array(),
    'i' => array(),
    'strike' => array(),
    'u' => array(),
    'p' => array(),
    'font' => array(
        'size' => true,
        'color' => true,
        'face' => true),
    'strong' => array(),
    'span' => array(
        'style' => true),
    'button' => array(),
    'div' => array(
        'class' => true),
    'blockquote' => array()
);
if (get_option("neoforum_allow_links")=='on'){
    $allowed_html['a'] = array(
        'href' => true,
        'title' => true,
    );
} 
$message=wp_kses( $message, $allowed_html ); //all non-allowed tags deleted

//sanitize classes

$take_class='/(?<=class=[\',"])(.|\s")*?(?=[\',"])/';
$message=preg_replace_callback($take_class, 'neoforum_sanitize_classes', $message);

//sanitize styles
$take_style='/(?<=style=[\',"])(.|\s)*?(?=[\',"])/';
$message=preg_replace_callback($take_style, 'neoforum_sanitize_styles', $message);

//insert nofollow
$take_a='/<a\s/';
$message=preg_replace($take_a, '<a rel="nofollow" ', $message);

//insert button type
$take_a='/<button\s/';
$message=preg_replace($take_a, '<button type="button" ', $message);

//remove nobraking spaces
$take_sp='/&nbsp;/';
$message=preg_replace($take_sp, "", $message);

//remove spaces
$take_sp='/\s{2,}/';
$message=preg_replace($take_sp, " ", $message);

$message=neoforum_fix_unclosed_tags($allowed_html, $message);
return $message;
}
function neoforum_sanitize_classes($match){ //only allowed classes left on the tags
    $allowed_classes=array('neoforum_offtopic', 'neoforum_show_offtopic_button', 'neoforum_offtopic_hidden', 'neoforum_quote', 'neoforum_quote_author', 'neoforum_quote_text');
    $res='(';
    foreach ($allowed_classes as $value) {
        $res.='(?<=^|\s)'.$value.'(?=\s|$)|';
    }
    $res=trim($res, '|');
    $res.=')';
    preg_match_all($res, $match[0], $classes);
    $result="";
    foreach ($classes[0] as $cl) {
        $result.=$cl." ";
    }
    return $result;
}

function neoforum_sanitize_styles($match){ //in "style" attribute only allowed style rules left
    $allowed_styles=array('text-align:[^;]{4,8}');
    $res='(';
    foreach ($allowed_styles as $value) {
        $res.='(?<=^|;|\s)'.$value.'(?=\s|;|$)|';
    }
    $res=trim($res, '|');
    $res.=')';
    preg_match_all($res, $match[0], $classes);
    $result="";
    foreach ($classes[0] as $cl) {
        $result.=$cl."; ";
    }
    return $result;
}

function neoforum_fix_unclosed_tags($allowed, $message){ //unclosed tags will be closed in the end of the post, so forum page won't be broken
    foreach ($allowed as $key => $value) {
        if ($key=="br" or $key=="img"){
            continue;
        }
        preg_match_all('/<'.$key.'(>|\s)/', $message, $opened);
        preg_match_all('/<\/'.$key.'/', $message, $closed);
        $unclosed=count($opened[0])-count($closed[0]);
            for ($i=0; $i < $unclosed; $i++) { 
                $message.="</".$key.">";
            }
    }
    return $message;
}

function neoforum_get_post_by_id($id){
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($id)." LIMIT 1", ARRAY_A)[0];
}

//here - functions to work with AJAX when modify posts

function neoforum_post_edit_handler(){
    if(strlen(wp_kses($_POST['data'], array('img' => array())))<1){
        $reply=array(
        'result' => false,
        'message' => esc_html__("Your post is empty!","neoforum"));
        echo(json_encode($reply));
        wp_die();
    }
    if (neoforum_is_user_can_post("edit", neoforum_get_post_by_id($_POST['post'])) and wp_verify_nonce( $_POST['nonce'], 'neoforum_edit_post' )){
        $reply=array(
        'result' => true,
        'data' => neoforum_clean_post_content($_POST['data']));
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET edited=content, content='".esc_sql($reply['data'])."', edit_date='".current_time('mysql', 1)."', editorid=".get_current_user_id()." WHERE postid=".esc_sql($_POST['post']));
        neoforum_save_attachment($_POST['post']);
        echo(json_encode($reply));
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_edit_post', 'neoforum_post_edit_handler');

function neoforum_delete_attach_handler(){
    global $wpdb;
    $post=neoforum_get_post_by_id($_POST['id']);
    if (neoforum_is_user_can_post("edit", $post) and wp_verify_nonce($_POST['nonce'], 'neoforum_delete_attach')){
        $reply=array(
        'result' => true);
        $wpdb->query("DELETE FROM ".$wpdb->prefix."neoforum_attachments WHERE postid=".esc_sql($_POST['id'])." AND filename='".esc_sql($_POST['name'])."'");
        error_log(neoforum_DIR."\\nf-userdata\attachments\\".$post['authorid']."\\".$_POST['name']);
        if (file_exists(neoforum_DIR."\\nf-userdata\attachments\\".$post['authorid']."\\".$_POST['name'])){
            global $wp_filesystem;
            wp_filesystem();
            $wp_filesystem->delete(neoforum_DIR."\\nf-userdata\attachments\\".$post['authorid']."\\".$_POST['name']);
        }
        echo(json_encode($reply));
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_delete_attach', 'neoforum_delete_attach_handler');

function neoforum_post_delete_handler(){
    if (wp_verify_nonce($_POST['nonce'], 'neoforum_delete_post' ) and neoforum_is_user_can_post("delete", neoforum_get_post_by_id($_POST['post']))){
        $reply=array(
        'result' => true);
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET in_trash=1, deleted='".current_time('mysql', 1)."' WHERE postid=".esc_sql($_POST['id']));
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num-1 WHERE topicid=".esc_sql($_POST['topicid']));
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num-1 WHERE forumid=(SELECT forumid FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($_POST['id'])." LIMIT 1)");
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_users SET posts_num=posts_num-1 WHERE userid=(SELECT authorid FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($_POST['id'])." LIMIT 1)");
        echo(json_encode($reply));
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_delete_post', 'neoforum_post_delete_handler');

function neoforum_posts_delete_handler(){
    if (wp_verify_nonce($_POST['nonce'], 'neoforum_topic_controls' )){
        $reply=array(
        'result' => true);
        global $wpdb;
        $posts=json_decode(stripslashes($_POST['posts']));
        $counter=0;
        $res=Array();
        foreach ($posts as $id) {
            $post=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($id)." LIMIT 1", ARRAY_A)[0];
            if (neoforum_is_user_can_post("delete", $post) and $_POST['topicid']==$post['topicid']){
                $counter+=1;
                $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET in_trash=1, deleted='".current_time('mysql', 1)."' WHERE postid=".esc_sql($id));
                $res[]=$id;
            }
        }
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num-$counter WHERE topicid=".esc_sql($_POST['topicid']));
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num-$counter WHERE forumid=".$post['forumid']);
        $reply["data"]=$res;
        if ($counter==count($posts))
            $reply["message"]=esc_html__("All posts were deleted successfully", "neoforum");
        if ($counter<count($posts))
            $reply["message"]=esc_html__("Some posts were not deleted: access denied.", "neoforum");
        if ($counter==0){
            $reply["result"]=false;
            $reply["message"]=esc_html__("No posts were deleted: access denied!", "neoforum");;
        }
        echo(json_encode($reply));
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_delete_posts', 'neoforum_posts_delete_handler');

function neoforum_move_posts_handler(){
    if (wp_verify_nonce($_POST['nonce'], 'neoforum_move_posts' ) and neoforum_is_topic_exists($_POST['point'])){
        $reply=array(
        'result' => true);
        global $wpdb;
        $posts=json_decode(stripslashes($_POST['posts']));
        $counter=0;
        $res=Array();
        $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($_POST['point'])." LIMIT 1", ARRAY_A)[0];
        foreach ($posts as $id) {
            $post=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($id)." LIMIT 1", ARRAY_A)[0];
            if (neoforum_is_user_can_post("delete", $post) and $_POST['topicid']==$post['topicid']){
                $counter+=1;
                $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET topicid=".esc_sql($_POST['point']).", forumid=".$topic['forumid'].", is_first=0 WHERE postid=".esc_sql($id));
                $res[]=$id;
            }
        }
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num-$counter WHERE topicid=".esc_sql($_POST['topicid']));
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num-$counter WHERE forumid=".$post['forumid']);
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num+$counter WHERE topicid=".esc_sql($_POST['point']));
        $topic=$wpdb->get_results("SELECT forumid FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($_POST['point'])." LIMIT 1", ARRAY_A)[0];
        $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num+$counter WHERE forumid=".$topic['forumid']);
        $reply["message"]=$res;
        if ($counter==count($posts))
            $reply["message"]=esc_html__("All posts were moved successfully", "neoforum");
        if ($counter<count($posts))
            $reply["message"]=esc_html__("Some posts were not moved: access denied.", "neoforum");
        if ($counter==0){
            $reply["result"]=false;
            $reply["message"]=esc_html__("No posts were moved: access denied!", "neoforum");;
        }
        echo(json_encode($reply));
        wp_die();
    }else{
        $reply=array(
        'result' => false,
        'message' => esc_html__("You can't do this action!", "neoforum"));
        echo(json_encode($reply));
        wp_die();

    }
}

add_action('wp_ajax_neoforum_move_posts', 'neoforum_move_posts_handler');

function neoforum_items_delete_handler(){
    if ( ! current_user_can( 'manage_options' ) or ! wp_verify_nonce($_POST['nonce'], 'neoforum_delete_selected')) {
        $res=array("result"=>false, "message"=>__("You can't do this action!", "neoforum"));
        echo json_encode($res);
        wp_die();
    }
    global $wpdb;
    $data=json_decode(stripslashes($_POST['items']), true);
    $type=$data[0]['type'];
    $ans=array();
    foreach ($data as $v) {
        if ($type!=$v['type'])
        {
            $res=array("result"=>false, "message"=>__("Items has diffetent types!", "neoforum"));
            echo json_encode($res);
            wp_die();
        }
    }
    $user=neoforum_get_user_by_id(get_current_user_id());
    if ($type=="topic"){
        foreach ($data as $v) {
            $topic=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_topics WHERE topicid=".esc_sql($v['id'])." LIMIT 1", ARRAY_A)[0];
            if (!neoforum_is_user_can_topic('delete', $topic, null, $user))
                continue;
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET in_trash=1, deleted='".current_time('mysql', 1)."' WHERE topicid=".esc_sql($v['id'])." LIMIT 1");
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num+".$topic['posts_num'].", topics_num=topics_num-1 WHERE forumid=".esc_sql($topic['forumid'])." LIMIT 1");
            $ans[]=$v['id'];
        }
    }
    if ($type=="post"){
        foreach ($data as $v) {
            $post=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."neoforum_posts WHERE postid=".esc_sql($v['id'])." LIMIT 1", ARRAY_A)[0];
            if (!neoforum_is_user_can_post('delete', $post, null, null, $user))
                return;
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_posts SET in_trash=1, deleted='".current_time('mysql', 1)."' WHERE postid=".esc_sql($v['id'])." LIMIT 1");
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_topics SET posts_num=posts_num-1 WHERE topicid=".$post['topicid']." LIMIT 1");
            $wpdb->query("UPDATE ".$wpdb->prefix."neoforum_forums SET posts_num=posts_num-1 WHERE forumid=".$post['forumid']." LIMIT 1");
            $ans[]=$v['id'];
        }
    }
    $res=array("result"=>true, "data"=>$ans);
    echo json_encode($res);
    wp_die();
}
add_action("wp_ajax_neoforum_items_delete", "neoforum_items_delete_handler");
?>
