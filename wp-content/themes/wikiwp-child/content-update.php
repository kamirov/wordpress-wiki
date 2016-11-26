<article class="entry inline-comment-entry entryTypePost">
    <header class="entryHeader">
        <?
            if ( have_posts() ) : the_post();
        ?>
        <!-- This is dirty. We manually put these here so that the Updates page appears as a parent to the update custom post type. There is a better way, probably. -->
        <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
            <!-- Breadcrumb NavXT 5.5.1 -->
            <span property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" title="Go to SAIL Wiki." href="<?= site_url() ?>" class="home"><span property="name">SAIL Wiki</span></a>
                <meta property="position" content="1">
            </span>
            &gt; 
            <span property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" title="Go to Updates." href="<?= site_url('updates') ?>" class="home"><span property="name">Updates</span></a>
                <meta property="position" content="2">
            </span>
            &gt; 
            <span property="itemListElement" typeof="ListItem">
                <span property="name"><?= the_title() ?> (<?= get_the_author() ?>)</span>
                <meta property="position" content="3">
            </span>
        </div>
        <h1 class="entryTitle">
            <?php
            the_title();
            endif;
            ?>
        </h1>
    </header>

    <div class="entryContent">
        <? 
/*
            $fields = array(
                'things_done' => get_field_object('things_done'),
                'questions_and_points' => get_field_object('questions_and_points'),
                'whats_next' => get_field_object('whats_next')
            );

//            var_dump($fields);
        ?>
        <? foreach ($fields as $field): ?>
            <? if ($field['value']): ?>
            <h1><?= $field['label'] ?></h1>
            <?= $field['value'] ?>
            <? endif; ?>
        <? endforeach; 
*/
        the_content();
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
        <? endif; ?>
    </div>

    <footer class="entryMeta">
        <?php
        // get the post info
//        get_template_part('postinfo' );
        ?>
    </footer>
</article>