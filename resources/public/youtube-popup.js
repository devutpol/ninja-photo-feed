import { wpsrInitInjectedContent } from './helper.js';

let $ = jQuery;

let videoId = null;

function wpsrYtIframe(ytVideoId, iframeClass) {
    return '<iframe class="' + iframeClass + '" src="https://www.youtube.com/embed/' + ytVideoId + '?autoplay=1&rel=0" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>';
}

// Latest request token and in-flight xhr per feed. One video plays per feed, so a newer
// selection supersedes any pending request, or its late callback would refill a cleared
// slot and start a hidden autoplaying player.
const wpsrPlayerRequests = {};

/**
 * Fill a player slot with the clicked video. Fluent Player markup comes from the
 * server, since only PHP can run the shortcode.
 *
 * @param {jQuery} slot
 * @param {number|string} templateId
 * @param {string} ytVideoId
 * @param {string} iframeClass
 * @param {boolean} useFluentPlayer
 * @param {number|string} duration video length in seconds, for "layers at end"
 */
function wpsrPlayVideo(slot, templateId, ytVideoId, iframeClass, useFluentPlayer, duration) {
    let feed = wpsrPlayerRequests[templateId] || (wpsrPlayerRequests[templateId] = {token: 0, xhr: null});

    // Bump the token so older callbacks bail, and abort their request if still pending.
    let requestToken = ++feed.token;
    if (feed.xhr) {
        feed.xhr.abort();
        feed.xhr = null;
    }

    if (!useFluentPlayer) {
        slot.html(wpsrYtIframe(ytVideoId, iframeClass));
        return;
    }

    feed.xhr = $.post(npfeed_popup_params.ajax_url, {
        action: 'npfeed_youtube_fluent_player',
        videoId: ytVideoId,
        templateId: templateId,
        // Read off the clicked thumbnail so the server skips a per-click feed lookup.
        duration: duration || 0,
        security: npfeed_popup_params.npfeed_nonce
    }).done(function (response) {
        // A newer selection already replaced this one; its slot may be cleared.
        if (requestToken !== feed.token) {
            return;
        }
        feed.xhr = null;
        slot.addClass('npfeed-fluent-player-slot').html(response.data.html);
        wpsrInitInjectedContent(slot, templateId, 'yt');
    }).fail(function (jqXHR, textStatus) {
        // Ignore our own abort and any request a newer selection superseded.
        if (textStatus === 'abort' || requestToken !== feed.token) {
            return;
        }
        feed.xhr = null;
        // Fluent Player became unavailable after this page was rendered.
        slot.removeClass('npfeed-fluent-player-slot').html(wpsrYtIframe(ytVideoId, iframeClass));
    });
}

export default {
    init: function (e, $this){
        this.checkFeedType(e, $this);
    },
    checkFeedType(e, identifier) {
        let playMode = $(identifier).data('playmode');
        let templateId = $(identifier).data('template_id');
        let feedId = $(identifier).data('index');
        let ytVideoId = $(identifier).data('videoid');
        let duration = $(identifier).data('duration');
        videoId = ytVideoId;
        let parent = $('#npfeed-yt-feed-'+templateId);
        let useFluentPlayer = parent.data('fluent_player') === true;

        if (playMode === 'popup') {
            this.openPopUpBox(e,identifier);
        } else if( playMode === 'gallery' ){
            e.preventDefault();
            let galleryModeSelector = parent.find('.npfeed-yt-video-player-gallery');
            wpsrPlayVideo(galleryModeSelector, templateId, ytVideoId, '', useFluentPlayer, duration);
            // Templates 4 & 5 highlight the thumbnail whose video is in the gallery slot.
            parent.find('.npfeed-yt-video').removeClass('npfeed-yt-gallery-active');
            $(identifier).closest('.npfeed-yt-video').addClass('npfeed-yt-gallery-active');
            $('html, body').animate({
                scrollTop: galleryModeSelector.offset().top
            }, 500);
        } else if (playMode === 'youtube') {
            let watchUrl = 'https://www.youtube.com/watch?v=' + ytVideoId + '';
            $(identifier).attr("target", "_blank");
            $(identifier).attr("href", watchUrl);
        } else {
            e.preventDefault();
            let inlineModeSelector = parent.find('#npfeed-video-play-' + feedId);
            // Only one inline video plays at a time.
            parent.find('.npfeed-yt-video-player').not(inlineModeSelector).empty().removeClass('npfeed-fluent-player-slot');
            parent.find('.npfeed-yt-video').removeClass('active');
            inlineModeSelector.parents('.npfeed-yt-video').addClass('active');
            wpsrPlayVideo(inlineModeSelector, templateId, ytVideoId, 'npfeed-yt-inline-video-iframe', useFluentPlayer, duration);
        }
    },
    /**
     * Open the popup for one video. The overlay is rebuilt on each open, so a stable
     * slot key tears the last one down.
     *
     * @param {Event} e
     * @param {Element} identifier clicked thumbnail
     */
    openPopUpBox(e, identifier) {
        let templateId = $(identifier).data('template_id');
        let feedId     = $(identifier).data('videoid');
        let data = {
            'action': 'npfeed_youtube_popup_feed',
            'feedId': feedId,
            'templateId': templateId,
            'security': npfeed_popup_params.npfeed_nonce
        };
        $.ajax({
            url: npfeed_popup_params.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#npfeed-yt-feed-'+templateId).find('.npfeed-yt-popup-overlay').remove();
            },
            success: function (data) {

                $('body').append(data);

                wpsrInitInjectedContent($("body > .npfeed-yt-popup-overlay").last(), templateId, 'popup');

                if( $(".npfeed-yt-popup-video-meta-description").length !== 0 ) {
                    let ytContent = $(".npfeed-yt-popup-video-meta-description").html();
                    let ytExcerpt = window.wpsrHelper.htmlSubstring(ytContent, 100);
                    if( ytExcerpt !== undefined ) {
                        $(".npfeed-yt-popup-video-meta-description").html(ytExcerpt.replace(/\n/g, "<br>"));
                    }
                }

                if( $(".npfeed-yt-popup-video-comment-text-inner").length !== 0 ) {
                    $(".npfeed-yt-popup-video-comment-text-inner").each(function () {
                        let ytComment = $(this).html();
                        let ytCommentExcerpt = window.wpsrHelper.htmlSubstring(ytComment, 150);
                        if( ytCommentExcerpt !== undefined ){
                            $(this).html(ytCommentExcerpt.replace(/\n/g, "<br>"));
                        }
                    });
                }
            },
            complete: function () {
            },
            error: function () {
            },
        });

        $(document).on('click', '.npfeed-yt-popup-video-preview', function (e) {
            window.open('https://www.youtube.com/watch?v=' + videoId, '_blank');
        });
    }
}
