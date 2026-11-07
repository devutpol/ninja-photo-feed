import TwitterPopup from './twitter-popup.js';
import YoutubePopup from './youtube-popup.js';
import InstagramPopup from './_instagram-popup';
import FacebookPopup from './_facebook-popup';
import TiktokPopup from './_tiktok-popup';
import ReviewPhotos from './_review-photos';

import Helper, { wpsrRelayoutMasonry } from './helper.js';

window.wpsrHelper = Helper;

jQuery(document).ready(function ($) {
    $(document).on('click', '.npfeed-yt-video-playmode', function (e) {
        YoutubePopup.init(e, this);
    });

    // template5's whole row looks clickable, but only the thumbnail/title are wired, so forward the rest here.
    // dispatchEvent rather than jQuery's .trigger(), which its own re-entrancy guard swallows.
    $(document).on('click', '.npfeed-yt-feed-template5 .npfeed-yt-all-feed .npfeed-yt-video', function (e) {
        if ($(e.target).closest('.npfeed-yt-video-playmode').length) {
            return;
        }
        let target = this.querySelector('.npfeed-yt-video-preview.npfeed-yt-video-playmode');
        if (target) {
            target.dispatchEvent(new MouseEvent('click', {bubbles: true, cancelable: true, view: window}));
        }
    });

    $(document).on('click', '.npfeed-twitter-playmode', function (e) {
        TwitterPopup.init(e, this);
    });

    $(document).on('click', '.npfeed-ig-playmode', function (e) {
        InstagramPopup.init(e, this);
    });

    $(document).on('click', '.npfeed-fb-feed-playmode', function (e) {
        FacebookPopup.init(e, this);
    });

    $(document).on('click', '.npfeed-tiktok-feed-playmode, .npfeed-tiktok-feed-video-playmode, .npfeed-tiktok-icon-position', function (e) {
        TiktokPopup.init(e, this);
    });

    $(document).on('click', '.npfeed-review-photos-playmode', function (e) {
        ReviewPhotos.init(e, this);
    });

    // Element that opened the popup, so focus can go back there on close.
    let wpsrPopupOpener = null;

    const wpsrFocusableInPopup = ($overlay) => $overlay
        .find('a[href], button, video[controls], [tabindex]:not([tabindex="-1"])')
        .filter(':visible');

    function wpsrCloseFeedPopup() {
        const $overlay = $('.npfeedm-overlay');
        if (!$overlay.length) {
            return;
        }

        let video = jQuery("#npfeed-feed-popup-video");
        if (video.length) {
            video[0].pause();
        }

        $overlay.remove();
        document.body.classList.remove('npfeed-feed-popup-active');

        if (wpsrPopupOpener && document.body.contains(wpsrPopupOpener)) {
            wpsrPopupOpener.focus();
        }
        wpsrPopupOpener = null;
    }

    // Popups are appended by each platform's init(), so adopt whichever one just opened.
    function wpsrAdoptOpenPopup(opener) {
        const $overlay = $('.npfeedm-overlay');
        if (!$overlay.length) {
            return;
        }

        wpsrPopupOpener = opener || null;
        $overlay.attr({'role': 'dialog', 'aria-modal': 'true'});
        if (!$overlay.attr('aria-label')) {
            $overlay.attr('aria-label', 'Post details');
        }

        const $target = $overlay.find('.npfeedm_close').first();
        ($target.length ? $target : $overlay).attr('tabindex', $target.length ? null : '-1').focus();
    }

    // Every popup opens through one of the .init() handlers above.
    $(document).on('click', '.npfeed-yt-video-playmode, .npfeed-twitter-playmode, .npfeed-ig-playmode, .npfeed-fb-feed-playmode, .npfeed-tiktok-feed-playmode, .npfeed-tiktok-feed-video-playmode, .npfeed-tiktok-icon-position, .npfeed-review-photos-playmode', function () {
        const opener = this;
        window.setTimeout(function () {
            wpsrAdoptOpenPopup(opener);
        }, 0);
    });

    $(document).on('keydown', function (e) {
        const $overlay = $('.npfeedm-overlay');
        if (!$overlay.length) {
            return;
        }

        if (e.key === 'Escape' || e.key === 'Esc') {
            e.preventDefault();
            wpsrCloseFeedPopup();
            return;
        }

        if (e.key !== 'Tab') {
            return;
        }

        // Keep Tab inside the dialog instead of walking the page behind it.
        const $focusable = wpsrFocusableInPopup($overlay);
        if (!$focusable.length) {
            e.preventDefault();
            return;
        }

        const first = $focusable.first()[0];
        const last = $focusable.last()[0];
        const active = document.activeElement;

        if (!$overlay[0].contains(active)) {
            e.preventDefault();
            first.focus();
        } else if (e.shiftKey && active === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && active === last) {
            e.preventDefault();
            first.focus();
        }
    });

    $(document).on('click', function (e) {
        if ($(e.target).closest(".npfeedm_close").length ||  $(e.target).has(".npfeedm_inner").length) {
            wpsrCloseFeedPopup();
        }
    });

    $(document).on("click", ".npfeed_read_more, .npfeed_read_less", function () {
        $(this).closest(".npfeed-yt-popup-video-meta-description, .npfeed-yt-popup-video-comment-text-inner").toggleClass("npfeed_show_less_content npfeed_show_more_content");
        // The card's own container, so a second template on the page keeps its columns.
        wpsrRelayoutMasonry($(this).closest('.npfeed-active-masonry-layout'));
    });
});