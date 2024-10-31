var neoforum_search_content=document.querySelector(".neoforum_search_content");
var neoforum_search_submit=document.querySelector(".neoforum_search_submit");

function neoforum_ajax_request(onsuccess=function(){}, onerror=function(){}){
    let ans=new XMLHttpRequest();
    ans.open('POST', neoforum_globals.site_url+"/wp-admin/admin-ajax.php");
    ans.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    ans.timeout=10000;
    let res;
    ans.ontimeout=function(){
        alert("Connetcion is timed out!");
        neoforum_search_submit.classList.remove("neoforum_loading");
    };
    ans.onerror=function(){
        alert("Error is occured!");
        neoforum_search_submit.classList.remove("neoforum_loading");
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
        neoforum_search_submit.classList.remove("neoforum_loading");
    }
    return ans;
}

function neoforum_form_search_data(){
    let res="";
    res+="&searchtype="+document.searchSearch.elements.searchtype.value;
    res+="&date_after="+document.searchSearch.elements.date_after.value;
    res+="&date_before="+document.searchSearch.elements.date_before.value;
    res+="&username="+document.searchSearch.elements.username.value;
    res+="&forum="+document.searchSearch.elements.forum.value;
    res+="&word_filter="+document.searchSearch.elements.word_filter.value;
    res+="&title_filter="+document.searchSearch.elements.title_filter.value;
    return res;
}

function neoforum_search(e, sender, nonce){
    e.preventDefault();
    neoforum_search_submit.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        neoforum_search_content.innerHTML=res['data'];
    });
    res.send("action=neoforum_search&trash=0"+neoforum_form_search_data()+"&nonce="+nonce);

}

function neoforum_search_topic_delete(button, topicid, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        document.querySelector("[data-id='"+topicid+"']").remove();
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_topic_delete&topicid="+topicid+"&nonce="+nonce);
}

function neoforum_search_post_delete(button, postid, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        document.querySelector("[data-id='"+postid+"']").remove();
    }, function(){
        button.classList.remove("neoforum_loading");
    });
    res.send("action=neoforum_post_delete&postid="+postid+"&nonce="+nonce);
}
function neoforum_collect_checked_items(){
    let ch=document.querySelectorAll(".neoforum_search_content [type='checkbox']");
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
function neoforum_controls_change(event, nonce){
    items=neoforum_collect_checked_items();
    let res=neoforum_ajax_request(function(res){
        res['data'].forEach(function (id){
            document.querySelector("[data-id='"+id+"']").remove();
        });
    });
    res.send("action=neoforum_items_delete&items="+items+"&nonce="+nonce);
}
