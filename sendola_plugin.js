// http://www.tinymce.com/wiki.php/Creating_a_plugin
(function(){
	tinymce.create('tinymce.plugins.sendola', {

		init : function(ed, url) {
			
			ed.addCommand('addShortcode', function() {
				ed.windowManager.open({
						title : 'Sendola'
					, file : url + '/editor-popup.php'	// popup's content
					, width : 800
					, height: 400
					, inline: 1
				});
			});

			ed.addButton('sendola', {
					title : 'Sendola Shortcode'
				, cmd :   'addShortcode'
				, image : url + '/images/sendola-editor-icon.png'
			});
		}
	});

	// registers the plugin
	tinymce.PluginManager.add('sendola', tinymce.plugins.sendola);
})();