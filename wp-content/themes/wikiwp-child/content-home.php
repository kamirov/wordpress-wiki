<article class="entry entryTypePost">
    <header class="entryHeader">
        <h1 class="entryTitle">
            <?php
            while ( have_posts() ) : the_post();
            echo '<h1>Welcome to the SAIL Wiki!</h1>';
            ?>
        </h1>
    </header>

    <div class="entryContent">
        <?php
        // get the content
        the_content();
        endwhile;
        ?>
    </div>

    <footer class="entryMeta">
        <?php
        // get the post info
//        get_template_part('postinfo' );
        ?>
    </footer>
</article>