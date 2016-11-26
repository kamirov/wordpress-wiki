<article class="entry inline-comment-entry entryTypePost">
    <header class="entryHeader">
        <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
            <? if(function_exists('bcn_display'))
            {
                bcn_display();
            }?>
        </div>
        <h1 class="entryTitle">
            <?
            while ( have_posts() ) : the_post();
            the_title();
            ?>
        </h1>
    </header>

    <div class="entryContent">
        <?
            // get the content
            the_content();
        ?>

        <?
            if (!is_page())
            {
            // Find connected papers
            $papers = new WP_Query( array(
            'connected_type' => 'paper_posts',
            'connected_items' => get_queried_object(),
            'nopaging' => true,
            ) );
        
            $parents = p2p_type('post_parents')->set_direction('from')->get_connected(get_the_id());
            $children = p2p_type('post_parents')->set_direction('to')->get_connected(get_the_id());

            if ($parents->have_posts() || $children->have_posts() || $papers->have_posts()): ?>
                <h1>Related</h1>

                <? if ($papers->have_posts()): ?>
                <h3>Papers:</h3>
                <ul>
                <? while ( $papers->have_posts() ) : $papers->the_post(); ?>
                    <li><a href="<? the_permalink(); ?>"><? the_title(); ?></a></li>
                <? endwhile; ?>
                </ul>
                <?  wp_reset_postdata();
                endif;   

                if ($parents->have_posts()): ?>
                <h3>Parents:</h3>
                <ul>
                <? while ( $parents->have_posts() ) : $parents->the_post(); ?>
                    <li><a href="<? the_permalink(); ?>"><? the_title(); ?></a></li>
                <? endwhile; ?>
                </ul>
                <?  wp_reset_postdata();
                endif;   

                if ($children->have_posts()): ?>
                <h3>Children:</h3>
                <ul>
                <? while ( $children->have_posts() ) : $children->the_post(); ?>
                    <li><a href="<? the_permalink(); ?>"><? the_title(); ?></a></li>
                <? endwhile; ?>
                </ul>
                <?  wp_reset_postdata();
                endif;
            endif;   
        ?>
        
        <? $attachments = new Attachments( 'sail_attachments' ); ?>
        <? if( $attachments->exist() ) : ?>
          <h1>Attachments</h1>
          <ul>
            <? while( $attachments->get() ): 
                $url = $attachments->url(); 
                $comments = $attachments->field( 'comments' );
            ?>
              <li>
                <a href="<? echo $url ?>" download><?= end(explode('/', $url));?></a>
                <? if ($comments): ?><ul><li><? echo $comments ?></li></ul><? endif; ?>
              </li>
            <? endwhile; ?>
          </ul>
        <? endif; 

            }
        ?>
        <? endwhile; ?>
    </div>

    <footer class="entryMeta">
        <?
        // get the post info
//        get_template_part('postinfo' );
        ?>
    </footer>
</article>