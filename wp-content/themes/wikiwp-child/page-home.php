<?php

if ($GLOBALS['IS_MATLAB_REQUEST'])
	return;


get_header();
get_template_part('navigation');

// post
?>

<div class="pageContainer">
<?php
	// get content format
	get_template_part( 'content', 'home' );
?>
</div>

<?php
// sidebar
get_sidebar('');

// footer
get_footer();