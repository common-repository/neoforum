const { __, _x, _n, sprintf } = wp.i18n;

var neoforum_offtopic_buttons;
(neoforum_offtopic_buttons=function(elem){
var button_list=elem.querySelectorAll(".neoforum_offtopic>button");
button_list=Array.from(button_list);
button_list.forEach(function (item, i, arr){
        item.addEventListener("click", function(){
            neoforum_open_offtopic(item);
        });
    });
})(document)

function neoforum_open_offtopic(elem){
    elem.nextElementSibling.classList.toggle("neoforum_show");
}



function neoforum_ban_user(button, id, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_create_ajax_request(function(res){
        neoforum_forum_selector.parentNode.style.display="none";
    }, function(){
        neoforum_forum_selector.parentNode.style.display="none";
    });
    let comment=button.parentNode.querySelector("textarea").value;
    let ban=button.parentNode.querySelector("[type='date']").value;
    res.send("action=neoforum_ban_user&id="+id+"&ban="+ban+"&comment="+comment+"&nonce="+nonce);
}
function neoforum_ban_user_menu(id, username, nonce){
    window.neoforum_forum_selector=document.querySelector(".neoforum_forum_selector");
    neoforum_forum_selector.querySelector(".neoforum_forum_selector_content").innerHTML="";
    neoforum_forum_selector.parentNode.style.display="block";
    document.querySelector(".neoforum_forum_selector_header").innerHTML="Ban"+" "+username;
    neoforum_get_ban_menu(id, nonce);
}

function neoforum_get_ban_menu(id, nonce){
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_forum_selector.querySelector(".neoforum_forum_selector_content").innerHTML=res['data'];
    }, function(){
        neoforum_forum_selector.parentNode.style.display="none";
    } );
    let request="action=neoforum_get_ban_menu&id="+id+"&nonce="+nonce;
    postRequest.send(request);
}

function neoforum_posts_callbacks(action, id, target=null){
    switch (action){
        case "neoforum_delete_post":
            document.getElementById("p"+id).remove();
            break;
        case "neoforum_delete_topic":
            neoforum_message(__("Topic has been deleted", "neoforum"));
            break;
        case "neoforum_close_topic":
            target.classList.add("neoforum_topic_button_switcher");
            break;
        case "neoforum_open_topic":
            target.previousElementSibling.classList.remove("neoforum_topic_button_switcher");
            break;
        case "neoforum_approve_topic":
            target.classList.add("neoforum_topic_button_switcher");
            break;
        case "neoforum_unapprove_topic":
            target.previousElementSibling.classList.remove("neoforum_topic_button_switcher");
            break;
        case "neoforum_sticky_topic":
            target.classList.add("neoforum_topic_button_switcher");
            break;
        case "neoforum_unsticky_topic":
            target.previousElementSibling.classList.remove("neoforum_topic_button_switcher");
            break;
        case "neoforum_solved_topic":
            target.classList.add("neoforum_topic_button_switcher");
            break;
        case "neoforum_notsolved_topic":
            target.previousElementSibling.classList.remove("neoforum_topic_button_switcher");
            break;
        case "neoforum_subscribe":
            target.classList.add("neoforum_topic_button_switcher");
            break;
        case "neoforum_unsubscribe":
            target.previousElementSibling.classList.remove("neoforum_topic_button_switcher");
            break;
    }
}

function neoforum_posts_handler(event, action, id, nonce){
    if (event!=null) {
        event.preventDefault();
        event.target.classList.add("neoforum_loading_pseudo");
    }
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_posts_callbacks(action, id, event!=null ? event.target : null);
        event!=null ? event.target.classList.remove("neoforum_loading_pseudo") : null;
    }, function(){
        event!=null ? event.target.classList.remove("neoforum_loading_pseudo") : null;
    });
    let request="action="+action+"&id="+id+"&nonce="+nonce;
    postRequest.send(request);
}

function neoforum_delete_topic(event, topicid){
    event!=null ? event.preventDefault() : null;
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_message(__("Topic successfully deleted","neoforum"));
    });
    let request="action=neoforum_delete_topic&id="+topicid;
    postRequest.send(request);

}



