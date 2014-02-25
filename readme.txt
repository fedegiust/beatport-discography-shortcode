=== Plugin Name ===
Tags: beatport, discography, artist, dj, digital, electronic, music
Requires at least: 3.4
Tested up to: 3.8.1
Stable tag: 1.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Wordpress plugin that adds a shortcode on posts or pages to read the artist discography using the Beatport API. 

== Description ==

Wordpress plugin that adds a shortcode on posts or pages to read the artist discography using the Beatport API. 
This plugin gets the feed on request and on the fly, so there is no need to save in database or do any updates.
As soon as a release is out on beatport it will be on the list.

There are three different types of feed:
- Artist Feed
	- You can get list of tracks or releases
	- If getting list of tracks, you have the option to enable/disable the sound player.
- Label Feed
	- You can get list of tracks or releases
	- If getting list of tracks, you have the option to enable/disable the sound player.
- Release Details Feed
	- Gets a detailed view of a release by ID. You can find the release id on beatport.com in the URL.
		For example: If you go to beatport and click on a release, the url on your browser will be something like this http://www.beatport.com/release/sonntag/1092381. Where the ID is the numbers at the end of it.
		
It's very easy to use:

- Install the plugin
- Activate
- Create a new post or page
- Click the icon with the green Beatport logo on the toolbar
- Type in your artist name, or the artist name you want to show the discography on your wordpress site.

Done.

This version will get a JSON object with the following information

- Release Detail
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

After the plugin is active, there will be a new icon on the text editor toolbar. So if you go to add a new page or post you will see the beatport green logo icon. If you click on it a new modal window will open asking for one of the following:

1. Type of feed do you want:
* Artist (Default)
* Label
* Release (By Id)

2. Which artist or label do you want:
* Artist Name (Default)
* Label Name
* Release Id

3. Type of catalogue you want:
* Releases (Default)
* Tracks

4. Additional Options
* Enable/Disable buy on beatport link

One option of each group is required.

This will add a shortcode like this:

[beatport_discography_sc feed="artist" artist="Richie Hawtin" items="track" buylink="on"]

Shortcodes parameters
* feed: string with value "artist", "label" or "id"
* artist: string
* label: string
* id: number
* items: string with value "release" or "track"
* buylink: string with values "on" or "off"

After you filled out the form, click on insert shortcode and publish the page/post.

== Screenshots ==

1. New button on toolbar
2. Shortcode setup form
3. Shortcode setup form
4. Shortcode setup form with test results
5. Shortcode on post or page
6. Public view of the releases list
7. Public view of the release details
8. Public view of the tracks list

== Changelog ==

= 1.3.2 = 

* Bug fixes
* Make sure we display an error message when no results were returned.
* Add a try shortocde button on the plugin panel to test the shortcode and show the JSON result to make sure we get a correct result
* Make sure we format the Artist Name and Label Name correctly when setting up the shortcode.

= 1.3.1 =

* Bug fixes

= 1.3.0 =

* Bug fixes
* New url to get the json feed from
* Soundplayer has been temporarily removed because beatport removed the samples urls.

= 1.2.0 = 

* Bug fixes.
* Added more options to the admin panel when inserting the shortcode.
* Added options to get details of a release using the release ID from Beatport.

= 1.1.2 =

* Bug fixes.
* Make audio player optional when getting tracks.

= 1.1.1 =

* Bug fixes.
* Layout fixes.

= 1.1.0 =
* Option to fetch tracks or releases.
* Option to fetch artist catalogue or label catalogue.
* Option to play audio samples from beatport.com using SoundManager 2 when displaying tracks.

= 1.0.1 =
* Bug fixes

= 1.0 =
* First release
