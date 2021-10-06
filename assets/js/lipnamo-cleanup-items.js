jQuery(document).ready(function($){
    const $wpbody = $("#wpbody");

    // Update post total
    $('select[name="lipnamo_post_type"]').on('change', function(){
        const post_type = $('select[name="lipnamo_post_type"]').val(),
            $cleanupButton = $('.lipnamo-cleanup');

        $wpbody.addClass("lipnamo-loading");
        $cleanupButton.addClass('disabled');

        $.ajax({
            url: lipnamo_items.ajax_url,
            type: 'POST',
            data: {
                action: 'lipnamo_total_items',
                lipnamo_ajax_nonce: lipnamo_items.ajax_nonce,
                post_type: post_type
            },
            success: function(response){
                let result = JSON.parse(response),
                    post_total = parseInt(result.post_total);

                $('input[name="lipnamo_post_total"]').val(post_total);

                console.log(post_total);
            }
        });

        $wpbody.removeClass("lipnamo-loading");
        $cleanupButton.removeClass('disabled');
    });

    // AJAX Cleanup
    let ajaxIndex = 1;

    $('.lipnamo-cleanup').click(function(e){
        e.preventDefault();
        lipnamoCleanupItems();
    });

    function lipnamoCleanupItems(){
        const post_total = parseInt($('input[name="lipnamo_post_total"]').val()),
            post_type = $('select[name="lipnamo_post_type"]').val(),
            $progressBar = $('.lipnamo-progress-bar');

        $wpbody.addClass("lipnamo-loading");
        $(".lipnamo-progress-wrapper").show();
        $(this).addClass('disabled');

        if(ajaxIndex >= post_total){
            return;
        }

        // Update progress total
        $('.lipnamo-progress-total').text(post_total);

        // Deleting items
        $.ajax({
            url: lipnamo_items.ajax_url,
            type: 'POST',
            data: {
                action: 'lipnamo_cleanup_items',
                lipnamo_ajax_nonce: lipnamo_items.ajax_nonce,
                post_total: post_total,
                post_type: post_type,
                post_step: ajaxIndex
            },
            success: function(response){
                let result = JSON.parse(response),
                    step = parseInt(result.step),
                    percent = step * 100 / post_total;

                $('input[name="lipnamo-cleanup__step"]').val(step);

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

                //do your thing
                ajaxIndex++;
                //go to next iteration of the loop
                lipnamoCleanupItems();
            }
        });

        $wpbody.removeClass("lipnamo-loading");
        $(this).removeClass('disabled');
    }
});