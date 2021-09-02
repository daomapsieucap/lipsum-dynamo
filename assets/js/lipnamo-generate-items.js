jQuery(document).ready(function($){
    let i = 0;

    $('.lipnamo-generate').click(function(e){
        e.preventDefault();
        lipnamoGenerateItems();
    });

    function lipnamoGenerateItems(){
        $("#wpbody").addClass("lipnamo-loading");
        $(".lipnamo-generate__progress-wrapper").show();
        $(this).addClass('disabled');

        const post_total = parseInt($('input[name="lipnamo_post_total"]').val()),
            post_type = $('select[name="lipnamo_post_type"]').val(),
            post_author = $('select[name="lipnamo_post_author"]').val(),
            post_status = $('select[name="lipnamo_post_status"]').val(),
            post_thumbnails = $('input[name="lipnamo_thumbnails"]').val(),
            $progressBar = $('.lipnamo-generate__progress-bar');

        if(i >= post_total){
            return;
        }

        // Update progress total
        $('.lipnamo-generate__progress-total').text(post_total);

        // Creating items
        $.ajax({
            url: lipnamo_items.ajax_url,
            type: 'POST',
            data: {
                action: 'lipnamo_generate_items',
                post_total: post_total,
                post_type: post_type,
                post_author: post_author,
                post_status: post_status,
                post_thumbnails: post_thumbnails,
                post_step: i
            },
            success: function(response){
                let result = JSON.parse(response),
                    step = parseInt(result.step),
                    percent = step * 100 / post_total;

                $('input[name="lipnamo-generate__step"]').val(step);

                console.log(step);
                console.log(post_total);
                console.log(percent);

                if(step < post_total){
                    $('.lipnamo-generate__progress-step').text(step);
                    $progressBar.animate({
                        width: percent + '%'
                    }, 150);
                }else{
                    $progressBar.animate({
                        width: '100%'
                    }, 150);
                    $('.lipnamo-generate__progress-text').text(result.message);
                }

                //do your thing
                i++;
                //go to next iteration of the loop
                lipnamoGenerateItems();
            }
        });

        $("#wpbody").removeClass("lipnamo-loading");
        $(this).removeClass('disabled');
    }
});