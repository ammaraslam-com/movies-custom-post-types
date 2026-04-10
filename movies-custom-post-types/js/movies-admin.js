jQuery(document).ready(function($) {
    var frame;
    $('#upload_gallery_button').on('click', function(e) {
        console.log("Button clicked");
        e.preventDefault();
        // if (frame) {
        //     frame.open();
        //     console.log(frame);
        //     return;
        // }
        frame = wp.media({
            title: 'Select or Upload Gallery Images',
            button: {
                text: 'Use these images'
            },
            multiple: true  // Set to true to allow multiple file selections
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var image_ids = [];
            var preview_html = '';
            attachments.forEach(function(attachment) {
                image_ids.push(attachment.id);
                preview_html += '<img src="' + attachment.url + '" style="max-width:100px; margin-right:10px;" />';
            });
            $('#gallery_images').val(image_ids.join(','));
            $('#gallery_preview').html(preview_html);
        });

        frame.open();
    });
});
