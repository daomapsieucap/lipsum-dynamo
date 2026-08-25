jQuery(document).ready(function($){
    'use strict';

    // Cache frequently used selectors
    const $thumbnailsField = $('#lipnamo-thumbnails');

    /**
     * Toggle preview visibility based on input value
     */
    $('.lipnamo-input__img input[type="text"]').on('change', function(){
        const $preview = $(this).closest('fieldset').find('img');
        $preview.toggle(!!$(this).val());
    });

    /**
     * Media uploader functionality
     */
    $('.lipnamo-upload').each(function(){
        const $uploadButton = $(this);
        const $preview = $uploadButton.closest('fieldset').find('.lipnamo-preview__list');
        let mediaUploader;

        $uploadButton.on('click', function(e){
            e.preventDefault();

            // Reopen existing uploader
            if(mediaUploader){
                mediaUploader.open();
                return;
            }

            // Create new media uploader
            mediaUploader = wp.media({
                title: 'Choose Images',
                button: {text: 'Choose Images'},
                multiple: 'add',
                library: {
                    type: 'image'
                }
            });

            // Handle media selection
            mediaUploader.on('select', function(){
                const attachments = mediaUploader.state().get('selection').toJSON();
                lipnamoAddSelectedImages(attachments, $preview);
            });

            // Pre-select existing images when opening
            mediaUploader.on('open', function(){
                lipnamoPreselectExistingImages(mediaUploader);
            });

            mediaUploader.open();
        });
    });

    /**
     * Remove thumbnail functionality
     */
    $(document).on('click', '.lipnamo-remove-thumbnail', function(e){
        e.preventDefault();

        const imageId = parseInt($(this).data('lipnamo-id'));
        const $previewItem = $(this).closest('.lipnamo-preview-item');

        lipnamoRemoveImageFromField(imageId);
        $previewItem.fadeOut(300, function(){
            $(this).remove();
        });
    });

    /**
     * Add selected images to preview and update field
     */
    function lipnamoAddSelectedImages(attachments, $preview){
        if(!attachments?.length) return;

        const currentIds = lipnamoGetCurrentImageIds();
        const newIds = [];

        attachments.forEach((attachment, index) => {
            const imageId = parseInt(attachment.id);

            // Skip if image already exists
            if(currentIds.includes(imageId)) return;

            // Add to new IDs array
            newIds.push(imageId);

            // Add to preview, before the add-tile so it stays last
            const altText = attachment.alt || `Image ${index + 1}`;
            const ariaLabel = attachment.title || lipnamoGetNoTitleText();
            $preview.find('.lipnamo-add-tile').before(lipnamoCreateThumbnailHTML(imageId, attachment.url, altText, ariaLabel));
        });

        // Update hidden field with combined IDs
        if(newIds.length){
            lipnamoUpdateThumbnailField([...currentIds, ...newIds]);
        }
    }

    /**
     * Matches wp.media.view.Attachment's own aria-label fallback (media-views.js)
     */
    function lipnamoGetNoTitleText(){
        return (typeof lipnamoAdmin !== 'undefined' && lipnamoAdmin.noTitleText) || '(no title)';
    }

    /**
     * Create thumbnail HTML
     */
    function lipnamoCreateThumbnailHTML(imageId, imageUrl, altText, ariaLabel){
        const coreIconSplit = typeof lipnamoAdmin !== 'undefined' && !!lipnamoAdmin.coreIconSplit;
        const removeButtonHTML = coreIconSplit
            ? `<button type="button" class="lipnamo-remove-thumbnail button-link attachment-close"
                        data-lipnamo-id="${imageId}" title="Remove image" aria-label="Remove image">
                    <span class="media-modal-icon" aria-hidden="true"></span>
                    <span class="screen-reader-text">Remove</span>
                </button>`
            : `<button type="button" class="lipnamo-remove-thumbnail button-link attachment-close media-modal-icon"
                        data-lipnamo-id="${imageId}" title="Remove image" aria-label="Remove image">
                    <span class="screen-reader-text">Remove</span>
                </button>`;

        return `
            <li class="lipnamo-preview-item attachment" data-lipnamo-id="${imageId}" aria-label="${ariaLabel}">
                <div class="attachment-preview">
                    <div class="thumbnail">
                        <div class="centered">
                            <img src="${imageUrl}" alt="${altText}" class="lipnamo-preview-image" />
                        </div>
                    </div>
                    ${removeButtonHTML}
                </div>
            </li>
        `;
    }

    /**
     * Get current image IDs from hidden field
     */
    function lipnamoGetCurrentImageIds(){
        const value = $thumbnailsField.val().trim();
        return value ? value.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id)) : [];
    }

    /**
     * Update thumbnail field with new IDs
     */
    function lipnamoUpdateThumbnailField(imageIds){
        const uniqueIds = [...new Set(imageIds)].filter(id => !isNaN(id));
        $thumbnailsField.val(uniqueIds.length ? uniqueIds.join(',') : '');
    }

    /**
     * Remove image ID from hidden field
     */
    function lipnamoRemoveImageFromField(imageId){
        const currentIds = lipnamoGetCurrentImageIds();
        const updatedIds = currentIds.filter(id => id !== imageId);
        lipnamoUpdateThumbnailField(updatedIds);
    }

    /**
     * Pre-select existing images in media uploader
     */
    function lipnamoPreselectExistingImages(uploader){
        const imageIds = lipnamoGetCurrentImageIds();
        if(!imageIds.length) return;

        const selection = uploader.state().get('selection');

        imageIds.forEach(id => {
            const attachment = wp.media.attachment(id);
            attachment.fetch();
            selection.add(attachment);
        });
    }
});