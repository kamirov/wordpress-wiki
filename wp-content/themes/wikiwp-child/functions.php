<?

/* SAIL_PHP_INI
   ============ */

@ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );


/* SAIL_CONSTANTS
   ============== */

// Uploads
define('ATTACHMENTS_DEFAULT_INSTANCE', false ); 
define('ALLOW_UNFILTERED_UPLOADS', true);           // Used for wp-admin file uploads
define('MATLAB_UPLOAD_USERNAME', 'wikiupload');
define('FROM_MATLAB_KEY', 'from_matlab');           // HTTP request key to indicate we're requesting from MATLAB

// Dates
define('DATES_PAGE_ID', 202);
define('PUB_PAGE_ID', 1040);
define('DATES_TABLE_SLUG', 'upcoming_dates');
define('DATES_TABLE_SHORTCODE', 'dates-table');
define('PUB_TABLE_SLUG', 'publications');
define('PUB_TABLE_SHORTCODE', 'pub-table');
define('MISC_DATES_TABLE_SLUG', 'miscellaneous_dates');
define('MISC_DATES_TABLE_SHORTCODE', 'misc-dates-table');
define('UPCOMING_TABLE_SHORTCODE', 'upcoming-table');

// Article listings
define('ALPHABETICAL_CONTENT_SHORTCODE', 'alphabetical-content');
define('RECENT_ARTICLES_SHORTCODE', 'recent-articles');
define('ALL_PAPERS_SHORTCODE', 'all-papers');

// Updates
define('UPDATES_DROPDOWNS_SHORTCODE', 'updates-dropdowns');
define('UPDATES_SHOW_SHORTCODE', 'updates-show');


// Sensors
define('SENSORS_SHOW_SHORTCODE', 'sensors-show');
define('SENSORS_INVENTORY_SHORTCODE', 'sensors-inventory');
define('SENSORS_AD_SHORTCODE', 'sensors-ad');
define('SENSORS_AD_RANGE_SHORTCODE', 'sensors-ad-range');


/* SAIL_CHILD_THEME_BOOTSTRAP
   ========================== */

function my_theme_enqueue_styles() {

    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(), array( 'parent-style' ) );

}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

// load stylesheet for bootstrap
function wikiwp_child_load_bootstrap_styles() {                       
  	wp_register_style( 'bootstrap-style', 
    get_template_directory_uri().'/css/bootstrap.min.css', array(), false, 'all' );    
  	wp_enqueue_style( 'bootstrap-style' );
}
add_action('wp_enqueue_scripts', 'wikiwp_child_load_bootstrap_styles');

// load stylesheet for the theme
function wikiwp_child_load_styles() {                       
  	wp_register_style( 'theme_style', 
    get_template_directory_uri().'/style.css', array(), false, 'all' );    
  	wp_enqueue_style( 'theme_style' );
}
add_action('wp_enqueue_scripts', 'wikiwp_child_load_styles');

// load stylesheet for navigation
function wikiwp_child_load_navigation_side_styles() {                       
  	wp_register_style( 'navigation-side-style', 
    get_template_directory_uri().'/css/navigation-side.css', array(), false, 'all' );    
  	wp_enqueue_style( 'navigation-side-style' );
}
add_action('wp_enqueue_scripts', 'wikiwp_child_load_navigation_side_styles');

// load stylesheet for wiki
function wikiwp_child_load_wiki_styles() {                       
    wp_register_style( 'wiki-style', 
    get_template_directory_uri().'/css/wiki.css', array(), false, 'all' );    
    wp_enqueue_style( 'wiki-style' );
}
add_action('wp_enqueue_scripts', 'wikiwp_child_load_wiki_styles');

// Load scripts for wiki
function wikiwpchild_function_script() {
    wp_enqueue_script(
        'functions-script',
        get_template_directory_uri() . '/js/functions.js',
        array( 'jquery' )
    );

    wp_enqueue_script(
        'ext-script',
        get_stylesheet_directory_uri() . '/js/scripts.js',
        array( 'jquery' )
    );

    wp_enqueue_script(
        'child-script',
        get_stylesheet_directory_uri() . '/js/sailwiki.js',
        array( 'jquery' )
    );
}
add_action( 'wp_enqueue_scripts', 'wikiwpchild_function_script' );


/* SAIL_UPDATES
   ============ */

function updates_name_and_slug_change( $post_ID, $post, $update ) {
    // allow 'publish', 'draft', 'future'
    if ($post->post_type != 'update' || $post->post_status == 'auto-draft')
        return;

    // only change slug when the post is created (both dates are equal)
    if ($post->post_date_gmt != $post->post_modified_gmt)
        return;

    $author_login = get_the_author_meta('user_login', $post->post_author);

    // Make post title
    $day = date('w');
    $week_start = date('M j, Y', strtotime('-'.$day.' days'));
    // $week_end = date('M j, Y', strtotime('+'.(6-$day).' days'));

    $post_title = 'Week of '.$week_start;

    // use title, since $post->post_name might have unique numbers added
    $new_slug = sanitize_title( $post_title, $post_ID ).'-'.$author_login;

    // Check if slug exists
    if (get_page_by_path($new_slug, OBJECT, 'update'))
    {
        // Add 7 days and try again (this is the next week's update)
        $week_start = date('M j, Y', strtotime('+'.(7-$day).' days'));
        $post_title = 'Week of '.$week_start;
        $new_slug = sanitize_title( $post_title, $post_ID ).'-'.$author_login;
    }

    // unhook this function to prevent infinite looping
    remove_action( 'save_post', 'updates_name_and_slug_change', 10, 3 );
    // update the post slug (WP handles unique post slug)
    wp_update_post( array(
        'ID' => $post_ID,
        'post_name' => $new_slug,
        'post_title' => $post_title
    ));

    // re-hook this function
    add_action( 'save_post', 'updates_name_and_slug_change', 10, 3 );
}
add_action( 'save_post', 'updates_name_and_slug_change', 10, 3 );


/* SAIL_AUTH
   ========= */

// The HTTP Authentication plugin handles what happens if wp-login.php is viewed. This causes any page to log an HTTP authenticated user
function apache_login()
{
	$username = $_SERVER['REMOTE_USER'];

	if ($username && !is_user_logged_in())
	{
		$user = get_user_by('login', $username);

		// Redirect URL //
		if ( !is_wp_error( $user ) )
		{
		    wp_clear_auth_cookie();
		    wp_set_current_user ( $user->ID );
		    wp_set_auth_cookie ( $user->ID );

		    $redirect_to = $_SERVER['REQUEST_URI'];
		    wp_safe_redirect( $redirect_to );
		    exit();
		}
	}	
}
add_action('init', 'apache_login');


/* SAIL_ENTRIES
   ============*/

/**
 * Tags output handling
 *
 * @return string formatted output in HTML
 */
function wikiwp_get_article_tags($post) {

    _e('Tags', 'wikiwp');
    echo ':&nbsp;';
    $tag = get_the_tags();
    if (! $tag) {
        echo 'There are no tags for this article';
    } else {
        the_tags('',', ','');
    }

}

