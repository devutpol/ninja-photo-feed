jQuery(document).ready(function($) {
    "use strict";

    function getWpsrImageFormat() {
        let imageFormat = window.npfeed_ajax_params
            && window.npfeed_ajax_params.image_settings
            && window.npfeed_ajax_params.image_settings.image_format
            ? window.npfeed_ajax_params.image_settings.image_format
            : '';

        return ['jpg', 'webp'].includes(imageFormat) ? imageFormat : 'jpg';
    }

    let reviews_selector = $(".npfeed-reviews-wrapper");
    reviews_selector.each(function () {

        let that = $(this);
        let template_id = '.' + that.attr('class');
        let match = template_id.match(/wpsr-reviews-(\d+)/);
        var post_id = null;
        if (match) {
             post_id = match[1];
        }
        // The pro drawer prints .npfeed-reviews-wrapper with no id class.
        if (!post_id) {
            return;
        }

        let upload_url = window.npfeed_ajax_params.upload_url;
        let element = document.querySelector(`.npfeed-reviews-${post_id}`);
        let dataPlatforms = element.getAttribute('data-platforms');
        const platforms = dataPlatforms.split(',');

        let submitData = {
            action: 'npfeed_review_resize_images',
            id: post_id,
            platforms: platforms,
            nonce: window.npfeed_ajax_params && window.npfeed_ajax_params.npfeed_nonce ? window.npfeed_ajax_params.npfeed_nonce : '',
        };

        $.ajax({
            url: window.npfeed_ajax_params.ajax_url,
            type: 'post',
            data: submitData,
        }).done(function(response) {
            if(response) {
                let jsonObjects = response.match(/({[^{}]+})/g);
                let image_data_arrays = JSON.parse(jsonObjects);

                if(image_data_arrays.images_data) {
                    jQuery(`.npfeed-all-reviews`).find(`.npfeed-review-template`).each(function (index) {
                        let self = $(this);
                        let source_id = self.attr("data-source_id");
                        let media_id = self.attr("data-media_id");
                        let image_resize = self.attr("data-image_resize");
                        let review_platform = self.attr("data-review_platform");
                        if (media_id) {
                            media_id = media_id.toString();
                            let image_format = getWpsrImageFormat();
                            if(review_platform && source_id && image_data_arrays.images_data.includes(media_id)) {
                                let image = upload_url + '/'+  review_platform + '/'+ source_id +'/' + media_id + '_' + image_resize + '.'+ image_format;
                                self.find(`.npfeed-reviewer-avatar`).attr('src', image);
                                self.find('.npfeed-reviewer-image-url').removeClass('npfeed-reviewer-image-animation');
                            }
                        }
                    });
                }
            }
        }).fail(function(errors) {
            console.error(errors);
        }).always(function() {

        });
    });
});
