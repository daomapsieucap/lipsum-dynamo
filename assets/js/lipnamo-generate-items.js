class LipnamoGenerator{
    constructor(){
        this.currentStep = 1;
        this.isRunning = false;
        this.config = {};
        this.elements = {};

        this.init();
    }

    init(){
        this.cacheElements();
        this.bindEvents();
    }

    cacheElements(){
        this.elements = {
            generateBtn: document.querySelector('.lipnamo-generate'),
            progressBar: document.querySelector('.lipnamo-progress-bar'),
            progressWrapper: document.querySelector('.lipnamo-progress-wrapper'),
            progressStep: document.querySelector('.lipnamo-progress-step'),
            progressTotal: document.querySelector('.lipnamo-progress-total'),
            progressText: document.querySelector('.lipnamo-progress-text'),
            wpBody: document.getElementById('wpbody'),
            stepInput: document.querySelector('input[name="lipnamo-generate__step"]'),
            
            // Form inputs
            postTotal: document.querySelector('input[name="lipnamo_post_total"]'),
            postType: document.querySelector('select[name="lipnamo_post_type"]'),
            postAuthor: document.querySelector('select[name="lipnamo_post_author"]'),
            postStatus: document.querySelector('select[name="lipnamo_post_status"]'),
            postThumbnails: document.querySelector('input[name="lipnamo_thumbnails"]'),
            titleMin: document.querySelector('input[name="length_title_min"]'),
            titleMax: document.querySelector('input[name="length_title_max"]'),
            excerptMin: document.querySelector('input[name="length_excerpt_min"]'),
            excerptMax: document.querySelector('input[name="length_excerpt_max"]'),
            bodyMin: document.querySelector('input[name="length_content_min"]'),
            bodyMax: document.querySelector('input[name="length_content_max"]')
        };
    }

    bindEvents(){
        if(this.elements.generateBtn){
            this.elements.generateBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.startGeneration();
            });
        }
    }

    getFormConfig(){
        return {
            postTotal: parseInt(this.elements.postTotal?.value) || 0,
            postType: this.elements.postType?.value || '',
            postAuthor: this.elements.postAuthor?.value || '',
            postStatus: this.elements.postStatus?.value || '',
            postThumbnails: this.elements.postThumbnails?.value || '',
            postTitleLength: `${this.elements.titleMin?.value || ''},${this.elements.titleMax?.value || ''}`,
            postExcerptLength: `${this.elements.excerptMin?.value || ''},${this.elements.excerptMax?.value || ''}`,
            postBodyLength: `${this.elements.bodyMin?.value || ''},${this.elements.bodyMax?.value || ''}`
        };
    }

    startGeneration(){
        if(this.isRunning) return;

        this.config = this.getFormConfig();
        this.currentStep = 1;
        this.isRunning = true;

        this.showProgress();
        if(this.elements.progressTotal){
            this.elements.progressTotal.textContent = this.config.postTotal;
        }

        this.lipnamoGenerateItems();
    }

    showProgress(){
        if(this.elements.wpBody){
            this.elements.wpBody.classList.add('lipnamo-loading');
        }
        if(this.elements.progressWrapper){
            this.elements.progressWrapper.style.display = 'block';
        }
        if(this.elements.generateBtn){
            this.elements.generateBtn.classList.add('disabled');
        }
    }

    hideProgress(){
        if(this.elements.wpBody){
            this.elements.wpBody.classList.remove('lipnamo-loading');
        }
        if(this.elements.generateBtn){
            this.elements.generateBtn.classList.remove('disabled');
        }
        this.isRunning = false;
    }

    updateProgress(step, total){
        const percent = Math.min((step * 100) / total, 100);

        if(this.elements.progressStep){
            this.elements.progressStep.textContent = step;
        }
        if(this.elements.stepInput){
            this.elements.stepInput.value = step;
        }

        if(this.elements.progressBar){
            this.animateProgressBar(percent);
        }
    }

    animateProgressBar(targetPercent){
        const progressBar = this.elements.progressBar;
        const currentWidth = parseFloat(progressBar.style.width) || 0;
        const targetWidth = targetPercent;
        const duration = 150;
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentPercent = currentWidth + (targetWidth - currentWidth) * easeOut;

            progressBar.style.width = `${currentPercent}%`;

            if(progress < 1){
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    lipnamoGenerateItems(){
        if(this.currentStep > this.config.postTotal){
            this.hideProgress();
            return;
        }

        const formData = new FormData();
        formData.append('action', 'lipnamo_generate_items');
        formData.append('lipnamo_ajax_nonce', lipnamo_items.ajax_nonce);
        formData.append('post_total', this.config.postTotal);
        formData.append('post_type', this.config.postType);
        formData.append('post_author', this.config.postAuthor);
        formData.append('post_status', this.config.postStatus);
        formData.append('post_thumbnails', this.config.postThumbnails);
        formData.append('post_title_length', this.config.postTitleLength);
        formData.append('post_excerpt_length', this.config.postExcerptLength);
        formData.append('post_body_length', this.config.postBodyLength);
        formData.append('post_step', this.currentStep);

        fetch(lipnamo_items.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(response => {
                if(!response.ok){
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(responseText => this.handleSuccess(responseText))
            .catch(error => this.handleError(error));
    }

    handleSuccess(responseText){
        try{
            const result = JSON.parse(responseText);
            const step = parseInt(result.step) || this.currentStep;

            this.updateProgress(step, this.config.postTotal);

            if(step >= this.config.postTotal){
                if(this.elements.progressText){
                    this.elements.progressText.textContent = result.message || 'Generation completed!';
                }
                this.hideProgress();
                return;
            }

            this.currentStep = step + 1;

            // Add small delay to prevent overwhelming the server
            setTimeout(() => {
                if(this.isRunning){
                    this.lipnamoGenerateItems();
                }
            }, 100);

        }catch(error){
            console.error('LipnamoGenerator: Error parsing response', error);
            this.handleError(error);
        }
    }

    handleError(error){
        console.error('LipnamoGenerator: Error', error);
        if(this.elements.progressText){
            this.elements.progressText.textContent = 'An error occurred during generation. Please try again.';
        }
        this.hideProgress();
    }

    // Public methods for external access
    stop(){
        this.isRunning = false;
        this.hideProgress();
    }

    reset(){
        this.stop();
        this.currentStep = 1;
        if(this.elements.progressBar){
            this.elements.progressBar.style.width = '0%';
        }
        if(this.elements.progressStep){
            this.elements.progressStep.textContent = '0';
        }
        if(this.elements.progressText){
            this.elements.progressText.textContent = '';
        }
        if(this.elements.stepInput){
            this.elements.stepInput.value = '';
        }
    }

    getStatus(){
        return {
            isRunning: this.isRunning,
            currentStep: this.currentStep,
            totalSteps: this.config.postTotal
        };
    }
}

document.addEventListener('DOMContentLoaded', function(){
    new LipnamoGenerator();
});