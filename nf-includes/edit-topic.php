<?php
//Functrions for topics here

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!is_numeric($_GET['topic']))
    {
        neoforum_message(__("Wrong topic id!", "neoforum"));
        return;
    }
        if (!neoforum_is_topic_exists($_GET['topic'])){
            neoforum_message(__("Topic doesn't exists!", "neoforum"));
            return;
        }


neoforum_show_edit_topic_fields();

wp_register_script(
    'neoforum_edit-topic_js',
    plugins_url( 'js/edit-topic.js', __FILE__ ),
    array( 'wp-i18n' ),
    '0.0.1'
);
wp_set_script_translations( 'neoforum_edit-topic_js', 'neoforum' );
wp_enqueue_script( "neoforum_edit-topic_js");
wp_localize_script('neoforum_edit-topic_js', 'neoforum_globals', $neoforum_globals);
return;
?>
