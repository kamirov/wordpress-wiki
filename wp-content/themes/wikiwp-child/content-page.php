<article class="entry entryTypePost">
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
            endwhile;
        ?>
    </div>

    <footer class="entryMeta">
        <?
        // get the post info
//        get_template_part('postinfo' );
        ?>
    </footer>
</article>