function neoforum_edit_post(event, id){
    let editing_id=id;
    event.preventDefault();
    if (document.querySelectorAll(".neoforum_edit_post").length>0){
        if(!confirm(__("Changes of other post editing will be lost. Continue?", "neoforum")))
            return;
        let elem=document.querySelector(".neoforum_post:not(#p"+id+") .neoforum_post_editor");
        elem.nextElementSibling.style.display="";
        elem.remove();
    }
    window.tex=document.getElementById("p"+id).querySelector(".neoforum_post_text");
    tex.style.display="none";
    document.querySelector("#p"+id+" .neoforum_post_content").insertBefore(neoforum_post_editor.content.cloneNode(true), tex);
    window.editor=document.querySelector("#p"+id+" .ne-editor");
    editor.innerHTML=tex.innerHTML;
    document.querySelector("#p"+id+" [name='postid']").value=id;
    document.querySelector("#p"+id+" .neoforum_attachments")!=null ? document.querySelector("#p"+id+" .neoforum_attachments").style.display="none" : null;
    let atts=Array.from(document.querySelectorAll("#p"+id+" .neoforum_attachments>a"));
    let edit_atts=document.querySelector("#p"+id+" .neoforum_edit_attachments");
    let res="";
    atts.forEach(function(item){
        res+=`<div class="neoforum_remove_attach" onclick="neoforum_remove_attach(this, ${item.dataset.user}, '${item.innerHTML}', ${id}, '${item.dataset.nonce}');">${item.innerHTML}</div>`;
    });
    edit_atts.innerHTML=res;
    neoforum_offtopic_buttons(editor);
}

function neoforum_remove_attach(sender, user, name, id, nonce){
    let postRequest=neoforum_create_ajax_request(function(res){
        sender.remove();
        document.querySelector("#p"+id+" .neoforum_attachments [href$='"+name+"']").remove();
    });
    let request="action=neoforum_delete_attach&id="+id+"&name="+name+"&nonce="+nonce;
    postRequest.send(request);
}

function neoforum_cancel_edit_post(event){
    if (event!=null)
        event.preventDefault();
    let editor=document.querySelector(".neoforum_post_editor");
    editor.nextElementSibling.style.display="";
    editor.nextElementSibling.nextElementSibling.style.display="";
    editor.remove();
    
}

function neoforum_commit_edit_post(event, data, postid, nonce){
    event.preventDefault();
    let postRequest=neoforum_create_ajax_request(function(res){
        tex.innerHTML=res['data'];
        neoforum_cancel_edit_post();
    }, function(){}, false);
    let request=new FormData();
    request.append('action', 'neoforum_edit_post');
    request.append('post', postid);
    request.append('data', data);
    request.append('nonce', nonce);

    let files=document.querySelector("#p"+postid+" [type='file']").files;
    for (var i=0; i <= files.length - 1; i++) {
        request.append('neoforum_file[]', files[i]);
    }
    postRequest.send(request);
}


function neoforum_delete_post(event, postid){
    event.preventDefault();
    let postRequest=neoforum_create_ajax_request(function(res){
        document.getElementById("p"+postid).remove();
    });
    let request="action=neoforum_delete_post&post="+postid;
    postRequest.send(request);

}

function neoforum_collect_checked_posts(){
    let ch=document.querySelectorAll("[data-postid]");
    let res=[];
    if (ch.length==0)
        return false;
    Array.from(ch).forEach(function(item, i, arr){
        if (item.checked){
            res.push(item.dataset.postid);
        }
    });
    if (res.length==0){
        return false;
    }
    return JSON.stringify(res);
}
function neoforum_move_topic(forumid, nonce){
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_message(__("Topic moved successfully", "neoforum"));
        location.href=res['URL'];
    });
    let request="action=neoforum_move_topic&topicid="+neoforum_topic_controls.dataset.id+"&forumid="+forumid+"&nonce="+nonce;
    postRequest.send(request);
}

function neoforum_get_new_topic_form(forumid, nonce){
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_forum_selector.querySelector(".neoforum_forum_selector_content").innerHTML=res;
        
    });
    let request="action=neoforum_get_new_topic_form&topicid="+neoforum_topic_controls.dataset.id+"&forumid="+forumid+"&nonce="+nonce;
    postRequest.send(request);
}

function neoforum_new_topic (event, nonce){
    event.preventDefault();
    let ch=neoforum_collect_checked_posts();
        if (!ch){
            alert(__("No posts selected!", "neoforum"));
            return;
        }
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_message(__("Topic created successfully", "neoforum"));
        location.href=res["URL"];
    });
    let title=neoforum_forum_selector.querySelector(".neoforum_new_topic_title").value;
    let descr=neoforum_forum_selector.querySelector(".neoforum_new_topic_descr").value;
    let message=neoforum_forum_selector.querySelector("[name='ne_text_field']").value;
    let forumid=neoforum_forum_selector.querySelector("[name='neoforum_forumid']").value;
    let request="action=neoforum_new_topic&posts="+ch+"&title="+title+"&descr="+descr+"&message="+message+"&forumid="+forumid+"&oldtopic="+neoforum_topic_controls.dataset.id+"&nonce="+nonce;
    postRequest.send(request);
}

