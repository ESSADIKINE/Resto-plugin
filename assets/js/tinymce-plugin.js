(function() {
    tinymce.PluginManager.add("lebonresto_button", function(editor, url) {
        editor.addButton("lebonresto_button", {
            title: "Le Bon Resto Map",
            icon: "dashicon dashicons-location-alt",
            onclick: function() {
                // Open a dialog to allow customization of shortcode attributes
                editor.windowManager.open({
                    title: 'Insert Le Bon Resto Map',
                    body: [
                        {
                            type: 'textbox',
                            name: 'width',
                            label: 'Width',
                            value: '100%'
                        },
                        {
                            type: 'textbox',
                            name: 'height',
                            label: 'Height',
                            value: '500px'
                        },
                        {
                            type: 'textbox',
                            name: 'zoom',
                            label: 'Zoom Level',
                            value: '12'
                        },
                        {
                            type: 'textbox',
                            name: 'center_lat',
                            label: 'Center Latitude',
                            value: '48.8566'
                        },
                        {
                            type: 'textbox',
                            name: 'center_lng',
                            label: 'Center Longitude',
                            value: '2.3522'
                        }
                    ],
                    onsubmit: function(e) {
                        // Build shortcode with attributes
                        let shortcode = '[lebonresto_map';
                        
                        if (e.data.width && e.data.width !== '100%') {
                            shortcode += ' width="' + e.data.width + '"';
                        }
                        
                        if (e.data.height && e.data.height !== '500px') {
                            shortcode += ' height="' + e.data.height + '"';
                        }
                        
                        if (e.data.zoom && e.data.zoom !== '12') {
                            shortcode += ' zoom="' + e.data.zoom + '"';
                        }
                        
                        if (e.data.center_lat && e.data.center_lat !== '48.8566') {
                            shortcode += ' center_lat="' + e.data.center_lat + '"';
                        }
                        
                        if (e.data.center_lng && e.data.center_lng !== '2.3522') {
                            shortcode += ' center_lng="' + e.data.center_lng + '"';
                        }
                        
                        shortcode += ']';
                        
                        // Insert the shortcode
                        editor.insertContent(shortcode);
                    }
                });
            }
        });
        
        // Add menu item as well
        editor.addMenuItem('lebonresto_map', {
            text: 'Le Bon Resto Map',
            context: 'insert',
            icon: 'dashicon dashicons-location-alt',
            onclick: function() {
                editor.execCommand('mceInsertContent', false, '[lebonresto_map]');
            }
        });
    });
})();
