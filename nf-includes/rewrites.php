<?php
//Rewrite API

function neoforum_insert_custom_rules($rules) {

    $newrules = array();

    $newrules=array(
        '^'.get_option("neoforum_forum_url").'/([^/]+)(/pg=([0-9]+)/?)?$' => 'pagename='.get_option('neoforum_forum_url').'&forum=$matches[1]&pg=$matches[3]',
        '^'.get_option("neoforum_forum_url").'/([^/]+)/([^/]+)(/pg=([0-9]+|last)/?)?$' => 'pagename='.get_option('neoforum_forum_url').'&forum=$matches[1]&topic=$matches[2]&pg=$matches[4]'
    );

    return $newrules + $rules;
}
function neoforum_insert_tags(){
    add_rewrite_tag('%forum%', '([^&]+)');
    add_rewrite_tag('%topic%', '([^&]+)');
    add_rewrite_tag('%pg%', '([^&]+)');
}
add_action('init', 'neoforum_insert_tags', 10, 0);
add_filter('rewrite_rules_array', 'neoforum_insert_custom_rules');
?>
