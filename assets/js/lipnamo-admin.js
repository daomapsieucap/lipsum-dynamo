jQuery(document).ready(function($){
    /**
     * Upload field
     */
    // show hide preview
    $('.lipnamo-input__img input[type="text"]').on('change', function(){
        const preview = $(this).closest('fieldset').find('img');
        if(!$(this).val()){
            preview.hide();
        }else{
            preview.show();
        }
    });

    // preview
    $('.lipnamo-upload').each(function(){
        const upload_element = $(this),
            preview = upload_element.closest('fieldset').find('.lipnamo-preview__list');

        let custom_uploader;

        upload_element.click(function(e){
            e.preventDefault();
            //If the uploader object has already been created, reopen the dialog
            if(custom_uploader){
                custom_uploader.open();
                return;
            }
            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: 'add'
            });
            //When a file is selected, grab the URL and set it as the text field's value
            custom_uploader.on('select', function(){
                const attachment = custom_uploader.state().get('selection').toJSON(),
                    $thumbnails = $('#lipnamo-thumbnails');

                let thumbnail_val = $thumbnails.val();

                if(attachment){
                    for(let i = 0; i < attachment.length; i++){
                        thumbnail_val = $thumbnails.val();
                        if(thumbnail_val){
                            $thumbnails.val(thumbnail_val + ',' + attachment[i].id);
                            let thumbnail_array = thumbnail_val.split(",").map(Number);
                            if(!thumbnail_array.includes(attachment[i].id)){
                                preview.append('<li><span><img src="' + attachment[i].url + '" /></span></li>');
                            }
                        }else{
                            $thumbnails.val(attachment[i].id);
                            preview.append('<li><span><img src="' + attachment[i].url + '" /></span></li>');
                        }
                    }
                }
            });
            //Show selected items when open media popup
            custom_uploader.on('open', function(){
                const selection = custom_uploader.state().get('selection'),
                    ids_value = $('#lipnamo-thumbnails').val();

                if(ids_value.length > 0){
                    var ids = ids_value.split(',');

                    ids.forEach(function(id){
                        let attachment = wp.media.attachment(id);
                        attachment.fetch();
                        selection.add(attachment ? [attachment] : []);
                    });
                }
            });
            //Open the uploader dialog
            custom_uploader.open();
        });
    });
});