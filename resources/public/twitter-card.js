let $ = jQuery;
let wpsrFeeds = {};

export default {
    init: function (){
        let $that = this;
        $('.npfeed-twitter-card-wrapper').each(function (index) {
            wpsrFeeds[index] = $that.wpsrCreateFeed($(this), 2);
            $that.wpsrTwitterCardGenerator(index);
        });
    },
    wpsrCreateFeed(el, checks) {
        let feed = {
            tcLinksToCheck: el.find('.npfeed-has-card-url').length,
            element: el,
            checksRemaining: checks
        };
        return feed;
    },
    wpsrTweetLinkRetriever(el, index) {
        let urls = [];
        el.find('.npfeed-has-card-url').each(function () {
            if (!$(this).hasClass('npfeed-tc-active')) {
                urls.push({url: $(this).attr('data-cardurl'), id:$(this).attr('data-id')});
            }
        });
        return urls;
    },
    // Only http(s) URLs may reach an href/src. javascript:, data: and any other
    // scheme are dropped; relative paths resolve against the page and pass.
    wpsrSafeUrl(url) {
        if (typeof url !== 'string' || url === '') {
            return '';
        }
        try {
            let protocol = new URL(url, window.location.href).protocol;
            return (protocol === 'http:' || protocol === 'https:') ? url : '';
        } catch (e) {
            return '';
        }
    },
    // Escape a URL for use inside a double-quoted CSS url("...") token.
    wpsrCssUrlEscape(url) {
        return url.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    },
    wpsrAddTwitterCards(tcObj, index) {
        let $that = this;
        let checkLinks = wpsrFeeds[index].element.find('.npfeed-has-card-url');
        checkLinks.each(function () {
            let $self = $(this),
                link = $self.attr('data-cardurl'),
                id = $self.attr('data-id');
            wpsrFeeds[index].tcLinksToCheck--;
            if (tcObj.hasOwnProperty(id)) {
                let youtube = (link.indexOf('youtube.com/watch') > -1),
                    youtu = (link.indexOf('youtu.be') > -1),
                    youtube_embed = (link.indexOf('youtube.com/embed') > -1),
                    vimeo = (link.indexOf('vimeo') > -1),
                    soundcloud = (link.indexOf('soundcloud.com') > -1),
                    active_card_video = false;

                if (( youtube || youtu || youtube_embed || vimeo || soundcloud) && $(this).hasClass('npfeed-active-video-card')) {
                    active_card_video = true;
                }

                let twitter_card = tcObj[id]['twitter_card'];
                let titleIndicator = ( twitter_card['twitter:title'] && twitter_card['twitter:title'].length >= 48 ) ? '...' : '';
                let desIndicator = (twitter_card['twitter:description'] && twitter_card['twitter:description'].length >= 100 ) ? '...' : '';

                if (( (twitter_card['twitter:card'] === 'summary_large_image' || twitter_card['twitter:card'] === 'summary')  ) || ( twitter_card['twitter:card'] === 'player' && active_card_video )) {
                    // Re-lay out masonry once images load, so load more stays at the bottom.
                    let templateId = $('.npfeed-twitter-feed-wrapper').data("template-id");
                    let gridId = $('#npfeed-twitter-tweet-' + templateId);
                    let templateType = gridId.data('template-type');
                    let column = gridId.data("column");

                    if( templateType === 'masonry' ) {
                        const $masonryElement = gridId.find('.npfeed-twitter-all-tweets');
                        if ($masonryElement.length && typeof $masonryElement.imagesLoaded === 'function') {
                            $masonryElement.imagesLoaded(function () {
                                if ($masonryElement.length && typeof $masonryElement.masonry === 'function') {
                                    $masonryElement.masonry({
                                        itemSelector: '.npfeed-col-' + column,
                                    });
                                }
                            });
                        }
                    }
                    let domainUrl = (typeof link !== 'undefined') ? link.replace(/^https?\:\/\//i, "").split('/')[0] : '';

                    // Card fields are scraped from third-party pages, so every value is
                    // attacker-controlled. Build the card with DOM APIs: .attr()/.css() and
                    // .text() are never parsed as markup, so a quote cannot break out of its context.
                    let $card = $('<a>', {
                        'class': 'npfeed-twitter-card npfeed-tc-type-' + twitter_card['twitter:card'],
                        target: '_blank'
                    }).attr('href', $that.wpsrSafeUrl(link));

                    let cardImage = $that.wpsrSafeUrl(twitter_card['twitter:image']);
                    if (cardImage) {
                        $('<div>', {'class': 'npfeed-tc-image'})
                            .css('background-image', 'url("' + $that.wpsrCssUrlEscape(cardImage) + '")')
                            .append($('<img>').attr({
                                src: cardImage,
                                alt: twitter_card['twitter:image:alt'] || ''
                            }))
                            .appendTo($card);
                    }

                    $('<div>', {'class': 'npfeed-tc-summary-info'})
                        .append($('<p>', {'class': 'npfeed-tc-heading'})
                            .text(twitter_card['twitter:title'].substr(0, 48) + titleIndicator))
                        .append($('<p>', {'class': 'npfeed-tc-desc'})
                            .text(twitter_card['twitter:description'].substring(0, 100) + desIndicator))
                        .append($('<p>', {'class': 'npfeed-tc-url'}).text(domainUrl))
                        .appendTo($card);

                    $self.find('.npfeed-tweet-text').append($card);
                }
            }
            $self.removeClass('npfeed-has-card-url');
            $(this).addClass('npfeed-tc-active');
            // Remove the url from the tweet text. Match on the href property instead of
            // interpolating the URL into a jQuery selector, which a quote or bracket would corrupt.
            $self.find('.npfeed-tweet-text').find('a.npfeed-link-tweet').filter(function () {
                return $(this).attr('href') === link;
            }).hide();
            return wpsrFeeds[index].tcLinksToCheck;
        });
    },
    wpsrTwitterCardGenerator(index) {
        let twitterCardUrls = this.wpsrTweetLinkRetriever(wpsrFeeds[index].element, index);
        wpsrFeeds[index].checksRemaining--;
        let $that = this;
        if (twitterCardUrls.length > 0) {
            jQuery.ajax({
                url: npfeed_ajax_params.ajax_url,
                type: 'post',
                data: {
                    action: 'npfeed_twitter_cards',
                    npfeed_urls: twitterCardUrls,
                    security: npfeed_ajax_params.npfeed_nonce
                },
                success: function (data) {
                    let urlObject = data;
                    // jQuery only hands back a string when the response has no
                    // JSON content type; when it does, data is already parsed.
                    if (typeof data === 'string') {
                        // A leading '<' or an early <meta> means something printed
                        // markup ahead of the JSON, which we can't parse.
                        if (data.substring(0, 1) === '<' || data.substring(0, 200).indexOf('<meta') !== -1) {
                            return;
                        }
                        try {
                            urlObject = JSON.parse(data);
                        } catch (e) {
                            return;
                        }
                    }
                    if (!urlObject || typeof urlObject !== 'object' || urlObject.hasOwnProperty('error')) {
                        return;
                    }
                    $that.wpsrAddTwitterCards(urlObject, index);
                    window.dispatchEvent(new Event('resize'));
                }
            });
        }
    },
}