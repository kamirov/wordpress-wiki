<?php

add_action('admin_menu', 'comment_menu');

function comment_menu(){
	add_menu_page( "TinyMCE Visual Editor Comment Setting", "Visual Comment", 'manage_options', "tinymce-visual-editor-comment", "setting_comment", null );
}
function setting_comment()
{
  comment_update();
  comment_html();
}
function comment_html(){
?>
<div class="wrap" style="margin-left:20px">
<form action="" method="post" id="setting" >
	<label for="ltr_rtf"><h3>Cursor Position</h3></label>
	<input type="radio" name="ltr_rtf" value="ltr" <?php if(get_option('ltr_rtf')=='ltr') echo "checked='checked'"; ?>>Left To Right<br>
	<input type="radio" name="ltr_rtf" value="rtl" <?php if(get_option('ltr_rtf')=='rtl') echo "checked='checked'"; ?>>Right To Left<br><br>
	<label for="mediabtn"><h3>Support image</h3></label>
	<input type="checkbox" name="mediabtn" value="mediabtn" <?php if(get_option('mediabtn')=='mediabtn') echo "checked='checked'"; ?>>Support image<br>
	<label for="mobilesp"><h3>Support mobile</h3></label>
	<input type="checkbox" name="mobilesp" value="mobilesp" <?php if(get_option('mobilesp')=='mobilesp') echo "checked='checked'"; ?>>Support visual on mobile<br>
	
	<br>
	<input type="submit" name="ltr_rtf_submit" value="Save change" class="button"/></p>
	<?php wp_nonce_field('ltr_rtf_update','ltr_rtf_submit_b'); ?>
</form>
</div>
<?php
}

function comment_update()
{
	if ( !empty($_POST) && wp_verify_nonce($_POST['ltr_rtf_submit_b'],'ltr_rtf_update') )
	  {
		update_option( 'ltr_rtf',($_POST['ltr_rtf']));
		update_option( 'mediabtn',($_POST['mediabtn']));
		update_option( 'mobilesp',($_POST['mobilesp']));
	?>
	<div class="updated">Option updated.</div><br/>
	<?php
	 }
}