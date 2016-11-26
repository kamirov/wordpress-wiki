<?php

// Necessary (without this, the current author is incorrect sometimes. I think there's a query somewhere that wasn't reset. Likely in functions.php)
wp_reset_query();

get_header();
get_template_part('navigation');


// post
?>

<div class="pageContainer">

<article class="entry entryTypePost">
    <header class="entryHeader clearfix">
        <div class="avatar-profile">
            <div class="alignleft avatar"><?= get_avatar( get_the_author_meta( 'email' ), '150' ) ?></div>
        </div>
        <h1 class="page-title"><? the_author() ?></h1>
        <? if ($description = nl2br(get_the_author_meta('description'))):  ?>
            <p class="author-description"><?= $description ?></p>
        <? endif; ?>
    </header>

    <div class="entryContent clearfix">
        <h1>Authored Articles</h1>
        <?  
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'author_name' => get_query_var( 'author_name' ),
            );
            $author_query = new WP_Query( $args );
             
            if ( $author_query->have_posts() ) : ?>
            <ul>
            <?
                while ( $author_query->have_posts() ) : $author_query->the_post(); 
            ?>
            <li>
                <a href="<? the_permalink(); ?>" rel="bookmark" title="<? the_title(); ?>"><? the_title(); ?></a>
            </li> 
            <?
                endwhile;
            ?>
            </ul>
            <?
            else: ?>
            <p>No articles by this person...yet!</p>
            <? 
            endif;
            ?>
    </div>

</article>

</div>

<?php
// sidebar
get_sidebar('');

// footer
get_footer();

/*

    echo '<div class="content">',
    echo '<div class="author-postings">',
         '<h3>'.get_the_author();
         _e('&acute;s postings', 'wikiwp');
    echo '</h3>',
         '<ul>'; 
    if ( have_posts() ) : while ( have_posts() ) : the_post();
    echo '<li>',
         '<a href="'.get_permalink().'" rel="bookmark" title="'.get_the_title().'">'.get_the_title().'</a>',
         '</li>';
         endwhile;
    echo '</ul>';
         // no posts by this autor so far 
         else:
    echo '<p>';
    _e('No posts by this author.', 'wikiwp');
    echo '</p>';
    endif;
    echo '</div>', // end of .author-postings
         '</div>';// end of .avatar-profile
    // author list
    echo '<div class="author-list">',
         '<h2>';
    _e('Our authors', 'wikiwp');
    echo '</h2>',
         '<ul>';
         wp_list_authors('show_fullname=1&optioncount=1&orderby=post_count&order=DESC');
    echo '</ul>';
    echo '</div>',
         '</div>'; // end of .content
    // footer
    get_footer();
    */