/**
 * Tags output handling
 *
 * @return string formatted output in HTML
 */
function wikiwp_child_get_tags($post) {

    $tags = get_the_tags();
    $tag_output = '';

    if ($tags)
    {
        foreach ($tags as $tag) 
            $tag_output .= '<a href="'.home_url().'/'.'tag/'.$tag->slug.'" class="tag-item '.$tag->slug.'">'.$tag->name.'</a>';

        return $tag_output;
    }
    else
    {
//        the_tags('',', ','');
    }
}

/**
 * Post excerpt output handling
 *
 * @return string formatted output in HTML
 */
function wikiwp_child_get_post_excerpt($post, $show_details = true, $show_tags = true, $show_prefixes = true) {
    $wikiwpAdditionalExcerptPostClasses = array(
        'entry',
        'entryTypePostExcerpt'
    );

    ?>

    <article <?php post_class($wikiwpAdditionalExcerptPostClasses); ?>>
        <div class="">
            <?
            // echo '<pre>';
            // var_dump($post);
            // echo '</pre>';
            ?>
            <div class="entryContainer">
                <header class="entryHeader">
                    <h2 class="entryTitle">
                        <a href="<?php the_permalink(); ?>">
                        	<? if ($show_prefixes): ?>
	                            <? if ($post->post_type == 'bug-library-bugs'): ?>
	                                Issue <?= $post->ID ?>:
	                            <? elseif ($post->post_type == 'sensor'): ?>
	                                Sensor: 
	                            <? elseif ($post->post_type == 'paper'): ?>
	                                Paper: 
	                            <? endif; ?>
	                        <? endif; ?>
                            <? the_title(); ?>
                        </a>
                    </h2>

                </header>
                <? if ($show_details): ?>
                    <? if ($show_tags): ?>
                    <span class="tags"><?= wikiwp_child_get_tags($post); ?></span>
                    <? endif; ?>
                <div class="entryContent">
                    <?php if (has_excerpt()) the_excerpt() ?>
                </div>
                <? endif; ?>

                <div class="postinfo postinfo-excerpt">
                    <span>
                    <? if ($post->post_type == 'post' || is_null($post->post_type)): ?>
                    <? coauthors_posts_links(); ?> | 
                    <? endif; ?>
                    <span class="mod-date">Modified: <?php the_modified_date('M j, Y'); ?> (<?php the_modified_date('g:i a'); ?>)</span></span>
                </div>

                <footer class="entryMeta">
                    <?php// get_template_part('postinfo' ); ?>
                </footer>
            </div>
        </div>
    </article>
<?php } ?>
<?php


function custom_excerpt_length( $length ) 
{
    return 20;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );


/* SAIL_SHORTCODES
   =============== */

function display_acf_table($slug, $post_id, $class = '', $extra_rows = array())
{
    $table = get_field($slug, $post_id);
    $table_html = '';
    if ( $table )
    {
        $table_html .= '<table class="'.$class.'" border="0">';
            if ( $table['header'] ) 
            {
                $table_html .= '<thead>';
                    $table_html .= '<tr>';
                        foreach ( $table['header'] as $th ) 
                        {
                            $table_html .= '<th>';
                                $table_html .= $th['c'];
                            $table_html .= '</th>';
                        }
                    $table_html .= '</tr>';
                $table_html .= '</thead>';
            }
            $table_html .= '<tbody>';
                foreach ( $table['body'] as $tr ) 
                {
                    // Check for blank row
                    $is_blank = true;
                    foreach ( $tr as $td ) 
                    {
                        if ($td['c'])
                        {
                            $is_blank = false;
                            break;
                        }
                    }

                    if ($is_blank)
                        continue;

                    $table_html .= '<tr>';
                        foreach ( $tr as $td ) 
                        {
                            $table_html .= '<td>';
                                $table_html .= $td['c'];
                            $table_html .= '</td>';
                        }
                    $table_html .= '</tr>';
                }
                foreach ($extra_rows as $tr) 
                {
                    $table_html .= '<tr>';
                        foreach ( $tr as $td) 
                        {
                            $table_html .= '<td>';
                                $table_html .= $td;         // Note, no ['c'] for custom rows
                            $table_html .= '</td>';
                        }
                    $table_html .= '</tr>';
                }
            $table_html .= '</tbody>';
        $table_html .= '</table>';
    }

    return $table_html;

}

function dates_table($atts)
{
    extract(shortcode_atts(array(
      'class' => '',
   ), $atts));

    return display_acf_table(DATES_TABLE_SLUG, DATES_PAGE_ID, $class);    
}

function pub_table($atts)
{
    extract(shortcode_atts(array(
      'class' => '',
   ), $atts));

    $paper_rows = get_paper_rows();


    return display_acf_table(PUB_TABLE_SLUG, PUB_PAGE_ID, $class, $paper_rows);    
}

function misc_dates_table($atts)
{
    extract(shortcode_atts(array(
      'class' => '',
   ), $atts));

    return display_acf_table(MISC_DATES_TABLE_SLUG, DATES_PAGE_ID, $class);    
}

function all_papers($atts)
{
    extract(shortcode_atts(array(
        'count' => -1,
    ), $atts));

    // Have to buffer this, since get_post_excerpt echos output
    ob_start();

    echo '<div class="paper-entry-container">';
    query_posts(array(
    	'post_type' => 'paper', 
    	'orderby' => 'date', 
    	'order' => 'DESC' , 
    	'showposts' => $count)
    );
    if (have_posts()) :
        while (have_posts()) : the_post();
            wikiwp_child_get_post_excerpt($post, false);
        endwhile;
    endif;
    echo '</div>';

    wp_reset_query();
    return ob_get_clean();    
}

function recent_articles($atts)
{
    extract(shortcode_atts(array(
        'count' => 1,
    ), $atts));

    // Have to buffer this, since get_post_excerpt echos output
    ob_start();

    echo '<div class="home-entry-container">';
    query_posts(array('post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' , 'showposts' => $count));
    if (have_posts()) :
        while (have_posts()) : the_post();
            wikiwp_child_get_post_excerpt($post, false);
        endwhile;
    endif;
    echo '</div>';

    wp_reset_query();
    return ob_get_clean();
}

// Returns an alphabetical list of content, organized by post type. 
function alphabetical_content() 
{

	$post_types = array('post', 'bug-library-bugs', 'paper', 'sensor', 'page', 'update');

    // Have to buffer this, since get_post_excerpt echos output
    ob_start();

	foreach ($post_types as $post_type) 
	{
	    $args = array(
	                'posts_per_page' => -1,
	                'post_type' => $post_type,
	                'post_status' => 'publish',
	                'order' => 'ASC',
	                'orderby' => 'title'
	            );
	    $posts = get_posts($args);

	    if ($posts)
	    {
	    	switch ($post_type) {
	    		case 'page':
	    			$type_name = 'Pages';
	    			break;
	    		case 'bug-library-bugs':
	    			$type_name = 'Issues';
	    			break;
	    		case 'sensor':
	    			$type_name = 'Sensors';
	    			break;
	    		case 'paper':
	    			$type_name = 'Papers';
	    			break;
	    		case 'update':
	    			$type_name = 'Updates';
	    			break;
	    		default:
	    			$type_name = 'Articles';
	    			break;
	    	}

	    	echo '<h2>'.$type_name.'</h2>';

		    global $post;
		    foreach ($posts as $post) 
		    {
		        setup_postdata($post);
		        wikiwp_child_get_post_excerpt($post, false, false, false);
		    }
	    }

	    wp_reset_query();	    	
	}

    return ob_get_clean();
}

