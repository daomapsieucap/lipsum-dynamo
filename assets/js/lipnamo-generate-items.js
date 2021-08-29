jQuery(document).ready(function($){
    $.ajax({
        url: lipnamo_items.ajax_url,
        type: 'POST',
        data: {
            action: 'lipnamo_generate_items',
            post_type: $('input[name="post_type"]').val(),
            post_status: $('input[name="post_status"]').val(),
        },
        success: function(){
            $("#wpbody").removeClass("fa-loading");
        }
    });
});