=== Plugin Name ===
Tags: beatport, discography, artist, dj, digital, electronic, music
Requires at least: 3.4
Tested up to: 3.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Wordpress plugin that adds a shortcode on posts or pages to read the artist discography using the Beatport API. 

== Description ==

Wordpress plugin that adds a shortcode on posts or pages to read the artist discography using the Beatport API. 
This plugin gets the feed on request and on the fly, so there is no need to save in database or do any updates.
As soon as a release is out on beatport it will be on the list.

It's very easy to use:

- Install the plugin
- Activate
- Create a new post or page
- Click the icon with the green Beatport logo on the toolbar
- Type in your artist name, or the artist name you want to show the discography on your wordpress site.

Done.

This version will get all releases with the following information

- Release Art Cover
- Release Artists (If it's a compilation, this will get an array with all artists included in the compilation)
- Catalogue Number
- Label
- Release Date
- Buy link which will direct the user to the release page on beatport.com

This will be displayed on on or two columns depending on the page width.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I use it? =

After the plugin is active, there will be a new icon on the text editor toolbar. So if you go to add a new page or post you will see this icon. If you click on it a new modal window will open asking for artist name. Click on insert shortcode after typing the artist name and all done.

== Screenshots ==

1. New button on toolbar
2. Artist name form
3. Shortcode on post or page
4. Public view of the post

== Changelog ==
= 1.1.0 =
* Option to fetch tracks or releases.
* Option to fetch artist catalogue or label catalogue.
* Option to play audio samples from beatport.com using SoundManager 2 when displaying tracks.

= 1.0.1 =
* Bug fixes

= 1.0 =
* First release
