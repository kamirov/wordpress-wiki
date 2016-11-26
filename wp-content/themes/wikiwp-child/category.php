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
                // category description if exists
                $category = $wp_query->get_queried_object();
                // echo '<pre>';
                // var_dump($category);
                // echo '</pre>';
                $link = strtok($_SERVER["REQUEST_URI"], '?');
                if( category_description( $category->cat_ID ) ) {
                    echo '<p class="categoryDescription">'.category_description( $category->cat_ID ).'</p>';
                }
            ?>
            <p>
                <?
                $tags = get_tags();
                $current_tag = $_GET['filter_tag'];
                if ($current_tag)
                {
                    $tag = get_term_by('name', $current_tag, 'post_tag');
                    if ($tag)
                        $tag_id = $tag->term_id;                    
                }

                ?>
                Showing:
                <select class="ad-range default-select-style" onChange="window.location.href=this.value">
                    <option <?= is_null($current_tag) ? 'selected' : '' ?> value="<?= $link ?>">-- All tags -- </option>
                    <? foreach ($tags as $tag): ?>
                    <option <?= ($current_tag == $tag->name) ? 'selected' : '' ?> value="?filter_tag=<?= $tag->name ?>"><?= $tag->name ?></option>
                    <? endforeach; ?>
                </select>
            </p>
            <hr>
        </div>
    </section>

    <section class="entryTypePostExcerptContainer">
    <?php
        $args = array(
            'category' => $category->cat_ID,
            'posts_per_page' => -1,
            'order' => 'desc',
            'orderby' => 'modified'
        );

        if (isset($tag_id))
            $args['tag_id'] = $tag_id;

        $articles = get_posts($args);

        global $post;
        if (empty($articles))
        { 
            $html .= '<article class="entry">';
                $html .= '<div class="entryContainer">';
                    $html .= '<div class="entryContent">';
                        $html .= '<p>No articles found.</p>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</article>';
            echo $html;
        }
        else
        {
            foreach ($articles as $post)
            {
                setup_postdata($post);
                wwe_get_post_excerpt($post);
            }            
        }
    ?>
    </section>
</div>

<?php
// sidebar
get_sidebar();

// footer
get_footer();