// Lists the current in-stock sensors
function sensors_inventory()
{
    $inventory = array();
    $inventory_html = '';

    $args = array(
        'post_type' => 'sensor',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'caller_get_posts'=> 1
    );

    $q = new WP_Query($args);
 
    if ($q->have_posts()) 
    {
        while ($q->have_posts())
        {
            $q->the_post();

            $header_key = 'header';
            $body_key = 'body';

            $history = get_field('history');
            if (!is_array($history))
            {
                $history = json_decode($history, true);
                $header_key = 'h';
                $body_key = 'b';
            }

            $num_rows = count($history[$body_key]);
            
            for ($i = $num_rows; $i >= 0 ; $i--) 
            {
                $row = $history[$body_key][$i];
                $action = $row[1]['c'];
                
                if ($action == 'Sensor-Exit')
                {
                    break;
                }    
                elseif ($action == 'Sensor-Delivery')
                {
                    $received_from = $row[2]['c'];
                    if (!in_array($received_from, array('Kofi', 'kofi', 'Kofi Amankwah', 'Doug Sinclair')))
                        $received_from = 'Sinclair Interplanetary';

                    $inventory[] = array(
                        'date' => $row[0]['c'],
                        'sensor_name' => get_the_title(),
                        'url' => get_the_permalink(),
                        'intended_use' => get_field('intended_use'),
                        'current_disposition' => get_field('current_disposition'),
                        'received_from' => $received_from,
                        'Received_by' => $row[3]['c'],
                        'notes' => $row[4]['c']
                    );
                }
            }
        }
    }

    if ($inventory)
    {
        $inventory_html .= '<table class="sortable">';
            $inventory_html .= '<thead>';
                $inventory_html .= '<tr>';
                    $inventory_html .= '<th>Date Received</th>';
                    $inventory_html .= '<th>Sensor</th>';
                    $inventory_html .= '<th>Intended Use</th>';
                    $inventory_html .= '<th>Disposition</th>';
                    $inventory_html .= '<th>Received From</th>';
                    $inventory_html .= '<th>Received By</th>';
                    $inventory_html .= '<th>Notes</th>';
                $inventory_html .= '</tr>';
            $inventory_html .= '</thead>';
            $inventory_html .= '<tbody>';
            foreach ($inventory as $item) 
            {
                $inventory_html .= '<tr>';
                    $inventory_html .= '<td>'.$item['date'].'</td>';
                    $inventory_html .= '<td><a href="'.$item['url'].'">'.$item['sensor_name'].'</a></td>';
                    $inventory_html .= '<td>'.$item['intended_use'].'</td>';
                    $inventory_html .= '<td>'.$item['current_disposition'].'</td>';
                    $inventory_html .= '<td>'.$item['received_from'].'</td>';
                    $inventory_html .= '<td>'.$item['received_by'].'</td>';
                    $inventory_html .= '<td>'.$item['notes'].'</td>';
                $inventory_html .= '<tr>';
            }
            $inventory_html .= '</tbody>';
        $inventory_html .= '</table>';
    }
    else
    {
        $inventory_html = '<h4>No sensors are currently at SAIL</h4>';
    }

    return $inventory_html;
    
    wp_reset_query();  // Restore global post data stomped by the_post().    
}

// Sensor arrivals/departures
function sensors_ad()
{
    if (isset($_GET['months_back']) && is_numeric($_GET['months_back']))
    {
        $months_back = floor($_GET['months_back']);

        // Sanitize
        if (!$months_back)
            $months_back = 99999;
        elseif ($months_back < 0)
            $months_back = 1;    
    }
    else
    {
        $months_back = 99999;       // Default is all-time value
    }


    $lim = (new DateTime())->modify("-$months_back months"); 

    $ad = array();
    $ad_html = '';

    $args = array(
        'post_type' => 'sensor',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'caller_get_posts'=> 1
    );

    $q = new WP_Query($args);
 
    if ($q->have_posts()) 
    {
        while ($q->have_posts())
        {
            $q->the_post();

            $header_key = 'header';
            $body_key = 'body';

            $history = get_field('history');
            if (!is_array($history))
            {
                $history = json_decode($history, true);
                $header_key = 'h';
                $body_key = 'b';
            }
            
            $num_rows = count($history[$body_key]);
            
            for ($i = $num_rows; $i >= 0 ; $i--) 
            {
                $row = $history[$body_key][$i];
                $action = $row[1]['c'];
                
                if ($action == 'Sensor-Delivery' || $action == 'Sensor-Exit')
                {
                    $action_date = $row[0]['c'];
                    // var_dump($action_date);

                    try {
                        $action_date_obj = new DateTime($action_date);
                    } catch (Exception $e) {
                        continue;                        
                    }

                    // Don't show if row is too old. Show if there is no date data however
                    if ($action_date_obj < $lim)
                        continue;

                    $performed_by = $row[2]['c'];
                    if (!in_array($performed_by, array('Kofi', 'kofi', 'Kofi Amankwah', 'Doug Sinclair')))
                        $performed_by = 'Sinclair Interplanetary';

                    $ad[] = array(
                        'date' => $action_date,
                        'sensor_name' => get_the_title(),
                        'url' => get_the_permalink(),
                        'action' => str_replace('Sensor-', '', $action),
                        'performed_by' => $performed_by,
                        'entered_by' => $row[3]['c'],
                        'notes' => $row[4]['c']
                    );
                }
            }
        }
    }

    if ($ad)
    {
        usort($ad, 'sensors_ad_sort');

        $ad_html .= '<table class="sortable">';
            $ad_html .= '<thead>';
                $ad_html .= '<tr>';
                    $ad_html .= '<th>Date</th>';
                    $ad_html .= '<th>Sensor</th>';
                    $ad_html .= '<th>Action</th>';
                    $ad_html .= '<th>Performed By</th>';
                    $ad_html .= '<th>Entered By</th>';
                    $ad_html .= '<th>Notes</th>';
                $ad_html .= '</tr>';
            $ad_html .= '</thead>';
            $ad_html .= '<tbody>';
            foreach ($ad as $item) 
            {
                $ad_html .= '<tr>';
                    $ad_html .= '<td>'.$item['date'].'</td>';
                    $ad_html .= '<td><a href="'.$item['url'].'">'.$item['sensor_name'].'</a></td>';
                    $ad_html .= '<td>'.$item['action'].'</td>';
                    $ad_html .= '<td>'.$item['performed_by'].'</td>';
                    $ad_html .= '<td>'.$item['entered_by'].'</td>';
                    $ad_html .= '<td>'.$item['notes'].'</td>';
                $ad_html .= '<tr>';
            }
            $ad_html .= '</tbody>';
        $ad_html .= '</table>';
    }
    else
    {
        $ad_html = '<h4>No arrivals/departures recorded for that time interval.</h4>';
    }

//    var_dump($ad);

    return $ad_html;
}

