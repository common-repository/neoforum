<?php
//Functrions for topics here

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!neoforum_is_user_can_topic("start")){
    neoforum_message(__("You can't do this action!", "neoforum"));
    return;
}

if (!is_numeric($_GET['forum']))
    {
        neoforum_message(__("Wrong forum id!", "neoforum"));
        return;
    }

neoforum_show_new_topic_fields();

return;
?>
