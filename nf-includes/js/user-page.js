function neoforum_remove_attach(sender, user, name, id){
    let postRequest=neoforum_create_ajax_request(function(res){
        sender.remove();
    });
    let request="action=neoforum_delete_attach&id="+id+"&name="+name;
    postRequest.send(request);
}

function neoforum_save_contact(sender, user, type, value, nonce){
    sender.parentNode.classList.add("neoforum_loading");
    let postRequest=neoforum_create_ajax_request(function(res){
        sender.parentNode.classList.remove("neoforum_loading");
    }, function(){
        sender.parentNode.classList.remove("neoforum_loading");
    });
    let request="action=neoforum_save_contact&userid="+user+"&type="+type+"&value="+value+"&nonce="+nonce;
    postRequest.send(request);
}

function neoforum_save_usercaption(sender, user, value, nonce){
    let postRequest=neoforum_create_ajax_request(function(res){
        document.querySelector(".neoforum_usercaption_content").innerHTML=res['data'];
        document.querySelector(".neoforum_usercaption_wrapper").style.display="block";
        document.querySelector(".neoforum_user_caption_editor").style.display="none";
    });
    let request="action=neoforum_save_usercaption&userid="+user+"&value="+value+"&nonce="+nonce;
    postRequest.send(request);
}

function neoforum_cancel_usercaption(sender){
    document.querySelector(".neoforum_usercaption_wrapper").style.display="block";
    document.querySelector('.neoforum_user_caption_editor').style.display="none";
}

function neoforum_avatar_upload(sender, user, nonce){
    let file=document.querySelector("[name='neoforum_avatar']").files;
    if (file.length==0)
        return;
    file=file[0];
    let postRequest=neoforum_create_ajax_request(function(res){
        document.querySelector(".neoforum_avatar").src=res['data'];
        document.querySelector(".neoforum_avatar_delete").style.display="inline-block";
    }, function(){}, false);
    let request=new FormData();
    

    request.append("action", "neoforum_save_avatar");
    request.append("user", user);
    request.append("nonce", nonce);
    request.append("avatar", file);
    postRequest.send(request);
}

var neoforum_save_contact_trottle;

document.addEventListener("DOMContentLoaded", function(){
    neoforum_save_contact_trottle=neoforum_trottle(neoforum_save_contact, 600);
})

function neoforum_avatar_delete(sender, user, nonce){
    let postRequest=neoforum_create_ajax_request(function(res){
        sender.style.display="none";
        document.querySelector(".neoforum_avatar").src="<?php echo get_avatar_url($user['ID']) ?>";
    });
    let request="action=neoforum_delete_avatar&userid="+user+"&nonce="+nonce;
    postRequest.send(request);
}
