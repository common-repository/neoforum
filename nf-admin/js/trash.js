const { __, _x, _n, sprintf } = wp.i18n;

document.addEventListener("DOMContentLoaded", ()=>{
window.neoforum_cover=document.getElementById("neoforum_options_blocked_cover");
window.neoforum_trash_content=document.querySelector(".neoforum_trash_content");
window.neoforum_trash_submit=document.querySelector(".neoforum_trash_submit");
});

function neoforum_ajax_request(onsuccess=function(){}, onerror=function(){}){
    let ans=new XMLHttpRequest();
    ans.open('POST', neoforum_globals.site_url+"/wp-admin/admin-ajax.php");
    ans.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    ans.timeout=10000;
    let res;
    ans.ontimeout=function(){
        alert(__("Connetcion is timed out!", "neoforum"));
        neoforum_trash_submit.classList.remove("neoforum_loading");
    };
    ans.onerror=function(){
        alert(__("Error is occured!", "neoforum"));
        neoforum_trash_submit.classList.remove("neoforum_loading");
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
        neoforum_trash_submit.classList.remove("neoforum_loading");
    }
    return ans;
}

function neoforum_form_search_data(){
    let res="";
    res+="&searchtype="+document.trashSearch.elements.searchtype.value;
    res+="&date_after="+document.trashSearch.elements.date_after.value;
    res+="&date_before="+document.trashSearch.elements.date_before.value;
    res+="&username="+document.trashSearch.elements.username.value;
    res+="&forum="+document.trashSearch.elements.forum.value;
    res+="&word_filter="+document.trashSearch.elements.word_filter.value;
    res+="&title_filter="+document.trashSearch.elements.title_filter.value;
    return res;
}

function neoforum_trash_search(e, sender, nonce){
    e.preventDefault();
    neoforum_trash_submit.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        neoforum_trash_content.innerHTML=res['data'];
    });
    res.send("action=neoforum_search&trash=1"+neoforum_form_search_data()+"&nonce="+nonce);

}

function neoforum_trash_topic_restore(button, topicid, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        document.querySelector("[data-id='"+topicid+"']").remove();
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_topic_restore&topicid="+topicid+"&nonce="+nonce);
}

function neoforum_trash_topic_eradicate(button, topicid, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        document.querySelector("[data-id='"+topicid+"']").remove();
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_topic_eradicate&topicid="+topicid+"&nonce="+nonce);
}

function neoforum_trash_post_restore(button, postid, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        document.querySelector("[data-id='"+postid+"']").remove();
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_post_restore&postid="+postid+"&nonce="+nonce);
}

function neoforum_trash_post_eradicate(button, postid, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        document.querySelector("[data-id='"+postid+"']").remove();
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_post_eradicate&postid="+postid+"&nonce="+nonce);
}
function neoforum_collect_checked_items(){
    let ch=document.querySelectorAll(".neoforum_trash_content [type='checkbox']");
    let res=[];
    if (ch.length==0)
        return false;
    Array.from(ch).forEach(function(item, i, arr){
        if (item.checked){
            res.push({"id":item.value, "type":item.dataset.type});
        }
    });
    if (res.length==0){
        return false;
    }
    return JSON.stringify(res);
}
function neoforum_controls_change(event){
    window.neoforum_controls=event.target;
    let act=neoforum_controls.options[neoforum_controls.selectedIndex].value;
    let nonce=neoforum_controls.options[neoforum_controls.selectedIndex].dataset.nonce;
    neoforum_controls.selectedIndex=0;
    items=neoforum_collect_checked_items();
    let res=neoforum_ajax_request(function(res){
        res['data'].forEach(function (id){
            document.querySelector("[data-id='"+id+"']").remove();
        });
    });
    res.send("action=neoforum_items_"+act+"&items="+items+"&nonce="+nonce);
}

function neoforum_delete_all_trash(nonce){
    if (!confirm("Are you sure?"))
        return;
    let res=neoforum_ajax_request(function(res){
        document.querySelector(".neoforum_trash_content").innerHTML="";
    });
    res.send("action=neoforum_delete_all_trash&nonce="+nonce);
}

