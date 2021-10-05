jQuery(document).ready(function($){
    let i = 1;

    $('.lipnamo-cleanup').click(function(e){
        e.preventDefault();
        lipnamoCleanupItems();
    });

    function lipnamoCleanupItems(){
        const post_total = parseInt($('input[name="lipnamo_post_total"]').val()),
            post_type = $('select[name="lipnamo_post_type"]').val(),
            $progressBar = $('.lipnamo-progress-bar'),
            $wpbody = $("#wpbody");

        $wpbody.addClass("lipnamo-loading");
        $(".lipnamo-progress-wrapper").show();
        $(this).addClass('disabled');

        if(i >= post_total){
            return;
        }

        // Update progress total
        $('.lipnamo-progress-total').text(post_total);

        // Creating items
        $.ajax({
            url: lipnamo_items.ajax_url,
            type: 'POST',
            data: {
                action: 'lipnamo_cleanup_items',
                lipnamo_ajax_nonce: lipnamo_items.ajax_nonce,
                post_total: post_total,
                post_type: post_type,
                post_step: i
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
                i++;
                //go to next iteration of the loop
                lipnamoGenerateItems();
            }
        });

        $wpbody.removeClass("lipnamo-loading");
        $(this).removeClass('disabled');
    }
});