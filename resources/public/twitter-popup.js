let $ = jQuery;
let twitterFeedId = null;
let templateId = null;
let totalFeed = null;
let currentFeed = null;
let currentPopupSettings = null;
let additionalSettings = null;
export default {
    init: function (e, $this){
        e.preventDefault();
        this.checkTwitterFeedType(e, $this);
    },
    getVideoUrl(media){
        if (media.variants) {
            let variants = media.variants;
            for (let index = 0; index < variants.length; ++index) {
                let variant = variants[index];
                if (variant.content_type === 'video/mp4') {
                    return variant.url;
                }
            }
        }
    },
    appendFeed(currentFeed, has_media, playMode){
        let parent = $('body');
        if( window.NPFEED_TwitterPopupSettings && window.NPFEED_TwitterPopupSettings[templateId] ) {
            document.body.classList.add('npfeed-feed-popup-active');
            currentPopupSettings = window.NPFEED_TwitterPopupSettings[templateId];
        }
        if( window.NPFEED_TwitterAdditionalSettings && window.NPFEED_TwitterAdditionalSettings[templateId] ) {
            additionalSettings = window.NPFEED_TwitterAdditionalSettings[templateId];
        }

        if(currentFeed && has_media && playMode === 'popup') {
            let data = this.renderPopupHtml(currentFeed, additionalSettings, currentPopupSettings);
            parent.find('.npfeedm-overlay').remove();
            parent.append(data);
            let commentBoxHeight = parent.find('.npfeed-feed-popup-comments-wrapper-inner').innerHeight();
            if (commentBoxHeight && commentBoxHeight > 450) {
                parent.find('.npfeed-feed-popup-comments-wrapper').toggleClass('npfeed-feed-popup-scrollbar');
            }
        }
    },
    renderPagination(direction, e){
        e.preventDefault();
        $('#npfeed-twitter-tweet-' + templateId).find('.npfeed-feed-popup-overlay').remove();

        let has_media = false;
        while(!has_media) {
            if(direction === 'prev') {
                twitterFeedId--;
            } else {
                twitterFeedId++;
            }

            if( window.NPFEED_TwitterFrontEndJson && window.NPFEED_TwitterFrontEndJson[templateId] && window.NPFEED_TwitterFrontEndJson[templateId][twitterFeedId] ) {
                currentFeed = window.NPFEED_TwitterFrontEndJson[templateId][twitterFeedId];
                has_media = currentFeed.media && currentFeed.media.length;
            } else {
                currentFeed = null;
            }

            if(!currentFeed) {
                $('.npfeed-feed-popup-overlay').remove();
                break;
            }

            this.appendFeed(currentFeed, has_media, 'popup');
        }

        if(currentFeed && currentFeed.media) {
            this.activePopupCarousel();
        }
    },
    checkTwitterFeedType(e, identifier){
        e.preventDefault();

        let playMode = $(identifier).data('playmode');
        templateId = $(identifier).data('template-id');
        twitterFeedId = $(identifier).data('index');
        let has_media = false;
        if( window.NPFEED_TwitterFrontEndJson[templateId] ){
            totalFeed = window.NPFEED_TwitterFrontEndJson[templateId].length;
        }

        if( window.NPFEED_TwitterFrontEndJson && window.NPFEED_TwitterFrontEndJson[templateId] && window.NPFEED_TwitterFrontEndJson[templateId][twitterFeedId] ) {
            currentFeed = window.NPFEED_TwitterFrontEndJson[templateId][twitterFeedId];
            has_media = (currentFeed.media && currentFeed.media.length);
        }

        this.appendFeed(currentFeed, has_media, playMode);

        // Start : twitter prev/next button
        let $that = this;
        $('body').off('click').on( 'click', '.npfeed-twitter-next-btn', function(e){
            $that.renderPagination('next', e);
        });

        $('body').on( 'click', '.npfeed-twitter-prev-btn', function(e){
            $that.renderPagination('prev', e);
        });

        this.activePopupCarousel();
    },
    activePopupCarousel() {
        let media = currentFeed.media;
        if(media && media.length > 1) {
            new Swiper('.npfeed-feed-popup-carousel-wrapper', {
                slidesPerView: 1,
                slidesPerGroup: 1,
                pagination: {
                    el: '.npfeed-swiper-pagination',
                    clickable: true
                },
                navigation: {
                    nextEl: '.npfeed-swiper-next',
                    prevEl: '.npfeed-swiper-prev',
                }
            })
        }
    },
    renderUserProfilePhoto(){
        return currentPopupSettings.avatar_image === 'true' ?
            `<a class="npfeed-feed-popup-user-photo" href="https://www.twitter.com/${currentFeed.user.username}" target="_blank" :title="${currentFeed.user.username}">
                <img src="${currentFeed.user.profile_image_url}" />
            </a>` : '';
    },
    renderUsername(){
        return (currentFeed.user.name && currentPopupSettings.author_name === 'true') ?
            `<div class="npfeed-feed-popup-user-name">
            <p>${currentFeed.user.name}</p>
            <a href="https://www.twitter.com/${currentFeed.user.username}" target="_blank" title="${currentFeed.user.username}">@${currentFeed.user.username}</a>
        </div>` : '';
    },
    renderCaptionAndDate(){
        return `<div class="npfeed-feed-popup-comment">
            <div class="npfeed-feed-popup-comment-inner">
             ${currentPopupSettings.tweet_text === 'true' ?
                `<div class="npfeed-feed-popup-comment-text">
                    <p>${window.wpsrHelper.formatText(currentFeed.text, 'https://twitter.com/hashtag/')}</p>
                </div>` : ''
             }
              ${currentPopupSettings.display_date === 'true' ?
                `<div class="npfeed-feed-popup-comment-meta">
                    <time datetime="${currentFeed.created_at}" title="${currentFeed.created_at}">${window.wpsrHelper.formatDate(currentFeed.created_at)}</time>
                </div>` : ''
             }
            </div>
        </div>`;
    },
    renderReplyAction(){
        return (currentPopupSettings.show_reply_action === 'true') ?
            `<a target="_blank" href="https://twitter.com/intent/tweet?in_reply_to=${currentFeed.id}&related=${currentFeed.user.username}" class="npfeed-tweet-reply">
                <svg viewBox="0 0 24 24"><g><path d="M14.046 2.242l-4.148-.01h-.002c-4.374 0-7.8 3.427-7.8 7.802 0 4.098 3.186 7.206 7.465 7.37v3.828c0 .108.044.286.12.403.142.225.384.347.632.347.138 0 .277-.038.402-.118.264-.168 6.473-4.14 8.088-5.506 1.902-1.61 3.04-3.97 3.043-6.312v-.017c-.006-4.367-3.43-7.787-7.8-7.788zm3.787 12.972c-1.134.96-4.862 3.405-6.772 4.643V16.67c0-.414-.335-.75-.75-.75h-.396c-3.66 0-6.318-2.476-6.318-5.886 0-3.534 2.768-6.302 6.3-6.302l4.147.01h.002c3.532 0 6.3 2.766 6.302 6.296-.003 1.91-.942 3.844-2.514 5.176z"></path></g></svg>
            </a>` : '';
    },
    renderRetweetAction(){
        if(currentFeed.statistics) {
            return (currentPopupSettings.show_retweet_action === 'true') ?
                `<a target="_blank" href="https://twitter.com/intent/retweet?tweet_id=${currentFeed.id}&related=${currentFeed.user.username}" class="npfeed-tweet-retweet">
                <svg viewBox="0 0 24 24"><g><path d="M23.77 15.67c-.292-.293-.767-.293-1.06 0l-2.22 2.22V7.65c0-2.068-1.683-3.75-3.75-3.75h-5.85c-.414 0-.75.336-.75.75s.336.75.75.75h5.85c1.24 0 2.25 1.01 2.25 2.25v10.24l-2.22-2.22c-.293-.293-.768-.293-1.06 0s-.294.768 0 1.06l3.5 3.5c.145.147.337.22.53.22s.383-.072.53-.22l3.5-3.5c.294-.292.294-.767 0-1.06zm-10.66 3.28H7.26c-1.24 0-2.25-1.01-2.25-2.25V6.46l2.22 2.22c.148.147.34.22.532.22s.384-.073.53-.22c.293-.293.293-.768 0-1.06l-3.5-3.5c-.293-.294-.768-.294-1.06 0l-3.5 3.5c-.294.292-.294.767 0 1.06s.767.293 1.06 0l2.22-2.22V16.7c0 2.068 1.683 3.75 3.75 3.75h5.85c.414 0 .75-.336.75-.75s-.337-.75-.75-.75z"></path></g></svg>
                <span>${window.wpsrHelper.shortNumberFormat(currentFeed.statistics.retweet_count)}</span>
            </a>` : '';
        }
    },
    renderLikeAction(){
        if(currentFeed.statistics) {
            return (currentPopupSettings.show_like_action === 'true') ?
                `<a target="_blank" href="https://twitter.com/intent/like?tweet_id=${currentFeed.id}&related=${currentFeed.user.username}" class="npfeed-tweet-like">
                <svg viewBox="0 0 24 24"><g><path d="M12 21.638h-.014C9.403 21.59 1.95 14.856 1.95 8.478c0-3.064 2.525-5.754 5.403-5.754 2.29 0 3.83 1.58 4.646 2.73.814-1.148 2.354-2.73 4.645-2.73 2.88 0 5.404 2.69 5.404 5.755 0 6.376-7.454 13.11-10.037 13.157H12zM7.354 4.225c-2.08 0-3.903 1.988-3.903 4.255 0 5.74 7.034 11.596 8.55 11.658 1.518-.062 8.55-5.917 8.55-11.658 0-2.267-1.823-4.255-3.903-4.255-2.528 0-3.94 2.936-3.952 2.965-.23.562-1.156.562-1.387 0-.014-.03-1.425-2.965-3.954-2.965z"></path></g></svg>
                <span>${window.wpsrHelper.shortNumberFormat(currentFeed.statistics.like_count)}</span>
            </a>` : '';
        }
    },
    renderCarousel(media) {
        if(!media) return;
        return `<div class="npfeed-feed-popup-carousel-wrapper swiper-container">
                    <div class="swiper-wrapper">
                        ${media.map((item) => {
                        let videoAttrs = item.type === 'video' ? 'controls' : 'loop';
                        return `<div class="swiper-slide">
                                    ${item.type === 'photo' ? `<img src="${item.url}" alt="Image">` : ''}
                                    ${item.type === 'animated_gif' || item.type === 'video' ? `<video id="npfeed-feed-popup-video"  playsinline="" src="${this.getVideoUrl(item)}" ${videoAttrs} muted="muted" autoplay="true" preload="none" poster=""></video>` : ''}
                                </div>`
                        }).join('')};
                    </div>
                    <div class="npfeed-swiper-prev-next npfeed-swiper-next icon-arrow-circle-o-right"></div>
                    <div class="npfeed-swiper-prev-next npfeed-swiper-prev icon-arrow-circle-o-left"></div>
                    <div class="npfeed-swiper-pagination swiper-pagination"></div>
                </div>`;
    },
    renderMedia(media) {
        if(!media || !media.length) return;

        let item = media[0];
        if (item.type === 'photo') {
            return `<img src="${item.url}" alt="Image">`;
        }  else if(item.type === 'animated_gif' || item.type === 'video') {
            let video_url = this.getVideoUrl(item);
            let videoAttrs = item.type === 'video' ? 'controls' : 'loop';
            return `<video id="npfeed-feed-popup-video"  playsinline="" src="${video_url}" ${videoAttrs} muted="muted" autoplay="true" preload="none" poster=""></video>`
        }
    },
    renderPopupHtml(currentFeed, additionalSettings, currentPopupSettings){
        let media = currentFeed.media;
        let className = '';
        if( currentPopupSettings.display_sidebar === 'false' ) {
            className = 'npfeed-feed-popup-box-no-sidebar';
        }

        return `<div class="npfeed-feed-popup-overlay npfeed-feed-popup-open npfeed_content npfeedm-overlay">
                <div class="npfeed-feed-popup-box-wraper">
                    <div class="npfeed-feed-popup-box-wraper-inner npfeedm_inner ${className}">
                        ${currentPopupSettings.display_next_prev_arrows === 'true' ?
                            `<div class="npfeed-prev-btn npfeed-twitter-prev-btn"><span class="icon-angle-left"></span></div>
                            <div class="npfeed-next-btn npfeed-twitter-next-btn"><span class="icon-angle-right"></span></div>` : ``
                        }
                        
                        <div class="npfeed-feed-popup-close-btn npfeedm_close"></div>
                        <div class="npfeed-feed-popup-media npfeed-twitter-feed-popup-media">
                            ${(media && media.length > 1) ? this.renderCarousel(media) : this.renderMedia(media)}
                        </div>     
                        ${ currentPopupSettings.display_sidebar === 'true' ?
                        `<div class="npfeed-feed-popup-box-content">
                                ${ currentPopupSettings.avatar_image === 'true' || currentPopupSettings.author_name === 'true' ?
                                `<div class="npfeed-feed-popup-user-info" >
                                        ${ this.renderUserProfilePhoto() }
                                        ${ this.renderUsername() }
                                    </div>` : ''
                                }
                                <div class="npfeed-feed-popup-comments-wrapper">
                                    <div class="npfeed-feed-popup-comments-wrapper-inner">
                                        ${this.renderCaptionAndDate()}
                                    </div>
                                </div>
                                <div class="npfeed-feed-popup-statistics-date">
                                    <div class="npfeed-feed-popup-tweet-actions">
                                        ${ this.renderReplyAction() }
                                        ${ this.renderRetweetAction() }
                                        ${ this.renderLikeAction() }
                                    </div>
                                </div>
                        </div>` : ''
                        }
                    </div>
                </div>
            </div>`;
    }
}