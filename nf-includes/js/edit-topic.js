function neoforum_create_ajax_request(onsuccess, onerror=function(res){
   
    }, headers=true){
    let postRequest=new XMLHttpRequest();
    postRequest.timeout=5000;
    postRequest.open('POST', neoforum_globals.site_url+"/wp-admin/admin-ajax.php", true);
    headers ? postRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded') : null;
    postRequest.ontimeout = function(){
        neoforum_message("Connection timed out!");
    }
    postRequest.onerror = function(event){
        neoforum_message("Request error!");
    }
    postRequest.onreadystatechange = function() {
        if (postRequest.readyState != 4) return;
        let res;
        try{
            res = JSON.parse(this.responseText);
        } catch(err){
            onsuccess(this.responseText);
            return;
        }
        if (res['result']){
            onsuccess(res);
        }
        else{
            neoforum_message(res['message']);
            onerror(res);
        }
    }
    return postRequest;
}

function neoforum_remove_attach(sender, user, name, id){
    let postRequest=neoforum_create_ajax_request(function(res){
        sender.remove();
    });
    let request="action=neoforum_delete_attach&id="+id+"&name="+name;
    postRequest.send(request);
}
