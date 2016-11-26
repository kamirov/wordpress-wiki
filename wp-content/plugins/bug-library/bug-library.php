<?php
/*
Plugin Name: Bug Library
Plugin URI: http://wordpress.org/extend/plugins/bug-library/
Description: Display bug manager on pages with a variety of options
Version: 1.4.5
Author: Yannick Lefebvre
Author URI: http://ylefebvre.ca/

A plugin for the blogging MySQL/PHP-based WordPress.
Copyright 2016 Yannick Lefebvre

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

You can also view a copy of the HTML version of the GNU General Public
License at http://www.gnu.org/copyleft/gpl.html

I, Yannick Lefebvre, can be contacted via e-mail at ylefebvre@gmail.com
*/

global $wpdb;

require_once( plugin_dir_path( __FILE__ ) . '/wp-admin-menu-classes.php' );

$pagehooktop          = "";
$pagehookstylesheet   = "";
$pagehookinstructions = "";

/*********************************** Bug Library Class *****************************************************************************/
class bug_library_plugin {

	//constructor of class, PHP4 compatible construction for backward compatibility
	function bug_library_plugin() {


		$newoptions = get_option( 'BugLibraryGeneral', "" );

		if ( $newoptions == "" ) {
			$this->bl_reset_gen_settings( 'return_and_set' );
		}

		// Functions to be called when plugin is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'bl_install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'bl_uninstall' ) );

		//add filter for WordPress 2.8 changed backend box system !
		add_filter( 'screen_layout_columns', array( $this, 'on_screen_layout_columns' ), 10, 2 );
		//register callback for admin menu  setup
		add_action( 'admin_menu', array( $this, 'on_admin_menu' ) );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		//register the callback been used if options of page been submitted and needs to be processed
		add_action( 'admin_post_save_bug_library_general', array( $this, 'on_save_changes_general' ) );
		add_action( 'admin_post_save_bug_library_stylesheet', array( $this, 'on_save_changes_stylesheet' ) );

		// Add short codes
		add_shortcode( 'bug-library', array( $this, 'bug_library_func' ) );

		// Function to print information in page header when plugin present
		add_action( 'wp_head', array( $this, 'bl_page_header' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'bl_admin_header' ) );

		add_action( 'init', array( $this, 'my_custom_taxonomies' ), 0 );
		add_action( 'init', array( $this, 'create_bug_post_type' ) );

		add_action( "manage_posts_custom_column", array( $this, "bugs_populate_columns" ) );
		add_filter( "manage_edit-bug-library-bugs_columns", array( $this, "bugs_columns_list" ) );

		add_action( 'restrict_manage_posts', array( $this, 'restrict_listings' ) );
		add_filter( 'parse_query', array( $this, 'convert_ids_to_taxonomy_term_in_query' ) );

		add_action( 'save_post', array( $this, 'add_bug_field' ), 10, 2 );
		add_action( 'delete_post', array( $this, 'delete_bug_field' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'filter_post_data' ), '99', 2 );

		add_action( 'template_redirect', array( $this, 'bl_template_redirect' ) );

		// Function to determine if Bug Library is used on a page before printing headers
		// add_filter( 'the_posts', array(
		// 	$this,
		// 	'conditionally_add_scripts_and_styles'
		// ) ); // the_posts gets triggered before wp_head

		// Load text domain for translation of admin pages and text strings
		load_plugin_textdomain( 'bug-library', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/************************** Bug Library Installation Function **************************/
	function bl_install() {

		global $wpdb;

		$productexist = $wpdb->get_var( "select * from " . $wpdb->get_blog_prefix() . "term_taxonomy where taxonomy = 'bug-library-products'" );

		if ( empty( $productexist ) ) {
			$wpdb->insert( $wpdb->get_blog_prefix() . 'terms', array(
				'name'       => 'Default Product',
				'slug'       => 'default-product',
				'term_group' => 0
			) );
			$producttermid = $wpdb->get_var( "select term_id from " . $wpdb->get_blog_prefix() . "terms where name = 'Default Product'" );
			$wpdb->insert( $wpdb->get_blog_prefix() . 'term_taxonomy', array(
				'term_id'     => $producttermid,
				'taxonomy'    => 'bug-library-products',
				'description' => '',
				'parent'      => 0,
				'count'       => 0
			) );
		}

		$typeexist = $wpdb->get_var( "select * from " . $wpdb->get_blog_prefix() . "term_taxonomy where taxonomy = 'bug-library-types'" );

		if ( empty( $typeexist ) ) {
			$wpdb->insert( $wpdb->get_blog_prefix() . 'terms', array(
				'name'       => 'Default Type',
				'slug'       => 'default-type',
				'term_group' => 0
			) );
			$typetermid = $wpdb->get_var( "select term_id from " . $wpdb->get_blog_prefix() . "terms where name = 'Default Type'" );
			$wpdb->insert( $wpdb->get_blog_prefix() . 'term_taxonomy', array(
				'term_id'     => $typetermid,
				'taxonomy'    => 'bug-library-types',
				'description' => '',
				'parent'      => 0,
				'count'       => 0
			) );
		}

		$statusexist = $wpdb->get_var( "select * from " . $wpdb->get_blog_prefix() . "term_taxonomy where taxonomy = 'bug-library-status'" );

		if ( empty( $statusexist ) ) {
			$wpdb->insert( $wpdb->get_blog_prefix() . 'terms', array(
				'name'       => 'Default Status',
				'slug'       => 'default-status',
				'term_group' => 0
			) );
			$statustermid = $wpdb->get_var( "select term_id from " . $wpdb->get_blog_prefix() . "terms where name = 'Default Status'" );
			$wpdb->insert( $wpdb->get_blog_prefix() . 'term_taxonomy', array(
				'term_id'     => $statustermid,
				'taxonomy'    => 'bug-library-status',
				'description' => '',
				'parent'      => 0,
				'count'       => 0
			) );
		}

		$priorityexist = $wpdb->get_var( "select * from " . $wpdb->get_blog_prefix() . "term_taxonomy where taxonomy = 'bug-library-priority'" );

		if ( empty( $priorityexist ) ) {
			$wpdb->insert( $wpdb->get_blog_prefix() . 'terms', array(
				'name'       => 'Default Priority',
				'slug'       => 'default-priority',
				'term_group' => 0
			) );
			$prioritytermid = $wpdb->get_var( "select term_id from " . $wpdb->get_blog_prefix() . "terms where name = 'Default Priority'" );
			$wpdb->insert( $wpdb->get_blog_prefix() . 'term_taxonomy', array(
				'term_id'     => $prioritytermid,
				'taxonomy'    => 'bug-library-priority',
				'description' => '',
				'parent'      => 0,
				'count'       => 0
			) );
		}

		$bugs = $wpdb->get_results( "select * from " . $wpdb->get_blog_prefix() . "posts where post_type = 'bug-library-bugs'" );

		if ( $bugs ) {
			foreach ( $bugs as $bug ) {
				$priorityterms = wp_get_post_terms( $bug->ID, 'bug-library-priority' );
				if ( ! $priorityterms ) {
					wp_set_post_terms( $bug->ID, 'Default Priority', 'bug-library-priority' );
				}
			}
		}
	}

	function admin_init() {
		add_meta_box( 'buglibrary_edit_bug_meta_box', __( 'Issue Details', 'bug-library' ), array(
			$this,
			'bug_library_edit_bug_details'
		), 'bug-library-bugs', 'normal', 'high' );
	}

	function my_custom_taxonomies() {

		register_taxonomy(
			'bug-library-products',        // internal name = machine-readable taxonomy name
			'bug-library-bugs',        // object type = post, page, link, or custom post-type
			array(
				'hierarchical'  => false,
				'label'         => 'Products',    // the human-readable taxonomy name
				'query_var'     => true,    // enable taxonomy-specific querying
				'rewrite'       => array( 'slug' => 'products' ),    // pretty permalinks for your taxonomy?
				'add_new_item'  => 'Add New Product',
				'new_item_name' => "New Product Name",
				'show_ui'       => true,
				'show_tagcloud' => false
			)
		);

		register_taxonomy(
			'bug-library-status',        // internal name = machine-readable taxonomy name
			'bug-library-bugs',        // object type = post, page, link, or custom post-type
			array(
				'hierarchical'  => false,
				'label'         => 'Status',    // the human-readable taxonomy name
				'query_var'     => true,    // enable taxonomy-specific querying
				'rewrite'       => array( 'slug' => 'status' ),    // pretty permalinks for your taxonomy?
				'add_new_item'  => 'Add New Status',
				'new_item_name' => "New Status",
				'show_ui'       => true,
				'show_tagcloud' => false
			)
		);

		register_taxonomy(
			'bug-library-types',        // internal name = machine-readable taxonomy name
			'bug-library-bugs',        // object type = post, page, link, or custom post-type
			array(
				'hierarchical'  => false,
				'label'         => 'Types',    // the human-readable taxonomy name
				'query_var'     => true,    // enable taxonomy-specific querying
				'rewrite'       => array( 'slug' => 'types' ),    // pretty permalinks for your taxonomy?
				'add_new_item'  => 'Add New Type',
				'new_item_name' => "New Type",
				'show_ui'       => true,
				'show_tagcloud' => false
			)
		);

		register_taxonomy(
			'bug-library-priority',        // internal name = machine-readable taxonomy name
			'bug-library-bugs',        // object type = post, page, link, or custom post-type
			array(
				'hierarchical'  => false,
				'label'         => 'Priorities',    // the human-readable taxonomy name
				'query_var'     => true,    // enable taxonomy-specific querying
				'rewrite'       => array( 'slug' => 'priority' ),    // pretty permalinks for your taxonomy?
				'add_new_item'  => 'Add New Priority',
				'new_item_name' => "New Priority",
				'show_ui'       => true,
				'show_tagcloud' => false
			)
		);
	}

	function create_bug_post_type() {
		$genoptions = get_option( 'BugLibraryGeneral', '' );
		if ( isset( $genoptions['permalinkpageid'] ) && $genoptions['permalinkpageid'] != - 1 ) {
			$page = get_post( $genoptions['permalinkpageid'] );
			$slug = $page->post_name;
		} else {
			$slug = 'issues';
		}

		$slug = 'issues';
		// var_dump($slug);

		register_post_type( 'bug-library-bugs',
			array(
				'labels'        => array(
					'name'               => __( 'Issues' ),
					'singular_name'      => __( 'Issue' ),
					'add_new'            => __( 'Add New' ),
					'add_new_item'       => __( 'Add New Issue' ),
					'edit'               => __( 'Edit' ),
					'edit_item'          => __( 'Edit Issue' ),
					'new_item'           => __( 'New Issue' ),
					'view'               => __( 'View Issue' ),
					'view_item'          => __( 'View Issue' ),
					'search_items'       => __( 'Search Issues' ),
					'not_found'          => __( 'No issues found' ),
					'not_found_in_trash' => __( 'No issues found in Trash' ),
					'parent'             => __( 'Parent Issue' ),
				),
				'public'        => true,
				'menu_position' => 20,
				'supports'      => array( 'title', 'author', 'editor', 'comments', 'revisions' ),
				'taxonomies'    => array( '' ),
				'menu_icon'     => 'dashicons-flag',
//				'menu_icon'     => plugins_url( 'icons/bug-icon.png', __FILE__ ),
				'rewrite'       => array( 'slug' => $slug )
			)
		);
	}

	function bugs_columns_list( $columns ) {
		$columns["bug-library-view-ID"]       = "ID";
		$columns["bug-library-view-product"]  = "Product";
		$columns["bug-library-view-status"]   = "Status";
		$columns["bug-library-view-type"]     = "Type";
		$columns["bug-library-view-priority"] = "Priority";
		$columns["bug-library-view-assignee"] = "Assignee";
		unset( $columns['comments'] );

		return $columns;
	}

	function bugs_populate_columns( $column ) {
		global $post;

		$products   = wp_get_post_terms( $post->ID, "bug-library-products" );
		$status     = wp_get_post_terms( $post->ID, "bug-library-status" );
		$types      = wp_get_post_terms( $post->ID, "bug-library-types" );
		$priorities = wp_get_post_terms( $post->ID, "bug-library-priority" );

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

		if ( "bug-library-view-ID" == $column && isset( $post->ID ) ) {
			echo $post->ID;
		} elseif ( "bug-library-view-title" == $column && isset( $post->post_title ) ) {
			echo $post->post_title;
		} elseif ( "bug-library-view-product" == $column && isset( $products ) && !empty( $products ) ) {
			echo $products[0]->name;
		} elseif ( "bug-library-view-status" == $column && isset( $status ) && !empty( $status ) ) {
			echo $status[0]->name;
		} elseif ( "bug-library-view-type" == $column && isset( $types ) && !empty( $types ) ) {
			echo $types[0]->name;
		} elseif ( "bug-library-view-priority" == $column && isset( $priorities ) && !empty( $priorities ) ) {
			echo $priorities[0]->name;
		} elseif ( "bug-library-view-assignee" == $column ) {
			echo $firstname . " " . $lastname;
		}
	}

	function restrict_listings() {
		global $typenow;
		global $wp_query;
		if ( $typenow == 'bug-library-bugs' ) {
			$taxonomy         = 'bug-library-products';
			$product_taxonomy = get_taxonomy( $taxonomy );
			wp_dropdown_categories( array(
				'show_option_all' => __( "Show All {$product_taxonomy->label}" ),
				'taxonomy'        => $taxonomy,
				'name'            => 'bug-library-products',
				'orderby'         => 'name',
				'selected'        => ( isset( $wp_query->query['bug-library-products'] ) ? $wp_query->query['bug-library-products'] : '' ),
				'hierarchical'    => true,
				'depth'           => 3,
				'show_count'      => false, // Show # listings in parens
				'hide_empty'      => true, // Don't show businesses w/o listings
			) );

			$taxonomy         = 'bug-library-types';
			$product_taxonomy = get_taxonomy( $taxonomy );
			wp_dropdown_categories( array(
				'show_option_all' => __( "Show All {$product_taxonomy->label}" ),
				'taxonomy'        => $taxonomy,
				'name'            => 'bug-library-types',
				'orderby'         => 'name',
				'selected'        => ( isset( $wp_query->query['bug-library-types'] ) ? $wp_query->query['bug-library-types'] : '' ),
				'hierarchical'    => true,
				'depth'           => 3,
				'show_count'      => false, // Show # listings in parens
				'hide_empty'      => true, // Don't show businesses w/o listings
			) );

			$taxonomy         = 'bug-library-status';
			$product_taxonomy = get_taxonomy( $taxonomy );
			wp_dropdown_categories( array(
				'show_option_all' => __( "Show All {$product_taxonomy->label}" ),
				'taxonomy'        => $taxonomy,
				'name'            => 'bug-library-status',
				'orderby'         => 'name',
				'selected'        => ( isset( $wp_query->query['bug-library-status'] ) ? $wp_query->query['bug-library-status'] : '' ),
				'hierarchical'    => true,
				'depth'           => 3,
				'show_count'      => false, // Show # listings in parens
				'hide_empty'      => true, // Don't show businesses w/o listings
			) );

			$taxonomy         = 'bug-library-priority';
			$product_taxonomy = get_taxonomy( $taxonomy );
			wp_dropdown_categories( array(
				'show_option_all' => __( "Show All {$product_taxonomy->label}" ),
				'taxonomy'        => $taxonomy,
				'name'            => 'bug-library-priority',
				'orderby'         => 'name',
				'selected'        => ( isset ( $wp_query->query['bug-library-priority'] ) ? $wp_query->query['bug-library-priority'] : '' ),
				'hierarchical'    => true,
				'depth'           => 3,
				'show_count'      => false, // Show # listings in parens
				'hide_empty'      => true, // Don't show businesses w/o listings
			) );
		}
	}

	function convert_ids_to_taxonomy_term_in_query( $query ) {
		global $pagenow;
		$qv = &$query->query_vars;

		if ( $pagenow == 'edit.php' &&
		     isset( $qv['bug-library-products'] ) && is_numeric( $qv['bug-library-products'] )
		) {

			$term                       = get_term_by( 'id', $qv['bug-library-products'], 'bug-library-products' );
			$qv['bug-library-products'] = $term->slug;
		}

		if ( $pagenow == 'edit.php' &&
		     isset( $qv['bug-library-types'] ) && is_numeric( $qv['bug-library-types'] )
		) {

			$term                    = get_term_by( 'id', $qv['bug-library-types'], 'bug-library-types' );
			$qv['bug-library-types'] = $term->slug;
		}

		if ( $pagenow == 'edit.php' &&
		     isset( $qv['bug-library-status'] ) && is_numeric( $qv['bug-library-status'] )
		) {

			$term                     = get_term_by( 'id', $qv['bug-library-status'], 'bug-library-status' );
			$qv['bug-library-status'] = $term->slug;
		}

		if ( $pagenow == 'edit.php' &&
		     isset( $qv['bug-library-priority'] ) && is_numeric( $qv['bug-library-priority'] )
		) {

			$term                       = get_term_by( 'id', $qv['bug-library-priority'], 'bug-library-priority' );
			$qv['bug-library-priority'] = $term->slug;
		}

	}

	function bug_library_edit_bug_details( $bug ) {
		$genoptions = get_option( 'BugLibraryGeneral', "" );
		$genoptions = wp_parse_args( $genoptions, $this->bl_reset_gen_settings( 'return' ) );

		global $wpdb;

		$products          = wp_get_post_terms( $bug->ID, "bug-library-products" );
		$statuses          = wp_get_post_terms( $bug->ID, "bug-library-status" );
		$types             = wp_get_post_terms( $bug->ID, "bug-library-types" );
		$priorities        = wp_get_post_terms( $bug->ID, "bug-library-priority" );
		$productversion    = get_post_meta( $bug->ID, "bug-library-product-version", true );
		$reportername      = get_post_meta( $bug->ID, "bug-library-reporter-name", true );
		$reporteremail     = get_post_meta( $bug->ID, "bug-library-reporter-email", true );
		$resolutiondate    = get_post_meta( $bug->ID, "bug-library-resolution-date", true );
		$resolutionversion = get_post_meta( $bug->ID, "bug-library-resolution-version", true );
		$imagepath         = get_post_meta( $bug->ID, "bug-library-image-path", true );
		$assigneduserid    = get_post_meta( $bug->ID, "bug-library-assignee", true );

		echo "<table>\n";

		echo "<tr><td>Assigned to</td><td style='width: 60%;'>\n";
		global $wp_roles;
		$role_names = $wp_roles->roles;

		$users = array();
		foreach( $role_names as $role ) {
			$args = array( 'role' => $role['name'] );
			$new_users = get_users( $args );
			if ( !empty( $new_users ) ) {
				foreach ( $new_users as $new_user ) {
					$users[$new_user->data->ID] = $new_user->data->user_login;
				}
			}

			if ( $role['name'] == $genoptions['rolelevel'] ) {
				break;
			}

		}

		asort( $users );

		if ( $users ) {
			echo "<select name='bug-library-assignee' style='width: 100%'>";
			echo "<option value='-1'>Unassigned</option>";
			foreach ( $users as $user_ID => $user ) {
				$firstname = get_user_meta( $user_ID, 'first_name', true );

				$lastname = get_user_meta( $user_ID, 'last_name', true );

				if ( $user_ID == $assigneduserid ) {
					$selectedterm = "selected='selected'";
				} else {
					$selectedterm = '';
				}

				echo "<option value='" . $user_ID . "' " . $selectedterm . ">";

				if ( $firstname != '' || $lastname != '' ) {
					echo $firstname . " " . $lastname;
				} else {
					echo $user;
				}

				echo "</option>";
			}
			echo "</select>";
		}

		echo "</td></tr>\n";

		echo "\t<tr>\n";
		echo "\t\t<td>Product</td><td style='width: 60%;'>";

		$productterms = get_terms( 'bug-library-products', 'orderby=name&hide_empty=0' );

		if ( $productterms  ) {
			echo "<select name='bug-library-product' style='width: 100%'>";
			foreach ( $productterms as $productterm ) {

				if ( !empty( $products ) && $products[0]->term_id == $productterm->term_id ) {
					$selectedterm = "selected='selected'";
				} else {
					$selectedterm = '';
				}

				echo "<option value='" . $productterm->term_id . "' " . $selectedterm . ">" . $productterm->name . "</option>";
			}
			echo "</select>";
		}

		echo "\t\t</td>\t";
		echo "\t</tr>\n";

		echo "\t<tr>\n";
		echo "\t\t<td>Status</td><td style='width: 60%;'>\n";

		$statusterms = get_terms( 'bug-library-status', 'orderby=name&hide_empty=0' );

		if ( $statusterms ) {
			echo "<select name='bug-library-status' style='width: 100%'>\n";
			foreach ( $statusterms as $statusterm ) {
				$selectedterm = '';

				if ( !empty( $statuses ) && !empty( $statuses[0]->term_id ) ) {
					if ( $statuses[0]->term_id == $statusterm->term_id ) {
						$selectedterm = "selected='selected'";
					}
				} elseif ( empty( $statuses[0]->term_id ) && !empty( $genoptions['defaultuserbugstatus'] ) ) {
					if ( $genoptions['defaultuserbugstatus'] == $statusterm->term_id ) {
						$selectedterm = "selected='selected'";
					}
				}

				echo "<option value='" . $statusterm->term_id . "' " . $selectedterm . ">" . $statusterm->name . "</option>\n";
			}
			echo "</select>\n";
		}

		echo "</td>\n";
		echo "</tr>\n";

		echo "\t<tr>\n";
		echo "\t\t<td>Type</td><td style='width: 60%;'>\n";

		$typesterms = get_terms( 'bug-library-types', 'orderby=name&hide_empty=0' );

		if ( $typesterms ) {
			echo "<select name='bug-library-types' style='width: 100%'>\n";
			foreach ( $typesterms as $typesterm ) {

				if ( !empty( $types ) && $types[0]->term_id == $typesterm->term_id ) {
					$selectedterm = "selected='selected'";
				} else {
					$selectedterm = '';
				}

				echo "<option value='" . $typesterm->term_id . "' " . $selectedterm . ">" . $typesterm->name . "</option>\n";
			}
			echo "</select>\n";
		}

		echo "</td>\n";
		echo "</tr>\n";

		echo "\t<tr>\n";
		echo "\t\t<td>Priority</td><td style='width: 60%;'>\n";

		$prioritiesterms = get_terms( 'bug-library-priority', 'orderby=name&hide_empty=0' );

		if ( $prioritiesterms ) {
			echo "<select name='bug-library-priority' style='width: 100%'>\n";
			foreach ( $prioritiesterms as $priorityterm ) {
				$selectedterm = '';
				if ( !empty( $priorities ) && $priorities[0]->term_id != '' ) {
					if ( $priorities[0]->term_id == $priorityterm->term_id ) {
						$selectedterm = "selected='selected'";
					}
				} elseif ( empty( $priorities[0]->term_id ) && isset( $genoptions['defaultuserbugpriority'] ) ) {
					if ( $genoptions['defaultuserbugpriority'] == $priorityterm->term_id ) {
						$selectedterm = "selected='selected'";
					}
				}

				echo "<option value='" . $priorityterm->term_id . "' " . $selectedterm . ">" . $priorityterm->name . "</option>\n";
			}
			echo "</select>\n";
		}

		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\t";

		echo "<script type='text/javascript'>\n";
		echo "\tjQuery(document).ready(function() {\n";
		echo "\t\tjQuery('#bug-library-resolution-date').datepicker({minDate: '+0', dateFormat: 'mm-dd-yy', showOn: 'both', constrainInput: true, buttonImage: '" . plugins_url( '/icons/calendar.png', __FILE__ ) . "'}) });\n";

		echo "jQuery( 'form#post' )\n";
		echo "\t.attr( 'enctype', 'multipart/form-data' )\n";
		echo "\t.attr( 'encoding', 'multipart/form-data' )\n";
		echo ";\n";

		echo "</script>\n";

	}

	function add_bug_field( $ID = false, $post = false ) {
		$post = get_post( $ID );
		if ( $post->post_type = 'bug-library-bugs' ) {
			if ( isset( $_POST['bug-library-product'] ) ) {
				$productterm = get_term_by( 'id', $_POST['bug-library-product'], 'bug-library-products' );

				if ( $productterm ) {
					wp_set_post_terms( $post->ID, $productterm->name, "bug-library-products" );
				}
			}

			if ( isset( $_POST['bug-library-status'] ) ) {
				$statusterm = get_term_by( 'id', $_POST['bug-library-status'], 'bug-library-status' );
				if ( $statusterm ) {
					wp_set_post_terms( $post->ID, $statusterm->name, "bug-library-status" );
				}
			}

			if ( isset( $_POST['bug-library-types'] ) ) {
				$typeterm = get_term_by( 'id', $_POST['bug-library-types'], "bug-library-types" );
				if ( $typeterm ) {
					wp_set_post_terms( $post->ID, $typeterm->name, "bug-library-types" );
				}
			}

			if ( isset( $_POST['bug-library-priority'] ) ) {
				$priorityterm = get_term_by( 'id', $_POST['bug-library-priority'], "bug-library-priority" );
				if ( $priorityterm ) {
					wp_set_post_terms( $post->ID, $priorityterm->name, "bug-library-priority" );
				}
			}

			if ( isset( $_POST['bug-library-product-version'] ) && $_POST['bug-library-product-version'] != '' ) {
				update_post_meta( $post->ID, "bug-library-product-version", $_POST['bug-library-product-version'] );
			}

			if ( isset( $_POST['bug-library-reporter-name'] ) && $_POST['bug-library-reporter-name'] != '' ) {
				update_post_meta( $post->ID, "bug-library-reporter-name", $_POST['bug-library-reporter-name'] );
			}

			if ( isset( $_POST['bug-library-reporter-email'] ) && $_POST['bug-library-reporter-email'] != '' ) {
				update_post_meta( $post->ID, "bug-library-reporter-email", $_POST['bug-library-reporter-email'] );
			}

			if ( isset( $_POST['bug-library-resolution-date'] ) && $_POST['bug-library-resolution-date'] != '' ) {
				update_post_meta( $post->ID, "bug-library-resolution-date", $_POST['bug-library-resolution-date'] );
			}

			if ( isset( $_POST['bug-library-resolution-version'] ) && $_POST['bug-library-resolution-version'] != '' ) {
				update_post_meta( $post->ID, "bug-library-resolution-version", $_POST['bug-library-resolution-version'] );
			}

			if ( isset( $_POST['bug-library-assignee'] ) && $_POST['bug-library-assignee'] != '' ) {
				update_post_meta( $post->ID, "bug-library-assignee", $_POST['bug-library-assignee'] );
			}

			$uploads = wp_upload_dir();

			if ( array_key_exists( 'attachimage', $_FILES ) ) {
				$file_extension = pathinfo( $_FILES['attachimage']['name'], PATHINFO_EXTENSION );
				$target_path    = $uploads['basedir'] . "/bug-library/bugimage-" . $post->ID . '.' . $file_extension;
				$file_path      = $uploads['baseurl'] . "/bug-library/bugimage-" . $post->ID . '.' . $file_extension;

				if ( move_uploaded_file( $_FILES['attachimage']['tmp_name'], $target_path ) ) {
					update_post_meta( $post->ID, "bug-library-image-path", $file_path );
				}
			}
		}
	}

	function delete_bug_field( $bug_id ) {
		delete_post_meta( $bug_id, "bug-library-product-version" );
		delete_post_meta( $bug_id, "bug-library-reporter-name" );
		delete_post_meta( $bug_id, "bug-library-reporter-email" );
		delete_post_meta( $bug_id, "bug-library-resolution-date" );
		delete_post_meta( $bug_id, "bug-library-resolution-version" );
	}

	function filter_post_data( $post, $postarr ) {
		$genoptions = get_option( 'BugLibraryGeneral' );
		$genoptions = wp_parse_args( $genoptions, $this->bl_reset_gen_settings( 'return' ) );

		if ( $post['post_type'] == 'bug-library-bugs' && $genoptions['closecommentsonclosure'] ) {
			if ( isset( $_POST['bug-library-status'] ) ) {
				$statusterm = get_term_by( 'id', $_POST['bug-library-status'], 'bug-library-status' );
				if ( $statusterm ) {
					if ( $statusterm->name == $genoptions['bugclosedstatus'] ) {
						$post['comment_status'] = 'closed';
					}
				}
			}
		}

		return $post;
	}

	/************************** Bug Library Uninstall Function **************************/
	function bl_uninstall() {
		$genoptions = get_option( 'BugLibraryGeneral' );
	}

	// Function used to set initial settings or reset them on user request
	function bl_reset_gen_settings( $setoptions = 'return' ) {
		global $wpdb;

		$genoptions['moderatesubmissions']    = true;
		$genoptions['showcaptcha']            = true;
		$genoptions['requirelogin']           = false;
		$genoptions['entriesperpage']         = 10;
		$genoptions['allowattach']            = false;
		$genoptions['defaultuserbugstatus']   = 'Default Status';
		$genoptions['defaultuserbugstatus']   = 'Default Priority';
		$genoptions['newbugadminnotify']      = true;
		$genoptions['bugnotifytitle']         = __( 'New bug added to Wordpress Bug Library: %bugtitle%', 'bug-library' );
		$genoptions['permalinkpageid']        = - 1;
		$genoptions['firstrowheaders']        = false;
		$genoptions['showpriority']           = false;
		$genoptions['showreporter']           = false;
		$genoptions['rolelevel']              = 'Administrator';
		$genoptions['showassignee']           = false;
		$genoptions['editlevel']              = 'Administrator';
		$genoptions['requirename']            = false;
		$genoptions['requireemail']           = false;
		$genoptions['hideproduct']            = false;
		$genoptions['hideversionnumber']      = false;
		$genoptions['hideissuetype']          = false;
		$genoptions['bugclosedstatus']        = 'Default Status';
		$genoptions['closecommentsonclosure'] = false;

		$stylesheetlocation           = plugins_url( 'stylesheet.css', __FILE__ );
//		$genoptions['fullstylesheet'] = wp_remote_fopen( $stylesheetlocation );

		if ( 'return_and_set' == $setoptions ) {
			update_option( 'BugLibraryGeneral', $genoptions );
		}

		return $genoptions;
	}

	//for WordPress 2.8 we have to tell, that we support 2 columns !
	function on_screen_layout_columns( $columns, $screen ) {
		return $columns;
	}

	function remove_querystring_var( $url, $key ) {
		$keypos = strpos( $url, $key );
		if ( $keypos ) {
			$ampersandpos = strpos( $url, '&', $keypos );
			$newurl       = substr( $url, 0, $keypos - 1 );

			if ( $ampersandpos ) {
				$newurl .= substr( $url, $ampersandpos );
			}
		} else {
			$newurl = $url;
		}

		return $newurl;
	}

	//extend the admin menu
	function on_admin_menu() {
		//add our own option page, you can also add it to different sections or use your own one
		global $wpdb, $pagehooktop, $pagehookstylesheet, $pagehookinstructions;

		$pagehooktop = add_submenu_page( 'edit.php?post_type=bug-library-bugs', __( 'Bug Library General Options', 'bug-library' ), "General Options", 'manage_options', 'bug-library-general-options', array(
			$this,
			'on_show_page'
		) );

		$pagehookstylesheet = add_submenu_page( 'edit.php?post_type=bug-library-bugs', __( 'Bug Library - Stylesheet Editor', 'bug-library' ), __( 'Stylesheet', 'bug-library' ), 'manage_options', 'bug-library-stylesheet', array(
			$this,
			'on_show_page'
		) );

		$pagehookinstructions = add_submenu_page( 'edit.php?post_type=bug-library-bugs', __( 'Bug Library - Instructions', 'bug-library' ), __( 'Instructions', 'bug-library' ), 'manage_options', 'bug-library-instructions', array(
			$this,
			'on_show_page'
		) );

		//register  callback gets call prior your own page gets rendered
		add_action( 'load-' . $pagehooktop, array( $this, 'on_load_page' ) );
		add_action( 'load-' . $pagehookstylesheet, array( $this, 'on_load_page' ) );
		add_action( 'load-' . $pagehookinstructions, array( $this, 'on_load_page' ) );
	}

	//will be executed if wordpress core detects this page has to be rendered
	function on_load_page() {

		global $pagehooktop, $pagehookstylesheet, $pagehookinstructions;

		wp_enqueue_script( 'tiptip', plugins_url( 'tiptip/jquery.tipTip.minified.js', __FILE__ ), array( 'jquery' ), "1.0rc3" );
		wp_enqueue_style( 'tiptipstyle', plugins_url( 'tiptip/tipTip.css', __FILE__ ) );
		wp_enqueue_script( 'postbox' );

		//add several metaboxes now, all metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
		add_meta_box( 'buglibrary_general_meta_box', __( 'General Settings', 'bug-library' ), array(
			$this,
			'general_meta_box'
		), $pagehooktop, 'normal', 'high' );
		add_meta_box( 'buglibrary_general_newissue_meta_box', __( 'User Submission Settings', 'bug-library' ), array(
			$this,
			'general_meta_newissue_box'
		), $pagehooktop, 'normal', 'high' );
		add_meta_box( 'buglibrary_general_import_meta_box', __( 'Import / Export', 'bug-library' ), array(
			$this,
			'general_importexport_meta_box'
		), $pagehooktop, 'normal', 'high' );
		add_meta_box( 'buglibrary_general_save_meta_box', __( 'Save', 'bug-library' ), array(
			$this,
			'general_save_meta_box'
		), $pagehooktop, 'normal', 'high' );

		add_meta_box( 'buglibrary_stylesheet_meta_box', __( 'Stylesheet', 'bug-library' ), array(
			$this,
			'stylesheet_meta_box'
		), $pagehookstylesheet, 'normal', 'high' );

		add_meta_box( 'buglibrary_instructions_meta_box', __( 'Instructions', 'bug-library' ), array(
			$this,
			'instructions_meta_box'
		), $pagehookinstructions, 'normal', 'high' );
	}

	//executed to show the plugins complete admin page
	function on_show_page() {
		//we need the global screen column value to beable to have a sidebar in WordPress 2.8
		global $screen_layout_columns;

		// Retrieve general options
		$genoptions = get_option( 'BugLibraryGeneral' );
		$genoptions = wp_parse_args( $genoptions, $this->bl_reset_gen_settings( 'return' ) );

		// If general options don't exist, create them
		if ( $genoptions == false ) {
			$this->bl_reset_gen_settings();
		}

		// Check for current page to set some page=specific variables
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'bug-library-general-options' ) {
			if ( isset( $_GET['message'] ) && $_GET['message'] == '1' ) {
				echo "<div id='message' class='updated fade'><p><strong>" . __( 'General Settings Saved', 'bug-library' ) . ".</strong></p></div>";
			} elseif ( isset( $_GET['message'] ) && $_GET['message'] == '2' ) {
				echo "<div id='message' class='updated fade'><p><strong>" . __( 'Please create a folder called uploads under your Wordpress /wp-content/ directory with write permissions to use this functionality.', 'bug-library' ) . ".</strong></p></div>";
			} elseif ( isset( $_GET['message'] ) && $_GET['message'] == '3' ) {
				echo "<div id='message' class='updated fade'><p><strong>" . __( 'Please make sure that the /wp-content/uploads/ directory has write permissions to use this functionality.', 'bug-library' ) . ".</strong></p></div>";
			} elseif ( isset( $_GET['message'] ) && $_GET['message'] == '4' ) {
				echo "<div id='message' class='updated fade'><p><strong>" . __( 'Invalid column count for bug on row', 'bug-library' ) . "</strong></p></div>";
			} elseif ( isset( $_GET['message'] ) && $_GET['message'] == '9' ) {
				echo "<div id='message' class='updated fade'><p><strong>" . $_GET['importrowscount'] . " " . __( 'row(s) found', 'bug-library' ) . ". " . $_GET['successimportcount'] . " " . __( 'bugs(s) imported successfully', 'bugs-library' ) . ".</strong></p></div>";
			}

			$formvalue = 'save_bug_library_general';
			$pagetitle = "Bug Library General Settings";
		} elseif ( $_GET['page'] == 'bug-library-stylesheet' ) {
			$formvalue = 'save_bug_library_stylesheet';

			$pagetitle = "Bug Library Stylesheet Editor";

			if ( isset( $_GET['message'] ) && $_GET['message'] == '1' ) {
				echo "<div id='message' class='updated fade'><p><strong>" . __( 'Stylesheet updated', 'link-library' ) . ".</strong></p></div>";
			} elseif ( isset( $_GET['message'] ) && $_GET['message'] == '2' ) {
				echo "<div id='message' class='updated fade'><p><strong>" . __( 'Stylesheet reset to original state', 'link-library' ) . ".</strong></p></div>";
			}
		} elseif ( $_GET['page'] == 'bug-library-instructions' ) {
			$formvalue = 'save_bug_library_instructions';

			$pagetitle = "Bug Library Usage Instructions";

		}

		$data               = array();
		$data['genoptions'] = $genoptions;
		global $pagehooktop, $pagehookstylesheet, $pagehookinstructions;
		?>
		<div id="bug-library-general" class="wrap">
			<div class='icon32'><img src="<?php echo plugins_url( '/icons/bug-icon32.png', __FILE__ ); ?>" /></div>
			<h2><?php echo $pagetitle; ?>
				<span style='padding-left: 50px'><a href="http://ylefebvre.ca/wordpress-plugins/bug-library/" target="buglibrary"><img src="<?php echo plugins_url( '/icons/btn_donate_LG.gif', __FILE__ ); ?>" /></a></span>
			</h2>

			<form name='buglibrary' enctype="multipart/form-data" action="admin-post.php" method="post">
				<input type="hidden" name="MAX_FILE_SIZE" value="100000" />

				<?php wp_nonce_field( 'bug-library' ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				<input type="hidden" name="action" value="<?php echo $formvalue; ?>" />

				<div id="poststuff" class="metabox-holder">
					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">
							<?php
							if ( $_GET['page'] == 'bug-library-general-options' ) {
								do_meta_boxes( $pagehooktop, 'normal', $data );
							} elseif ( $_GET['page'] == 'bug-library-stylesheet' ) {
								do_meta_boxes( $pagehookstylesheet, 'normal', $data );
							} elseif ( $_GET['page'] == 'bug-library-instructions' ) {
								do_meta_boxes( $pagehookinstructions, 'normal', $data );
							}
							?>
						</div>
					</div>
					<br class="clear" />
				</div>
			</form>
		</div>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready(function ($) {
				// close postboxes that should be closed
				jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php
				if ($_GET['page'] == 'bug-library')
					{echo $pagehooktop;}
				elseif ($_GET['page'] == 'bug-library-stylesheet')
					{echo $pagehookstylesheet;}
				elseif ($_GET['page'] == 'bug-library-instructions')
					{echo $pagehookinstructions;}
				?>');

				jQuery('.bltooltip').each(function () {
						$(this).tipTip();
					}
				);

			});
			//]]>

		</script>

	<?php
	}

	//executed if the post arrives initiated by pressing the submit button of form
	function on_save_changes_general() {
		//user permission check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Not allowed', 'bug-library' ) );
		}
		//cross check the given referer
		check_admin_referer( 'bug-library' );

		$message = '';

		$genoptions = get_option( 'BugLibraryGeneral' );
		$genoptions = wp_parse_args( $genoptions, $this->bl_reset_gen_settings( 'return' ) );

		if ( isset( $_POST['importbugs'] ) ) {
			global $wpdb;
			$row = 1;
			$successfulimport = 0;

			$handle = fopen( $_FILES['bugsfile']['tmp_name'], "r" );

			if ( $handle ) {
				$skiprow = 1;

				while ( ( $data = fgetcsv( $handle, 5000, "," ) ) !== false ) {
					$row += 1;
					if ( $skiprow == 1 && isset( $_POST['firstrowheaders'] ) && $row >= 2 ) {
						$skiprow = 0;
					} elseif ( ! isset( $_POST['firstrowheaders'] ) ) {
						$skiprow = 0;
					}

					if ( ! $skiprow ) {
						if ( count( $data ) == 13 ) {
							$new_bug_data = array(
								'post_status'           => $data[9],
								'post_type'             => 'bug-library-bugs',
								'post_author'           => '',
								'ping_status'           => get_option( 'default_ping_status' ),
								'post_parent'           => 0,
								'menu_order'            => 0,
								'to_ping'               => '',
								'pinged'                => '',
								'post_password'         => '',
								'guid'                  => '',
								'post_content_filtered' => '',
								'post_excerpt'          => '',
								'import_id'             => 0,
								'comment_status'        => 'open',
								'post_content'          => wp_specialchars( stripslashes( $data[5] ) ),
								'post_date'             => date( 'Y-m-d H:i:s', strtotime( $data[8] ) ),
								'post_excerpt'          => '',
								'post_title'            => wp_specialchars( stripslashes( $data[4] ) )
							);

							$newbugid = wp_insert_post( $new_bug_data );

							if ( $newbugid != - 1 ) {
								$successfulimport += 1;
								$message = '9';

								if ( $data[1] != '' ) {
									wp_set_post_terms( $newbugid, $data[1], "bug-library-products" );
								}

								if ( $data[3] != '' ) {
									wp_set_post_terms( $newbugid, $data[3], "bug-library-status" );
								}

								if ( $data[0] != '' ) {
									wp_set_post_terms( $newbugid, $data[0], "bug-library-types" );
								}

								if ( $data[2] != '' ) {
									update_post_meta( $newbugid, "bug-library-product-version", $data[2] );
								}

								if ( $data[6] != '' ) {
									update_post_meta( $newbugid, "bug-library-reporter-name", $data[6] );
								}

								if ( $data[7] != '' ) {
									update_post_meta( $newbugid, "bug-library-reporter-email", $data[7] );
								}

								if ( $data[10] != '' ) {
									update_post_meta( $newbugid, "bug-library-resolution-date", $data[10] );
								}

								if ( $data[11] != '' ) {
									update_post_meta( $newbugid, "bug-library-resolution-version", $data[11] );
								}

								if ( $data[12] != '' ) {
									wp_set_post_terms( $newbugid, $data[12], "bug-library-priority" );
								}

							}
						} else {
							$messages[] = '4';
						}
					}
				}
			}

			if ( isset( $_POST['firstrowheaders'] ) ) {
				$row -= 1;
			}

			$message = '9';
		} else {
			$statusterm                         = get_term_by( 'id', $_POST['bug-library-status'], 'bug-library-status' );
			$genoptions['defaultuserbugstatus'] = $statusterm->name;

			$closedstatusterm              = get_term_by( 'id', $_POST['bug-library-closed-status'], 'bug-library-status' );
			$genoptions['bugclosedstatus'] = $closedstatusterm->name;

			$priorityterm                         = get_term_by( 'id', $_POST['bug-library-priority'], 'bug-library-priority' );
			$genoptions['defaultuserbugpriority'] = $priorityterm->name;

			if ( ( ! isset( $genoptions['allowattach'] ) || false == $genoptions['allowattach'] ) && isset( $_POST['allowattach'] ) ) {
				$uploads = wp_upload_dir();

				if ( ! file_exists( $uploads['basedir'] ) ) {
					$message                   = 2;
					$genoptions['allowattach'] = false;
				} elseif ( ! is_writable( $uploads['basedir'] ) ) {
					$message                   = 3;
					$genoptions['allowattach'] = false;
				} else {
					if ( ! file_exists( $uploads['basedir'] . '/bug-library' ) ) {
						mkdir( $uploads['basedir'] . '/bug-library' );
					}

					$genoptions['allowattach'] = true;
				}
			} elseif ( ! isset( $_POST['allowattach'] ) ) {
				$genoptions['allowattach'] = false;
			}

			foreach (
				array(
					'entriesperpage',
					'bugnotifytitle',
					'permalinkpageid',
					'rolelevel',
					'editlevel'
				) as $option_name
			) {
				if ( isset( $_POST[ $option_name ] ) ) {
					$genoptions[ $option_name ] = $_POST[ $option_name ];
				}
			}

			foreach (
				array(
					'moderatesubmissions',
					'showcaptcha',
					'requirelogin',
					'newbugadminnotify',
					'firstrowheaders',
					'showpriority',
					'showreporter',
					'showassignee',
					'requirename',
					'requireemail',
					'hideproduct',
					'hideversionnumber',
					'hideissuetype',
					'closecommentsonclosure'
				) as $option_name
			) {
				if ( isset( $_POST[ $option_name ] ) ) {
					$genoptions[ $option_name ] = true;
				} else {
					$genoptions[ $option_name ] = false;
				}
			}

			update_option( 'BugLibraryGeneral', $genoptions );

			if ( $message == '' ) {
				$message = 1;
			}
		}

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		wp_redirect( $this->remove_querystring_var( $_POST['_wp_http_referer'], 'message' ) . "&message=" . $message . ( isset( $row ) && $row != 0 ? "&importrowscount=" . $row : '' ) . ( $successfulimport != 0 ? "&successimportcount=" . $successfulimport : "" ) );
	}

	//executed if the post arrives initiated by pressing the submit button of form
	function on_save_changes_stylesheet() {
		//user permission check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Not allowed', 'bug-library' ) );
		}
		//cross check the given referer
		check_admin_referer( 'bug-library' );

		$message = '';
		global $wpdb;

		if ( isset( $_POST['submitstyle'] ) ) {
			$genoptions = get_option( 'BugLibraryGeneral' );
			$genoptions = wp_parse_args( $genoptions, $this->bl_reset_gen_settings( 'return' ) );

//			$genoptions['fullstylesheet'] = $_POST['fullstylesheet'];

			update_option( 'BugLibraryGeneral', $genoptions );
			$message = 1;
		} elseif ( isset( $_POST['resetstyle'] ) ) {
			$genoptions = get_option( 'BugLibraryGeneral' );
			$genoptions = wp_parse_args( $genoptions, $this->bl_reset_gen_settings( 'return' ) );

			$stylesheetlocation = BLDIR . '/stylesheet.css';
			if ( file_exists( $stylesheetlocation ) ) {
//				$genoptions['fullstylesheet'] = wp_remote_fopen( $stylesheetlocation );
			}

			update_option( 'BugLibraryGeneral', $genoptions );

			$message = 2;
		}

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		$cleanredirecturl = $this->remove_querystring_var( $_POST['_wp_http_referer'], 'message' );

		if ( $message != '' ) {
			$cleanredirecturl .= "&message=" . $message;
		}

		wp_redirect( $cleanredirecturl );
	}

