jQuery(document).ready(function($){
    $.ajax({
        url: lipnamo_items.ajax_url,
        type: 'POST',
        data: {
            action: 'lipnamo_generate_items',
            post_items: $('input[name="lipnamo_post_items"]').val(),
            post_type: $('input[name="lipnamo_post_type"]').val(),
            post_author: $('input[name="lipnamo_post_author"]').val(),
            post_status: $('input[name="lipnamo_post_status"]').val(),
            post_thumbnails: $('input[name="lipnamo_thumbnails"]').val(),
        },
        success: function(){
            $("#wpbody").removeClass("fa-loading");
        }
    });
});