<article class="entry inline-comment-entry entryTypePost">
    <header class="entryHeader">
        <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
            <?php if(function_exists('bcn_display'))
            {
                bcn_display();
            }?>
        </div>
        <h1 class="entryTitle">
            <?php
            while ( have_posts() ) : the_post();
            echo 'Item '. get_the_id().': ';
            the_title();
            ?>
        </h1>
    </header>

    <div class="entryContent">

        <?php
        global $post;

        $product   = wp_get_post_terms( $post->ID, "bug-library-products" );
        $status     = wp_get_post_terms( $post->ID, "bug-library-status" );
        $type      = wp_get_post_terms( $post->ID, "bug-library-types" );
        $priority = wp_get_post_terms( $post->ID, "bug-library-priority" );

        $assigneduserid = get_post_meta( $post->ID, "bug-library-assignee", true );
        if ( $assigneduserid != - 1 && $assigneduserid != '' ) {
            $assigneedata = get_userdata( $assigneduserid );
            if ( $assigneedata ) {
                $firstname = get_user_meta( $assigneduserid, 'first_name', true );
                $lastname  = get_user_meta( $assigneduserid, 'last_name', true );

                if ( $firstname == "" && $lastname == "" ) {
                    $firstname = $assigneedata->user_login;
                }
            } else {
                $firstname = "Unassigned";
                $lastname  = "";
            }
        } else {
            $firstname = "Unassigned";
            $lastname  = "";
        }

        ?>

        <table class="bug-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $product[0]->name ?></td>
                    <td><?= $type[0]->name ?></td>
                    <td><?= $priority[0]->name ?></td>
                    <td><?= $status[0]->name ?></td>
                    <td><?= $firstname.' '.$lastname ?></td>
                </tr>
            </tbody>
        </table>

        <?

        // get the content
        the_content();
        endwhile;
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