	//executed if the post arrives initiated by pressing the submit button of form
	function on_save_changes_instructions() {
		//user permission check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Not allowed', 'bug-library' ) );
		}
		//cross check the given referer
		check_admin_referer( 'bug-library' );

		wp_redirect( $this->remove_querystring_var( $_POST['_wp_http_referer'], 'message' ) . "&message=1" );
	}

	function general_meta_box( $data ) {
		$genoptions = $data['genoptions'];

		?>
		<table>
			<tr>
				<td style='vertical-align: top; padding-right: 10px;'>
					<table>
						<tr>
							<td style='width: 200px'>Number of entries per page</td>
							<td>
								<input style="width:100%" type="text" name="entriesperpage" <?php echo "value='" . $genoptions['entriesperpage'] . "'"; ?>/>
							</td>
						</tr>
						<tr>
							<td class='bltooltip' title='Must re-apply permalink rules for this option to take effect'>Parent page (for permalink structure)</td>
							<td class='bltooltip' title='Must re-apply permalink rules for this option to take effect'>
								<?php $pages = get_pages( array( 'parent' => 0, 'sort_column' => 'post_title' ) );

								if ( $pages ): ?>
									<select name='permalinkpageid' style='width: 200px'>
										<option value='-1'>Default (bugs)</option>
										<?php foreach ( $pages as $page ):
											if ( $page->ID == $genoptions['permalinkpageid'] ) {
												$selectedterm = "selected='selected'";
											} else {
												$selectedterm = '';
											} ?>

											<option value='<?php echo $page->ID; ?>' <?php echo $selectedterm; ?>><?php echo $page->post_title; ?></option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td>Show bug priorities</td>
							<td>
								<input type="checkbox" id="showpriority" name="showpriority" <?php if ( $genoptions['showpriority'] ) {
									echo ' checked="checked" ';
								} ?>/></td>
						</tr>
						<tr>
							<td>Show reporter name</td>
							<td>
								<input type="checkbox" id="showreporter" name="showreporter" <?php if ( $genoptions['showreporter'] ) {
									echo ' checked="checked" ';
								} ?>/></td>
						</tr>
						<tr>
							<td>Show assigned user</td>
							<td>
								<input type="checkbox" id="showassignee" name="showassignee" <?php if ( $genoptions['showassignee'] ) {
									echo ' checked="checked" ';
								} ?>/></td>
						</tr>
						<tr>
							<td>Minimum role for bug assignment</td>
							<td>
								<?php global $wp_roles;
								if ( $wp_roles ):?>
									<select name='rolelevel' style='width: 200px'>
										<?php $roles = $wp_roles->roles;

										foreach ( $roles as $role ):
											if ( $genoptions['rolelevel'] == $role['name'] ) {
												$selectedterm = "selected='selected'";
											} else {
												$selectedterm = '';
											} ?>
											<option value='<?php echo $role['name']; ?>' <?php echo $selectedterm; ?>><?php echo $role['name']; ?></option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td>Minimum role to get bug edit link</td>
							<td>
								<?php if ( $wp_roles ): ?>
									<select name='editlevel' style='width: 200px'>
										<?php $roles = $wp_roles->roles;

										foreach ( $roles as $role ):
											if ( $genoptions['editlevel'] == $role['name'] ) {
												$selectedterm = "selected='selected'";
											} else {
												$selectedterm = '';
											} ?>
											<option value='<?php echo $role['name']; ?>' <?php echo $selectedterm; ?>><?php echo $role['name']; ?></option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	<?php }

	function general_meta_newissue_box( $data ) {
		$genoptions = $data['genoptions'];
		?>
		<table>
			<tr>
				<td style='width: 300px'>Moderate new submissions</td>
				<td>
					<input type="checkbox" id="moderatesubmissions" name="moderatesubmissions" <?php checked( $genoptions['moderatesubmissions'] ); ?>/>
				</td>
				<td style='width: 40px'></td>
				<td style='width: 300px'>Show Captcha in submission form</td>
				<td>
					<input type="checkbox" id="showcaptcha" name="showcaptcha" <?php checked( $genoptions['showcaptcha'] );; ?>/>
				</td>
			</tr>
			<tr>
				<td>Allow file attachments</td>
				<td>
					<input type="checkbox" id="allowattach" name="allowattach" <?php checked( $genoptions['allowattach'] ); ?>/>
				</td>
				<td></td>
				<td>Require login to submit new issues</td>
				<td>
					<input type="checkbox" id="requirelogin" name="requirelogin" <?php checked( $genoptions['requirelogin'] ); ?>/>
				</td>
			</tr>
			<tr>
				<td>Require Reporter Name</td>
				<td>
					<input type="checkbox" id="requirename" name="requirename" <?php checked( $genoptions['requirename'] ); ?>/>
				</td>
				<td></td>
				<td>Require Reporter E-mail</td>
				<td>
					<input type="checkbox" id="requireemail" name="requireemail" <?php checked( $genoptions['requireemail'] ); ?>/>
				</td>
			</tr>
			<tr>
				<td>Hide product selection field</td>
				<td>
					<input type="checkbox" id="hideproduct" name="hideproduct" <?php checked( $genoptions['hideproduct'] ); ?>/>
				</td>
				<td></td>
				<td>Hide version number field</td>
				<td>
					<input type="checkbox" id="hideversionnumber" name="hideversionnumber" <?php checked( $genoptions['hideversionnumber'] ); ?>/>
				</td>
			</tr>
			<tr>
				<td>Hide issue type field</td>
				<td>
					<input type="checkbox" id="hideissuetype" name="hideissuetype" <?php checked( $genoptions['hideissuetype'] ); ?>/>
				</td>
				<td></td>
				<td></td>
				<td>
				</td>
			</tr>
			<tr>
				<td>Default user bug status</td>
				<td>

					<?php $statusterms = get_terms( 'bug-library-status', 'orderby=name&hide_empty=0' );

					if ( $statusterms ): ?>
						<select name='bug-library-status' style='width: 200px'>
							<?php foreach ( $statusterms as $statusterm ):
								if ( $statusterm->name == $genoptions['defaultuserbugstatus'] ) {
									$selectedterm = "selected='selected'";
								} else {
									$selectedterm = '';
								} ?>

								<option value='<?php echo $statusterm->term_id; ?>' <?php echo $selectedterm; ?>><?php echo $statusterm->name; ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</td>
				<td></td>
				<td>Default user bug priority</td>
				<td>

					<?php $priorityterms = get_terms( 'bug-library-priority', 'orderby=name&hide_empty=0' );

					if ( $priorityterms ): ?>
						<select name='bug-library-priority' style='width: 200px'>
							<?php foreach ( $priorityterms as $priorityterm ): ?>
								<option value='<?php echo $priorityterm->term_id; ?>' <?php selected( isset( $genoptions['defaultuserbugpriority'] ) && $priorityterm->name == $genoptions['defaultuserbugpriority'] ); ?>><?php echo $priorityterm->name; ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>Closed bug status</td>
				<td>

					<?php $closedstatusterms = get_terms( 'bug-library-status', 'orderby=name&hide_empty=0' );

					if ( $closedstatusterms ): ?>
						<select name='bug-library-closed-status' style='width: 200px'>
							<?php foreach ( $closedstatusterms as $closedstatusterm ): ?>
								<option value='<?php echo $closedstatusterm->term_id; ?>' <?php selected( $closedstatusterm->name == $genoptions['bugclosedstatus'] ); ?>><?php echo $closedstatusterm->name; ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</td>
				<td></td>
				<td>Close comments on closed status</td>
				<td>
					<input type="checkbox" id="closecommentsonclosure" name="closecommentsonclosure" <?php checked( $genoptions['closecommentsonclosure'] ); ?>/>
				</td>
			<tr>
				<td>Notify admin of new bugs</td>
				<td>
					<input type="checkbox" id="newbugadminnotify" name="newbugadminnotify" <?php checked( $genoptions['newbugadminnotify'] ); ?>/>
				</td>
			</tr>
			<tr>
				<td class='bltooltip' title='Set the title of new bug e-mail notifications. Use variable %bugtitle% to be replaced by the new bug title.'>New bug notification title</td>
				<td colspan='4' class='bltooltip' title='Set the title of new bug e-mail notifications. Use variable %bugtitle% to be replaced by the new bug title.'>
					<input style="width:100%" type="text" size='80' name="bugnotifytitle" <?php echo "value='" . $genoptions['bugnotifytitle'] . "'"; ?>/>
				</td>
			</tr>
		</table>

	<?php }

	function general_importexport_meta_box( $data ) {
		$genoptions = $data['genoptions'];
		?>
		<table>
			<tr>
				<td>First Row Contains Headers</td>
				<td>
					<input type="checkbox" id="firstrowheaders" name="firstrowheaders" <?php if ( $genoptions['firstrowheaders'] ) {
						echo ' checked="checked" ';
					} ?>/></td>
			</tr>
			<tr>
				<td class='bltooltip' title='<?php _e( 'Allows for bugs to be added in batch to the Wordpress bugs database. CSV file needs to follow template for column layout.', 'bug-library' ); ?>' style='width: 330px'><?php _e( 'CSV file to upload to import bugs', 'bug-library' ); ?> (<a href="<?php echo plugins_url( 'importtemplate.csv', __FILE__ ); ?>"><?php _e( 'file template', 'bug-library' ); ?></a>)
				</td>
				<td><input size="80" name="bugsfile" type="file" /></td>
				<td><input type="submit" name="importbugs" value="<?php _e( 'Import Bugs', 'link-library' ); ?>" /></td>
			</tr>
		</table>
	<?php
	}

	function general_save_meta_box() {
		?>
		<div class="submitbox">
			<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save', 'bug-library' ); ?>" />
		</div>
	<?php
	}

	function stylesheet_meta_box( $data ) {
		return;
		$genoptions = $data['genoptions'];
		?>
<!-- 		<textarea name='fullstylesheet' id='fullstylesheet' style='font-family:Courier' rows="30" cols="90"><?php echo stripslashes( $genoptions['fullstylesheet'] ); ?></textarea> -->
		<div>
			<input type="submit" name="submitstyle" value="<?php _e( 'Submit', 'bug-library' ); ?>" /><input type="submit" name="resetstyle" value="<?php _e( 'Reset to default', 'bug-library' ); ?>" />
		</div>

	<?php
	}

	function instructions_meta_box() {
		?>
		<ol>
			<li>To get a basic Bug Library list showing on one of your Wordpress pages, create a new page and type the following text: [bug-library]</li>
			<li>Configure the Bug Library General Options section for more control over the plugin functionality.</li>
			<li>Copy the file single-bug-library-bugs.php from the bug-library plugin directory to your theme directory to display all information related to your bugs. You might have to edit this file a bit and compare it to single.php to get the proper layout to show up on your web site.</li>
		</ol>
	<?php
	}


	/******************************************** Print style data to header *********************************************/

	function bl_page_header() {
		$genoptions = get_option( 'BugLibraryGeneral' );
		$genoptions = wp_parse_args( $genoptions, $this->bl_reset_gen_settings( 'return' ) );

		// echo "<style id='BugLibraryStyle' type='text/css'>\n";
		// echo stripslashes( $genoptions['fullstylesheet'] );
		// echo "</style>\n";
	}

	function bl_admin_header() {
		wp_enqueue_style( 'datePickerstyle-css', plugins_url( 'css/ui-lightness/jquery-ui-1.8.4.custom.css', __FILE__ ) ); 
		wp_enqueue_script( 'datePicker-js', plugins_url( 'js/ui.datepicker.js', __FILE__ ), array( 'jquery' ) );
	}

	function bl_highlight_phrase( $str, $phrase, $tag_open = '<strong>', $tag_close = '</strong>' ) {
		if ( $str == '' ) {
			return '';
		}

		if ( $phrase != '' ) {
			return preg_replace( '/(' . preg_quote( $phrase, '/' ) . '(?![^<]*>))/i', $tag_open . "\\1" . $tag_close, $str );
		}

		return $str;
	}

	function BugLibrary(
		$entriesperpage = 10, $moderatesubmissions = true, $bugcategorylist = '', $requirelogin = false, $permalinkpageid = - 1,
		$showpriority = false, $showreporter = false, $showassignee = false, $shortcodebugtypeid = '', $shortcodebugstatusid = '', $shortcodebugpriorityid = ''
	) {

		global $wpdb;

		if ( isset( $_GET['bugid'] ) ) {
			$bugid = intval( $_GET['bugid'] );
			$view  = 'single';
		} else {
			$bugid = - 1;
			$view  = 'list';

			if ( isset( $_GET['bugpage'] ) ) {
				$pagenumber = intval( $_GET['bugpage'] );
			} else {
				$pagenumber = 1;
			}

			if ( isset( $_GET['bugcatid'] ) ) {
				$bugcatid = intval( $_GET['bugcatid'] );
			} else {
				$bugcatid = - 1;
			}

			if ( isset( $_GET['bugtypeid'] ) ) {
				$bugtypeid = intval( $_GET['bugtypeid'] );
			} elseif ( $shortcodebugtypeid != '' ) {
				$bugtypeid = $shortcodebugtypeid;
			} else {
				$bugtypeid = - 1;
			}

			if ( isset( $_GET['bugstatusid'] ) ) {
				$bugstatusid = intval( $_GET['bugstatusid'] );
			} elseif ( $shortcodebugstatusid != '' ) {
				$bugstatusid = $shortcodebugstatusid;
			} else {
				$bugstatusid = - 1;
			}

			if ( isset( $_GET['bugpriorityid'] ) ) {
				$bugpriorityid = intval( $_GET['bugpriorityid'] );
			} elseif ( $shortcodebugpriorityid != '' ) {
				$bugpriorityid = $shortcodebugpriorityid;
			} else {
				$bugpriorityid = - 1;
			}
		}

		$bugquery = "SELECT bugs.*, UNIX_TIMESTAMP(bugs.post_date) as bug_date_unix, pt.name as productname, pt.term_id as pid, st.name as statusname, ";
		$bugquery .= "st.term_id as sid, tt.name as typename, tt.term_id as tid, pt.slug as productslug, st.slug as statusslug, tt.slug as typeslug, tpr.name as priorityname ";
		$bugquery .= "FROM $wpdb->posts bugs LEFT JOIN " . $wpdb->get_blog_prefix() . "term_relationships trp ";
		$bugquery .= "ON bugs.ID = trp.object_id LEFT JOIN ";
		$bugquery .= $wpdb->get_blog_prefix() . "term_taxonomy ttp ON trp.term_taxonomy_id = ttp.term_taxonomy_id LEFT JOIN " . $wpdb->get_blog_prefix();
		$bugquery .= "terms pt ON ttp.term_id = pt.term_id LEFT JOIN " . $wpdb->get_blog_prefix() . "term_relationships trs ON bugs.ID = trs.object_id ";
		$bugquery .= "LEFT JOIN " . $wpdb->get_blog_prefix() . "term_taxonomy tts ON trs.term_taxonomy_id = tts.term_taxonomy_id LEFT JOIN " . $wpdb->get_blog_prefix();
		$bugquery .= "terms st ON tts.term_id = st.term_id LEFT JOIN " . $wpdb->get_blog_prefix() . "term_relationships trt ON bugs.ID = trt.object_id ";
		$bugquery .= "LEFT JOIN " . $wpdb->get_blog_prefix() . "term_taxonomy ttt ON trt.term_taxonomy_id = ttt.term_taxonomy_id LEFT JOIN " . $wpdb->get_blog_prefix();
		$bugquery .= "terms tt ON ttt.term_id = tt.term_id LEFT JOIN " . $wpdb->get_blog_prefix() . "term_relationships trpr ON bugs.ID = trpr.object_id ";
		$bugquery .= "LEFT OUTER JOIN " . $wpdb->get_blog_prefix() . "term_taxonomy ttpr ON trpr.term_taxonomy_id = ttpr.term_taxonomy_id LEFT OUTER JOIN " . $wpdb->get_blog_prefix();
		$bugquery .= "terms tpr ON ttpr.term_id = tpr.term_id ";

		$bugquery .= "WHERE bugs.post_type = 'bug-library-bugs' AND ttp.taxonomy = 'bug-library-products' ";
		$bugquery .= "AND tts.taxonomy = 'bug-library-status' AND ttt.taxonomy = 'bug-library-types' AND ttpr.taxonomy = 'bug-library-priority' ";
		$bugquery .= "AND bugs.post_status != 'trash' ";

		if ( $bugcategorylist != '' ) {
			$bugquery .= "AND pt.term_id in ('" . $bugcategorylist . "') ";
		}

		if ( $view == 'single' ) {
			if ( $bugid != - 1 ) {
				$bugquery .= " and ID = " . $bugid;
			}
		} elseif ( $view == 'list' ) {
			if ( $bugstatusid != - 1 ) {
				$bugquery .= " and tts.term_id = " . $bugstatusid;
			}

			if ( $bugcatid != - 1 ) {
				$bugquery .= " and ttp.term_id = " . $bugcatid;
			}

			if ( $bugtypeid != - 1 ) {
				$bugquery .= " and ttt.term_id = " . $bugtypeid;
			}

			if ( $bugpriorityid != - 1 ) {
				$bugquery .= " and ttpr.term_id = " . $bugpriorityid;
			}
		}

		if ( $moderatesubmissions == true ) {
			$bugquery .= " and bugs.post_status = 'publish' ";
		}

		$bugquery .= " order by bugs.post_date DESC";

		//echo $bugquery;

		$startingentry = ( $pagenumber - 1 ) * $entriesperpage;
		$quantity      = $entriesperpage + 1;

		$countbugsquery = str_replace( 'bugs.*, UNIX_TIMESTAMP(bugs.post_date) as bug_date_unix, pt.name as productname, pt.term_id as pid, st.name as statusname, st.term_id as sid, tt.name as typename, tt.term_id as tid, pt.slug as productslug, st.slug as statusslug, tt.slug as typeslug', 'count(*)', $bugquery );

		$bugscount = $wpdb->get_var( $countbugsquery );

		if ( $view == 'list' ) {
			$bugquery .= " LIMIT " . $startingentry . ", " . $quantity;
		}

		$bugs = $wpdb->get_results( $bugquery, ARRAY_A );

		//print_r($bugs);

		if ( $entriesperpage == 0 && $entriesperpage == '' ) {
			$entriesperpage = 10;
		}

		if ( count( $bugs ) > $entriesperpage ) {
			array_pop( $bugs );
			$nextpage = true;
		} else {
			$nextpage = false;
		}

		$preroundpages = $bugscount / $entriesperpage;
		$numberofpages = ceil( $preroundpages * 1 ) / 1;


		$output = "<div id='bug-library-list'>\n";
		$output .= "<div id='bug-library-showing'>Showing: ";

		$term_types = array('type', 'status', 'cat', 'priority');
		$term_pls = array('types', 'status', 'products', 'priority');		// status and priority use their sg as pls
		$term_alls = array('All Types', 'All Statuses', 'All Products', 'All Priorities');

		for ($i = 0; $i < count($term_types); $i++) 
		{ 
			$term_type = $term_types[$i];
			$term_pl = $term_pls[$i];
			$bug_id = "bug{$term_type}id";

			$term = get_terms( 'bug-library-'.$term_pl, 'orderby=name&hide_empty=0' );
//			echo $_SERVER['REQUEST_URI'];
		    $uri = $this->remove_querystring_var( $_SERVER['REQUEST_URI'], $bug_id );
//    	    var_dump($uri);
//  	    $uri .= (strpos($uri, '?') === false) ? '?' : '&';

    	    if (strpos($uri, '?') === false)
    	    {
    	    	if (strpos($uri, '&') === false)
    	    	{
    	    		$uri .= '?';
    	    	}	
    	    	else
    	    	{
    	    		// & but no ? --> Looks like an error. Make a replacement
    	    		$uri = str_replace('&', '?', $uri);
	   	    		$uri .= '&';
    	    	}
    	    }
    	    else
    	    {
    	    	$uri .= '&';
    	    }

		    $selects .= '<select class="default-select-style" onChange="window.location.href=this.value">';
			    $selects .= '<option '.(isset($_GET[$bug_id]) ? '' : 'selected').' value="'.$uri.'">-- '.$term_alls[$i].' --</option>';
				foreach ($term as $item)
					$selects .='<option '.($item->term_id == $_GET[$bug_id] ? 'selected' : '').' value="'.$uri.$bug_id.'='.$item->term_id.'">'.$item->name.'</option>';	
	      	$selects .= '</select>';
	      	$selects .= ' ';

		}

      	$output .= $selects;

		if ( $view == 'list' ) {
			// Filter List

			// if ( $bugcatid != - 1 ) {
			// 	$products = get_term_by( 'id', $bugcatid, "bug-library-products", ARRAY_A );
			// 	$filter_output .= "Products (" . $products['name'] . ")";
			// }

			// if ( $bugtypeid != - 1 ) {
			// 	if ( $bugcatid != - 1 ) {
			// 		$filter_output .= ", ";
			// 	}

			// 	$types = get_term_by( 'id', $bugtypeid, "bug-library-types", ARRAY_A );
			// 	$filter_output .= "Type (" . $types['name'] . ")";
			// }

			// if ( $bugstatusid != - 1 ) {
			// 	if ( ( $bugcatid != - 1 ) || ( $bugtypeid != - 1 ) ) {
			// 		$filter_output .= ", ";
			// 	}
			// 	$statuses = get_term_by( 'id', $bugstatusid, "bug-library-status", ARRAY_A );
			// 	$filter_output .= "Status (" . $statuses['name'] . ")";
			// }

			// if ( $bugpriorityid != - 1 ) {
			// 	if ( ( $bugcatid != - 1 ) || ( $bugtypeid != - 1 ) || ( $bugstatusid != - 1 ) ) {
			// 		$filter_output .= ", ";
			// 	}
			// 	$priorities = get_term_by( 'id', $bugpriorityid, "bug-library-priority", ARRAY_A );
			// 	$filter_output .= "Priority (" . $priorities['name'] . ")";
			// }

			// if ( ( $bugcatid == - 1 ) && ( $bugtypeid == - 1 ) && ( $bugstatusid == - 1 ) && ( $bugpriorityid == - 1 ) ) {
			// 	$filter_output .= "None";
			// }
			// else {
			// 	$filter_output .= ' | <a href="'.home_url().'/issue-tracker" title="Clear filters">Clear</a>';
			// 	$filter_output .= '</div>';
			// 	$output .= $filter_output;
			// }

			// $output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id='bug-library-filterchange'>Change Filter</span>";

			// $cleanuri = $this->remove_querystring_var( $_SERVER['REQUEST_URI'], "bugid" );
			// $cleanuri = $this->remove_querystring_var( $cleanuri, "bugcatid" );
			// $cleanuri = $this->remove_querystring_var( $cleanuri, "bugstatusid" );
			// $cleanuri = $this->remove_querystring_var( $cleanuri, "bugtypeid" );
			// $cleanuri = $this->remove_querystring_var( $cleanuri, "bugpriorityid" );

			// if ( $permalinkpageid != - 1 ) {
			// 	$parentpage = get_post( $permalinkpageid );
			// 	$parentslug = $parentpage->post_name;
			// } else {
			// 	$parentslug = 'issue-tracker';
			// }

			// $output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . home_url() .  "/" . $parentslug . "'>Remove all filters</a>";

			// $output .= "</div>";

			// if ( $view == 'list' && ( $requirelogin == false || is_user_logged_in() ) ) {
			// 	echo '<a href="'.get_site_url().'/wp-admin/post-new.php?post_type=bug-library-bugs">Add issue</a>';
			// }

			// $output .= "<div id='bug-library-filters'>";
			// $output .= "<div id='bug-library-filter-product'>";
			// $output .= "<div id='bug-library-filter-producttitle'>Products</div>";

			// $output .= "<div id='bug-library-filter-productitems'>";

			// $products = get_terms( 'bug-library-products', 'orderby=name&hide_empty=0' );

			// if ( $products ) {
			// 	$bugcaturi = $this->remove_querystring_var( $_SERVER['REQUEST_URI'], "bugcatid" );

			// 	if ( strpos( $bugcaturi, '?' ) === false ) {
			// 		if ( strpos( $bugcaturi, '&' ) === false ) {
			// 			$queryoperator = '?';
			// 		} elseif ( strpos( $bugcaturi, '&' ) !== false ) {
			// 			$ampersandpos  = strpos( $bugcaturi, '&' );
			// 			$bugcaturi     = preg_replace( '/&/', '?', $bugcaturi, 1 );
			// 			$queryoperator = '&';
			// 		}
			// 	} else {
			// 		$queryoperator = '&';
			// 	}

			// 	if ( $bugcatid == - 1 ) {
			// 		$output .= "<span id='bug-library-filter-currentproduct'>All Products</span><br />";
			// 	} else {
			// 		$output .= "<a href='" . $bugcaturi . "'>All Products</a><br />";
			// 	}

			// 	foreach ( $products as $product ) {
			// 		$bugcategoryarray = explode( ",", $bugcategorylist );

			// 		if ( ( $bugcategorylist != '' && in_array( $product->term_id, $bugcategoryarray ) ) || $bugcategorylist == '' ) {
			// 			if ( $product->term_id == $bugcatid ) {
			// 				$output .= "<span id='bug-library-filter-currentproduct'>" . stripslashes( $product->name ) . "</span><br />";
			// 			} else {
			// 				$output .= "<a href='" . $bugcaturi . $queryoperator . "bugcatid=" . $product->term_id . "'>" . stripslashes( $product->name ) . "</a><br />";
			// 			}
			// 		}
			// 	}
			// }

			// $output .= "</div></div>";

			// $output .= "<div id='bug-library-filter-types'>";
			// $output .= "<div id='bug-library-filter-typestitle'>Types</div>";

			// $output .= "<div id='bug-library-filter-typesitems'>";

			// $types = get_terms( 'bug-library-types', 'orderby=name&hide_empty=0' );

			// if ( $types ) {
			// 	$bugtypeuri = $this->remove_querystring_var( $_SERVER['REQUEST_URI'], "bugtypeid" );

			// 	if ( strpos( $bugtypeuri, '?' ) === false ) {
			// 		if ( strpos( $bugtypeuri, '&' ) === false ) {
			// 			$queryoperator = '?';
			// 		} elseif ( strpos( $bugtypeuri, '&' ) !== false ) {
			// 			$ampersandpos  = strpos( $bugtypeuri, '&' );
			// 			$bugtypeuri    = preg_replace( '/&/', '?', $bugtypeuri, 1 );
			// 			$queryoperator = '&';
			// 		}
			// 	} else {
			// 		$queryoperator = '&';
			// 	}

			// 	if ( $bugtypeid == - 1 ) {
			// 		$output .= "<span id='bug-library-filter-currentproduct'>All Types</span><br />";
			// 	} else {
			// 		$output .= "<a href='" . $bugtypeuri . "'>All Types</a><br />";
			// 	}

			// 	foreach ( $types as $type ) {
			// 		if ( $type->term_id == $bugtypeid ) {
			// 			$output .= "<span id='bug-library-filter-currentproduct'>" . stripslashes( $type->name ) . "</span><br />";
			// 		} else {
			// 			$output .= "<a href='" . $bugtypeuri . $queryoperator . "bugtypeid=" . $type->term_id . "'>" . stripslashes( $type->name ) . "</a><br />";
			// 		}
			// 	}
			// }

			// $output .= "</div></div>";

			// $output .= "<div id='bug-library-filter-status'>";
			// $output .= "<div id='bug-library-filter-statustitle'>Status</div>";

			// $output .= "<div id='bug-library-filter-statusitems'>";

			// $statuses = get_terms( 'bug-library-status', 'orderby=name&hide_empty=0' );

			// if ( $statuses ) {
			// 	$bugstatusuri = $this->remove_querystring_var( $_SERVER['REQUEST_URI'], "bugstatusid" );

			// 	if ( strpos($bugstatusuri, '?') === false ) {
			// 		if ( strpos( $bugstatusuri, '&' ) === false ) {
			// 			$queryoperator = '?';
			// 		} elseif ( strpos( $bugstatusuri, '&' ) !== false ) {
			// 			$ampersandpos  = strpos( $bugstatusuri, '&' );
			// 			$bugstatusuri  = preg_replace( '/&/', '?', $bugstatusuri, 1 );
			// 			$queryoperator = '&';
			// 		}
			// 	} else {
			// 		$queryoperator = '&';
			// 	}

			// 	if ( $bugstatusid == - 1 ) {
			// 		$output .= "<span id='bug-library-filter-currentstatus'>All Statuses</span><br />";
			// 	} else {
			// 		$output .= "<a href='" . $bugstatusuri . "'>All Statuses</a><br />";
			// 	}

			// 	foreach ( $statuses as $status ) {
			// 		if ( $status->term_id == $bugstatusid ) {
			// 			$output .= "<span id='bug-library-filter-currentproduct'>" . stripslashes( $status->name ) . "</span><br />";
			// 		} else {
			// 			$output .= "<a href='" . $bugstatusuri . $queryoperator . "bugstatusid=" . $status->term_id . "'>" . stripslashes( $status->name ) . "</a><br />";
			// 		}
			// 	}
			// }

			// $output .= "</div></div>";

			// $output .= "<div id='bug-library-filter-priorities'>";
			// $output .= "<div id='bug-library-filter-prioritiestitle'>Priorities</div>";

			// $output .= "<div id='bug-library-filter-prioritiesitems'>";

			// $priorities = get_terms( 'bug-library-priority', 'orderby=name&hide_empty=0' );

			// if ( $priorities ) {
			// 	$bugpriorityuri = $this->remove_querystring_var( $_SERVER['REQUEST_URI'], "bugpriorityid" );

			// 	if ( strpos( $bugpriorityuri, '?' ) === false ) {
			// 		if ( strpos( $bugpriorityuri, '&' ) === false ) {
			// 			$queryoperator = '?';
			// 		} elseif ( strpos( $bugpriorityuri, '&' ) !== false ) {
			// 			$ampersandpos   = strpos( $bugpriorityuri, '&' );
			// 			$bugpriorityuri = preg_replace( '/&/', '?', $bugpriorityuri, 1 );
			// 			$queryoperator  = '&';
			// 		}
			// 	} else {
			// 		$queryoperator = '&';
			// 	}

			// 	if ( $bugpriorityid == - 1 ) {
			// 		$output .= "<span id='bug-library-filter-currentpriorities'>All Priorities</span><br />";
			// 	} else {
			// 		$output .= "<a href='" . $bugpriorityuri . "'>All Priorities</a><br />";
			// 	}

			// 	foreach ( $priorities as $priority ) {
			// 		if ( $priority->term_id == $bugpriorityid ) {
			// 			$output .= "<span id='bug-library-filter-currentproduct'>" . stripslashes( $priority->name ) . "</span><br />";
			// 		} else {
			// 			$output .= "<a href='" . $bugpriorityuri . $queryoperator . "bugpriorityid=" . $priority->term_id . "'>" . stripslashes( $priority->name ) . "</a><br />";
			// 		}
			// 	}
			// }

			// $output .= "</div></div>";

			// $output .= "</div>";
		}

		if ( $bugs ) {
			$output .= "<div id='bug-library-item-table'>";

			$counter = 1;

			$output .= "<table><thead>";

			$output .= "<tr class='header-row'>";

			$output .= "<th>Issue #</th>
						<th>Summary</th>
						<th>Product</th>
						<th>Type</th>
						<th>Priority</th>
						<th>Status</th>
						<th>Creation Date</th>
						<th>Assigned To</th>
						<th class='blank-cell'></th>";

			$output .= "</tr>";

			$output .= "</thead>";

			foreach ( $bugs as $bug ) {
				$productversion    = get_post_meta( $bug['ID'], "bug-library-product-version", true );
				$reportername      = get_post_meta( $bug['ID'], "bug-library-reporter-name", true );
				$reporteremail     = get_post_meta( $bug['ID'], "bug-library-reporter-email", true );
				$resolutiondate    = get_post_meta( $bug['ID'], "bug-library-resolution-date", true );
				$resolutionversion = get_post_meta( $bug['ID'], "bug-library-resolution-version", true );
				$assigneduserid    = get_post_meta( $bug['ID'], "bug-library-assignee", true );
				$assigneedata = get_userdata( $assigneduserid );

				$permalink = get_permalink($bug['ID']);

				$dateformat = get_option( "date_format" );

				$output .= "<tr class='".lcfirst($bug['statusname']).'-row '.lcfirst($bug['priorityname'])."-row' id='" . ( $counter % 2 == 1 ? 'odd' : 'even' ) . "'>";

				$output .= "<td>".$bug['ID']."</td>";
				$output .= "<td><a href='".$permalink."'>".$bug['post_title']."</a></td>";
				$output .= "<td>".$bug['productname']."</td>";
				$output .= "<td>".$bug['typename']."</td>";
				$output .= "<td class='".lcfirst($bug['priorityname'])."-cell'>".$bug['priorityname']."</td>";
				$output .= "<td>".$bug['statusname']."</td>";
				$output .= "<td>".date( $dateformat, $bug['bug_date_unix'] )."</td>";
				$output .= "<td>".$assigneedata->display_name."</td>";
				$output .= "<td class='blank-cell'><a href='".admin_url().'post.php/?action=edit&post='.$bug['ID']."'>Edit</a></td>";


				$output .= "</tr>";

/*
				<td id='bug-library-type'><div id='bug-library-type-" . $bug['typeslug'];

				$output .= "'>" . $bug['typename'] . "</div></td><td id='bug-library-title'><a href='" . get_permalink( $bug['ID'] ) . "'>" . stripslashes( $bug['post_title'] ) . "</a></td>";

				$output .= "</tr>";
				$output .= "<tr id='" . ( $counter % 2 == 1 ? 'odd' : 'even' ) . "'><td id='bug-library-data' colspan='2'>ID: <a href='" . get_permalink( $bug['ID'] ) . "'>";
				$output .= $bug['ID'] . "</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Product: " . $bug['productname'];
				$output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Version: " . ( $productversion != '' ? $productversion : 'N/A' );
				$output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Report Date: " . date( $dateformat, $bug['bug_date_unix'] ) . "</td></tr>";

				$output .= "<tr id='" . ( $counter % 2 == 1 ? 'odd' : 'even' ) . "'><td id='bug-library-data2' colspan='2'>Status: " . $bug['statusname'];

				if ( $showpriority ) {
					$output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Priority: " . $bug['priorityname'];
				}

				if ( $showreporter ) {
					$output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Reporter: " . $reportername;
				}

				$output .= "</td></tr>";

				if ( $showassignee && $assigneduserid != - 1 && $assigneduserid != '' ) {
					$output .= "<tr id='" . ( $counter % 2 == 1 ? 'odd' : 'even' ) . "'><td id='bug-library-data' colspan='2'>\n";
					$firstname    = get_user_meta( $bug['ID'], 'first_name', true );
					$lastname     = get_user_meta( $bug['ID'], 'last_name', true );
					$assigneedata = get_userdata( $assigneduserid );

					$output .= "Assigned to: ";

					if ( $firstname != '' || $lastname != '' ) {
						$output .= $firstname . " " . $lastname;
					} else {
						$output .= $assigneedata->user_login;
					}

					$output .= "</td></tr>\n";
				}
*/
				$counter ++;

			}

			$output .= "</table>\n";

			$previouspagenumber = $pagenumber - 1;
			$nextpagenumber     = $pagenumber + 1;
			$dotbelow           = false;
			$dotabove           = false;

			$currentpageuri = $this->remove_querystring_var( $_SERVER['REQUEST_URI'], "bugpage" );
			$currentpageuri = $this->remove_querystring_var( $currentpageuri, "page_id" );

			if ( strpos( $currentpageuri, '?' ) === false ) {
				if ( strpos( $currentpageuri, '&' ) === false ) {
					$queryoperator = '?';
				} elseif ( strpos( $currentpageuri, '&' ) !== false ) {
					$ampersandpos   = strpos( $currentpageuri, '&' );
					$currentpageuri = preg_replace( '/&/', '?', $currentpageuri, 1 );
					$currentpageuri = '&';
				}
			} else {
				$queryoperator = '&';
			}

			if ( $numberofpages > 1 && $view == 'list' ) {
				$output .= "<div class='bug-library-pageselector'>";

				if ( $pagenumber != 1 ) {
					$output .= "<span class='bug-library-previousnextactive'>";

					$output .= "<a href='" . $currentpageuri . $queryoperator . "page_id=" . get_the_ID() . "&bugpage=" . $previouspagenumber . "'>" . __( 'Previous', 'bug-library' ) . "</a>";

					$output .= "</span>";
				} else {
					$output .= "<span class='bug-library-previousnextinactive'>" . __( 'Previous', 'bug-library' ) . "</span>";
				}

				for ( $counter = 1; $counter <= $numberofpages; $counter ++ ) {
					if ( $counter <= 2 || $counter >= $numberofpages - 1 || ( $counter <= $pagenumber + 2 && $counter >= $pagenumber - 2 ) ) {
						if ( $counter != $pagenumber ) {
							$output .= "<span class='bug-library-unselectedpage'>";
						} else {
							$output .= "<span class='bug-library-selectedpage'>";
						}

						$output .= "<a href='" . $currentpageuri . $queryoperator . "page_id=" . get_the_ID() . "&bugpage=" . $counter . "'>" . $counter . "</a>";

						$output .= "</a></span>";
					}

					if ( $counter >= 2 && $counter < $pagenumber - 2 && $dotbelow == false ) {
						$output .= "...";
						$dotbelow = true;
					}

					if ( $counter > $pagenumber + 2 && $counter < $numberofpages - 1 && $dotabove == false ) {
						$output .= "...";
						$dotabove = true;
					}
				}

				if ( $pagenumber != $numberofpages ) {
					$output .= "<span class='bug-library-previousnextactive'>";

					$output .= "<a href='" . $currentpageuri . $queryoperator . "page_id=" . get_the_ID() . "&bugpage=" . $nextpagenumber . "'>" . __( 'Next', 'bug-library' ) . "</a>";

					$output .= "</span>";
				} else {
					$output .= "<span class='bug-library-previousnextinactive'>" . __( 'Next', 'bug-library' ) . "</span>";
				}

				$output .= "</div>";
			}

			$output .= "</div>";
		} else {
			$output .= "<div id='bug-library-item-table'>";
			$output .= "<span class='no-issues'>There are 0 issues to view based on the currently selected filters.</span>";
			$output .= "</div>";
		}

		$output .= "</div>";
		
		// $output .= "<SCRIPT LANGUAGE='JavaScript'>";
		// $output .= "/* <![CDATA[ */";
		// $output .= "jQuery(document).ready(function() {";
		// $output .= "\tjQuery('#bug-library-filterchange').click(function() { jQuery('#bug-library-filters').slideToggle('slow'); });";

		// $querystring = '';

		// if ( $bugcatid != - 1 ) {
		// 	$querystring = "?bugcatid=" . $bugcatid;
		// }

		// $output .= "\tjQuery('#submitnewissue').colorbox({href:'" . home_url() . ( empty( $querystring ) ? '?' : '&' ) . "bug_library_popup_content=true" . $querystring . "', opacity: 0.3, iframe:true, width:'580px', height:'720px'});";
		// $output .= "});";
		// $output .= "/* ]]> */";
		// $output .= "</SCRIPT>";

		return $output;
	}


	/********************************************** Function to Process [bug-library] shortcode *********************************************/

	function bug_library_func( $atts ) {
		extract( shortcode_atts( array(
			'bugcategorylist' => '',
			'bugtypeid'       => '',
			'bugstatusid'     => '',
			'bugpriorityid'   => ''
		), $atts ) );

		$genoptions = get_option( 'BugLibraryGeneral' );
		$genoptions = wp_parse_args( $genoptions, $this->bl_reset_gen_settings( 'return' ) );
		
		return $this->BugLibrary( $genoptions['entriesperpage'], $genoptions['moderatesubmissions'], $bugcategorylist, $genoptions['requirelogin'],
			$genoptions['permalinkpageid'], $genoptions['showpriority'], $genoptions['showreporter'], $genoptions['showassignee'], $bugtypeid, $bugstatusid, $bugpriorityid );
	}


	function conditionally_add_scripts_and_styles( $posts ) {
		// if ( empty( $posts ) ) {
		// 	return $posts;
		// }

		// $load_jquery   = false;
		// $load_fancybox = false;
		// $load_style    = false;

		// if ( is_admin() ) {
		// 	$load_jquery   = false;
		// 	$load_fancybox = false;
		// 	$load_style    = false;
		// } else {
		// 	foreach ( $posts as $post ) {
		// 		$buglibrarypos = stripos( $post->post_content, 'bug-library' );
		// 		if ( $buglibrarypos !== false ) {
		// 			$load_jquery   = true;
		// 			$load_fancybox = true;
		// 			$load_style    = true;
		// 		}
		// 	}
		// }

		// global $blstylesheet;

		// if ( $load_style ) {
		// 	global $blstylesheet;
		// 	$blstylesheet = true;
		// } else {
		// 	global $blstylesheet;
		// 	$blstylesheet = false;
		// }

		// if ( $load_jquery ) {
		// 	wp_enqueue_script( 'jquery' );
		// }

		// if ( $load_fancybox ) {
		// 	wp_enqueue_script( 'colorbox', plugins_url( 'colorbox/jquery.colorbox-min.js', __FILE__ ), array( 'jquery' ), "1.3.9" );
		// 	wp_enqueue_style( 'colorboxstyle', plugins_url( 'colorbox/colorbox.css', __FILE__ ) );
		// }

		// return $posts;
	}

	function bl_template_redirect( $template ) {
		if ( ! empty( $_GET['bug_library_popup_content'] ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'submitnewissue.php';
			exit;
		} else {
			return $template;
		}
	}

}



$my_bug_library_plugin = new bug_library_plugin();

?>