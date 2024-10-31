document.execCommand("defaultParagraphSeparator", false, "p");


function neoforum_doFormat(el, command, value){
    let edit=el.parentNode.nextElementSibling;
    document.execCommand(command, false, value); 
    edit.focus();
}

//onclick='this.nextElementSibling.classList.toggle("neoforum_show");'
function neoforum_offtopic(){
    return `
    <br>
    <div class='neoforum_offtopic'>
        <button type='button' onclick="neoforum_open_offtopic(this);">
            Show offtopic
        </button>
        <div class='neoforum_offtopic_hidden'>${document.getSelection()}</div>
    </div>
    <br>`;

}
function neoforum_quote(text="", author=""){
    return `
    <br>
    <blockquote class='neoforum_quote'>
        <div class='neoforum_quote_author'>
${author!="" ? author : ""}
        </div>
        <div class='neoforum_quote_text'>${text=="" ? document.getSelection() : text}</div>
    </blockquote>
    <br>`;
}
