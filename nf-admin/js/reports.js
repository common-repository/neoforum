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
    };
    ans.onerror=function(){
        alert(__("Error is occured!", "neoforum"));
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

function neoforum_report_leave_post(button, id, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        button.classList.remove("neoforum_loading");
        document.querySelector("[data-id='"+id+"']").remove();
    });
    res.send("action=neoforum_report_leave_post&reportid="+id+"&nonce="+nonce);
}

function neoforum_report_delete_post(button, id, nonce){
    button.classList.add("neoforum_loading");
    let res=neoforum_ajax_request(function(res){
        button.classList.remove("neoforum_loading");
        document.querySelector("[data-id='"+id+"']").remove();
    });
    res.send("action=neoforum_report_delete_post&reportid="+id+"&nonce="+nonce);
}
