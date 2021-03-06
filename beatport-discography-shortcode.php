<?php
/*
Plugin Name: Beatport Discography shortcode
Plugin URI: http://wordpress.org/plugins/beatport-discography-shortcode/
Description: Embed Beatport Discography using shortcodes
Version: 1.3.7
Author: Federico Giust
Author URI: http://www.tora-soft.com
License: GPL2

Copyright 2013 Federico Giust  (email : fede@tora-soft.com)

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

	var $plugin_version = '137'; // version 1.3.7
	public $beatport_url = 'http://www.beatport.com/';
	public $beatport_api_proxy = 'www.federicogiust.com/beatportapi/beatport_api.php';

	/**
	* Constructor / Initialize the plugin
	*
	* Pass the shortcode parameters to output_html to generate the output
	*
	* Get the beatport button for the admin side or the html for the public side
	*/
	function BeatportDiscography_shortcode()
	{
		// Load our shortcode only on the frontend
		if ( !is_admin() )
		{
			// Execute output_html to replace shortcode
			add_shortcode('beatport_discography_sc', array($this, 'output_html'));
			// Add css to the output
			add_action('wp_enqueue_scripts', array($this, 'output_css'));
			add_action('wp_enqueue_scripts', array($this, 'output_js'));
		}
		// Allow the button to appear on any rich text editor (i.e. text editor in a widget)
		else
		{
			// If we are on the dashboard enable the beatport button
			add_action('admin_init', array($this, 'setup_tinymce_button'));
		}
	}

	/**
	* Output CSS
	* This gets the name of the CSS file we use to add some styling to the list of items
	*/
	function output_css()
	{
		wp_register_style('BeatportDiscographyShortcode', plugins_url('css/beatport-discography-shortcode.css', __FILE__));
		wp_enqueue_style('BeatportDiscographyShortcode');
	}

	/**
	* Output JS
	* This gets the name of the JS file we use in the plugin
	*/
	function output_js()
	{
		wp_register_script('BeatportDiscographyShortcode', plugins_url('js/beatport-discography-shortcode.js', __FILE__), array( 'jquery' ));
		wp_enqueue_script('BeatportDiscographyShortcode');

	}

	/**
	* pretty_print_tracks
	* This function creates a formatted list of a release tracklist
	* It accepts two parameters:
	* $dataArray - Array with the tracklist
	* $short_info - boolean
	*/
	function pretty_print_tracks(array $dataArray, $short_info = False) {


		if (empty($dataArray)) {
			return 'Release not found';
		}

		$output = '';

		$tracks = $dataArray;

		$output .= '<div class="beatport-discography-results-release-tracks">';

		$i = 0;

		foreach ($tracks as $track) {

			$title = $track->title;
			if (isset($track->mixName)) {
				$title = str_replace('('.$track->mixName.')', '', $title);
			}
			$title_href = $this->beatport_url.'track/'.$track->slug.'/'.$track->id;
			$title_output = '<a target="_new" href="'.$title_href.'"><b>'.$title.'</b></a>';
			if (isset($track->mixName)) {
				$title_output .= ' <a target="_new" style="color:grey;" href="'.$title_href.'"> ( '.$track->mixName.' )</a>';
			}

			if (!$short_info) {

				$artist_list = array();
				foreach ($track->artists as $artist) {
					$artist_list[] = '<a target="_new" href="'.$this->beatport_url.'artist/'.$artist->slug.'/'.$artist->id.'" >'.$artist->name.'</a>';
				}
				$artist_output = implode(' ', $artist_list);

				$genre_list = array();
				foreach ($track->genres as $genre) {
					$genre_list[] = '<a target="_new" href="'.$this->beatport_url.'genre/'.$genre->slug.'/'.$genre->id.'" >'.$genre->name.'</a>';
				}
				$genre_output = implode(' ', $genre_list);
			}

			$tr_class = ($i % 2 == 0) ? 'even' : 'odd';
	
			$output .= '	<span class="track-'.$tr_class.'">
								<span class="track-number">'.$track->trackNumber.'</span>
								<span class="track-title">'.$title_output.'</span>';

			if (!$short_info) {
				$output .= '	<span class="track-timing">'.$track->length.' / '.$track->bpm.' BPM</span>	
								<span class="track-price">'.$track->price->display.'</span>	
							</span><br />
							<span class="track-'.$tr_class.'">
								<span class="track-artists">'.$artist_output.'</span>
								<span class="track-generes">'.$genre_output.'</span>';
								
				$output .= '</span>';
				$output .= '';
				$output .= '<br />';
			}
			$i++;
		}
  		$output .= '</div>';
		return $output;
	}

	function validateData(array $dataArray){
		
		$error = '';

		if ( empty($dataArray) || is_null($dataArray)) {
			$error .= 'No results found. Please verify your shortcode.<br />' . PHP_EOL;
		}

		if( $error != '' ){
			return $error;
		}

	}


	/**
	* Generate unordered list with the items from the feed.
	* Depending on the type of items we want, we generate the corresponding list.
	* This function takes three parameters
	* $items - Is the type of items to render (tracks or releases)
	* $feed - Wich feed are we using, artist or label
	* $dataArray - Array with the data we got from the API
	*/
	function getRenderedFeed($items, $feed, $soundplayer = 'off', $buylink = 'on', array $dataArray ){

		$output = '';
		$output .= '<div id="beatport-discography-results">' . PHP_EOL;
		$output .= '<ul class="beatport-discography-results-list">' . PHP_EOL;
		if($feed == 'artist' || $feed == 'label' ){

			$error = $this->validateData($dataArray['results']);

			if( !empty($error) ){
				return $error;
			}

			if($items == 'release'){
				/** If we want the releases we then use the releases object from the API (http://api.beatport.com/releases.html) */

				for ($i = 0; $i < count($dataArray['results']); $i++){
					$artistsTemp = (array) $dataArray['results'][$i] -> artists; 
					$output .= '<li class="beatport-discography-results-row">' . PHP_EOL;
					$output .= '<div id="release' . $dataArray['results'][$i] -> catalogNumber . '" class="beatport-discography-results-release">' . PHP_EOL;
					$output .= '<div class="beatport-discography-results-art">' . PHP_EOL;
					$output .= '<a href="https://www.beatport.com/release/' . $dataArray['results'][$i] -> slug . '/' . $dataArray['results'][$i] -> id . '" target="_new">' . PHP_EOL;
					$output .= '<img src="' . str_replace("{hq}", "", str_replace("{w}", "60", str_replace("{h}", "60", $dataArray['results'][$i]->dynamicImages->main->url)))  . '"/>' . PHP_EOL;
					$output .= '</a>' . PHP_EOL;
					$output .= '</div>' . PHP_EOL;
					$output .= '<div class="beatport-discography-results-releaseinfo">' . PHP_EOL;
					$output .= '<span class="releasename">' . PHP_EOL;
					$output .= '<a href="https://www.beatport.com/release/' . $dataArray['results'][$i] -> slug . '/' . $dataArray['results'][$i] -> id . '" target="_new">' . PHP_EOL;
					$output .= $dataArray['results'][$i] -> name . PHP_EOL;
					$output .= '</a>' . PHP_EOL;
					$output .= '</span>' . PHP_EOL;
					$output .= '<br />' . PHP_EOL;
					$output .= '<span class="beatport-discography-results-releaseartists">' . PHP_EOL;
					for($j = 0; $j < count($artistsTemp); $j++){
						$output .= $artistsTemp[$j] -> name;
						if(count($artistsTemp)>0 && $j < count($artistsTemp)-1){
							$output .= ', ';
						}
					}
					
					$output .= '</span>' . PHP_EOL;
					$output .= '<br />' . PHP_EOL;
					$output .= '<span class="beatport-discography-results-moreinfo">' . PHP_EOL;
					$genreTemp = (array) $dataArray['results'][$i]->genres; 
					for($j = 0; $j < count($genreTemp); $j++){
						$output .= $genreTemp[$j]->name;
						if(count($genreTemp)>0 && $j < count($genreTemp)-1){
							$output .= ', ';
						}
					}
					$output .= '<br />' . PHP_EOL;
					$output .= $dataArray['results'][$i] -> catalogNumber . ' | ';
					$output .= $dataArray['results'][$i] -> label -> name . ' | ';
					$output .= $dataArray['results'][$i] -> releaseDate . PHP_EOL;
					if($buylink == 'on'){
						$output .= ' | <a href="https://www.beatport.com/release/' . $dataArray['results'][$i] -> slug . '/' . $dataArray['results'][$i] -> id . '" target="_new">Buy</a>';
					}

					$output .= '</span>' . PHP_EOL;
					$output .= '</div>' . PHP_EOL;
					$output .= '</div>' . PHP_EOL;
					$output .= '</li>' . PHP_EOL;
				}

			}elseif ($items == 'track'){
				/** If we want the tracks, we then use the tracks object (http://api.beatport.com/tracks.html) */
				for ($i = 0; $i < count($dataArray['results']); $i++){
					$artistsTemp = (array) $dataArray['results'][$i] -> artists; 
					$genreTemp = (array) $dataArray['results'][$i] -> genres;
					
					$output .= '<li class="beatport-discography-results-">' . PHP_EOL;
					$output .= '<div id="release' . $dataArray['results'][$i] -> catalogNumber . '" class="beatport-discography-results-release">' . PHP_EOL;
					$output .= '<div class="beatport-discography-results-art">' . PHP_EOL;
					if ( $soundplayer == 'on' ){
						$output .= '<a href="http://geo-samples.beatport.com/lofi/' . $dataArray['results'][$i] -> id . '.LOFI.mp3" class="inline-playable" >' . PHP_EOL;
					}
					$output .= '<img src="' . $dataArray['results'][$i] -> images -> medium -> url . '"/>' . PHP_EOL;
					if ( $soundplayer == 'on' ){
						$output .= '</a>' . PHP_EOL;			
					}
					$output .= '</div>' . PHP_EOL;
					$output .= '<div class="beatport-discography-results-releaseinfo">' . PHP_EOL;
					$output .= '<span class="beatport-discography-results-releasename">' . PHP_EOL;
					$output .= '<a href="https://www.beatport.com/track/' . $dataArray['results'][$i] -> slug . '/' . $dataArray['results'][$i] -> id . '" target="_new">' . PHP_EOL;
					
					$output .= $dataArray['results'][$i] -> title . ' [' . $dataArray['results'][$i] -> length . '] - (' . $dataArray['results'][$i] -> bpm . ' bpm - ' . $dataArray['results'][$i] -> key -> shortName . ')' . PHP_EOL;
					$output .= '</a>' . PHP_EOL;
					
					$output .= '</span>' . PHP_EOL;
					$output .= '<br />' . PHP_EOL;		
					$output .= '<span class="beatport-discography-results-releaseartists">' . PHP_EOL;
					for($j = 0; $j < count($artistsTemp); $j++){
						$output .= $artistsTemp[$j] -> name;
						if(count($artistsTemp)>0 && $j < count($artistsTemp)-1){
							$output .= ', ';
						}
					}
					$output .= '</span>' . PHP_EOL;
					$output .= '<br />' . PHP_EOL;
					$output .= '<span class="beatport-discography-results-moreinfo">' . PHP_EOL;
					for($j = 0; $j < count($genreTemp); $j++){
						$output .= $genreTemp[$j] -> name;
						if(count($genreTemp)>0 && $j < count($genreTemp)-1){
							$output .= ', ';
						}
					}
					$output .= ' | ';		
					$output .= $dataArray['results'][$i] -> label -> name . ' | ';
					$output .= $dataArray['results'][$i] -> releaseDate . PHP_EOL;
					if($buylink == 'on'){
						$output .= ' | <a href="https://www.beatport.com/track/' . $dataArray['results'][$i] -> slug . '/' . $dataArray['results'][$i] -> id . '" target="_new">Buy</a>' . PHP_EOL;
					}
					$output .= '</span>' . PHP_EOL;
					$output .= '<br />' . PHP_EOL;

					$output .= '</div>' . PHP_EOL;
					$output .= '</div>' . PHP_EOL;
					$output .= '</li>' . PHP_EOL;
				}
			}elseif ($items == 'biography') {

				$bioData = (array) $dataArray['results'][0];
				$output .= '<div id="beatport-discography-biography>' . PHP_EOL;
				$output .= '<span id="beatport-discography-biography-name">' . PHP_EOL;
				$output .= '<a href="https://www.beatport.com/artist/' . $bioData['slug'] . '/' . $bioData['id'] . '" target="_new"><h3>' . $bioData['name'] . '</h3></a>' . PHP_EOL;
				$output .= '</span>' . PHP_EOL;
				$output .= '<br />' . PHP_EOL;
				$output .= '<span id="beatport-discography-biography-bio">' . PHP_EOL;
				$output .= $bioData['biography'] . PHP_EOL;
				$output .= '</span>' . PHP_EOL;
				$output .= '</div>' . PHP_EOL;


			}


		}elseif ($feed == 'id') {

			$error = $this->validateData( (array) $dataArray['results'] -> release);

			if( !empty($error) ){
				return $error;
			}

			$metadata = $dataArray['results'] -> release;	

			$output = '';
			
			$dynamicImg = urldecode($metadata->dynamicImages->main->url);

			$dynamicImg = str_replace('{hq}', '', $dynamicImg);
			$img500 = str_replace('{w}x{h}','500x500', $dynamicImg);
			$img212 = str_replace('{w}x{h}','212x212', $dynamicImg);

			$artist_list = array();
			$artist_names = array();
			foreach ($metadata->artists as $artist) {
				$artist_list[] = '<a target="_new" href="'.$this->beatport_url.'artist/'.$artist->slug.'/'.$artist->id.'" >'.$artist->name.'</a>';
				$artist_names[] = $artist->name;
				if($artist->type == 'artist') { $artist_original = $artist->name; }
			}
			$artist_output = implode(' ', $artist_list);
			$artist_names_output = implode(',  ', $artist_names);
	 
			$genre_list = array();
			foreach ($metadata->genres as $genre) {
				$genre_list[] = '<a target="_new" href="'.$this->beatport_url.'genre/'.$genre->slug.'/'.$genre->id.'" >'.$genre->name.'</a>';
			}
			$genre_output = implode(' ', $genre_list);

			$price = $metadata->price->value;

			$price_output = intval($price / 100);
			if ($price % 100 != 0) {
				$price_output .= ','.(($price % 100));
			}

			$output .= '';

			$output .= '<div class="beatport-discography-results">';

			$output .= 		'<div class="beatport-discography-results-detail-metadata">
								<div class="beatport-discography-results-album-intro">
									<div class="beatport-discography-results-album-title"><p>'.$artist_original . ' - ' . $metadata->name.'</p></div>
									<div class="beatport-discography-results-album-artist"><p>Artists: '.$artist_output.'</p></div>
								</div>
								<div class="beatport-discography-results-coverart-wrapper">
									<a target="_new" href="'.$this->beatport_url.'release/'.$metadata->slug.'/'.$metadata->id.'" data-full-image-url="'.$img500.'">
										<img class="beatport-discography-results-coverart" src="'.$img500.'" alt="'.$artist_names_output.' - '.$metadata->name.'" >
									</a>
								</div>';
			$output .= 			'<div class="beatport-discography-results-description">
									<table class="beatport-discography-results-meta-data">
										<colgroup>
											<col class="beatport-discography-results-meta-data-col1">
											<col class="beatport-discography-results-meta-data-col2">
										</colgroup>
									<tbody>
										<tr>
											<td class="beatport-discography-results-meta-data-label">Release Date</td>
											<td class="beatport-discography-results-meta-data-value">'.$metadata->releaseDate.'</td>
											</tr>
										<tr>
											<td class="beatport-discography-results-meta-data-label">Label</td>
											<td class="beatport-discography-results-meta-data-value"><a target="_new" href="'.$this->beatport_url.'/label/'.$metadata->label->slug.'/'.$metadata->label->id.'">'.$metadata->label->name.'</a></td>
										</tr>
										<tr><td class="beatport-discography-results-meta-data-label">Catalogue #</td>
											<td class="beatport-discography-results-meta-data-value">'.$metadata->catalogNumber.'</td>
										</tr>
										<tr><td class="beatport-discography-results-meta-data-label">Price</td>
											<td class="beatport-discography-results-meta-data-value">'.$metadata->price->symbol.' '.$price_output.'</td>
										</tr>
										<tr><td class="beatport-discography-results-meta-data-label">Genere</td>
											<td class="beatport-discography-results-meta-data-value">'.$genre_output.'</td>
										</tr>
									</tbody>
									</table>						
								</div>
								<div class="beatport-discography-results-description-album"><p>'.$metadata->description.'</p></div>
							</div>
						</div>
						<div style="clear:both;"></div>
						';

				$tracks = (array) $dataArray['results'] -> tracks;
				$output .= $this->pretty_print_tracks($tracks);

		}else{
			$output .= 'An error has ocurred.';
		}

		$output .= '</ul>' . PHP_EOL;
		$output .= '<span class="poweredBy">Powered by <a href="http://www.beatport.com" target="_blank">Beatport</a><br />
					THIS SITE (OR APPLICATION) IS NOT AFFILIATED WITH, MAINTAINED, ENDORSED OR SPONSORED BY BEATPORT, LLC OR ANY OF ITS AFFILIATES.</span>';
		$output .= '</div>' . PHP_EOL;
		return $output;
	}

	function getRemoteFile($url, $timeout = 10) {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);
		return ($file_contents) ? $file_contents : FALSE;
	}

	function getData($url, $qrystring){
		try{
			$json = $this->getRemoteFile( $url . $qrystring);              
			$data = json_decode($json);
			$dataArray = (array) $data;
			return $dataArray;
		}catch(Exception $e){
			exit( 'An error ocurred: ' .  $e->getMessage() . "<br />" );
		}

	}

	function output_html( $atts, $content = null )
	{

		if ( !isset($this->is_feed) )
		{
			$this->is_feed = is_feed();
		}

		extract( shortcode_atts( array(
			// custom parameters
			'feed' => '',
			'artist' => '',
			'label' => '',
			'id' => '',
   			'items' => '',
   			'soundplayer' => '',
   			'buylink' => '',
   			'perpage' => ''
		), $atts ) );

		// HTML OUTPUT
		$output = '';

		$urlhost = 'http://' . $this -> get_server_host();
		
		$url = '';

		if( !array_key_exists('items', $atts)) {
			$atts['items'] = 'release';
		}

		if( isset($atts['items']) && $atts['items'] == 'release'){	
			if($atts['feed'] == 'artist' || $atts['feed'] == 'label'){
				$url .= 'releases';	
			}else{
				$url .= 'beatport/release';
			}
		}elseif(isset($atts['items']) && $atts['items'] == 'track'){
			if($atts['feed'] == 'artist' || $atts['feed'] == 'label'){
				$url .= 'tracks';	
			}else{
				$url .= 'beatport/track';
			}
		}elseif (isset($atts['items']) && $atts['items'] == 'biography') {
			$url .= 'artists';
		}
		// update here ucword
		if(isset($atts['feed']) && $atts['feed'] == 'artist'){
			$url .= '';
			$qrystring = '?facets=performerName:' . str_replace(' ', '+', ucwords((trim($atts['artist'])))) . '&sortBy=publishDate%20desc&perPage=150';
			
		}elseif(isset($atts['feed']) && $atts['feed'] == 'label'){
			$url .= '';
			$qrystring = '?facets=labelName:' . str_replace(' ', '+', ucwords((trim($atts['label'])))) . '&sortBy=publishDate%20desc&perPage=150';
		}elseif(isset($atts['feed']) && $atts['feed'] == 'id'){
			$url .= '';
			$qrystring = '?id=' . str_replace(' ', '+', $atts['id']).'';
		}

		if(isset($_GET['debug']) && $_GET['debug'] == 'y'){
			echo '<pre>';
			print_r($atts);
			echo $urlhost . $qrystring . '&url=' . $url;
			echo '</pre>';
		}
var_dump(extension_loaded('OAuth'));

		$dataArray = $this->getData($urlhost, $qrystring . '&url=' . $url);

		$output .= $this->getRenderedFeed($atts['items'], $atts['feed'], $atts['soundplayer'], $atts['buylink'], $dataArray);
		return $output;

	}


	function get_server_host()
	{
		$api_url = $this->beatport_api_proxy;
		return $api_url;
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
		$plugin_array['BeatportDiscographyShortcode'] = plugins_url('js/tinymcebutton.js', __FILE__);
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