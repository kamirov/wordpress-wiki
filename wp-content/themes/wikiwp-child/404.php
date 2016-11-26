<?php
get_header();
get_template_part('navigation');

function random_404()
{
	$user = wp_get_current_user();
	$first_name = $user->user_firstname;
	$display_name = $user->display_name;

	$phrases = array(
	    "I'm sorry, $first_name. I'm afraid I can't do that.",
	    "I find your lack of webpage disturbing.",
	    "He's dead, $first_name.",
	    "Damn it, $first_name. I'm an error, not a webpage.",
	    "Worst. 404 Page. Ever.",
	    "Don't believe there's a power in the verse that can make this the page exist.",
	    "After very careful consideration, sir, I've come to the conclusion that your page doesn't exist.",
	    "This isn't the page you're looking for.",
	    "Danger $display_name! Danger!",
	    "They may take our webpage, but they'll never take our freedom!",
	    "The greatest trick this webpage ever pulled was convincing the world it didn't exist.",
	    "I love the smell of missing pages in the morning.",
	    "You had me at \"Page not found\".",
	    "Houston, we have a missing page.",
	    "This page? You can't handle this page!",
	    "I see dead webpages.",
	    "404. Error 404.",
	    "We'll always have the home page.",
	    "Of all the 404 pages in all the websites in all the internet, you walk into mine."
	);
	return $phrases[array_rand($phrases)];
}

?>

<div class="pageContainer">
	<article class="entry entryTypePost">
		<header class="entryHeader">
			<h3 class="entryTitle">

				<?php echo random_404(); ?>
			</h3>
		</header>
	</article>

	<article class="entry">
	    <section class="entryTypePostExcerptContainer">
			<p>This page doesn't exist (error 404). If you got here through a link on the site, please mention it to someone. Thanks!</p>
			<hr>
			<h4 class="lastPostsListTitle">
				<?php echo __('Latest articles', 'wikiwp'); ?>
			</h4>

	        <?= do_shortcode('[recent-articles count="5"]'); ?>
		</section>
	</article>
</div>

<?php
// sidebar
get_sidebar();

// footer
get_footer();