// Sort by date (asc), action (asc), name (asc)
function sensors_ad_sort($a, $b)
{
    $t1 = strtotime($a['date']);
    $t2 = strtotime($b['date']);

    if ($t1 === $t2)
    {
        $a1 = substr($a['action'], 0, 1);
        $a2 = substr($b['action'], 0, 1);

        if ($a1 === $a2)
        {
            $s1 = $a['sensor_name'];
            $s2 = $b['sensor_name'];

            if ($s1 === $s2)
            {
                return 0;               
            }
            else
            {
                if ($s1 < $s2)
                    return -1;
                else
                    return 1;                
            }

        }
        else
        {
            if ($a1 < $a2)
                return -1;
            else
                return 1;
        }
    }
    else
    {
        if ($t1 < $t2)
            return -1;
        else
            return 1;
    }
}

function sensors_ad_range()
{
    if (isset($_GET['months_back']))
        $months_back = $_GET['months_back'];
    else
        $months_back = null;

    $range = '<select class="ad-range default-select-style" onChange="window.location.href=this.value">
                    <option '.($months_back == 1 ? 'selected' : '').' value="?months_back=1">last month</option>
                    <option '.($months_back == 3 ? 'selected' : '').' value="?months_back=3">last 3 months</option>
                    <option '.($months_back == 12 ? 'selected' : '').' value="?months_back=12">last year</option>
                    <option '.($months_back == 0 ? 'selected' : '').' value="?months_back=0">all time</option>
              </select>';

    return $range;
}

function sensors_show()
{
    $loop = new WP_Query( array( 'post_type' => 'sensor', 'posts_per_page' => -1 ) ); 

    $html = '';
    while ( $loop->have_posts() ) : $loop->the_post(); 
        $html .= '<div class="entryTypePostExcerpt">';
            $html .= '<div class="entryContainer">';
                $html .= '<header class="entryHeader">';
                    $html .= '<h2 class="entryTitle">';
                        $html .= '<a href="'.get_permalink().'">'.get_the_title().'</a>';
                    $htnk .= '</h2>'; 
                $html .= '</header>';
                $html .= '<div class="postinfo postinfo-excerpt">';
                    $html .= '<span>Modified: '.get_the_modified_date('g:i a').' - '.get_the_modified_date('F j, Y').'</span>';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';
    endwhile;

    return $html;
}

function get_paper_rows()
{
// Get extra rows from 'papers' posts
    $query_args = array(
        'post_type'      => 'paper',
        'post_status'    => 'publish',
        'meta_key'       => 'target_date',
        'orderby'        => 'meta_value_num',
        'order'          => 'asc',
        'posts_per_page' => -1,
    );
    $posts = new WP_Query( $query_args );

    $paper_rows = array();
    if ( $posts->have_posts() ) 
    {
        while ( $posts->have_posts() ) 
        {
            $posts->the_post();

            if (get_field('status') == 'Published')
                continue;

            $paper = array();

            $paper['title'] = '<a href="'.get_permalink().'">'.get_the_title().'</a>';
            $paper['authors'] = coauthors_posts_links(null, null, null, null, false);
            $paper['journal'] = get_field('target_journal');
            $paper['target_date'] = date("F d, Y", strtotime(get_field('target_date')));
            $paper['status'] = get_field('status');
            $paper['completeness'] = get_field('completeness');

            // Try to parse a "required" section
            $content = get_the_content();
            preg_match('/<h1>Required<\/h1>(.+)<h1>/s', $content, $needs);
//            var_dump($needs);
            if (isset($needs[1]) && $needs[1])
            {
                $needs[1] = preg_replace('~\r\n?~', "\n", $needs[1]);
                $needs[1] = str_replace("\n\n", '<br />', $needs[1]);
                $paper['needs'] = $needs[1];                
            }
            else
            {
                $paper['needs'] = '<a href="'.get_permalink().'">See page</a>';
            }

            $paper_rows[] = $paper;
        }
    }
    wp_reset_query();

    return $paper_rows;
}

function get_publications_data()
{
    $pub_data = get_field(PUB_TABLE_SLUG, PUB_PAGE_ID);
    $paper_rows = get_paper_rows();

    foreach ($paper_rows as $row) 
    {
        $formatted_row = array();
        foreach ($row as $cell) 
        {
            $formatted_row[] = array('c' => $cell);
        }

        $pub_data['body'][] = $formatted_row;
    }
 
    // echo '<pre>';
    // var_dump($pub_data);
    // echo '</pre>';
    return $pub_data;

}

function upcoming_events($months_ahead)
{
    $events = array();

    // Get all dates data
    $tables = array(
        'conferences' => array(
            'name' => 'Conference',
            'data' => get_field('upcoming_dates', DATES_PAGE_ID),
            'date_cols' => array(2, 3, 4),
            'date_col_names' => array('conference starts', 'abstracts due', 'papers due')
        ),
        'publications' => array(
            'name' => 'Publication',
            'data' => get_publications_data(),
            'date_cols' => array(3),
            'date_col_names' => array('target date')
        ),
        'misc' => array(
            'name' => 'Misc',
            'data' => get_field('miscellaneous_dates', DATES_PAGE_ID),
            'date_cols' => array(1),
            'date_col_names' => array('')
        )
    );

    $cur = new DateTime();
    $lim = (new DateTime())->modify("+$months_ahead months");

    foreach ($tables as $table_key => $table)
    {
        foreach($table['data']['body'] as $tr)
        {
            foreach ($table['date_cols'] as $el_idx => $td_idx) 
            {
                $raw = $tr[$td_idx]['c'];
                $date = extract_date($raw);

                if ($date && $date > $cur && $date < $lim)
                {
                    $event = array(
                    	'from_table' => $table_key,
                        'type' => $table['date_col_names'][$el_idx],
                        'date' => $date->format('d M Y'),
                        'event' => $tr[0]['c'] 
                    );

                    $events[] = $event;
                }
            }
        }
    }

    function date_compare($a, $b)
    {
        $t1 = strtotime($a['date']);
        $t2 = strtotime($b['date']);
        return $t1 - $t2;
    }    
    usort($events, 'date_compare');

    return $events;
}

function extract_date($raw)
{
    $date_time = null;

    // Try just getting date
    try 
    {
        $date_time = new DateTime($raw);    
    } 
    catch (Exception $e) 
    {
        // Check for a %START% - %FINISH% situation. Take just the start date
        try 
        {
            $dash_idx = strpos($raw, '-');
            if ($dash_idx)
                $date_time = new DateTime(substr($raw, 0, $dash_idx-1));   // offset '-'        
        } 
        catch (Exception $e) 
        {
            // OK...try the start date
            try 
            {
//                if ($dash_idx)
  //                  $date_time = new DateTime(substr($raw, $dash_idx+1));   // offset '-' 
            } 
            catch (Exception $e) 
            {
                
            }
        }    
    }

    return $date_time;
}

