# Wordpress Wiki Extended (WWE)

This is a theme and set of plugins I used to manage an internal wiki while working at the Space Avionics and Instrumentation Laboratory (SAIL) at Ryerson University in Canada. It is based in large part on the [WikiWP theme](http://wikiwp.com/), with a good deal of theme and plugin customization.

It's good for anyone looking for a wiki for their team or company, but is especially useful for academic teams working with MATLAB.

[See a demo](http://andreis.space/projects/wwe).

## Features

WWE has a few interesting features:

1. **HTTP authentication** - Users are authenticated using an Apache `.htpasswd` file rather than the build-in Wordpress authentication (this is easy to disable if you wish).

1. **Bug/Issue tracker** - Easy to keep track of and assign issues, feature requests, and bugs.

1. **MATLAB integration** - Upload figures and images to posts directly from MATLAB using a MATLAB class.

1. **Wikipedia-like formatting** - Styles are modelled to be similar to Wikipedia (it's aesthetically pleasing, but also people are used to the style).

1. **Regular-update post types** - Templated, auto-titled posts that allow for quick, regular team or division updates.

1. **LaTeX support** - Global embedded TeX support. Type your documents like you would in a TeX editor.

1. **Multiple authors** - Multiple people can author individual posts and papers.

1. **Academic Papers Support** - Custom post type for papers. Template for paper summaries, abstract and paper deadlines, links to other articles on the wiki, and paper attachments.

## Installation

Follow these instructions to install this wiki from scratch:

1. Clone this repository.

1. Download [Wordpress](https://wordpress.org/) into your project's root directory.

1. Fill in the `.htpasswd` file with your user authentication information.

1. From the root directory run `composer install` to download the parent theme and the non-edited plugins (you'll need to install [Composer](https://getcomposer.org) first).

1. Go through the Wordpress setup process (visit the project in a browser, follow the setup wizard's steps).

1. Log in and switch the Wordpress theme to "Wordpress Wiki Extended (WWE)".

1. Enable all plugins.

1. Configure plugins. This is a bit tedious, but I'm unsure of an automated way of doing this (short of importing an SQL export of the plugin options):
   - QuickLaTeX:
      - If you're not using TeX on your pages, disable this plugin. Otherwise...
      - Set "Use LaTeX Syntax Sitewide" to ON. This allows you to write LaTeX directly in your posts.
      - Add any additional LaTeX package imports
   - TOC+
      - I recommend enabling auto-insert on 'post', 'bug-library-bugs', 'update', and 'paper'

1. Install the Wordpress Importer and import `imports.xml` from the root directory. This file contains (a) default categories and taxonomies, (b) field groups used by the Advanced Custom Fields plugin, (c) default pages, and (d) some wiki maintenance and usage articles.

1. Set the front page to 'Home'. On the admin screen, go to 'Settings', then 'Reading', and modify 'Front page displays'.

1. Configure admin screen aesthetics. Short of doing an SQL dump, I'm not sure of how to import/export the Wordpress metaboxes. So this step is a bit more freeform, based on how you feel about the admin interface for each of the post types and the dashboard. Visit each of the admin pages and show/hide unnecessary metaboxes.

1. If you plan on using MATLAB with the wiki, modify the `url` and `opts.password` static properties in `matlab_wiki.m`.

## Support

For any questions, send me a message at andrei.khramtsov@gmail.com.