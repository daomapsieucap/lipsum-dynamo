jQuery(document).ready(function($){
    let lipnamoCurrentStep = 1;

    $('.lipnamo-generate').click(function(e){
        e.preventDefault();
        lipnamoGenerateItems();
    });

    function lipnamoGenerateItems(){
        const post_total = parseInt($('input[name="lipnamo_post_total"]').val()),
            post_type = $('select[name="lipnamo_post_type"]').val(),
            post_author = $('select[name="lipnamo_post_author"]').val(),
            post_status = $('select[name="lipnamo_post_status"]').val(),
            post_thumbnails = $('input[name="lipnamo_thumbnails"]').val(),
            post_title_min = $('input[name="length_title_min"]').val(),
            post_title_max = $('input[name="length_title_max"]').val(),
            post_excerpt_min = $('input[name="length_excerpt_min"]').val(),
            post_excerpt_max = $('input[name="length_excerpt_max"]').val(),
            post_body_min = $('input[name="length_content_min"]').val(),
            post_body_max = $('input[name="length_content_max"]').val(),
            $progressBar = $('.lipnamo-progress-bar'),
            $wpbody = $("#wpbody");

        $wpbody.addClass("lipnamo-loading");
        $(".lipnamo-progress-wrapper").show();
        $(this).addClass('disabled');

        // stop when current step is greater than total posts
        if(lipnamoCurrentStep > post_total){
            return;
        }

        // Update progress total
        $('.lipnamo-progress-total').text(post_total);

        // Creating items
        $.ajax({
            url: lipnamo_items.ajax_url,
            type: 'POST',
            data: {
                action: 'lipnamo_generate_items',
                lipnamo_ajax_nonce: lipnamo_items.ajax_nonce,
                post_total: post_total,
                post_type: post_type,
                post_author: post_author,
                post_status: post_status,
                post_thumbnails: post_thumbnails,
                post_title_length: post_title_min + ',' + post_title_max,
                post_excerpt_length: post_excerpt_min + ',' + post_excerpt_max,
                post_body_length: post_body_min + ',' + post_body_max,
                post_step: i
            },
            success: function(response){
                let result = JSON.parse(response),
                    step = parseInt(result.step),
                    percent = step * 100 / post_total;

                $('input[name="lipnamo-generate__step"]').val(step);

                if(step < post_total){
                    $('.lipnamo-progress-step').text(step);
                    $progressBar.animate({
                        width: percent + '%'
                    }, 150);
                }else{
                    $progressBar.animate({
                        width: '100%'
                    }, 150);
                    $('.lipnamo-progress-text').text(result.message);
                }

                // increase step
                lipnamoCurrentStep++;

                //continue generating items
                lipnamoGenerateItems();
            }
        });

        $wpbody.removeClass("lipnamo-loading");
        $(this).removeClass('disabled');
    }
});