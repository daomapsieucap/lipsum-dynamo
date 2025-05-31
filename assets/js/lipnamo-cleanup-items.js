class LipnamoCleanupManager{
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
            wpBody: document.getElementById('wpbody'),
            cleanupButton: document.querySelector('.lipnamo-cleanup.button'),
            progressBar: document.querySelector('.lipnamo-progress-bar'),
            progressWrapper: document.querySelector('.lipnamo-progress-wrapper'),
            progressStep: document.querySelector('.lipnamo-progress-step'),
            progressTotal: document.querySelector('.lipnamo-progress-total'),
            progressText: document.querySelector('.lipnamo-progress-text'),
            stepInput: document.querySelector('input[name="lipnamo-cleanup__step"]'),
            // Form inputs
            postTotalInput: document.querySelector('input[name="lipnamo_post_total"]'),
            postTypeSelect: document.querySelector('select[name="lipnamo_post_type"]')
        };
    }

    bindEvents(){
        if(this.elements.postTypeSelect){
            this.elements.postTypeSelect.addEventListener('change', () => {
                this.updatePostTotal();
            });
        }

        if(this.elements.cleanupButton){
            this.elements.cleanupButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.startCleanup();
            });
        }
    }

    async updatePostTotal(){
        const postType = this.elements.postTypeSelect?.value || '';

        this.showLoading();
        this.disableCleanupButton();

        try{
            const formData = new FormData();
            formData.append('action', 'lipnamo_total_items');
            formData.append('lipnamo_ajax_nonce', lipnamo_items.ajax_nonce);
            formData.append('post_type', postType);

            const response = await fetch(lipnamo_items.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if(!response.ok){
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            const result = JSON.parse(responseText);
            const postTotal = parseInt(result.post_total) || 0;

            if(this.elements.postTotalInput){
                this.elements.postTotalInput.value = postTotal;
            }

        }catch(error){
            console.error('LipnamoCleanupManager: Error updating post total', error);
        }finally{
            this.hideLoading();
            this.enableCleanupButton();
        }
    }

    startCleanup(){
        if(this.isRunning) return;

        this.config = this.getCleanupConfig();
        this.currentStep = 1;
        this.isRunning = true;

        this.showProgress();
        if(this.elements.progressTotal){
            this.elements.progressTotal.textContent = this.config.postTotal;
        }

        this.lipnamoCleanupItems();
    }

    getCleanupConfig(){
        return {
            postTotal: parseInt(this.elements.postTotalInput?.value) || 0,
            postType: this.elements.postTypeSelect?.value || ''
        };
    }

    showLoading(){
        if(this.elements.wpBody){
            this.elements.wpBody.classList.add('lipnamo-loading');
        }
    }

    hideLoading(){
        if(this.elements.wpBody){
            this.elements.wpBody.classList.remove('lipnamo-loading');
        }
    }

    showProgress(){
        this.showLoading();
        if(this.elements.progressWrapper){
            this.elements.progressWrapper.style.display = 'block';
        }
        this.disableCleanupButton();
    }

    hideProgress(){
        this.hideLoading();
        this.enableCleanupButton();
        this.isRunning = false;
    }

    disableCleanupButton(){
        if(this.elements.cleanupButton){
            this.elements.cleanupButton.classList.add('disabled');
        }
    }

    enableCleanupButton(){
        if(this.elements.cleanupButton){
            this.elements.cleanupButton.classList.remove('disabled');
        }
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

    async lipnamoCleanupItems(){
        if(this.currentStep > this.config.postTotal){
            this.hideProgress();
            return;
        }

        try{
            const formData = new FormData();
            formData.append('action', 'lipnamo_cleanup_items');
            formData.append('lipnamo_ajax_nonce', lipnamo_items.ajax_nonce);
            formData.append('post_total', this.config.postTotal);
            formData.append('post_type', this.config.postType);
            formData.append('post_step', this.currentStep);

            const response = await fetch(lipnamo_items.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if(!response.ok){
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            await this.handleSuccess(responseText);

        }catch(error){
            this.handleError(error);
        }
    }

    async handleSuccess(responseText){
        try{
            const result = JSON.parse(responseText);
            const step = parseInt(result.step) || this.currentStep;

            this.updateProgress(step, this.config.postTotal);

            if(step >= this.config.postTotal){
                if(this.elements.progressText){
                    this.elements.progressText.textContent = result.message || 'Cleanup completed!';
                }
                this.hideProgress();
                return;
            }

            this.currentStep = step + 1;

            // Add small delay to prevent overwhelming the server
            setTimeout(() => {
                if(this.isRunning){
                    this.lipnamoCleanupItems();
                }
            }, 100);

        }catch(error){
            console.error('LipnamoCleanupManager: Error parsing response', error);
            this.handleError(error);
        }
    }

    handleError(error){
        console.error('LipnamoCleanupManager: Error', error);
        if(this.elements.progressText){
            this.elements.progressText.textContent = 'An error occurred during cleanup. Please try again.';
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

    async refreshPostTotal(){
        await this.updatePostTotal();
    }

    getStatus(){
        return {
            isRunning: this.isRunning,
            currentStep: this.currentStep,
            totalSteps: this.config.postTotal,
            postType: this.config.postType
        };
    }
}

document.addEventListener('DOMContentLoaded', function(){
    new LipnamoCleanupManager();
});