function upcoming_table()
{
    $events = upcoming_events(2);

    $events_html = '';
    if ($events)
    {
        $events_html .= '<table>';
            $events_html .= '<thead>';
                $events_html .= '<tr>';
                    $events_html .= '<th>Date</th>';
                    $events_html .= '<th>Event</th>';
                $events_html .= '</tr>';
            $events_html .= '</thead>';
            $events_html .= '<tbody>';
            foreach ($events as $event) 
            {
                $events_html .= '<tr>';
                    $events_html .= '<td>'.$event['date'].'</td>';
                    if ($event['type'])
                    {
//                    	var_dump($event['from_table']);
                    	if ($event['from_table'] == 'publications')
	                        $events_html .= '<td>Paper: '.$event['event'].' ('.$event['type'].')</td>';
                		else
	                        $events_html .= '<td>'.$event['event'].' ('.$event['type'].')</td>';
                    }
                    else
                        $events_html .= '<td>'.$event['event'].'</td>';
                $events_html .= '</tr>';
            }        
            $events_html .= '</tbody>';
        $events_html .= '</table>';
    }
    else
    {
        $events_html .= '<p>No events on the horizon.</p>';
    }

    return $events_html;
}


function updates_dropdowns()
{
    if (isset($_GET['author']))
        $filter_author = $_GET['author'];
    else
        $filter_author = null;

    $authors = get_users();

    $link = strtok($_SERVER["REQUEST_URI"], '?');

    $filter = '<select class="updates-authors default-select-style" onChange="window.location.href=this.value">';

    $filter .= '<option '.($filter_author ? '' : 'selected').' value="'.$link.'">-- All Authors -- </option>';
    foreach($authors as $author)
    {
        if (author_has_posts($author->ID, 'update'))
            $filter .= '<option '.($author->user_login == $filter_author ? 'selected' : '').' value="?author='.$author->user_login.'">'.$author->display_name.'</option>';
    }
    $filter .= '</select>';

    return $filter;
}

function author_has_posts($user_id, $post_type)
{
    $result = new WP_Query(array(
        'author' => $user_id,
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => 1,
    ));

    return (count($result->posts) != 0);
}

function updates_show()
{
    if (isset($_GET['author']))
        $filter_author = $_GET['author'];
    else
        $filter_author = null;

    $loop = new WP_Query( array( 'post_type' => 'update', 'posts_per_page' => -1, 'author_name' => $filter_author ) ); 
    $html = '';
    if (!$loop->have_posts())
    {
       $html .= '<article class="entry">';
            $html .= '<div class="entryContainer">';
                $html .= '<div class="entryContent">';
                    $html .= '<p>No updates found.</p>';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</article>';
    }
    else
    {
        while ( $loop->have_posts() ) : $loop->the_post(); 
            $html .= '<div class="entryTypePostExcerpt">';
                $html .= '<div class="entryContainer">';
                    $html .= '<header class="entryHeader">';
                        $html .= '<h2 class="entryTitle">';
                            $html .= '<a href="'.get_permalink().'">'.get_the_title().'</a>';
                        $htnk .= '</h2>'; 
                    $html .= '</header>';
                    $html .= '<div class="postinfo postinfo-excerpt">';
                        $html .= '<span>'.coauthors_posts_links(null, null, null, null, false).' | Modified: '.get_the_modified_date('g:i a').' - '.get_the_modified_date('F j, Y').'</span>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
        endwhile;        
    }

    return $html;
}

function register_shortcodes() 
{
   add_shortcode(ALL_PAPERS_SHORTCODE, 'all_papers');
   add_shortcode(DATES_TABLE_SHORTCODE, 'dates_table');
   add_shortcode(PUB_TABLE_SHORTCODE, 'pub_table');
   add_shortcode(MISC_DATES_TABLE_SHORTCODE, 'misc_dates_table');
   add_shortcode(RECENT_ARTICLES_SHORTCODE, 'recent_articles');
   add_shortcode(ALPHABETICAL_CONTENT_SHORTCODE, 'alphabetical_content');
   add_shortcode(SENSORS_INVENTORY_SHORTCODE, 'sensors_inventory');
   add_shortcode(SENSORS_AD_SHORTCODE, 'sensors_ad');
   add_shortcode(SENSORS_AD_RANGE_SHORTCODE, 'sensors_ad_range');
   add_shortcode(SENSORS_SHOW_SHORTCODE, 'sensors_show');
   add_shortcode(UPCOMING_TABLE_SHORTCODE, 'upcoming_table');
   add_shortcode(UPDATES_DROPDOWNS_SHORTCODE, 'updates_dropdowns');
   add_shortcode(UPDATES_SHOW_SHORTCODE, 'updates_show');
}
add_action( 'init', 'register_shortcodes');


/* SAIL_POST_CONNECTIONS
   ===================== */

function setup_post_connection() {

    p2p_register_connection_type( array(
        'name' => 'paper_posts',
        'from' => 'paper',
        'to' => 'post',
        'reciprocal' => false,
        'can_create_post' => false,
        'title' => array(
            'to' => 'Related Papers',
            'from' => 'Related Articles'
        ),
        'from_labels' => array(
            'singular_name' => 'Paper',
            'search_items' => 'Search papers',
            'not_found' => 'No papers',
            'create' => 'Add Paper',
        ),
        'to_labels' => array(
            'singular_name' => 'Article',
            'search_items' => 'Search articles',
            'not_found' => 'No articles',
            'create' => 'Add Article',
        ),
    ) );


    p2p_register_connection_type( array(
        'name' => 'post_parents',
        'from' => 'post',
        'to' => 'post',
        'reciprocal' => false,
        'can_create_post' => false,
        'title' => array(
            'from' => 'Parents',
            'to' => 'Children'
        ),
        // to and from lables are backwards from the titles (why?)
        'to_labels' => array(
            'singular_name' => 'Parent',
            'search_items' => 'Search articles',
            'not_found' => 'No articles',
            'create' => 'Add Parent',
        ),
        'from_labels' => array(
            'singular_name' => 'Child',
            'search_items' => 'Search articles',
            'not_found' => 'No articles',
            'create' => 'Add Child',
        ),
    ) );
}
add_action( 'p2p_init', 'setup_post_connection' );


/* SAIL_ADMIN
   ========== */

/**
 * Fills the default content for post types if it is not empty.
 *
 * @param string $content
 * @param object $post
 * @return string
 */
function preset_content( $content, $post )
{
    switch ($post->post_type) 
    {
        case 'update':
            return '<h1>Things Done</h1>
                &nbsp;
                <h1>Questions and Points</h1>
                &nbsp;
                <h1>What\'s Next?</h1>
                &nbsp;
            ';
            break;
        case 'paper':
            return '<h1>Abstract</h1>
                &nbsp;
                <h1>Required</h1>
                &nbsp;
                <h1>Timeline</h1>
                &nbsp;
            ';
            break;
        default:
            return $content;        
    }

}
add_filter( 'default_content', 'preset_content', 10, 2 );


