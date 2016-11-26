<?php
	// DISPLAY COMMENTS IF COMMENTS ARE OPENED
    if ( comments_open() ) {
		echo '<div class="comments">',
			 '<h2>';
		_e('Comments', 'wikiwp');
		echo '</h2>';
		if ( have_comments() ) {
			// this is displayed if there are comments
			// echo '<h3>';
			// _e('This article currently has', 'wikiwp');
			// echo '&nbsp;';
			// comments_number( __('no notes','wikiwp'), __('one note', 'wikiwp'), __('% notes','wikiwp') );
			// echo '</h3>',
			echo '<ul class="commentlist">';
			wp_list_comments();
			echo '</ul>',
				 '<div class="comment-nav">',
				 '<div class="alignleft">';
			previous_comments_link();
			echo '</div>',
				 '<div class="alignright">';
			next_comments_link();
			echo '</div>',
				 '</div>';
		} else {
			// this is displayed if there are no comments so far
//			_e('So empty here ... leave a comment!', 'wikiwp');
		}
		// load comment form
		comment_form(array(
			'title_reply' => null,
			'label_submit' => 'Add comment',
			'logged_in_as' => null,
			'must_log_in' => '<p class="must-log-in">' .  sprintf( __( 'You must be <a href="%s">logged in</a> to post a note.' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( ) ) ) ) . '</p>'
		));
		echo '</div>'; // end of .content
	}