<?php
/*
Plugin Name: Beatport Discography shortcode
Plugin URI: http://www.federicogiust.com/
Description: Embed Beatport Discography using shortcodes
Version: 1.0
Author: Federico Giust
Author URI: http://www.federicogiust.com
License: GPL2

Copyright 2013 Federico Giust  (email : info@federicogiust.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

if (!class_exists('BeatportDiscography_shortcode')):

class BeatportDiscography_shortcode {

	var $plugin_version = '100'; // version 1.0

	// Constructor / initialize plugin
	function BeatportDiscography_shortcode()
	{
		// Load our shortcode only on the frontend
		if ( !is_admin() )
		{
			// do_shortcode() is registered as a default filter on 'the_content' with a priority of 11.
			// To make videos appear on post excerpts follow this guide: http://bloggingsquared.com/blogging-tips/make-shortcodes-work-in-all-wordpress-post-excerpts/
			add_shortcode('beatport_discography_sc', array($this, 'output_html'));
			add_action('wp_enqueue_scripts', array($this, 'output_css'));
		}
		// Allow the button to appear on any rich text editor (i.e. text editor in a widget)
		else
		{
			add_action('admin_init', array($this, 'setup_tinymce_button'));
		}
	}


	function output_css()
	{
		wp_register_style('BeatportDiscographyShortcode', plugins_url('beatport-discography-shortcode.css', __FILE__));
		wp_enqueue_style('BeatportDiscographyShortcode');
	}


	function output_html( $atts, $content = null )
	{

		if ( !isset($this->is_feed) )
		{
			$this->is_feed = is_feed();
		}

		extract( shortcode_atts( array(
			// custom parameters
			'artist' => ''
   
		), $atts ) );

		// HTML OUTPUT
		$output = '';


		$json = file_get_contents('http://api.beatport.com/catalog/3/releases?facets[]=performerName:Federico+Giust&publishDateStart=2000-02-06&sortBy=publishDate%20desc&perPage=100');        
		$data = json_decode($json);
		$dataArray = (array) $data;
		
		$output .= '<ul class="releaselist">' . PHP_EOL;
		for ($i = 0; $i < count($dataArray['results']); $i++){
			$artistsTemp = (array) $dataArray['results'][$i] -> artists; 
			$output .= '<li class="releaserow">' . PHP_EOL;
			$output .= '<div id="release' . $dataArray['results'][$i] -> catalogNumber . '" class="release">' . PHP_EOL;
			$output .= '<div class="releaseart">' . PHP_EOL;
			$output .= '<img src="' . $dataArray['results'][$i] -> images -> medium -> url . '"/>' . PHP_EOL;
			$output .= '</div>' . PHP_EOL;
			$output .= '<div class="releaseinfo">' . PHP_EOL;
			$output .= '<span class="releasename">' . PHP_EOL;
			$output .= $dataArray['results'][$i] -> name . PHP_EOL;
			
			$output .= '</span>' . PHP_EOL;
			$output .= '<br />' . PHP_EOL;
			$output .= '<span class="releaseartists">' . PHP_EOL;
			for($j = 0; $j < count($artistsTemp); $j++){
				$output .= $artistsTemp[$j] -> name;
				if(count($artistsTemp)>0 && $j < count($artistsTemp)-1){
					$output .= ', ';
				}
			}
			$output .= '</span>' . PHP_EOL;
			$output .= '<br />' . PHP_EOL;
			$output .= '<span class="releasemoreinfo">' . PHP_EOL;
			$output .= $dataArray['results'][$i] -> catalogNumber . ' | ';
			$output .= $dataArray['results'][$i] -> label -> name . ' | ';
			$output .= $dataArray['results'][$i] -> releaseDate . ' | ' . PHP_EOL;
			$output .= '<a href="https://www.beatport.com/release/' . $dataArray['results'][$i] -> slug . '/' . $dataArray['results'][$i] -> id . '">Buy</a>';
			$output .= $dataArray['results'][$i] -> genres -> name;
			$output .= '</span>' . PHP_EOL;
			$output .= '</div>' . PHP_EOL;
			$output .= '</div>' . PHP_EOL;
			$output .= '</li>' . PHP_EOL;
		}
		$output .= '</ul>' . PHP_EOL;
		return $output;

	}


	function get_server_host()
	{
		// if (preg_match('/^http\:\/\/([a-zA-Z0-9\-\_\.]+\.)(?:youtube\.com|youtu\.be)/i', $url, $matches)) {
		//     return $matches[1];
		// }

		return 'api.beatport.com/catalog/3/releases';
	}

	
	// TinyMCE Button

	// Set up our TinyMCE button
	function setup_tinymce_button()
	{
		if (get_user_option('rich_editing') == 'true' && current_user_can('edit_posts')) {
			add_action('admin_print_scripts', array($this, 'output_tinymce_dialog_vars'));
			add_filter('mce_external_plugins', array($this, 'add_tinymce_button_script'));
			add_filter('mce_buttons', array($this, 'register_tinymce_button'));
		}
	}


	// Register our TinyMCE button
	function register_tinymce_button($buttons) {
		array_push($buttons, '|', 'BeatportDiscographyShortcodeButton');
		return $buttons;
	}


	// Register our TinyMCE Script
	function add_tinymce_button_script($plugin_array) {
		$plugin_array['BeatportDiscographyShortcode'] = plugins_url('tinymcebutton.js', __FILE__);
		return $plugin_array;
	}


	function output_tinymce_dialog_vars()
	{
		$data = array(
			'pluginVersion' => $this->plugin_version,
			'includesUrl' => includes_url(),
			'pluginsUrl' => plugins_url()
		);

		?>
		<script type="text/javascript">
		// <![CDATA[
			window.beatportDiscographyShortcodeDialogData = <?php echo json_encode($data); ?>;
		// ]]>
		</script>
		<?php
	}

}

// Create just one instance per request
new BeatportDiscography_shortcode();

endif;