/**
 *
 * Show custom post types in dashboard activity widget
 *
 */

// unregister the default activity widget
add_action('wp_dashboard_setup', 'remove_dashboard_widgets' );
function remove_dashboard_widgets() {

    global $wp_meta_boxes;
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);

}

// register your custom activity widget
add_action('wp_dashboard_setup', 'add_custom_dashboard_activity' );
function add_custom_dashboard_activity() {
    wp_add_dashboard_widget('custom_dashboard_activity', 'Activities', 'custom_wp_dashboard_site_activity');
}

// the new function based on wp_dashboard_recent_posts (in wp-admin/includes/dashboard.php)
function wp_dashboard_recent_post_types( $args ) {

/* Chenged from here */

    if ( ! $args['post_type'] ) {
        $args['post_type'] = 'any';
    }

    $query_args = array(
        'post_type'      => $args['post_type'],

/* to here */

        'post_status'    => $args['status'],
        'orderby'        => 'date',
        'order'          => $args['order'],
        'posts_per_page' => intval( $args['max'] ),
        'no_found_rows'  => true,
        'cache_results'  => false
    );
    $posts = new WP_Query( $query_args );

    if ( $posts->have_posts() ) {

        echo '<div id="' . $args['id'] . '" class="activity-block">';

        // if ( $posts->post_count > $args['display'] ) {
        //     echo '<small class="show-more hide-if-no-js"><a href="#">' . sprintf( __( 'See %s more…'), $posts->post_count - intval( $args['display'] ) ) . '</a></small>';
        // }

        echo '<h4>' . $args['title'] . '</h4>';

        echo '<ul>';

        $i = 0;
        $today    = date( 'Y-m-d', current_time( 'timestamp' ) );
        $tomorrow = date( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );

        while ( $posts->have_posts() ) {
            $posts->the_post();

            $time = get_the_time( 'U' );
            if ( date( 'Y-m-d', $time ) == $today ) {
                $relative = __( 'Today' );
            } elseif ( date( 'Y-m-d', $time ) == $tomorrow ) {
                $relative = __( 'Tomorrow' );
            } else {
                /* translators: date and time format for recent posts on the dashboard, see http://php.net/date */
                $relative = date_i18n( __( 'M jS' ), $time );
            }


            $post_type = get_post_type();
            $pre_text = '';
            $post_text = '';


            if ($post_type == 'bug-library-bugs')
            {
                $pre_text = 'Issue: ';
            }
            elseif ($post_type == 'sensor')
            {
                $pre_text = 'Sensor: ';
            }
            elseif ($post_type == 'paper')
            {
                $pre_text = 'Paper: ';
            }
            elseif ($post_type == 'update')
            {
                $pre_text = 'Update: ';
                $post_text = '<br><small>('.get_the_author().')</small>';
            }

            $text = sprintf(
                /* translators: 1: relative date, 2: time, 4: post title */
                __( '<span>%1$s, %2$s</span> <a href="%3$s">%4$s%5$s%6$s </a>' ),
                $relative,
                get_the_time(),
                get_edit_post_link(),
                $pre_text,
                _draft_or_post_title(),
                $post_text
            );

            $hidden = $i >= $args['display'] ? ' class="hidden"' : '';
            echo "<li{$hidden}>$text</li>";
            $i++;
        }

        echo '</ul>';
        echo '</div>';

    } else {
        return false;
    }

    wp_reset_postdata();

    return true;
}

// The replacement widget
function custom_wp_dashboard_site_activity() {

    echo '<div id="activity-widget">';
/*
    $future_posts = wp_dashboard_recent_post_types( array(
        'post_type'  => 'any',
        'display' => 3,
        'max'     => 7,
        'status'  => 'future',
        'order'   => 'ASC',
        'title'   => __( 'Publishing Soon' ),
        'id'      => 'future-posts',
    ) );
*/
    $recent_posts = wp_dashboard_recent_post_types( array(
        'post_type'  => 'any',
        'display' => 3,
        'max'     => 5,
        'status'  => 'publish',
        'order'   => 'DESC',
        'title'   => __( 'Recently Published' ),
        'id'      => 'published-posts',
    ) );

    $recent_comments = wp_dashboard_recent_comments(5);

    if ( !$future_posts && !$recent_posts && !$recent_comments ) {
        echo '<div class="no-activity">';
        echo '<p class="smiley"></p>';
        echo '<p>' . __( 'No activity yet!' ) . '</p>';
        echo '</div>';
    }

    echo '</div>';
}


// Make all edit screens follow format set by given user
//if (is_admin()) 
//{
    if (class_exists('\GlobalMetaBoxOrder\Config'))
    {
        // The path to the configuation is rather long, so let's
        // make us a shorthand.
        class_alias('\GlobalMetaBoxOrder\Config', 'MetaBoxConfig');

        MetaBoxConfig::$getBlueprintUserId = function () 
        {                
            // Whoever becomes the next wiki admin, feel free to change this to your own username
            $user = get_user_by('slug', 'andrei');
            return $user ? $user->ID : false; 
        };        
    }
    else
    {
        echo 'Can\'t find metabox configuration class.';
    }
//}

function switch_on_comments_automatically(){
    // global $wpdb;
    // $wpdb->query( $wpdb->prepare("UPDATE %s SET comment_status = 'open'", $wpdb->posts)); 
    // Switch comments on automatically
//    add_post_type_support( 'bug-lirary-bugs', array( 'comments' ) );
}
add_action( 'init', 'switch_on_comments_automatically');

/**
* Replaces "Post" in the update messages for custom post types on the "Edit"post screen.
* For example, for a "Product" custom post type, "Post updated. View Post." becomes "Product updated. View Product".
*
* @param array $messages The default WordPress messages.
*/

function custom_update_messages( $messages ) 
{
    global $post, $post_ID;

    $post_types = get_post_types( array( 'show_ui' => true, '_builtin' => false), 'objects' );
    $post_types_builtin = get_post_types( array( 'show_ui' => true, '_builtin' => true), 'objects' );
    $post_types = array_merge($post_types, $post_types_builtin);

    foreach( $post_types as $post_type => $post_object ) 
    {
        $messages[$post_type] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf( __( '%s updated. <a href="%s">View %s</a>' ), $post_object->labels->singular_name, esc_url( get_permalink( $post_ID ) ), $post_object->labels->singular_name ),
            2  => __( 'Custom field updated.' ),
            3  => __( 'Custom field deleted.' ),
            4  => sprintf( __( '%s updated.' ), $post_object->labels->singular_name ),
            5  => isset( $_GET['revision']) ? sprintf( __( '%s restored to revision from %s' ), $post_object->labels->singular_name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( __( '%s published. <a href="%s">View %s</a>' ), $post_object->labels->singular_name, esc_url( get_permalink( $post_ID ) ), $post_object->labels->singular_name ),
            7  => sprintf( __( '%s saved.' ), $post_object->labels->singular_name ),
            8  => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>'), $post_object->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $post_object->labels->singular_name ),
            9  => sprintf( __( '%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>'), $post_object->labels->singular_name, date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), $post_object->labels->singular_name ),
            10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>'), $post_object->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $post_object->labels->singular_name ),
            );
    }

    return $messages;
}
add_filter( 'post_updated_messages', 'custom_update_messages' );

