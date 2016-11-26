=== Bug Library ===
Contributors: jackdewey
Donate link: http://ylefebvre.ca/wordpress-plugins/bug-library
Tags: bug, issue, tracker, manager, enhancement, feature, request, page, feature, custom, posts, type
Requires at least: 3.0
Tested up to: 4.4
Stable tag: trunk

This plugin provides an easy way to incorporate a bug/enhancement tracking system to a WordPress site. By adding a shortcode to a page, users will be able to display a bug list and allow visitors to submit new bugs / enhancements. The plugin will also provide search and sorting capabilities. A captcha and approval mechanism will allow the site admin to avoid spam. 

== Description ==

This plugin provides an easy way to incorporate a bug/enhancement tracking system to a WordPress site. By adding a shortcode to a page, users will be able to display a bug list and allow visitors to submit new bugs / enhancements. The plugin will also provide search and sorting capabilities. A captcha and approval mechanism will allow the site admin to avoid spam. 

* [Changelog](http://wordpress.org/extend/plugins/bug-library/other_notes/)
* [Support Forum](http://wordpress.org/tags/bug-library)

== Installation ==

1. Download the plugin
1. Upload the extracted folder to the /wp-content/plugins/ directory
1. Activate the plugin in the Wordpress Admin
1. To get a basic Bug Library list showing on one of your Wordpress pages, create a new page and type the following text: [bug-library]

There are a number of optional arguments that can be specified with the shortcode. Here they are with examples for each:

[bug-library bugcategorylist='3,4,5'] = List of bug categories to display
[bug-library bugtypeid='4'] = List of bugs from a specific category
[bug-library bugstatusid='5'] = List of bugs that have a specific status
[bug-library bugpriorityid='6'] = List of bugs that have a specific priority

These shortcode options can be combined:

[bug-library bugcategorylist='3,4,5' bugtypeid='4' bugstatusid='5' bugpriorityid='6']

1. Configure the Bug Library General Options section for more control over the plugin functionality.
1. Copy the file single-bug-library-bugs.php from the bug-library plugin directory to your theme directory to display all information related to your bugs. You might have to edit this file a bit and compare it to single.php to get the proper layout to show up on your web site.

== Changelog ==

= 1.4.5 =
* Re-wrote user query code for bug assignment to avoid issues on some installations

= 1.4.4 =
* Fixes to work on sites that are not installed at the root of a URL

= 1.4.3 =
* Fixes to work on sites that are not installed at the root of a URL

= 1.4.2 =
* Fixed issue accessing category menus

= 1.4.1 =
* Added options to be able to hide the product, version number and issue type fields

= 1.4 =
* Modified bug query for shortcode display to avoid displaying bugs that are in trash

= 1.3.9 =
* Re-arranged all menu items under Bugs menu in admin to make it easier to find all related items
* General code cleanup

= 1.3.8 =
* Changed file_get_contents for wp_remote_fopen

= 1.3.6 =
* Updated single item template for twenty-fifteen theme
* Corrected some issues with default options not being created correctly
* Added uninstall function
* Corrected label in admin

= 1.3.5 =
* Corrected PHP code warning

= 1.3.4 =
* Added new option to allow comments to be closed automatically when a user-defined closure status is assigned to a bug

= 1.3.3 =
* Fixed problem with activating file attachments

= 1.3.2 =
* Removed hard-coded image file extension when uploading attachments

= 1.3.1 =
* Corrected PHP warnings

= 1.3 =
* Changed mechanism to display the submit new issues popup

= 1.2.9 =
* Corrected PHP warnings

= 1.2.8 =
* Updated colorbox script to fix problem with black box when submitting new issues in latest version of WordPress

= 1.2.7 =
* Updated jQuery datapicker script to fix problem with latest versions of WordPress

= 1.2.6 =
* Fixed uncaught reference error in javascript code

= 1.2.5 =
* Added field to define default user bug priority in configuration panel
* New user reported issues now have a priority so they can appear in the list

= 1.2.4 =
* Added code to make sure that post data is available when saving custom bug data

= 1.2.3 =
* Added CDATA tags around javascript code
* Removed unnecessary quotes around PHP code to render meta boxes

= 1.2.2 =
* Removed reference to non-existent table in admin menu code

= 1.2.1 =
* Update to ensure compatibility with WordPress 3.3

= 1.2 =
* Added options to shortcode to allow users to specify bug priority, type and status as arguments

= 1.1.2 =
* Fixed issue with status field not display correct entry when editing bugs
* Modified join condition in bug display code to avoid upgrade issues with missing priorities

= 1.1.1 =
* Changed Upload Image option to Upload File. Changed code that displayed image to become link to attached file.
= 1.0.3 =
* Added options to make the reporter name and reporter e-mail required fields in the user issue submission form

= 1.0.2 =
* Corrected variable with bad name

= 1.0.1 =
* Added filters in admin bug list page to filters bugs by type, status and product
* Corrected problem with product, status and type getting deleted if you quick edited a bug

= 1.0 =
* First release of Bug Library

== Frequently Asked Questions ==

None at this time

== Screenshots ==

1. Bug Listing
2. Form to report new issues