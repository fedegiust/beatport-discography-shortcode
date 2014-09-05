(function() {
    tinymce.create('tinymce.plugins.BeatportDiscographyShortcode', {
        
        init : function(ed, url) {
        
        	var popUpURL = url.replace('/js', '') + '/beatport-discography-shortcode-tinymce.html';
        	
			ed.addCommand('BeatportDiscographyShortcodePopUp', function() {
				ed.windowManager.open({
					url : popUpURL,
					width : 600,
					height : 500, 
					inline : 1
				});
			});

			ed.addButton('BeatportDiscographyShortcodeButton', {
				title : 'Beatport Discography Shortcode',
				image : url.replace('js', 'image') + '/beatport-shortcode-button.png',
				cmd : 'BeatportDiscographyShortcodePopUp'
			});
		}
    });
    tinymce.PluginManager.add('BeatportDiscographyShortcode', tinymce.plugins.BeatportDiscographyShortcode);
}());