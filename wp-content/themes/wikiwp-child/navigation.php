<div class="navMenuButton">
    <header class="navMenuButtonTitle"><?php echo __('Menu', 'wikiwp'); ?></header>
    <div class="navMenuButtonContent">
        <hr>
        <hr>
        <hr>
    </div>
</div>

<div class="primary-menu primary-menu-side">
    <div class="primary-menu-container">
        <nav class="nav-container">
        <?php
            wp_reset_query();
            // Main menu with fallback
            wp_nav_menu(array(
                'theme_location' => 'main-menu',
                'items_wrap' => '<ul class="main-menu">%3$s</ul>',
                'fallback_cb' => 'main_menu_fallback',
            ));

            // Main menu fallback
            function main_menu_fallback() {
                echo '<ul class="default-nav">';

        		// show meta
        		echo '<li class="metanav">';
        		echo '<span class="menu-title"></span>';
        			echo '<ul>';
        			echo '<li><b><a href="'.admin_url().'">'.wp_get_current_user()->display_name.'</a></b></li>';
        			echo '</ul>';
        		echo '</li>';

                // show pages
                $pages = get_pages( array(
                    'exclude'      => '118, 921, 925',       // Home, Inventory, Sensor A/Ds
                    'sort_column'   => 'menu_order'
                ));
                // $sliders = new WP_Query(
                //     array('post_type' => 'sliders', 'showposts' => '10', 'orderby' => 'menu_order', 'order' => 'ASC', 'countries'=> 'Default', 'supress_filters' => true, 'meta_query' => array(

                // echo '<pre>';
                // var_dump($pages);
                // echo '</pre>';

                $page_html = '<li class="pages">';
                $page_html .= '<hr>';
                $page_html .= '<ul>';
                foreach ($pages as $page) 
                {
//                    if ($page->ID == 17)                    // Issue Tracker
//                        $page->guid .= '&bugstatusid=14';   // Open status
                        // var_dump($page->guid);
                    $page_html .= '<li class="page-item"><a href="'.$page->guid.'" title="">'.$page->post_title.'</a></li>';
                }
                $page_html .= '</ul>';
                $page_html .= '</li>';

                echo $page_html;

                // New links
                // $links = array(
                //     'Article' => '',
                //     'Issue' => 'post_type=bug-library-bugs',
                //     'Sensor' => 'post_type=sensor',
                //     'Date' => 'post_type=date');
                // echo '<li class="new-links">';
                //     echo '<hr>';
                //     echo '<span class="menu-title">Add New</span>';
                //     echo '<ul>';
                //         foreach ($links as $text => $query_string) 
                //         {
                //             echo '<a href="'.admin_url().'post-new.php/?'.$query_string.'">'.$text.'</a>';
                //         }
                //     echo '</ul>';
                // echo '</li>';


                // show categories
                wp_list_categories( $args = array(
                    'title_li'     => '<hr><h4 class="menu-title">'. __('Categories', 'wikiwp') .'</h4>'
                ));

            }
                // Meta menu
                // wp_nav_menu(array(
                //     'theme_location' => 'meta-menu',
                //     'items_wrap' => '<ul class="meta-menu">%3$s</ul>',
                //     'fallback_cb' => '',
                // ));

            echo '<li class="tags">';
                echo '<hr>';
                echo '<h4 class="menu-title">Tags</h4>';
            echo '</li>';

            tfi_all_taxonomies( array('post_tag' ) );

        ?>

        </nav>

        <div class="dynamic-sidebar dynamic-sidebar-navigation">
            <div class="row sidebarContent">
                <div class="col-md-12">
                    <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('navigation') ) : endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>