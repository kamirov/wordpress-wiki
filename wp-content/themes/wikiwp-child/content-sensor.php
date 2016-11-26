<article class="entry entryTypePost">
    <header class="entryHeader">
        <?
            if ( have_posts() ) : the_post();
        ?>
        <!-- This is dirty. We manually put these here so that the Sensors page appears as a parent to the sensor custom post type. There is a better way, probably. -->
        <div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
            <!-- Breadcrumb NavXT 5.5.1 -->
            <span property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" title="Go to SAIL Wiki." href="<?= site_url() ?>" class="home"><span property="name">SAIL Wiki</span></a>
                <meta property="position" content="1">
            </span>
            &gt; 
            <span property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" title="Go to Sensors." href="<?= site_url('sensors') ?>" class="home"><span property="name">Sensors</span></a>
                <meta property="position" content="2">
            </span>
            &gt; 
            <span property="itemListElement" typeof="ListItem">
                <span property="name"><?= the_title() ?></span>
                <meta property="position" content="3">
            </span>
        </div>
        <h1 class="entryTitle">
            <?php
            echo '<h1>'.get_the_title().' Star Tracker Testing Record</h1>';
            endif;
            ?>
        </h1>
    </header>

    <div class="entryContent">
    <?php
        $tables = array(
            'Sensor History' => get_field('history'),
            'Focus Notes' => get_field('focus_notes'),
            'Calibration Notes' => get_field('calib_notes'),
            'Environmental Testing' => get_field('env_testing'),
            'General Notes and Discussion' => get_field('gen_notes')
        );
        $header_key = 'header';
        $body_key = 'body';

        // JSON encode if needed (note, when saved in WP, fields returned from get_field as arrays. When uploaded by MATLAB, returned as JSON). Note, header and body keys are different for JSON encoded tables (this is silly, but without editing the plugin files, this is the best way to check)
        foreach ($tables as $key => $table) 
        {
            if (!is_array($table))
            {
                $header_key = 'h';
                $body_key = 'b';
                $tables[$key] = json_decode($table, true);
            }
        }

        echo '<h1>Sensor Information</h1>';
        echo '<table>';
            echo '<tr>';
                echo '<th>Sensor Serial #</th>';
                echo '<td>'.get_field('serial').'</th>';
            echo '</tr>';
            echo '<tr>';
                echo '<th>Sensor Type</th>';
                echo '<td>'.get_field('sensor_type').'</th>';
            echo '</tr>';
            echo '<tr>';
                echo '<th>Lens #</th>';
                echo '<td>'.get_field('lens').'</th>';
            echo '</tr>';
            echo '<tr>';
                echo '<th>Intended Use</th>';
                echo '<td>'.get_field('intended_use').'</th>';
            echo '</tr>';
            echo '<tr>';
                echo '<th>Current Disposition</th>';
                echo '<td>'.get_field('current_disposition').'</th>';
            echo '</tr>';                
            echo '<tr>';
                echo '<th>Record Created</th>';
                if ($record_created = get_field('record_created'))
                    echo '<td>'.$record_created.'</th>';
                else
                    echo '<td>'.get_the_author().' - '.get_the_date().'</th>';
            echo '</tr>';                
        echo '</table>';


        foreach($tables as $heading => $table) 
        {
            // Check for an empty table
            $is_empty = false;
            if (count($table[$body_key]) == 1)
            {
                $is_empty = true;
                foreach ($table[$body_key][0] as $td)
                {
                    if ($td['c'])
                    {
                        $is_empty = false;
                        break;
                    }
                }
            }


            if ($is_empty)
            {
                // Echo nothing
                //echo '<p>No '.strtolower($heading).' information available.</p>';
            }
            else
            {
                echo '<h1>'.$heading.'</h1>';
                echo '<table border="0">';
                    echo '<thead>';
                        echo '<tr>';
                            foreach ( $table[$header_key] as $th ) {
                                echo '<th>';
                                    echo $th['c'];
                                echo '</th>';
                            }
                        echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                        foreach ( $table[$body_key] as $tr ) {
                            echo '<tr>';
                                foreach ( $tr as $td ) {
                                    echo '<td>';
                                        echo nl2br($td['c']);
                                    echo '</td>';
                                }
                            echo '</tr>';
                        }
                    echo '</tbody>';
                echo '</table>';
                echo '<br>';
            }
        }
    ?>
    </div>

    <footer class="entryMeta">
        <?php
        // get the post info
//        get_template_part('postinfo' );
        ?>
    </footer>
</article>