function neoforum_move_posts(event, topicid, nonce){
    event!=null ? event.preventDefault() : null;
    let ch=neoforum_collect_checked_posts();
        if (!ch){
            alert(__("No posts selected!", "neoforum"));
            return;
        }
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_message(__("Posts moved successfully", "neoforum"));
        neoforum_forum_selector.parentNode.style.display="none";
        let posts=[].concat(res['data']);
        Array.prototype.forEach.call(posts, function(item){
            document.getElementById("p"+item).remove();
        });
    });
    let request="action=neoforum_move_posts&posts="+ch+"&topicid="+neoforum_topic_controls.dataset.id+"&point="+topicid+"&nonce="+nonce;
    postRequest.send(request);
}

function neoforum_controls_change(event){
    window.neoforum_topic_controls=event.target;
    let res=neoforum_topic_controls.options[neoforum_topic_controls.selectedIndex].value;
    let nonce=neoforum_topic_controls.options[neoforum_topic_controls.selectedIndex].dataset.nonce;
    neoforum_topic_controls.selectedIndex=0;
    if (res=="delete_topic"){
        neoforum_posts_handler(null, "neoforum_delete_topic", neoforum_topic_controls.dataset.id, nonce);
        return; 
    }
    if (res=="delete_posts"){
        let ch=neoforum_collect_checked_posts();
        if (!ch){
            alert(__("No posts selected!","neoforum"));
            return;
        }
        let postRequest=neoforum_create_ajax_request(function(res){
            let posts=[].concat(res['data']);
            Array.prototype.forEach.call(posts, function(item){
                document.getElementById("p"+item).remove();
            });
            //neoforum_message(res['message']);
        });
        let request="action=neoforum_delete_posts&posts="+ch+"&topicid="+neoforum_topic_controls.dataset.id+"&nonce="+neoforum_topic_controls.dataset.nonce;
        postRequest.send(request);
        return; 
    }
    if (res!="def"){
        neoforum_open_move_popup(neoforum_topic_controls.dataset.id, res);
    }
    
}

function neoforum_open_move_popup(topicid, action){
    if(action=="move_posts" || action=="new_topic"){
        let ch=neoforum_collect_checked_posts();
            if (!ch){
                alert(__("No posts selected!", "neoforum"));
                return;
            }
    }
    window.neoforum_forum_selector=document.querySelector(".neoforum_forum_selector");
    neoforum_forum_selector.querySelector(".neoforum_forum_selector_content").innerHTML="";
    neoforum_forum_selector.parentNode.style.display="block";
    document.querySelector(".neoforum_forum_selector_header").innerHTML=__("Select forum", "neoforum");
    neoforum_get_forums_list(null, action, neoforum_topic_controls.dataset.forum);
}

function neoforum_get_forums_list(event, action, currentforum, forumid=null, page=1){
    event != null ? event.preventDefault() : null;
    neoforum_forum_selector==undefined ? window.neoforum_forum_selector=document.querySelector(".neoforum_forum_selector") : null;
    neoforum_forum_selector.classList.add("neoforum_loading");
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_forum_selector.classList.remove("neoforum_loading");
        neoforum_forum_selector.querySelector(".neoforum_forum_selector_content").innerHTML=res['data'];
    });
    let request="action=neoforum_get_forums&do="+action+"&topicid="+neoforum_forum_selector.dataset.id+"&currentforum="+currentforum+"&forumid="+forumid+"&page="+page+"&nonce="+neoforum_topic_controls.dataset.nonce;
    postRequest.send(request);
}
function neoforum_postquote(e, name){
    e.preventDefault();
    //document.execCommand(e.target, 'insertHTML', neoforum_quote());
    let sel=window.getSelection().toString();
    if (sel.length<1){
        alert(__("You must select text for quote", "neoforum"));
        return;
    }
    let edit=document.querySelector(".ne-editor");
    edit.focus();
    document.execCommand('insertHTML', false, neoforum_quote(sel, "by "+name)); 
    edit.focus();
}

function neoforum_report_post(e, id, nonce){
    e.preventDefault();
    window.neoforum_forum_selector=document.querySelector(".neoforum_forum_selector");
    neoforum_forum_selector.querySelector(".neoforum_forum_selector_content").innerHTML="";
    neoforum_forum_selector.parentNode.style.display="block";
    document.querySelector(".neoforum_forum_selector_header").innerHTML=__("Comment your report", "neoforum");
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_forum_selector.querySelector(".neoforum_forum_selector_content").innerHTML=res['data'];
    });
    let request="action=neoforum_report&postid="+id+"&nonce="+nonce;
    postRequest.send(request);
};

function neoforum_commit_report(button, id, nonce){
    button.classList.add("neoforum_loading");
    let postRequest=neoforum_create_ajax_request(function(res){
        neoforum_forum_selector.parentNode.style.display="none";
        button.classList.remove("neoforum_loading");
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    let request="action=neoforum_commit_report&postid="+id+"&data="+button.previousElementSibling.value+"&nonce="+nonce;
    postRequest.send(request);
}
