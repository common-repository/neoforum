function neoforum_change_theme_descr(event, nonce){
let descr=document.querySelector(".neoforum_theme_description");
let postRequest=new XMLHttpRequest();
postRequest.timeout=5000;
postRequest.open('POST', neoforum_globals.site_url+"/wp-admin/admin-ajax.php", true);
postRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
postRequest.ontimeout = function(){
    alert("Connection timed out!");
}
postRequest.onerror = function(event){
    alert("Request error!");
}
postRequest.onreadystatechange = function() {
    descr.innerHTML=this.responseText;
}
let request="action=neoforum_theme_descr&theme="+event.target.options[event.target.selectedIndex].value+"&nonce="+nonce;
postRequest.send(request);
}
