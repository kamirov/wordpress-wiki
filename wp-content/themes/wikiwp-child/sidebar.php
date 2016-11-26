<div class="asideMenuButton">
    <header class="asideMenuButtonTitle"><?php echo __('Sidebar', 'wikiwp'); ?></header>
    <div class="asideMenuButtonContent">
        <hr>
        <hr>
        <hr>
    </div>
</div>

<aside>
    <div class="aside-container container-full">
        <div class="customSidebar">
            <?php
            if (is_single() || is_page_template( 'wiki-page.php' )) {
                while (have_posts()) : the_post();
                    // get thumbnail
                    wikiwp_get_thumbnail($post);
            ?>

            <div class="row sidebarContent">
                <div class="col-md-12">
                    <?php
                    // show edit button if user is logged in
                    wikiwp_get_edit_post_link($post);
                    ?>

                    <div class="widget">
                        <h3 class="widgetTitle"><?php the_title(); ?></h3>
                    </div>

                    <div class="widget">
                        <?php
                        // modified date
                        _e('Last update on ', 'wikiwp');
                        echo '&nbsp;';
                        the_modified_date();
                        ?>
                    </div>

                    <div class="widget">
                        <div class="">
                            <?php
                            // publishing date
                            _e('Published', 'wikiwp');
                            echo '&nbsp;';
                            the_date();
                            ?>
                        </div>

                        <div class="">
                            <?php
                            _e('Author(s)', 'wikiwp');
                            echo ':</strong>&nbsp;';
                            coauthors_posts_links();
                            echo '</span>';
                            ?>
                        </div>

                        <div class="">
                            <?php
                                // categories
                                $cat = get_the_category();
                                if ($cat)
                                {
                                    $category_link = get_category_link( $cat[0]->cat_ID );

                                    _e('Category', 'wikiwp');
                                    echo ':&nbsp;';
                                    echo '<a href="'.$category_link.'">'.$cat[0]->name.'</a>';
                                }
//                               var_dump($cat);
                            ?>
                        </div>

                        <? $tags = wikiwp_child_get_tags($post);
                           if ($tags): ?>
                            Tags: <?= $tags ?>
                        <? endif; ?>
                    </div>

                    <?php //wikiwp_get_related_posts($post); ?>
                </div>
            </div>

            <?php
                endwhile;
            } else {
                ?>
                <div class="row sidebarContent">
                    <div class="col-md-12">
                        <?php
                        // show edit button if user is logged in
//                        wikiwp_get_edit_post_link($post);
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <div class="dynamicSidebar">
            <div class="row sidebarContent">
                <div class="col-md-12">
                    <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : endif; ?>
                </div>
            </div>
        </div>
    </div>
</aside>