// Updates messages for custom post types ()
function bulk_custom_updated_messages( $bulk_messages, $bulk_counts ) 
{
    global $post, $post_ID;

    $post_types = get_post_types( array( 'show_ui' => true, '_builtin' => false), 'objects' );
    $post_types_builtin = get_post_types( array( 'show_ui' => true, '_builtin' => true), 'objects' );
    $post_types = array_merge($post_types, $post_types_builtin);

    foreach( $post_types as $post_type => $post_object ) 
    {
        $bulk_messages[$post_type] = array(
            'updated'   => _n( '%s '.lcfirst($post_object->labels->singular_name).' updated.', '%s '.lcfirst($post_object->labels->singular_name).'s updated.', $bulk_counts['updated'] ),
            'locked'    => _n( '%s '.lcfirst($post_object->labels->singular_name).' not updated, somebody is editing it.', '%s '.lcfirst($post_object->labels->singular_name).'s not updated, somebody is editing them.', $bulk_counts['locked'] ),
            'deleted'   => _n( '%s '.lcfirst($post_object->labels->singular_name).' permanently deleted.', '%s '.lcfirst($post_object->labels->singular_name).'s permanently deleted.', $bulk_counts['deleted'] ),
            'trashed'   => _n( '%s '.lcfirst($post_object->labels->singular_name).' moved to the Trash.', '%s '.lcfirst($post_object->labels->singular_name).'s moved to the Trash.', $bulk_counts['trashed'] ),
            'untrashed' => _n( '%s '.lcfirst($post_object->labels->singular_name).' restored from the Trash.', '%s '.lcfirst($post_object->labels->singular_name).'s restored from the Trash.', $bulk_counts['untrashed'] ),
        );
    }
    return $bulk_messages;
}
add_filter( 'bulk_post_updated_messages', 'bulk_custom_updated_messages', 10, 2);

function remove_admin_bar_links() {
    global $wp_admin_bar;
    global $wp_query;

    if ($wp_query->is_page)
        $page_type = 'page';
    elseif ($wp_query->is_single)
        $page_type = 'single';
    elseif ($wp_query->is_category)
        $page_type = 'category';
    elseif ($wp_query->is_tag)
        $page_type = 'tag';
    else
        $page_type = null;

    $roles = wp_get_current_user()->roles;  //$roles is an array
    $wp_admin_bar->remove_menu('wp-logo');
    $wp_admin_bar->remove_menu('updates');
    $wp_admin_bar->remove_menu('customize');
    $wp_admin_bar->remove_menu('my-account');


    // Hide "Edit Page/Taxonomy" from non admins. Let them edit other post types though
    if (in_array($page_type, array('category', 'tag')) && $roles[0] != 'administrator')
    {
        $wp_admin_bar->remove_menu('edit');
    }
}
add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links' );

// Rewrites tag taxonomy to display all tags as checklist
function wd_hierarchical_tags_register() {

  // Maintain the built-in rewrite functionality of WordPress tags

  global $wp_rewrite;

  $rewrite =  array(
    'hierarchical'              => false, // Maintains tag permalink structure
    'slug'                      => get_option('tag_base') ? get_option('tag_base') : 'tag',
    'with_front'                => ! get_option('tag_base') || $wp_rewrite->using_index_permalinks(),
    'ep_mask'                   => EP_TAGS,
  );

  // Redefine tag labels (or leave them the same)

  $labels = array(
    'name'                       => _x( 'Tags', 'Taxonomy General Name', 'hierarchical_tags' ),
    'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'hierarchical_tags' ),
    'menu_name'                  => __( 'Tags', 'hierarchical_tags' ),
    'all_items'                  => __( 'All Tags', 'hierarchical_tags' ),
    'parent_item'                => __( 'Parent Tag', 'hierarchical_tags' ),
    'parent_item_colon'          => __( 'Parent Tag:', 'hierarchical_tags' ),
    'new_item_name'              => __( 'New Tag Name', 'hierarchical_tags' ),
    'add_new_item'               => __( 'Add New Tag', 'hierarchical_tags' ),
    'edit_item'                  => __( 'Edit Tag', 'hierarchical_tags' ),
    'update_item'                => __( 'Update Tag', 'hierarchical_tags' ),
    'view_item'                  => __( 'View Tag', 'hierarchical_tags' ),
    'separate_items_with_commas' => __( 'Separate tags with commas', 'hierarchical_tags' ),
    'add_or_remove_items'        => __( 'Add or remove tags', 'hierarchical_tags' ),
    'choose_from_most_used'      => __( 'Choose from the most used', 'hierarchical_tags' ),
    'popular_items'              => null,
    'search_items'               => __( 'Search Tags', 'hierarchical_tags' ),
    'not_found'                  => __( 'Not Found', 'hierarchical_tags' ),
  );

  // Override structure of built-in WordPress tags

  register_taxonomy( 'post_tag', 'post', array(
    'hierarchical'              => true, // Was false, now set to true
    'query_var'                 => 'tag',
    'labels'                    => $labels,
    'rewrite'                   => $rewrite,
    'public'                    => true,
    'show_ui'                   => true,
    'show_admin_column'         => true,
    '_builtin'                  => true,
  ) );

}
add_action('init', 'wd_hierarchical_tags_register');

function sail_attachments( $attachments )
{
  $args = array(
    // title of the meta box (string)
    'label'         => 'Attachments',
    // all post types to utilize (string|array)
    'post_type'     => array( 'post', 'page', 'bug', 'update' ),
    // allowed file type(s) (array) (image|video|text|audio|application)
    'filetype'      => null,  // no filetype limit
    // include a note within the meta box (string)
    'note'          => 'Files attached here will show up in a list after the content.',
    // text for 'Attach' button in meta box (string)
    'button_text'   => __( 'Attach Files', 'attachments' ),
    // text for modal 'Attach' button (string)
    'modal_text'    => __( 'Attach', 'attachments' ),
    /**
     * Fields for the instance are stored in an array. Each field consists of
     * an array with three keys: name, type, label.
     *
     * name  - (string) The field name used. No special characters.
     * type  - (string) The registered field type.
     *                  Fields available: text, textarea
     * label - (string) The label displayed for the field.
     */
    'fields'        => array(
      array(
        'name'  => 'comments',                        // unique field name
        'type'  => 'textarea',                       // registered field type
        'label' => __( 'Comments', 'attachments' ),   // label to display
      )
    )
  );
  $attachments->register( 'sail_attachments', $args ); // unique instance name
}
add_action( 'attachments_register', 'sail_attachments' );


function allow_forbidden_mimes($mime_types){
    $mime_types['fig'] = 'application/x-cabri';
    return $mime_types;
}
add_filter('upload_mimes', 'allow_forbidden_mimes', 1, 1);

