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
                            _e('Author', 'wikiwp');
                            echo ':</strong>&nbsp;';
                            coauthors_posts_links();
                            echo '</span>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</aside>