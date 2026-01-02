"use strict";

(function($) {
    $('.upload_map_image_button').click(function(e) {
        e.preventDefault();

        let button = $(this);
        let imageField = $('#map_image');
        let preview = $('#map_image_preview');

        const frame = wp.media({
            title: 'Select or Upload Map Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            imageField.val(attachment.id);
            preview.html('<img src="' + attachment.url + '" style="max-width: 200px;">');
        });

        frame.open();
    });
})(jQuery);