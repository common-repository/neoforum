<?php wp_enqueue_style("neoforum_visual_editor", neoforum_URL."neo visual editor/css/style.css"); ?>
<div class="ne-container">
    <div class="ne-panel">
        <select onchange="neoforum_doFormat(this, 'fontsize',this[this.selectedIndex].value);this.selectedIndex=0;">
            <option selected><?php _e("Select font size", "neoforum"); ?></option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
        </select>
        <select onchange="neoforum_doFormat(this, 'forecolor',this[this.selectedIndex].style.backgroundColor);this.selectedIndex=0;">
            <option selected><?php _e("Select font color", "neoforum"); ?></option>
            <option style="background-color: black;"></option>
            <option style="background-color: red;"></option>
            <option style="background-color: blue;"></option>
            <option style="background-color: green;"></option>
            <option style="background-color: aqua;"></option>
            <option style="background-color: brown;"></option>
            <option style="background-color: cornflowerblue;"></option>
            <option style="background-color: white;"></option>
        </select>
        <select onchange="neoforum_doFormat(this, 'fontname',this[this.selectedIndex].value);this.selectedIndex=0;">
            <option><?php _e("Select font", "neoforum"); ?></option>
            <option>Arial</option>
            <option>Arial Black</option>
            <option>Courier New</option>
            <option>Times New Roman</option>
        </select> 
        <br>
        <button type="button" class="ne-button ne-b" title="<?php esc_html_e("Set bold text", "neoforum") ?>" onclick="neoforum_doFormat(this, 'bold');">
            <b>b</b>
        </button>
        <button type="button"  class="ne-button ne-i" title="<?php esc_html_e("Set italic text", "neoforum") ?>" onclick="neoforum_doFormat(this, 'italic');">
            <i>i</i>
        </button>
        <button type="button"  class="ne-button ne-u" title="<?php esc_html_e("Set underline text", "neoforum") ?>" onclick="neoforum_doFormat(this, 'underline');">
            <u>u</u>
        </button>
        <button type="button"  class="ne-button ne-u" title="<?php esc_html_e("Set strikethrough  text", "neoforum") ?>" onclick="neoforum_doFormat(this, 'strikeThrough');">
            <strike>&nbsp;s&nbsp;</strike>
        </button>
        <span class="ne-divider">
        </span>
        <button type="button"  class="ne-button ne-align-left" title="<?php esc_html_e("Set left alignment", "neoforum") ?>" onclick="neoforum_doFormat(this, 'justifyLeft');">
            <img alt="icon" src="<?php echo(neoforum_URL."neo visual editor/img/left-align.svg"); ?>" width="25px" height="25px">
        </button>
        <button type="button" class="ne-button ne-align-center" title="<?php esc_html_e("Set center alignment", "neoforum") ?>" onclick="neoforum_doFormat(this, 'justifyCenter');">
            <img alt="icon" src="<?php echo(neoforum_URL."neo visual editor/img/center-align.svg"); ?>" width="25px" height="25px">
        </button>
        <button type="button" class="ne-button ne-align-right" title="<?php esc_html_e("Set right alignment", "neoforum") ?>" onclick="neoforum_doFormat(this, 'justifyRight');">
            <img alt="icon" src="<?php echo(neoforum_URL."neo visual editor/img/right-align.svg"); ?>" width="25px" height="25px">
        </button>
        <button type="button" class="ne-button ne-align-justify" title="<?php esc_html_e("Set justify alignment", "neoforum") ?>" onclick="neoforum_doFormat(this, 'justifyFull');">
            <img alt="icon" src="<?php echo(neoforum_URL."neo visual editor/img/justify-align.svg"); ?>" width="25px" height="25px">
        </button>
        <br>
        <?php if (get_option("neoforum_allow_links")=='on'){ ?>
        <button type="button" class="ne-button ne-link" title="<?php esc_html_e("Create link", "neoforum") ?>" onclick="neoforum_doFormat(this, 'createLink', prompt('<?php _e("Enter URL"); ?>'));">
            <img alt="icon" src="<?php echo(neoforum_URL."neo visual editor/img/link.svg"); ?>" width="25px" height="25px">
        </button>
        <? } ?>
        <button type="button" class="ne-button ne-unlink" title="<?php esc_html_e("Remove link", "neoforum") ?>" onclick="neoforum_doFormat(this, 'unlink');">
            <img alt="icon" src="<?php echo(neoforum_URL."neo visual editor/img/unlink.svg"); ?>" width="25px" height="25px">
        </button>
        <button type="button" class="ne-button ne-img"  title="<?php esc_html_e("Insert image", "neoforum") ?>" onclick="neoforum_doFormat(this, 'insertImage', prompt('<?php _e("Enter image address"); ?>'));">
            <img alt="icon" src="<?php echo(neoforum_URL."neo visual editor/img/picture.svg"); ?>" width="25px" height="25px">
        </button>
        <span class="ne-divider"></span>
        <button type="button" class="ne-button ne-quote" title="<?php esc_html_e("Wrap text in quote block", "neoforum") ?>" onclick="neoforum_doFormat(this, 'insertHTML', neoforum_quote())">
            <img alt="icon" src="<?php echo(neoforum_URL."neo visual editor/img/quote.svg"); ?>" width="25px" height="25px">
        </button>
        <button type="button" class="ne-button ne-offtopic" title="<?php esc_html_e("Mark text as offtopic", "neoforum") ?>" onclick="neoforum_doFormat(this, 'insertHTML', neoforum_offtopic())">
            Offtopic
        </button>
    </div>
    <div class="ne-editor" contenteditable="true" oninput="this.nextElementSibling.value=this.innerHTML;"><?php if($text!=""){echo(neoforum_clean_post_content($text));} ?>
    </div>
    <input type="hidden" name="ne_text_field" value="<?php if($text!=""){echo(neoforum_clean_post_content($text));} ?>" id="ne_text_handler">
</div>
<?php 
    wp_register_script(
    'neoforum_visual_editor',
    plugins_url( 'js/script.js', __FILE__ ),
    array( 'wp-i18n' ),
    '0.0.1'
    );
    wp_set_script_translations( 'neoforum_visual_editor', 'neoforum' );
    wp_enqueue_script( "neoforum_visual_editor");
?>
