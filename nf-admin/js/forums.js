const { __, _x, _n, sprintf } = wp.i18n;

document.addEventListener("DOMContentLoaded", ()=>{
window.neoforum_blocked=false;
window.neoforum_drags=null;
window.neoforum_drops=null;
window.neoforum_currentdrag=null;
window.neoforum_currentpoint=null;
window.neoforum_currentforum=null;
window.neoforum_currenttype=null;
window.neoforum_orders=neoforum_form_order();
window.neoforum_parents=neoforum_parents_calc();
window.neoforum_cursor={};
window.neoforum_cover=document.getElementById("neoforum_options_blocked_cover");
neoforum_dragevents();

document.addEventListener("mouseup", function(e){
    if (neoforum_blocked) return;
    if (!neoforum_currentpoint&&neoforum_currentdrag){
        neoforum_currentdrag.style.transition="all 0.2s ease";
        neoforum_currentdrag.style.top=0;
        neoforum_currentdrag.style.left=0;
        neoforum_currentdrag.style.zIndex="1";
        neoforum_currentdrag.style.opacity="1";
        neoforum_currentdrag.style.pointerEvents="auto";
        neoforum_hidhlight_available_neoforum_drops(neoforum_currentdrag, true);
        document.body.style.userSelect="auto";
        neoforum_cover.classList.remove("neoforum_options_blocked_cover");
        neoforum_currentdrag=null;
        return;
    }
    if(!neoforum_currentdrag) return;
    if (!neoforum_is_node_inside(neoforum_currentpoint, neoforum_currentdrag)&&neoforum_currentpoint!=neoforum_currentdrag.nextElementSibling&&neoforum_currentpoint!=neoforum_currentdrag.previousElementSibling)
    {
        neoforum_currentpoint.parentNode.insertBefore(neoforum_currentdrag.previousElementSibling, neoforum_currentpoint);
        neoforum_currentpoint.parentNode.insertBefore(neoforum_currentdrag, neoforum_currentpoint);
        neoforum_currentdrag.style.top=0;
        neoforum_currentdrag.style.left=0;
        neoforum_currentdrag.style.zIndex="1";
        neoforum_currentdrag.style.opacity="1";
        neoforum_currentdrag.style.pointerEvents="auto";
        neoforum_hidhlight_available_neoforum_drops(neoforum_currentdrag, true);
        document.body.style.userSelect="auto";
        neoforum_order_commit(neoforum_currentdrag.dataset.nonce);
        neoforum_currentdrag=null;
    }
});
document.addEventListener("mousemove", function(e){
    if (neoforum_blocked) return;
    if (neoforum_currentdrag!=null){
        neoforum_currentdrag.style.top=(e.clientY-neoforum_cursor.top)+"px";
        neoforum_currentdrag.style.left=(e.clientX-neoforum_cursor.left)+"px";
    }
});

});


function neoforum_dragevents(){
    neoforum_drops=document.querySelectorAll(".neoforum_forum_drag_place");
    Array.from(neoforum_drops).forEach(function(item, i, arr){
        item.addEventListener("mouseover", function(e){
            if (neoforum_currentdrag!=null&&!neoforum_is_node_inside(this, neoforum_currentdrag)&&this!=neoforum_currentdrag.nextElementSibling&&this!=neoforum_currentdrag.previousElementSibling)
            {
                neoforum_currentpoint=this;
                neoforum_currentpoint.classList.add("neoforum_forum_drag_place_hover");
            }
        });
        item.addEventListener("mouseout", function(e){
            if(neoforum_currentpoint){
                neoforum_currentpoint.classList.remove("neoforum_forum_drag_place_hover");
                neoforum_currentpoint=null;
            }
        });
        
    });
    neoforum_drags=document.querySelectorAll(".neoforum_forum, .neoforum_section");
    dr=Array.from(neoforum_drags);
    dr.forEach(function(item, i, arr){
        item.addEventListener("mousedown", function(e){
            if (neoforum_blocked) return;
            e.cancelBubble=true;
            if (e.target.onclick!=null&&!dr.includes(e.target)) return;
            let parent=e.target;
            do{
                if (dr.includes(parent))
                    break;
                parent=parent.parentNode;
                if (parent.onclick!=null)
                    return;
            }while(!dr.includes(parent))

            neoforum_currentdrag=this;
            neoforum_cursor.top=e.clientY;
            neoforum_cursor.left=e.clientX;
            neoforum_currentdrag.style.transition="none";
            neoforum_currentdrag.style.pointerEvents="none";
            neoforum_currentdrag.style.zIndex="9999";
            neoforum_currentdrag.style.opacity="0.65";
            document.body.style.userSelect="none";
            neoforum_hidhlight_available_neoforum_drops(neoforum_currentdrag);
        });
    });
}


