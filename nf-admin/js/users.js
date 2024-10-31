const { __, _x, _n, sprintf } = wp.i18n;

document.addEventListener("DOMContentLoaded", ()=>{
window.neoforum_search=document.querySelector(".neoforum_users_editor_list");
window.neoforum_users_content=document.querySelector(".neoforum_users_content");
});

function neoforum_ajax_request(onsuccess=function(){}, onerror=function(){}){
    let ans=new XMLHttpRequest();
    ans.open('POST', neoforum_globals.site_url+"/wp-admin/admin-ajax.php");
    ans.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    ans.timeout=10000;
    let res;
    ans.ontimeout=function(){
        alert(__("Connetcion is timed out!", "neoforum"));
        onerror(res);
    };
    ans.onerror=function(){
        alert(__("Error is occured!", "neoforum"));
        onerror(res);
    };
    ans.onreadystatechange = function() {
        if (ans.readyState != 4) return;
        try{
        res = JSON.parse(this.responseText);}
        catch(err){
            alert(this.responseText);
        }
        if(res['result']){
            if (res['message']!=null)
                alert(res['message']);
            onsuccess(res);
        }else{
            onerror(res);
            alert(res['message']);
        }
    }
    return ans;
}

function neoforum_users_search(button, str, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        neoforum_search.innerHTML=res['data'];
        button.classList.remove("neoforum_loading");
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_search_users&str="+str+"&nonce="+nonce);

}

function neoforum_add_admin(id){
    document.querySelector(".neoforum_users_window_wrap").style.display="block";
    document.querySelector(".neoforum_choose_admin").style.display="block";
    document.querySelector(".neoforum_choose_admin").dataset.id=id;
    document.querySelector(".neoforum_choose_ban").style.display="none";
}
function neoforum_make_admin(button, id, type, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        button.classList.remove("neoforum_loading");
        document.querySelector(".neoforum_users_window_wrap").style.display="none";
        document.querySelector("[type='radio']:checked")!= null ? document.querySelector("[type='radio']:checked").onclick() : null;
    }, function(){
        document.querySelector(".neoforum_users_window_wrap").style.display="none";
        button.classList.remove("neoforum_loading");
        document.querySelector(".neoforum_users_input_load").oninput();
    });
    res.send("action=neoforum_make_admin&id="+id+"&type="+type+"&nonce="+nonce);
}
function neoforum_ban_user(id){
    document.querySelector(".neoforum_users_window_wrap").style.display="block";
    document.querySelector(".neoforum_choose_admin").style.display="none";
    document.querySelector(".neoforum_choose_ban").dataset.id=id;
    document.querySelector(".neoforum_choose_ban").style.display="block";
}
function neoforum_ban(button, id, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        button.classList.remove("neoforum_loading");
        document.querySelector(".neoforum_users_window_wrap").style.display="none";
        document.querySelector("[type='radio']:checked")!= null ? document.querySelector("[type='radio']:checked").onclick() : null;
        document.querySelector(".neoforum_users_input_load").oninput();
    }, function(){
        document.querySelector(".neoforum_users_window_wrap").style.display="none";
        button.classList.remove("neoforum_loading");
    });
    let comment=button.parentNode.querySelector("textarea").value;
    let ban=button.parentNode.querySelector("[type='date']").value;
    res.send("action=neoforum_ban_user&id="+id+"&ban="+ban+"&comment="+comment+"&nonce="+nonce);
}

function neoforum_unban_user(button, id, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        document.querySelector(".neoforum_users_input_load").oninput();
        document.querySelector("[type='radio']:checked")!= null ? document.querySelector("[type='radio']:checked").onclick() : null;
        button.classList.remove("neoforum_loading");
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_unban_user&id="+id+"&nonce="+nonce);
}

function neoforum_remove_admin(button, id, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        document.querySelector(".neoforum_users_input_load").oninput();
        document.querySelector("[type='radio']:checked")!= null ? document.querySelector("[type='radio']:checked").onclick() : null;
        button.classList.remove("neoforum_loading");
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_remove_admin&id="+id+"&nonce="+nonce);
}
function neoforum_get_users_list(type, nonce){
    neoforum_users_content.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        neoforum_users_content.classList.remove("neoforum_loading");
        neoforum_users_content.innerHTML=res['data'];
    }, function(){
        neoforum_users_content.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_search_users&type="+type+"&nonce="+nonce);
}
function neoforum_recalculate_users(button, nonce){
    button.classList.add("neoforum_loading");
    let ans=neoforum_ajax_request(function(){
        button.classList.remove("neoforum_loading");
    }, function(){
        button.classList.remove("neoforum_loading");

    });
    ans.timeout=20000;
    ans.send("action=neoforum_recalculate_users&nonce="+nonce);
}

