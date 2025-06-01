jQuery(document).ready(function($){
    let lipnamoCurrentStep = 1;

    $('.lipnamo-generate').click(function(e){
        e.preventDefault();
        lipnamoInitializeGeneration();
    });

    function lipnamoInitializeGeneration(){
        lipnamoCurrentStep = 1;
        const config = lipnamoGetFormConfig();

        if(!lipnamoValidateConfig(config)){
            return;
        }

        lipnamoSetupUI();
        lipnamoExecuteGeneration(config);
    }

    function lipnamoGetFormConfig(){
        return {
            total: parseInt($('input[name="lipnamo_post_total"]').val()) || 0,
            type: $('select[name="lipnamo_post_type"]').val(),
            author: $('select[name="lipnamo_post_author"]').val(),
            status: $('select[name="lipnamo_post_status"]').val(),
            thumbnails: $('input[name="lipnamo_thumbnails"]').val(),
            titleLength: lipnamoGetLengthRange('length_title_min', 'length_title_max'),
            excerptLength: lipnamoGetLengthRange('length_excerpt_min', 'length_excerpt_max'),
            bodyLength: lipnamoGetLengthRange('length_content_min', 'length_content_max')
        };
    }

    function lipnamoGetLengthRange(minName, maxName){
        const min = $(`input[name="${minName}"]`).val();
        const max = $(`input[name="${maxName}"]`).val();
        return `${min},${max}`;
    }

    function lipnamoValidateConfig(config){
        if(config.total <= 0){
            alert('Please enter a valid number of posts to generate.');
            return false;
        }
        return true;
    }

    function lipnamoSetupUI(){
        const $wpbody = $("#wpbody");
        const $progressWrapper = $(".lipnamo-progress-wrapper");
        const $generateBtn = $('.lipnamo-generate');

        $wpbody.addClass("lipnamo-loading");
        $progressWrapper.show();
        $generateBtn.addClass('disabled');

        $('.lipnamo-progress-total').text(lipnamoGetFormConfig().total);
        $('.lipnamo-progress-step').text('0');
        $('.lipnamo-progress-bar').css('width', '0%');
    }

    function lipnamoExecuteGeneration(config){
        if(lipnamoCurrentStep > config.total){
            lipnamoCompleteGeneration();
            return;
        }

        const ajaxData = lipnamoBuildAjaxData(config);

        $.ajax({
            url: lipnamo_items.ajax_url,
            type: 'POST',
            data: ajaxData,
            success: function(response){
                lipnamoHandleAjaxSuccess(response, config);
            },
            error: function(xhr, status, error){
                lipnamoHandleAjaxError(error);
            }
        });
    }

    function lipnamoBuildAjaxData(config){
        return {
            action: 'lipnamo_generate_items',
            lipnamo_ajax_nonce: lipnamo_items.ajax_nonce,
            post_total: config.total,
            post_type: config.type,
            post_author: config.author,
            post_status: config.status,
            post_thumbnails: config.thumbnails,
            post_title_length: config.titleLength,
            post_excerpt_length: config.excerptLength,
            post_body_length: config.bodyLength,
            post_step: lipnamoCurrentStep
        };
    }

    function lipnamoHandleAjaxSuccess(response, config){
        try{
            const result = JSON.parse(response);
            const step = parseInt(result.step);

            lipnamoUpdateProgress(step, config.total);
            lipnamoUpdateStepInput(step);

            if(step < config.total){
                lipnamoCurrentStep++;
                lipnamoExecuteGeneration(config);
            }else{
                lipnamoFinalizeGeneration(result.message);
            }
        }catch(error){
            lipnamoHandleAjaxError('Invalid response format');
        }
    }

    function lipnamoUpdateProgress(step, total){
        const percent = Math.min((step * 100) / total, 100);

        $('.lipnamo-progress-step').text(step);
        $('.lipnamo-progress-bar').animate({
            width: percent + '%'
        }, 150);
    }

    function lipnamoUpdateStepInput(step){
        $('input[name="lipnamo-generate__step"]').val(step);
    }

    function lipnamoFinalizeGeneration(message){
        $('.lipnamo-progress-bar').animate({
            width: '100%'
        }, 150);

        if(message){
            $('.lipnamo-progress-text').text(message);
        }

        setTimeout(lipnamoCompleteGeneration, 500);
    }

    function lipnamoCompleteGeneration(){
        const $wpbody = $("#wpbody");
        const $generateBtn = $('.lipnamo-generate');

        $wpbody.removeClass("lipnamo-loading");
        $generateBtn.removeClass('disabled');

        lipnamoCurrentStep = 1;
    }

    function lipnamoHandleAjaxError(error){
        console.error('Lipnamo Generation Error:', error);
        alert('An error occurred during generation. Please try again.');
        lipnamoCompleteGeneration();
    }
});