function neoforum_is_node_inside(node, parentnode){
    parent=node.parentNode;
    while(parent!=null){
        if (parent==parentnode){
            return true;
        }
        else{
            parent=parent.parentNode;
        }
    }
    return false;
}

function neoforum_hidhlight_available_neoforum_drops(elem, remove=false){
    if(remove){
        Array.from(neoforum_drops).forEach(function (item, i, array){
                item.classList.remove("neoforum_forum_drag_place_active");
        });
    }else{
        Array.from(neoforum_drops).forEach(function (item, i, array){
            if(!neoforum_is_node_inside(item, elem)&&item!=elem.nextElementSibling&&item!=elem.previousElementSibling){
                item.classList.add("neoforum_forum_drag_place_active");
            }
        });
    }
}

function neoforum_form_order(){
    neoforum_orders={};
    neoforum_drags=document.querySelectorAll(".neoforum_forum, .neoforum_section");
    Array.from(neoforum_drags).forEach(function(item, i, arr){
        neoforum_orders[item.dataset.id]=i+1;
    });
    return neoforum_orders;
}

function neoforum_parents_calc(){
    neoforum_parents={};
    let par;
    Array.from(neoforum_drags).forEach(function(item, i, arr){
        par=item.parentNode;
        while(par.getAttribute("data-id")==null){
            if (par.className=="wrap"){
                break;
            }
            par=par.parentNode;
        }
        if (par.className!="wrap")
        neoforum_parents[item.dataset.id]=par.dataset.id;
        else
        neoforum_parents[item.dataset.id]="0";
    });
    return neoforum_parents;
}

function neoforum_ajax_request(onsuccess=function(){}, onerror=function(){}){
    let ans=new XMLHttpRequest();
    ans.open('POST', neoforum_globals.site_url+"/wp-admin/admin-ajax.php");
    ans.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    ans.timeout=5000;
    ans.ontimeout=function(){
        alert(__("Connetcion is timed out!", "neoforum"));
        neoforum_cover.classList.remove("neoforum_options_blocked_cover");
        neoforum_blocked=false;
    };
    ans.onerror=function(){
        alert(__("Error is occured!", "neoforum"));
        neoforum_cover.classList.remove("neoforum_options_blocked_cover");
        neoforum_blocked=false;
    };

    ans.onreadystatechange = function() {
        if (ans.readyState != 4) return;
        try{
        res = JSON.parse(this.responseText);}
        catch(err){
            alert(this.responseText);
        }
        if(res['result']){
            onsuccess(res);
        }else{
            onerror(res);
            alert(res['message']);
        }
        neoforum_cover.classList.remove("neoforum_options_blocked_cover");
        neoforum_blocked=false;
    }
    return ans;
}
function neoforum_order_commit(nonce){
    neoforum_blocked=true;
    neoforum_cover.classList.add("neoforum_options_blocked_cover");
    neoforum_form_order();
    neoforum_parents_calc();
    let res=JSON.stringify({'neoforum_orders':neoforum_orders, 'neoforum_parents':neoforum_parents});
    let ans=neoforum_ajax_request();
    ans.send("action=neoforum_order_commit&data="+res+"&nonce="+nonce);

}

function neoforum_forum_option_change(e, id, nonce, action, data=""){
    e.cancelBubble=true;
    if (action=="neoforum_delete_forum"){
        if(!confirm(__("This action will delete ALL topics and posts in this forum (but not in child forums). Are you sure? This cannot be undone.","neoforum")))
            return
    }
    let sendbutton=e.target;
    neoforum_blocked=true;
    neoforum_cover.classList.add("neoforum_options_blocked_cover");
    let ans=neoforum_ajax_request(function(){
        switch(action){
            case "neoforum_close_forum":
                if(res['locked']){
                    sendbutton.classList.add("neoforum_forum_control_close_locked");
                }
                else{
                    sendbutton.classList.remove("neoforum_forum_control_close_locked");
                }
            break;
            case "neoforum_restrict_forum":
                if(res['restricted']){
                    sendbutton.classList.add("neoforum_forum_control_restrict_true");
                }
                else{
                    sendbutton.classList.remove("neoforum_forum_control_restrict_true");
                }
            break;
            case "neoforum_delete_forum":
                 location.reload();
            break;
            case "neoforum_create_section":
                 location.reload();
            break;
            case "neoforum_create_forum":
                 location.reload();
            break;
            case "neoforum_edit_forum_title":
                 neoforum_forum_text_editor_fin(e, res['data']);
            break;
            case "neoforum_edit_forum_descr":
                 neoforum_forum_text_editor_fin(e, res['data']);
            break;
        }
    });
    ans.send("action="+action+"&forumid="+id+"&nonce="+nonce+"&data="+data);
}

