let $ = jQuery;
let reviewId = null;
let currentImageIndex = 0;
let allReviewImages = [];

export default {
    init: function (e, $this) {
        e.preventDefault();
        reviewId = $($this).data('review-id');
        currentImageIndex = parseInt($($this).data('image-index') || 0, 10);

        // Get all review images from the current review
        this.collectAllReviewImages();

        // Append the feed content to the popup wrapper
        this.appendFeed( $('body'));
    },

    collectAllReviewImages() {
        // Get all review images from the current review
        allReviewImages = [];

        $('[data-review-id="' + reviewId + '"]').each(function(index) {
            allReviewImages.push({
                src: $(this).attr('src'),
                alt: $(this).attr('alt') || 'Review Image',
                index: index
            });
        });
    },

    appendFeed(parent) {
        // If we have images, render and append the HTML
        if (allReviewImages.length > 0) {
            document.body.classList.add('npfeed-feed-popup-active');
            
            let data = this.renderPopupHtml();
            parent.append(data);

            // Initialize pagination if there are multiple images
            if (allReviewImages.length > 1) {
                this.initializePagination();
            }
        }
    },

    initializePagination() {
        let $that = this;
        
        // Handle next button click
        $('body').off('click', '.npfeed-review-next-btn').on('click', '.npfeed-review-next-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $that.navigateImage('next');
        });

        // Handle prev button click
        $('body').off('click', '.npfeed-review-prev-btn').on('click', '.npfeed-review-prev-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $that.navigateImage('prev');
        });

        // Update button states
        this.updatePaginationButtons();
    },

    navigateImage(direction) {
        if (direction === 'next' && currentImageIndex < allReviewImages.length - 1) {
            currentImageIndex++;
        } else if (direction === 'prev' && currentImageIndex > 0) {
            currentImageIndex--;
        }

        // Update the image
        this.updateCurrentImage();
        this.updatePaginationButtons();
    },

    updateCurrentImage() {
        let currentImage = allReviewImages[currentImageIndex];
        if (currentImage) {
            $('.npfeed-review-popup-current-image').attr('src', currentImage.src);
            $('.npfeed-review-popup-image-counter').text((currentImageIndex + 1) + ' / ' + allReviewImages.length);
        }
    },

    updatePaginationButtons() {
        let $prevBtn = $('.npfeed-review-prev-btn');
        let $nextBtn = $('.npfeed-review-next-btn');

        if (currentImageIndex === 0) {
            $prevBtn.addClass('npfeed-link-disable');
        } else {
            $prevBtn.removeClass('npfeed-link-disable');
        }

        if (currentImageIndex === allReviewImages.length - 1) {
            $nextBtn.addClass('npfeed-link-disable');
        } else {
            $nextBtn.removeClass('npfeed-link-disable');
        }
    },

    renderPopupHtml() {
        let currentImage = allReviewImages[currentImageIndex] || allReviewImages[0];
        let imageCounter = allReviewImages.length > 1 ? 
            '<div class="npfeed-review-popup-image-counter">' + (currentImageIndex + 1) + ' / ' + allReviewImages.length + '</div>' : '';

        return `<div class="npfeed-feed-popup-overlay npfeed-review-feed-popup npfeedm-overlay npfeed_content npfeed-feed-popup-open">
            <div class="npfeed-feed-popup-box-wraper">
                <div class="npfeed-feed-popup-box-wraper-inner npfeedm_inner">
                    ${allReviewImages.length > 1 ?
                        `<div class="npfeed-prev-btn npfeed-review-prev-btn"><span class="icon-angle-left"></span></div>
                         <div class="npfeed-next-btn npfeed-review-next-btn"><span class="icon-angle-right"></span></div>` : ''
                    }
                    
                    <div class="npfeed-feed-popup-close-btn npfeedm_close"></div>
                    
                    <div class="npfeed-feed-popup-media npfeed-review-popup-media">
                        <div class="npfeed-review-popup-photo-media">
                            <img class="npfeed-review-popup-current-image" src="${currentImage ? currentImage.src : ''}" alt="${currentImage ? currentImage.alt : 'Review Image'}"/>
                            ${imageCounter}
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }
};