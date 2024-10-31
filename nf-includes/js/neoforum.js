/*this file will be on EVERY plugin page*/
function neoforum_create_ajax_request(onsuccess, onerror=function(res){
   
}, headers=true){
    let postRequest=new XMLHttpRequest();
    postRequest.timeout=5000;
    postRequest.open('POST', neoforum_globals.site_url+"/wp-admin/admin-ajax.php", true);
    headers ? postRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded') : null;
    postRequest.ontimeout = function(){
        neoforum_message(__("Connection timed out!", "neoforum"));
    }
    postRequest.onerror = function(event){
        neoforum_message(__("Request error!", "neoforum"));
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


function neoforum_message(text, red=false){
    alert(text);
}
function neoforum_trottle(f, ms){
    var sThis, args, timer;

    let wrap=function(){
        sThis=this;
        args=arguments;
        clearTimeout(timer);
        timer=setTimeout(function(){
            f.apply(sThis, args);    
            }, ms);
    }
    return wrap;
}

function neoforum_mark_as_read(sender, nonce, id){
    let res=neoforum_create_ajax_request(function(res){
        location.reload();
    }, function(){
    });
    
    res.send("action=neoforum_mark_as_read&id="+id+"&nonce="+nonce);
}