function neoforum_forum_text_editor(e){
    let par=e.target.parentNode;
    par.querySelector("span").style.display="none";
    par.querySelector("input, textarea").style.display="inline";
    par.querySelector("input, textarea").value=par.querySelector("span").innerText;
    par.querySelector(".neoforum_forum_edit_button").style.display="none";
    par.querySelector(".neoforum_forum_edit_button_save").style.display="inline";
}

function neoforum_forum_text_editor_fin(e, res){
    let par=e.target.parentNode;
    par.querySelector("span").style.display="inline";
    par.querySelector("span").innerText=res;
    par.querySelector("input, textarea").style.display="none";
    par.querySelector(".neoforum_forum_edit_button").style.display="inline";
    par.querySelector(".neoforum_forum_edit_button_save").style.display="none";
}

function neoforum_users_change(id, type, nonce){
    neoforum_currentforum=id;
    neoforum_currenttype=type;
    neoforum_users_editor.parentNode.style.display="block";
    document.querySelector("#neoforum_users_editor input").classList.add("neoforum_users_input_load");
    document.querySelector("#neoforum_users_editor .neoforum_users_editor_users").innerHTML="";
    document.querySelector("#neoforum_users_editor .neoforum_users_editor_list").innerHTML="";
    document.querySelector("#neoforum_users_editor input").value="";
    let ans=neoforum_ajax_request(function(){
        document.querySelector("#neoforum_users_editor .neoforum_users_editor_users").innerHTML=res['data'];
        document.querySelector("#neoforum_users_editor input").classList.remove("neoforum_users_input_load");
        let users="";
        let lastuser=document.querySelector("#neoforum_users_editor .neoforum_users_editor_users>:last-child");
        Array.from(document.querySelector("#neoforum_users_editor .neoforum_users_editor_users").childNodes).forEach(function(item, i, arr){
            users+="<span class='neoforum_user_of_forum_item'>"+item.innerText+"</span>, ";
        });
        document.querySelector("[data-id='"+neoforum_currentforum+"'] .neoforum_"+type+"_list").innerHTML=users.slice(0, -2);
    }, function(){
        document.querySelector("#neoforum_users_editor input").classList.remove("neoforum_users_input_load");
    });
    ans.send("action=neoforum_get_"+type+"&id="+id+"&type="+type+"&nonce="+nonce);

}

function neoforum_delete_user(forumid, userid, type, nonce, button){
    button.classList.add("neoforum_users_input_load");
    let ans=neoforum_ajax_request(function(){
        button.classList.remove("neoforum_users_input_load");
        let userlist=document.querySelector("[data-id='"+neoforum_currentforum+"'] .neoforum_"+type+"_list");
        userlist.innerHTML=userlist.innerHTML.replace('<span class="neoforum_user_of_forum_item">'+button.parentNode.dataset.username+'</span>, ',"");
        userlist.innerHTML=userlist.innerHTML.replace('<span class="neoforum_user_of_forum_item">'+button.parentNode.dataset.username+'</span>',"");
        button.parentNode.remove();
    }, function(){
        button.classList.remove("neoforum_users_input_load");
    });
    ans.send("action=neoforum_delete_"+type+"&forumid="+forumid+"&userid="+userid+"&type="+type+"&nonce="+nonce);

}

function neoforum_search_user(data, forumid, nonce, button){
    button.classList.add("neoforum_users_input_load");
    let ans=neoforum_ajax_request(function(){
        button.classList.remove("neoforum_users_input_load");
         document.querySelector("#neoforum_users_editor .neoforum_users_editor_list").innerHTML=res['data'];
    }, function(){
         button.classList.remove("neoforum_users_input_load");
    });
    ans.send("action=neoforum_search_"+neoforum_currenttype+"&data="+data+"&forumid="+forumid+"&nonce="+nonce);
}

function neoforum_add_user(userid, forumid, type, nonce, get_moders_nonce, button){
    button.classList.add("neoforum_users_input_load");
    let ans=neoforum_ajax_request(function(){
        document.querySelector("#neoforum_users_editor .neoforum_users_editor_list").style.display="none";
        neoforum_users_change(forumid, type, get_moders_nonce);
        document.querySelector("#neoforum_users_editor input").value="";
        document.querySelector("#neoforum_users_editor .neoforum_users_editor_list").style.display="block";
    }, function(){
        button.classList.remove("neoforum_users_input_load");

    });
    ans.send("action=neoforum_add_"+neoforum_currenttype+"&userid="+userid+"&forumid="+forumid+"&nonce="+nonce);
}
function neoforum_recalculate_forums(button, nonce){
    button.classList.add("neoforum_users_input_load");
    let ans=neoforum_ajax_request(function(){
        button.classList.remove("neoforum_users_input_load");
    }, function(){
        button.classList.remove("neoforum_users_input_load");

    });
    ans.timeout=20000;
    ans.send("action=neoforum_recalculate_forums&nonce="+nonce);
}

