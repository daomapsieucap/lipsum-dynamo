/**
 * Lipnamo Media Upload Handler
 * WordPress Plugin Safe Implementation
 */
(function($){
    'use strict';

    // Namespace object to prevent conflicts
    window.LipnamoMediaUploader = window.LipnamoMediaUploader || {};

    /**
     * Main Lipnamo Media Upload Class
     */
    class LipnamoMediaUploader{
        constructor(){
            this.prefix = 'lipnamo';
            this.selectors = {
                imageInput: '.lipnamo-input__img input[type="text"]',
                uploadBtn: '.lipnamo-upload',
                previewList: '.lipnamo-preview__list',
                thumbnailsInput: '#lipnamo-thumbnails',
                removeThumbnail: '.lipnamo-remove-thumbnail'
            };
            this.mediaUploaders = new Map();
            this.init();
        }

        /**
         * Initialize all components
         */
        init(){
            if(!this.validateEnvironment()){
                this.lipnamo_log('Media uploader functionality disabled - WordPress media library not found', 'warn');
                return;
            }

            try{
                this.lipnamo_initImagePreview();
                this.lipnamo_initMediaUploader();
                this.lipnamo_initThumbnailRemoval();
                this.lipnamo_log('Lipnamo Media Uploader initialized successfully');
            }catch(error){
                this.lipnamo_log('Error initializing upload handlers: ' + error.message, 'error');
            }
        }

        /**
         * Validate WordPress environment
         */
        validateEnvironment(){
            return typeof wp !== 'undefined' &&
                typeof wp.media !== 'undefined' &&
                typeof jQuery !== 'undefined';
        }

        /**
         * Safe logging with plugin prefix
         */
        lipnamo_log(message, type = 'log'){
            const prefixedMessage = `[Lipnamo Plugin] ${message}`;
            if(console && typeof console[type] === 'function'){
                console[type](prefixedMessage);
            }
        }

        /**
         * Image Preview Toggle Handler
         */
        lipnamo_initImagePreview(){
            const self = this;

            $(document).on('change.lipnamo', this.selectors.imageInput, function(){
                const $input = $(this);
                const $preview = $input.closest('fieldset').find('img');

                $preview.toggle(!!$input.val());
            });
        }

        /**
         * Media Upload Handler
         */
        lipnamo_initMediaUploader(){
            const self = this;

            $(document).on('click.lipnamo', this.selectors.uploadBtn, function(e){
                e.preventDefault();

                const $uploadBtn = $(this);
                const uploaderId = $uploadBtn.data('lipnamo-uploader-id') || self.lipnamo_generateUploaderId();

                // Store uploader ID for this button
                $uploadBtn.data('lipnamo-uploader-id', uploaderId);

                self.lipnamo_handleUploaderClick($uploadBtn, uploaderId);
            });
        }

        /**
         * Generate unique uploader ID
         */
        lipnamo_generateUploaderId(){
            return `${this.prefix}_uploader_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        }

        /**
         * Handle uploader button click
         */
        lipnamo_handleUploaderClick($uploadBtn, uploaderId){
            const $preview = $uploadBtn.closest('fieldset').find(this.selectors.previewList);
            const $thumbnailsInput = $(this.selectors.thumbnailsInput);

            // Reopen existing uploader
            if(this.mediaUploaders.has(uploaderId)){
                this.mediaUploaders.get(uploaderId).open();
                return;
            }

            // Create new media uploader
            const mediaUploader = this.lipnamo_createMediaUploader($thumbnailsInput, $preview);
            this.mediaUploaders.set(uploaderId, mediaUploader);
            mediaUploader.open();
        }

        /**
         * Create WordPress Media Uploader
         */
        lipnamo_createMediaUploader($thumbnailsInput, $preview){
            const self = this;
            const frameId = `${this.prefix}_media_frame_${Date.now()}`;

            const uploader = wp.media({
                title: 'Choose Images - Lipnamo',
                button: {text: 'Choose Images'},
                multiple: 'add',
                library: {type: 'image'}
            });

            // Handle file selection
            uploader.on('select', function(){
                self.lipnamo_handleFileSelection(uploader, $thumbnailsInput, $preview);
            });

            // Handle uploader open event
            uploader.on('open', function(){
                self.lipnamo_handleUploaderOpen(uploader, $thumbnailsInput);
            });

            return uploader;
        }

        /**
         * Handle file selection from media uploader
         */
        lipnamo_handleFileSelection(uploader, $thumbnailsInput, $preview){
            const selectedFiles = uploader.state().get('selection').toJSON();

            if(!selectedFiles || selectedFiles.length === 0) return;

            const currentIds = this.lipnamo_getCurrentThumbnailIds($thumbnailsInput);
            const newIds = [];
            const previewItems = [];

            selectedFiles.forEach(file => {
                if(!currentIds.includes(file.id)){
                    newIds.push(file.id);
                    previewItems.push(this.lipnamo_createPreviewItem(file));
                }
            });

            // Update thumbnail input value
            if(newIds.length > 0){
                this.lipnamo_updateThumbnailIds($thumbnailsInput, currentIds.concat(newIds));
                $preview.append(previewItems.join(''));
            }
        }

        /**
         * Handle uploader open event - pre-select existing images
         */
        lipnamo_handleUploaderOpen(uploader, $thumbnailsInput){
            const selection = uploader.state().get('selection');
            const currentIds = this.lipnamo_getCurrentThumbnailIds($thumbnailsInput);

            currentIds.forEach(id => {
                if(id){
                    const attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment);
                }
            });
        }

        /**
         * Get current thumbnail IDs from input
         */
        lipnamo_getCurrentThumbnailIds($input){
            const value = $input.val().trim();
            return value ? value.split(',')
                .map(id => parseInt(id.trim()))
                .filter(id => !isNaN(id) && id > 0) : [];
        }

        /**
         * Update thumbnail IDs in input field
         */
        lipnamo_updateThumbnailIds($input, ids){
            const uniqueIds = [...new Set(ids)].filter(id => id && !isNaN(id) && id > 0);
            $input.val(uniqueIds.join(','));

            // Trigger change event for other scripts
            $input.trigger('change.lipnamo');
        }

        /**
         * Create preview item HTML
         */
        lipnamo_createPreviewItem(file){
            const imageUrl = this.lipnamo_sanitizeUrl(file.url || '');
            const altText = this.lipnamo_sanitizeText(file.alt || file.title || 'Selected image');
            const imageId = parseInt(file.id);

            return `<li class="lipnamo-preview-item attachment" data-lipnamo-id="${imageId}">
                        <div class="attachment-preview">
                            <div class="thumbnail">
                                <div class="centered">
                                    <img src="${imageUrl}" alt="${altText}" class="lipnamo-preview-image" />
                                </div>
                            </div>
                            <button type="button" 
                                    class="lipnamo-remove-thumbnail button-link attachment-close media-modal-icon" 
                                    data-lipnamo-id="${imageId}" 
                                    title="Remove image"
                                    aria-label="Remove image">
                                    <span class="screen-reader-text">Remove</span>
                            </button>
                        </div>
                    </li>`;
        }

        /**
         * Handle thumbnail removal
         */
        lipnamo_initThumbnailRemoval(){
            const self = this;

            $(document).on('click.lipnamo', this.selectors.removeThumbnail, function(e){
                e.preventDefault();

                const $btn = $(this);
                const imageId = parseInt($btn.data('lipnamo-id'));
                const $thumbnailsInput = $(self.selectors.thumbnailsInput);
                const $listItem = $btn.closest('.lipnamo-preview-item');

                if(imageId && $listItem.length){
                    self.lipnamo_removeThumbnail(imageId, $thumbnailsInput, $listItem);
                }
            });
        }

        /**
         * Remove thumbnail from list and input
         */
        lipnamo_removeThumbnail(imageId, $thumbnailsInput, $listItem){
            // Remove from input value
            const currentIds = this.lipnamo_getCurrentThumbnailIds($thumbnailsInput);
            const updatedIds = currentIds.filter(id => id !== imageId);
            this.lipnamo_updateThumbnailIds($thumbnailsInput, updatedIds);

            // Remove from preview with animation
            $listItem.fadeOut(300, function(){
                $(this).remove();
            });
        }

        /**
         * Sanitize URL for output
         */
        lipnamo_sanitizeUrl(url){
            if(typeof url !== 'string') return '';
            return url.replace(/[<>"']/g, '');
        }

        /**
         * Sanitize text for output
         */
        lipnamo_sanitizeText(text){
            if(typeof text !== 'string') return '';
            return text.replace(/[<>"'&]/g, function(match){
                const entities = {
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '&': '&amp;'
                };
                return entities[match] || match;
            });
        }

        /**
         * Cleanup method for plugin deactivation
         */
        lipnamo_destroy(){
            // Remove event listeners
            $(document).off('.lipnamo');

            // Clear media uploaders
            this.mediaUploaders.clear();

            this.lipnamo_log('Lipnamo Media Uploader destroyed');
        }

        /**
         * Reinitialize after AJAX calls
         */
        lipnamo_refresh(){
            this.lipnamo_destroy();
            this.init();
        }
    }

    // Initialize when document is ready
    $(document).ready(function(){
        if(!window.lipnamoMediaUploaderInstance){
            window.lipnamoMediaUploaderInstance = new LipnamoMediaUploader();

            // Expose public methods
            window.LipnamoMediaUploader.refresh = function(){
                if(window.lipnamoMediaUploaderInstance){
                    window.lipnamoMediaUploaderInstance.lipnamo_refresh();
                }
            };

            window.LipnamoMediaUploader.destroy = function(){
                if(window.lipnamoMediaUploaderInstance){
                    window.lipnamoMediaUploaderInstance.lipnamo_destroy();
                    window.lipnamoMediaUploaderInstance = null;
                }
            };
        }
    });

})(jQuery);