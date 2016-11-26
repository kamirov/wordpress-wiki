<?php
    get_header();
    get_template_part('navigation');
    wp_reset_query();
?>

<div class="catContainer">
    <section class="entryTypePostExcerptHeader">
        <header class="entryHeader">
            <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
                <?php if(function_exists('bcn_display'))
                {
                    bcn_display();
                }?>
            </div>
            <h1 class="entryTitle">
                <?php single_cat_title(); ?>
            </h1>
        </header>

        <div class="entryContent">
            <?php
                // tag description if exists
                $tag = get_the_tags();
                if( tag_description( $tag[0]->tag_ID ) ) {
                    echo '<p class="tagDescription">'.tag_description( $tag[0]->tag_ID ).'</p>';
                }
            ?>
        </div>
    </section>

    <section class="entryTypePostExcerptContainer">
        <?php
        $args = array(
            'tag_id' => $tag[0]->term_id,
            'posts_per_page' => -1,
            'order' => 'desc',
            'orderby' => 'modified'
        );

        $articles = get_posts($args);

        global $post;
        foreach ($articles as $post)
        {
            setup_postdata($post);
            wwe_get_post_excerpt($post, false);
        }
        ?>
    </section>

</div>

<?php
// sidebar
get_sidebar();

// footer
get_footer();