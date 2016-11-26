<?php
require_once('setting.php');
$mobilesp = get_option('mobilesp');
if ($mobilesp=="mobilesp"){
	add_filter( 'comment_form_field_comment', 'comment_editor' );
}
else {
	if( !wp_is_mobile() )
		add_filter( 'comment_form_field_comment', 'comment_editor' );
}
function comment_editor() {
  global $post;
  ob_start();
  $rtl = get_option('ltr_rtf');
  $mediabtn = get_option('mediabtn');
  if ($mediabtn=='mediabtn'){$mediabtn=true;}
  else{$mediabtn=false;}
  wp_editor( '', 'comment', array(
    'textarea_rows' => 15,
    'teeny' => true,
    'quicktags' => false,
	'media_buttons' => $mediabtn,
	'tinymce' => array('directionality' => $rtl),
  ) );
 
  $editor = ob_get_contents();
 
  ob_end_clean();
 
  //make sure comment media is attached to parent post
  $editor = str_replace( 'post_id=0', 'post_id='.get_the_ID(), $editor );
 
  return $editor;
}
 
// wp_editor doesn't work when clicking reply. Here is the fix.
add_action( 'wp_enqueue_scripts', '__THEME_PREFIX__scripts' );
function __THEME_PREFIX__scripts() {
    wp_enqueue_script('jquery');
}
add_filter( 'comment_reply_link', '__THEME_PREFIX__comment_reply_link' );
function __THEME_PREFIX__comment_reply_link($link) {
    return str_replace( 'onclick=', 'data-onclick=', $link );
}
add_action( 'wp_head', '__THEME_PREFIX__wp_head' );

function __THEME_PREFIX__wp_head() {
?>
<script type="text/javascript">
  jQuery(function($){
    $('.comment-reply-link').click(function(e){
      e.preventDefault();
      var args = $(this).data('onclick');
      args = args.replace(/.*\(|\)/gi, '').replace(/\"|\s+/g, '');
      args = args.split(',');
      tinymce.EditorManager.execCommand('mceRemoveEditor', true, 'comment');
      addComment.moveForm.apply( addComment, args );
      tinymce.EditorManager.execCommand('mceAddEditor', true, 'comment');
    });
    $('#cancel-comment-reply-link').click(function(e){
        e.preventDefault();
        tinymce.EditorManager.execCommand('mceRemoveEditor', true, 'comment');
        setTimeout(function(){ tinymce.EditorManager.execCommand('mceAddEditor', true, 'comment'); }, 1);
    });
  });
</script>
<?php }

function at_comment_css() {
	// This makes sure that the positioning is also good for right-to-left languages
	$x = is_rtl() ? 'left' : 'right';

	echo "
	<style type='text/css'>
	#wp-comment-editor-container {
		border: 2px solid #DFDFDF;
	}
	</style>
	";
}

add_action('wp_head', 'at_comment_css');