function load_admin_styles() 
{
    echo '<link rel="stylesheet" type="text/css" href="'.get_stylesheet_directory_uri().'/style.css">';
}
add_action('admin_head', 'load_admin_styles');


/* SAIL_MISC_UTILITY
   ================= */

// Get all nth-position elements or $n-key-elements in all subarrays of a 2D array. Note, $arr must be an array of arrays. 
function combine_subarrays($arr, $n = 0) {
    $combined = array();

    foreach($arr as $sub) {
        array_push($combined, $sub[$n]);
    }

    return $combined;
}


/* SAIL_MATLAB INTERFACE
   ===================== */

// Handles all requests from MATLAB
function matlab_handler()
{
    $is_write = isset($_POST[FROM_MATLAB_KEY]);
    $is_read = isset($_GET[FROM_MATLAB_KEY]);
    
    if ($is_read || $is_write)
    {
        // Needed to prevent "output sent before header" messages
        error_reporting(E_ERROR);

        $GLOBALS['IS_MATLAB_REQUEST'] = true;

        if ($is_read)
            $func = 'matlab_read_'.$_GET['read_type'];
        else
            $func = 'matlab_write_'.$_POST['write_type'];

        $func();
    }
    else
    {
        $GLOBALS['IS_MATLAB_REQUEST'] = false;
    }
}

// Returns a sensor post type and its meta given its name. Does some filtering on which meta fields are taken.
function matlab_read_sensor()
{
    $sensor_post = get_page_by_title($_GET['sensor_name'], OBJECT, 'sensor');
    
    if (!$sensor_post)
        return;

    $meta = get_metadata('post', $sensor_post->ID);
    foreach ($meta as $key => &$item) 
    {
        if (substr($key, 0, 1) === "_")
        {
            unset($meta[$key]);                             // Keys starting with '_' can't be fields in MATLAB (also they don't contain useful info for us)
        }
        elseif ($decoded_item = json_decode($item[0]))
            $item[0] = $decoded_item;
    }
    unset($item);
   $sensor_post->meta = $meta;

//    print_r($sensor_post->meta['history'][0]);    
    echo json_encode($sensor_post);

//   print_r($sensor_post);
}

// Makes/updates sensor on wiki
function matlab_write_sensor() 
{
    // echo 'fiddlesticks';
    // return;

    $write_data =  json_decode(stripslashes($_POST['write_data']), true);
    $write_data = $write_data['write_data'];

    foreach ($write_data['meta'] as &$item) {
        $decoded_item = json_decode($item, true);

        if ($decoded_item)
        {
            // For all assoc arrays: move all children of the 'root' key to the root of the array
            if (is_array($decoded_item) && isset($decoded_item['root']))
            {
                $item = json_encode($decoded_item['root']);
            }                
        }
    }
    unset($item);

    // Make post data
    $user = get_user_by('login', MATLAB_UPLOAD_USERNAME);   
    $post_data = array(
        'post_type' => 'sensor',
        'post_title' => $write_data['post_title'],
        'post_author' => $user->ID, 
        'post_status' => 'publish',
        'meta_input' => $write_data['meta']
    );

    $old_post = get_page_by_title($write_data['post_title'], OBJECT, 'sensor');
    if ($old_post)
    {
        if ($write_data['is_update'])
        {
            $post_data['ID'] = $old_post->ID;
            $post_id = wp_update_post($post_data);
        }
        else
        {
            echo $write_data['post_title'].' already has a wiki page. To update it, set is_update to true.';
            return;
        }
    }
    else
    {
        if ($write_data['is_update'])
        {
            echo $write_data['post_title'].' is not on the wiki. Confirm the sensor name is correct.';
            return;
        }
        else
        {
            $post_id = wp_insert_post($post_data);             
        }
    }

    if ($post_id)
        echo '1';
}


// Adds an attachment to an article
function matlab_write_attachment()
{
    $write_data = json_decode(stripslashes($_POST['write_data']), true);
    $write_data = $write_data['write_data'];

    // Confirm post
    $post = get_page_by_title($write_data['article_title'], OBJECT, 'post');
    if (!$post)
    {
        echo 'No article by that name exists';
        return;
    }

    if ($write_data['attachment_ext'] == '.png')
    {
        $dec = base64_decode($write_data['encoded_attachment']);

        // Decode image
        $encoded_img = $write_data['encoded_attachment'];
        $encoded_img = str_replace('data:image/png;base64,', '', $encoded_img);
        $encoded_img = str_replace(' ', '+', $encoded_img);
        $bits = base64_decode($encoded_img);
    }
    else
    {
        echo 'Only images allowed (for now)';
        $bits = base64_decode($write_data['encoded_attachment']);
        return;
    }

    $attachment_title = $write_data['title'];
    
    if (isset($write_data['filename']))
        $attachment_filename = $write_data['filename'].$write_data['attachment_ext'];
    else
        $attachment_filename = strtolower(preg_replace('/[^a-zA-Z\d\s]/', '_', $attachment_title)).$write_data['attachment_ext'];

    $upload_file = wp_upload_bits($attachment_filename, null, $bits);

    // if ()

    // Add the image to the Media Library
    $wp_filetype = wp_check_filetype($attachment_filename, null );

    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_parent' => $parent_post_id,
        'post_title' => $attachment_title,
        'post_content' => $write_data['description'],
        'post_excerpt' => $write_data['caption'] ? $write_data['caption'] : $write_data['description'],
        'post_status' => 'inherit',
        'post_author' => get_user_by('login', MATLAB_UPLOAD_USERNAME)->ID
    );

    $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );
    if (!is_wp_error($attachment_id)) 
    {
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
        wp_update_attachment_metadata( $attachment_id,  $attachment_data );
    }


    // Get the article's meta
    $meta = get_post_meta($post->ID, 'attachments');

    $meta_attachment = array(
        'id' => $attachment_id,
        'fields' => array('comments' => $write_data['description'])
    );

    // Get an attachments array (make one if we don't already have one for the post) and add this to the attachments
    if ($meta)
    {
        $meta_attachments = json_decode($meta[0], true);
        $meta_attachments['sail_attachments'][] = $meta_attachment;
    }
    else
    {
        $meta_attachments = array('sail_attachments' => array($meta_attachment));
    }

    // print_r(array(json_encode($meta_attachments)));
    // print_r($meta);

    $meta_id = update_post_meta($post->ID, 'attachments', json_encode($meta_attachments));


    if ($upload_file && $attachment_id && $meta_id)
        echo '1';
}

add_action( 'init', 'matlab_handler');


/* SAIL_MISC
   ========= */

// Allows comments on issues. This can be extened to allow it for other custom post types.
function activate_update_comment_status() {
    global $wpdb;
    $wpdb->update( 
        $wpdb->posts, 
        array( 
            'comment_status' => 'open'
        ), 
        array(
            'post_type' => 'bug-library-bugs'
        ), 
        array( 
            '%s'
        ), 
        array(
            '%s'
        ) 
    );
}
add_action( 'init', 'activate_update_comment_status');