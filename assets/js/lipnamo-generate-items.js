jQuery(document).ready(function($){
    $('.lipnamo-generate').click(function(e){
        e.preventDefault();

        $("#wpbody").addClass("lipnamo-loading");

        const post_items = $('input[name="lipnamo_post_items"]').val(),
            post_type = $('select[name="lipnamo_post_type"]').val(),
            post_author = $('select[name="lipnamo_post_author"]').val(),
            post_status = $('select[name="lipnamo_post_status"]').val(),
            post_thumbnails = $('input[name="lipnamo_thumbnails"]').val();

        $.ajax({
            url: lipnamo_items.ajax_url,
            type: 'POST',
            data: {
                action: 'lipnamo_generate_items',
                post_items: post_items,
                post_type: post_type,
                post_author: post_author,
                post_status: post_status,
                post_thumbnails: post_thumbnails
            },
            success: function(){
                $("#wpbody").removeClass("lipnamo-loading");
            }
        });
    });
});