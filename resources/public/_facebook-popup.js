import {checkBFScrollbar, formatText, escapeHtml, escapeHtmlAttr} from './helper';

let $ = jQuery;
let templateId = null;
let feedId = null;
let totalFeed = null;
let currentFeed = null;
let currentPopupSettings = null;
let additionalSettings = null;
let feedType = null;
let albumCta = null;
let albumMessage = null;
let optimizedImages = null;
let hasGdpr = null;
let postId = null;
let imageSize = null;
let userName = null;
let uploadUrl = null;

function getWpsrImageFormat() {
    let imageFormat = window.npfeed_ajax_params
        && window.npfeed_ajax_params.image_settings
        && window.npfeed_ajax_params.image_settings.image_format
        ? window.npfeed_ajax_params.image_settings.image_format
        : '';

    return ['jpg', 'webp'].includes(imageFormat) ? imageFormat : 'jpg';
}

// The href must be encoded, otherwise a permalink carrying its own query
// string (/watch/?v=123) is truncated by the plugin endpoint.
function wpsrVideoEmbedUrl(videoUrl, autoplay = 1) {
    if (!videoUrl) {
        return '';
    }

    return `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(videoUrl)}&autoplay=${autoplay}`;
}

// Only facebook knows what it will frame, so trust the graph field and treat a
// missing one (the /posts edge omits it) as embeddable.
function wpsrIsEmbeddable(feed) {
    return !feed || feed.embeddable !== false;
}

// Playable mp4 a timeline video ships with, mirroring the editor preview.
// Strictly the media source, never a permalink, so it is safe in a <video>.
function wpsrTimelineVideoSource(feed) {
    let attachment = feed && feed.attachments && feed.attachments.data ? feed.attachments.data[0] : null;

    if (!attachment || !attachment.media) {
        return '';
    }

    return attachment.media.source
        || (attachment.media.video && attachment.media.video.source)
        || '';
}

function wpsrVideoThumbnail(feed) {
    if (!feed) {
        return '';
    }

    if (feed.media_url) {
        return feed.media_url;
    }

    if (feed.format && feed.format.length) {
        let format = feed.format[1] || feed.format[0];
        return format && format.picture ? format.picture : '';
    }

    return '';
}

export default {
    init: function (e, $this){
        this.checkFacebookFeedType(e, $this);

        // Initialize video click handlers
        this.handleMainFeedVideoClick();

        // Checks and ensures compatibility of the Bricksforge custom scrollbar functionality
        checkBFScrollbar();
    },

    renderPopupHtml() {
        let className = '';
        if(currentPopupSettings.display_sidebar === 'false' ) {
            className = 'npfeed-feed-popup-box-no-sidebar';
        }
        let image_format = getWpsrImageFormat();
        currentFeed.media_url =  optimizedImages ? uploadUrl +'/facebook_feed' +'/' +  userName + '/' + postId + '_' + imageSize + '.' + image_format : currentFeed.media_url;
        let video_attachment_url = '';
        if(currentFeed.status_type === 'added_video'){
           video_attachment_url = currentFeed.attachments && currentFeed.attachments.data && currentFeed.attachments.data[0].url ? currentFeed.attachments.data[0].url : '';
        }
        let feed_type = '';
        if(currentFeed.attachments && currentFeed.attachments.data && (currentFeed.attachments.data[0].type === 'video_inline' || currentFeed.attachments.data[0].type === 'animated_image_video')) {
            video_attachment_url = currentFeed.attachments.data[0].url;
            feed_type = 'video';
        }
        let cta_url = currentFeed.attachments && currentFeed.attachments.data && currentFeed.attachments.data[0].url ? currentFeed.attachments.data[0].url : currentFeed.permalink_url ? currentFeed.permalink_url : currentFeed.link;
        if (additionalSettings.feed_type === 'event_feed') {
            cta_url = `https://www.facebook.com/events/${currentFeed.id}`;
        }

        this.renderAvatar();
        return `<div class="npfeed-feed-popup-overlay npfeed-fb-feed-popup npfeedm-overlay npfeed_content npfeed-feed-popup-open">
    <div class="npfeed-feed-popup-box-wraper">
      <div class="npfeed-feed-popup-box-wraper-inner npfeedm_inner ${className} npfeed-feed-${feedType}">
       ${currentPopupSettings.display_next_prev_arrows === 'true' ?
            `<div class="npfeed-prev-btn npfeed-fb-feed-prev-btn"><span class="icon-angle-left"></span></div>
             <div class="npfeed-next-btn npfeed-fb-feed-next-btn"><span class="icon-angle-right"></span></div>` : ``
        }
        
        <div class="npfeed-feed-popup-close-btn npfeedm_close"></div>
        
         ${(additionalSettings.feed_type === 'video_feed' || additionalSettings.feed_type === 'video_playlist_feed') ?
            `${this.renderVideo()}` : ``
         }
         ${additionalSettings.feed_type !== 'video_feed' && (currentFeed.attachments || currentFeed.images || currentFeed.cover || currentFeed.photos) ?
            `<div class="npfeed-feed-popup-media ${optimizedImages ? 'npfeed-optimize-media ' : ''} ${!optimizedImages && (feed_type === 'video') && !wpsrTimelineVideoSource(currentFeed) ? 'npfeed-gdpr-off' : ''}">
              ${video_attachment_url && (currentFeed.status_type === 'added_video' || feed_type === 'video') ?
                `<div class="npfeed-feed-popup-photo-media">
                    ${optimizedImages ?
                        `<a href="${escapeHtmlAttr(cta_url)}" target="_blank"><img src="${escapeHtmlAttr(currentFeed.media_url)}" alt="${escapeHtmlAttr(currentFeed.description ? currentFeed.description : '')}"><div class="npfeed-fb-feed-video-play">
                        <div class="npfeed-fb-feed-video-play-icon"></div></div></a>` :
                        wpsrTimelineVideoSource(currentFeed) ?
                        this.renderTimelineVideo(wpsrTimelineVideoSource(currentFeed)) :
                        !wpsrIsEmbeddable(currentFeed) ?
                        this.renderNonEmbeddableVideo(video_attachment_url) :
                        `<div class="npfeed-feed-popup-loader npfeed-popup-loading">
                      <div class="npfeed-spinner-animation"></div>
                    </div><iframe id="npfeed-feed-popup-video-iframe" src="${escapeHtmlAttr(wpsrVideoEmbedUrl(video_attachment_url))}" allowtransparency="true" allowfullscreen="true" frameborder="0" scrolling="no" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture"></iframe>`
                    }
                </div>` : ``
                }
        
                ${ (currentFeed.attachments && currentFeed.attachments.data && currentFeed.attachments.data[0].subattachments && !optimizedImages) ? this.renderCarousel() : this.renderPhoto() }
            </div>` : ``
        }
        ${currentPopupSettings.display_sidebar === 'true' ?
            `<div class="npfeed-feed-popup-box-content">
          
            ${currentFeed.from || additionalSettings.feed_type !== 'event_feed' ?
             `<div class="npfeed-feed-popup-user-info">
                    ${this.renderAvatar()}
                    <div class="npfeed-feed-popup-group">
                      ${this.renderUserName()}
                      ${ this.renderDate() }
                    </div>
                </div>` : ``
            }
            
           ${currentPopupSettings.display_likes_count === 'true' && additionalSettings.feed_type !== 'video_feed' && (currentFeed.comments_count || currentFeed.react_count  || (currentFeed.comments && currentFeed.comments.data && currentFeed.comments.data.length > 0)) ?
             `<div class="npfeed-fb-feed-statistics">
                <div class="npfeed-fb-feed-reactions">
                    ${currentFeed.like && currentFeed.like.summary.total_count ?
                     `<div class="npfeed-fb-feed-reactions-icon-like npfeed-fb-feed-reactions-icon"></div>` : ``
                     }
                    
                     ${currentFeed.love && currentFeed.love.summary.total_count ?
                         `<div class="npfeed-fb-feed-reactions-icon-love npfeed-fb-feed-reactions-icon"></div>` : ``
                     }
                     
                     ${currentFeed.wow && currentFeed.wow.summary.total_count ?
                         `<div class="npfeed-fb-feed-reactions-icon-wow npfeed-fb-feed-reactions-icon"></div>` : ``
                     }
                      
                     ${currentFeed.sad && currentFeed.sad.summary.total_count ?
                         `<div class="npfeed-fb-feed-reactions-icon-sad npfeed-fb-feed-reactions-icon"></div>` : ``
                     }
                     
                     ${currentFeed.angry && currentFeed.angry.summary.total_count ?
                         `<div class="npfeed-fb-feed-reactions-icon-angry npfeed-fb-feed-reactions-icon"></div>` : ``
                     }
                     
                    ${currentFeed.react_count ?
                         `<div class="npfeed-fb-feed-reaction-count">${currentFeed.react_count}</div>` : ``
                     }
                </div>
                
                 ${ currentFeed.comments_count ?
                     `<div class="npfeed-fb-feed-comments-count">${currentFeed.comments_count}</div>` : ``
                 }
            </div>` : ``
            }
           
           ${additionalSettings.feed_type !== 'video_feed' ?
            `<div class="npfeed-feed-popup-comments-wrapper">
                <div class="npfeed-feed-popup-comments-wrapper-inner">
                    ${currentPopupSettings.display_caption === 'true' ?
                        `<div class="npfeed-feed-popup-comment">
                            <div class="npfeed-feed-popup-comment-inner">
                                ${additionalSettings.feed_type !== 'event_feed' ?
                                    `<div class="npfeed-feed-popup-comment-text">
                                        ${this.renderMessage()}
                                        ${additionalSettings.feed_type !== 'album_feed' ? this.renderName() : ''}
                                    </div>` : ``
                                }
                                ${additionalSettings.feed_type === 'event_feed' ? this.rednderEventPopup() : ''}
                            </div>
                          </div>` : ``
                    }
                    
                  ${currentPopupSettings.display_comments === 'true' && currentFeed.comments && currentFeed.comments.data ? Object.keys(currentFeed.comments.data).map(function (key) {
                        return `<div class="npfeed-feed-popup-comment">
                            <div class="npfeed-feed-popup-comment-inner npfeed-feed-popup-comment-list">
                                <div class="npfeed-feed-popup-comment-text npfeed-feed-popup-comment-item">
                                    ${currentPopupSettings.display_comments_user_picture === 'true' && !optimizedImages && currentFeed.comments.data[key].from.picture ?
                                        `<img class="npfeed-feed-popup-comment-user-avatar" src="${escapeHtmlAttr(currentFeed.comments.data[key].from.picture.data.url)}" alt="${escapeHtmlAttr(currentFeed.comments.data[key].from.name)}">` : ``
                                    }
                                    <div class="npfeed-feed-popup-comment-group">
                                        <span>${escapeHtml(currentFeed.comments.data[key].from.name)}</span>
                                        <p>${formatText(currentFeed.comments.data[key].message)}</p>
                                    </div>
                                </div>
                                </div>
                          </div>`
                        }).join('') : '' }
                </div>
              </div>` : ``
            }
          
           ${additionalSettings.feed_type !== 'video_feed' && currentPopupSettings.display_cta_btn === 'true' ? 
            `<div class="npfeed-feed-popup-view-post-cta">
                <a href="${escapeHtmlAttr(albumCta ? albumCta : cta_url)}" target="_self">
                  <span class="npfeed-icon icon-facebook-square"></span>
                  <span>${escapeHtml(window.npfeed_ajax_params.view_on_fb)}</span>
                </a>
            </div>` : ``
           }
          
        </div>` : ``
        }
        
      </div>
    </div>
  </div>
`
    },

    renderVideo() {
        if(additionalSettings.feed_type === 'video_feed' || additionalSettings.feed_type === 'video_playlist_feed') {
            return `<div class="npfeed-fb-feed-popup-video-player npfeed-feed-popup-media ${optimizedImages ? 'npfeed-optimize-media' : ''}">
                <div>

                ${optimizedImages ?
                    `<a class="npfeed-optimize-image-wrapper" href="${escapeHtmlAttr(`https://www.facebook.com/${currentFeed.permalink_url}?autoplay=1`)}" target="_blank"><img src="${escapeHtmlAttr(currentFeed.media_url)}" alt="${escapeHtmlAttr(currentFeed.description ? currentFeed.description : '')}" class="${optimizedImages ? 'npfeed-video-optimized-img' : ''}"><div class="npfeed-fb-feed-video-play"><div class="npfeed-fb-feed-video-play-icon"></div></div></a>` :
                    !wpsrIsEmbeddable(currentFeed) ?
                    this.renderNonEmbeddableVideo(`https://www.facebook.com${currentFeed.permalink_url}`) :
                    `<div class="npfeed-feed-popup-loader npfeed-popup-loading">
                    <div class="npfeed-spinner-animation"></div>
                </div><iframe id="npfeed-feed-popup-video-iframe" src="${escapeHtmlAttr(wpsrVideoEmbedUrl(`https://www.facebook.com${currentFeed.permalink_url}`))}" allowfullscreen="true" frameborder="0" scrolling="no" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture">
                    </iframe>`
                }
            </div>
            </div>`
        }

        return '';
    },

    // Facebook restricts framing per video, so play its own mp4 where one exists.
    renderTimelineVideo(videoUrl) {
        let poster = wpsrVideoThumbnail(currentFeed);
        let attachment = currentFeed.attachments && currentFeed.attachments.data ? currentFeed.attachments.data[0] : null;
        // Animated images are gifs in disguise: silent, and expected to loop.
        let gifAttrs = attachment && attachment.type === 'animated_image_video' ? ' loop muted' : '';

        // src sits on the element, not a <source> child, so a failed load fires
        // error here rather than only on the child and reaches the fallback.
        return `<video id="npfeed-feed-popup-video" class="npfeed-feed-popup-video-player" controls autoplay playsinline preload="metadata" src="${escapeHtmlAttr(videoUrl)}" type="video/mp4"${gifAttrs}${poster ? ` poster="${escapeHtmlAttr(poster)}"` : ''}></video>`
    },

    // A signed cdn source can expire before the feed cache refreshes, so hand
    // the slot back to facebook instead of leaving dead controls behind.
    replaceFailedVideo($video) {
        let attachment = currentFeed.attachments && currentFeed.attachments.data ? currentFeed.attachments.data[0] : null;
        let watchUrl = attachment && attachment.url ? attachment.url
            : currentFeed.permalink_url ? currentFeed.permalink_url : '';

        if (!watchUrl) {
            return;
        }

        if (!wpsrIsEmbeddable(currentFeed)) {
            $video.replaceWith(this.renderNonEmbeddableVideo(watchUrl));
            return;
        }

        // The iframe is absolutely positioned, so restore the aspect-ratio box
        // that the <video> branch deliberately leaves off.
        $video.closest('.npfeed-feed-popup-media').addClass('npfeed-gdpr-off');
        $video.replaceWith(`<iframe id="npfeed-feed-popup-video-iframe" src="${escapeHtmlAttr(wpsrVideoEmbedUrl(watchUrl))}" allowtransparency="true" allowfullscreen="true" frameborder="0" scrolling="no" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture"></iframe>`);
    },

    renderNonEmbeddableVideo(watchUrl) {
        let thumbnail = wpsrVideoThumbnail(currentFeed);

        return `<a class="npfeed-fb-feed-video-not-embeddable" href="${escapeHtmlAttr(watchUrl)}" target="_blank" rel="nofollow">
            ${thumbnail ? `<img src="${escapeHtmlAttr(thumbnail)}" alt="${escapeHtmlAttr(currentFeed.description ? currentFeed.description : '')}">` : ''}
            <div class="npfeed-fb-feed-video-play"><div class="npfeed-fb-feed-video-play-icon"></div></div>
            <span class="npfeed-fb-feed-video-not-embeddable-note">${escapeHtml(window.npfeed_ajax_params.view_on_fb)}</span>
        </a>`
    },

    renderAvatar() {
        if (additionalSettings.feed_type === 'event_feed') return '';
        let picture = optimizedImages ? currentFeed.user_avatar : currentFeed.from && currentFeed.from.picture ? currentFeed.from.picture.data.url : '';
        let user_name = currentFeed.from ? currentFeed.from.name : '';
        let id = currentFeed.from ? currentFeed.from.id : '';

        return currentPopupSettings.display_profile_photo === 'true' ?
            `<a class="npfeed-feed-popup-user-photo" href="https://www.facebook.com/${id}" target="_blank" title="${user_name}">
              <img src="${picture}" />
            </a>` : '';
    },

    renderUserName() {
        let user_name = currentFeed.from ? currentFeed.from.name : '';
        let id = currentFeed.from ? currentFeed.from.id : '';

        return currentPopupSettings.display_username === 'true' ?
            `<div class="npfeed-feed-popup-user-name">
                <a href="https://www.facebook.com/${id}" target="_blank" title="${user_name}">${user_name}</a>
              </div>` : '';
    },

    renderDate(){
        return (currentPopupSettings.display_date === 'true' && currentFeed.created_time ) ?
            `<div class="npfeed-feed-popup-date">
               <time datetime="${ currentFeed.created_time }" title="${ currentFeed.created_time }">${ currentFeed.created_time  }</time>
            </div>` : '';
    },

    renderMessage() {
        if (additionalSettings.feed_type === 'album_feed') {
            return albumMessage !== undefined ? ` <p>${formatText(albumMessage, 'https://facebook.com/hashtag/')}</p>` : ``;
        } else {
            return currentFeed.message !== undefined ? ` <p>${formatText(currentFeed.message, 'https://facebook.com/hashtag/')}</p>` : ``;
        }
    },

    renderName()
    {
            return currentFeed.name !== undefined ? ` <p>${formatText( currentFeed.name, 'https://facebook.com/hashtag/' )}</p>` : ``;
    },

    rednderEventPopup()
    {
        return `
            <div class="npfeed-feed-popup-user-info npfeed-feed-popup-event-info">     
                <div class="npfeed-feed-popup-group">
                    <div class="npfeed-feed-popup-user-name">
                        <a href="https://www.facebook.com/${currentFeed.id}" target="_blank" title="${currentFeed.name}">${currentFeed.name}</a>
                    </div>
                </div>
            </div>
                
            <div class="npfeed-feed-popup-event">
              <div class="npfeed-feed-popup-event-item-icon">
                <i class="dashicons dashicons-groups"></i>
              </div>
              <div class="npfeed-feed-popup-event-item">
                ${(currentFeed.interested_count || 0) +( currentFeed.attending_count || 0) + (currentFeed.declined_count || 0) + (currentFeed.noreply_count ||0)}
                ${window.npfeed_ajax_params.people_responded}
              </div>
            </div>
        
            <div class="npfeed-feed-popup-event">
              <div class="npfeed-feed-popup-event-item-icon">
                <i class="dashicons dashicons-location"></i>
              </div>
              ${currentFeed.is_online ? 
                `<div class="npfeed-feed-popup-event-item npfeed-popup-location">
                    ${window.npfeed_ajax_params.online_event}
                </div>` : 
                `<div class="npfeed-feed-popup-event-item npfeed-popup-location">
                    ${currentFeed.place.name}
                </div>`
              }
            </div>   
            ${currentPopupSettings.display_date === 'true' && `
                <div class="npfeed-feed-popup-event">
                  <div class="npfeed-feed-popup-event-item-icon">
                    <i class="dashicons dashicons-calendar-alt"></i>
                  </div>
                      <div class="npfeed-feed-popup-event-item npfeed-feed-popup-date" >
                        <time datetime="${currentFeed.start_time}" title="${currentFeed.start_time}">${currentFeed.start_time}</time>
                        ${currentFeed.end_time ? `
                            - <time datetime="${currentFeed.end_time}" title="${currentFeed.end_time}">${currentFeed.end_time}</time>
                        ` : ''}
                      </div>
                </div>`
            }
            ${currentPopupSettings.display_caption === 'true' && `
                <div class="npfeed-feed-popup-description-text">
                  <p>${currentFeed.description}</p>
                </div>`
            }
    
            ${currentFeed.category && `
                <div class="npfeed-feed-popup-chip" v-if="feed.category">
                  <div class="npfeed-feed-popup-chip-item">
                    <span>${currentFeed.category}</span>
                  </div>
                </div>`
            }
        `
    },

    renderPhoto() {
        if(currentFeed.attachments && currentFeed.attachments.data && currentFeed.attachments.data[0].type === 'video_inline' && additionalSettings.feed_type !== 'event_feed') {
            return '';
        }
        let photo = '';
        let altText = '';

        if(additionalSettings.feed_type === 'timeline_feed' && currentFeed.status_type !== 'added_video') {
            if(currentFeed.media_url && optimizedImages) {
                photo = currentFeed.media_url;
            } else if( currentFeed.attachments && currentFeed.attachments.data && currentFeed.attachments.data[0].media && currentFeed.attachments.data[0].media.image.src) {
                photo = currentFeed.attachments.data[0].media.image.src;
            }
           // altText = this.renderMessage();
        }
        if(additionalSettings.feed_type === 'photo_feed'){
            if(currentFeed.media_url && optimizedImages) {
                photo = currentFeed.media_url;
            }else{
                photo = currentFeed.images && currentFeed.images[2] ? currentFeed.images[2]['source'] : '#';
            }
            //altText = this.renderName();
        }

        if(additionalSettings.feed_type === 'album_feed'){
            let activeAlbums = $('.npfeed-album-cover-photo-wrapper-inner.active');
            let albumId = activeAlbums.attr('id');
            let allAlums = window.NPFEED_FacebookFeedFrontEndJson[templateId];

            let album = allAlums.find(function (album) {
                return album.id === albumId;
            });

            currentFeed = album;
            totalFeed = album.photos && album.photos.data.length;
            photo = album.photos && album.photos.data ? album.photos.data[feedId].source : '#';
            albumCta = album.photos && album.photos.data ? album.photos.data[feedId].link : '#';
            albumMessage = album.photos && album.photos.data ? album.photos.data[feedId].name : '';

            if(optimizedImages && currentFeed && currentFeed.media_url && currentFeed.photos && currentFeed.photos.data && currentFeed.photos.data[feedId]) {
                photo = currentFeed.photos.data[feedId].media_url;
            }
            //altText = this.renderName();
        }

        if(additionalSettings.feed_type === 'event_feed'){
            photo = currentFeed.cover.source;
            if(currentFeed.media_url && optimizedImages) {
                photo = currentFeed.media_url;
            }
        }

        if(additionalSettings.feed_type === 'single_album_feed' && currentFeed.images && currentFeed.images.length > 0){
            photo = currentFeed.images[0].source;
            if(currentFeed.media_url && optimizedImages) {
                photo = currentFeed.media_url;
            }
            
        }


        if(photo){
            return `<div class="npfeed-feed-popup-photo-media npfeed-popup-photo" > <div class="npfeed-feed-popup-loader npfeed-popup-loading">
                      <div class="npfeed-spinner-animation"></div>
                    </div><img src="${photo}" target="_blank"/></div>`;
        } else {
           return photo;
        }
    },

    appendFeed(parent, templateId, feedId) {

        if( window.NPFEED_FacebookFeedAdditionalSettings && window.NPFEED_FacebookFeedAdditionalSettings[templateId] ) {
            additionalSettings = window.NPFEED_FacebookFeedAdditionalSettings[templateId];
        }
        
        if( window.NPFEED_FacebookFeedFrontEndJson && window.NPFEED_FacebookFeedFrontEndJson[templateId] && window.NPFEED_FacebookFeedFrontEndJson[templateId][feedId] && additionalSettings.feed_type !== 'album_feed') {
            document.body.classList.add('npfeed-feed-popup-active');
            currentFeed = window.NPFEED_FacebookFeedFrontEndJson[templateId][feedId];
            postId = currentFeed.id || null;
            userName = currentFeed.page_id || '';
        }else if(window.NPFEED_FacebookFeedFrontEndJson && window.NPFEED_FacebookFeedFrontEndJson[templateId] && additionalSettings.feed_type === 'album_feed'){
            let activeAlbums = $('.npfeed-album-cover-photo-wrapper-inner.active');
            let albumId = activeAlbums.attr('id');
            let allAlums = window.NPFEED_FacebookFeedFrontEndJson[templateId];
            let album = allAlums.find(function (album) {
                return album.id === albumId;
            });
            currentFeed = album;
        }
        if( window.NPFEED_FacebookFeedPopupSettings && window.NPFEED_FacebookFeedPopupSettings[templateId] ) {
            currentPopupSettings = window.NPFEED_FacebookFeedPopupSettings[templateId];
        }
        
        
        if( currentPopupSettings ) {
            let data = this.renderPopupHtml(currentFeed, additionalSettings, currentPopupSettings);
            parent.append(data);
            let commentBoxHeight = parent.find('.npfeed-feed-popup-comments-wrapper-inner').innerHeight();
            if (commentBoxHeight && commentBoxHeight > 270) {
                parent.find('.npfeed-feed-popup-comments-wrapper').toggleClass('npfeed-feed-popup-scrollbar');
            }

            if (currentFeed.attachments && currentFeed.comments.data.length > 5) {
                parent.find('.npfeed-feed-popup-comments-wrapper').toggleClass('npfeed-feed-popup-have-comments');
            }

            let video = jQuery("#npfeed-feed-popup-video");
            if(video.length) {
                video.on('error', () => this.replaceFailedVideo(video));
                video[0].play();
            }
        }
    },
    activePopupCarousel() {
        if(currentFeed.attachments && currentFeed.attachments.data && currentFeed.attachments.data[0].subattachments) {
            const swiper = new Swiper('.npfeed-feed-popup-carousel-wrapper', {
                slidesPerView: 1,
                slidesPerGroup: 1,
                pagination: {
                    el: '.npfeed-swiper-pagination',
                    clickable: true
                },
                navigation: {
                    nextEl: '.npfeed-swiper-next',
                    prevEl: '.npfeed-swiper-prev',
                },
                on: {
                    slideChange: function() {
                        // Pause all videos when slide changes
                        const allVideos = document.querySelectorAll('.npfeed-feed-popup-carousel-video');
                        allVideos.forEach(video => {
                            if (!video.paused) {
                                video.pause();
                            }
                        });
                        
                        // Play video if current slide contains a video
                        const activeSlide = this.slides[this.activeIndex];
                        const video = activeSlide.querySelector('.npfeed-feed-popup-carousel-video');
                        if (video) {
                            // Add a small delay to ensure the slide transition is complete
                            setTimeout(() => {
                                video.play().catch(e => {
                                    console.log('Video autoplay failed:', e);
                                });
                            }, 300);
                        }
                    },
                    init: function() {
                        // Play first video if it exists
                        const firstSlide = this.slides[0];
                        const firstVideo = firstSlide.querySelector('.npfeed-feed-popup-carousel-video');
                        if (firstVideo) {
                            setTimeout(() => {
                                firstVideo.play().catch(e => {
                                    console.log('First video autoplay failed:', e);
                                });
                            }, 500);
                        }
                    }
                }
            });
        }
    },

    /**
     * Handle video clicks in the main feed (outside popup)
     */
    handleMainFeedVideoClick() {
        const $that = this;
        $('body').off('click', '.npfeed-fb-feed-video-thumbnail').on('click', '.npfeed-fb-feed-video-thumbnail', function(e) {
            e.preventDefault();
            const $this = $(this);
            const videoUrl = $this.data('video-url');
            const displayMode = $this.data('display-mode');
            
            if (videoUrl && displayMode === 'popup') {
                // Trigger popup for video
                const templateId = $this.closest('[data-template-id]').data('template-id');
                const feedId = $this.closest('[data-index]').data('index');
                
                if (templateId && feedId !== undefined) {
                    // Create a temporary element to trigger popup
                    const tempElement = $('<div>', {
                        'data-template-id': templateId,
                        'data-index': feedId,
                        'data-playmode': 'popup'
                    });
                    
                    // Trigger the popup
                    $that.checkFacebookFeedType(e, tempElement[0]);
                }
            }
        });
    },
    renderCarousel(){        
        return `<div class="npfeed-feed-popup-carousel-wrapper swiper-container">
                    <div class="swiper-wrapper">
                       ${ Object.keys(currentFeed.attachments.data[0].subattachments.data).map(function (key) {
                            const attachment = currentFeed.attachments.data[0].subattachments.data[key];
                            const attachmentType = attachment.type;
                            
                            if (attachmentType === 'video') {
                                // Handle video attachments
                                const videoUrl = this.getVideoUrlFromAttachment(attachment);
                                const thumbnailUrl = attachment.media && attachment.media.image ? attachment.media.image.src : '';
                                
                                if (videoUrl) {
                                    return `<div class="swiper-slide npfeed-video-slide">
                                        <div class="npfeed-feed-popup-video-container">
                                            <video 
                                                class="npfeed-feed-popup-carousel-video"
                                                controls 
                                                preload="metadata"
                                                poster="${thumbnailUrl}"
                                                data-video-url="${videoUrl}"
                                            >
                                                <source src="${videoUrl}" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    </div>`;
                                } else {
                                    return `<div class="swiper-slide npfeed-image-slide">
                                        <img src="${thumbnailUrl}" alt="${currentFeed.message}" class="npfeed-feed-popup-carousel-image" style="width: 100%; height: auto; max-height: 70vh; object-fit: contain;">
                                        <div class="npfeed-fb-feed-video-play">
                                            <div class="npfeed-fb-feed-video-play-icon"></div>
                                        </div>
                                    </div>`;
                                }
                            } else {
                                // Handle image attachments
                                return `<div class="swiper-slide npfeed-image-slide">
                                    <img src="${attachment.media.image.src}" alt="${currentFeed.message}" class="npfeed-feed-popup-carousel-image">
                                </div>`;
                            }
                        }.bind(this)).join('') }
                    </div>
              
                    <div class="npfeed-swiper-prev-next npfeed-swiper-next icon-arrow-circle-o-right"></div>
                    <div class="npfeed-swiper-prev-next npfeed-swiper-prev icon-arrow-circle-o-left"></div>
                    <div class="npfeed-swiper-pagination swiper-pagination"></div>
                </div>`;
    },

    /**
     * Get video URL from sub-attachment (similar to PHP method)
     */
    getVideoUrlFromAttachment(attachment) {
        const videoPaths = [
            () => attachment.media && attachment.media.source,
            () => attachment.target && attachment.target.url,
            () => attachment.url,
            () => attachment.media && attachment.media.video && attachment.media.video.source,
            () => attachment.media && attachment.media.video && attachment.media.video.hd_quality,
            () => attachment.media && attachment.media.video && attachment.media.video.sd_quality,
            () => attachment.media && attachment.media.video && attachment.media.video.standard_quality,
            () => attachment.media && attachment.media.video && attachment.media.video.low_quality,
            () => attachment.media && attachment.media.video && attachment.media.video.medium_quality
        ];
        
        for (const getPath of videoPaths) {
            const videoUrl = getPath();
            if (videoUrl) {
                return videoUrl;
            }
        }
        
        if (attachment.target && attachment.target.id) {
            const fallbackUrl = `https://www.facebook.com/video.php?v=${attachment.target.id}`;
            return fallbackUrl;
        }
        
        return '';
    },
    renderPagination(e, parent, templateId, feedId){
        e.preventDefault();
        parent.find('.npfeed-feed-popup-overlay').remove();
        this.appendFeed(parent, templateId, feedId);

        if(currentFeed.attachments && currentFeed.attachments.data && currentFeed.attachments.data[0].subattachments){
            this.activePopupCarousel();
        }
        
        // Handle video loading for iframes and videos
        jQuery('iframe, img, video').on('load', function() {
            jQuery('.npfeed-feed-popup-loader').removeClass('npfeed-popup-loading');
        });
    },

    checkFacebookFeedType(e, identifier) {
        let parent = $('body');
        let playMode = $(identifier).data('playmode');
        templateId = $(identifier).data('template-id');
        feedId = $(identifier).data('index');
        feedType = $(identifier).data('feed_type');
        postId = $(identifier).data('post_id');
        imageSize = $(identifier).data('image_size');
        userName = $(identifier).data('user_name');
        hasGdpr = $(identifier).data('has_gdpr');
        optimizedImages = $(identifier).data('optimized_images');
        uploadUrl = window.npfeed_ajax_params.upload_url;
        
        if( window.NPFEED_FacebookFeedFrontEndJson && window.NPFEED_FacebookFeedFrontEndJson[templateId] && window.NPFEED_FacebookFeedFrontEndJson[templateId][feedId] && feedType !== 'album_feed' ) {
            currentFeed = window.NPFEED_FacebookFeedFrontEndJson[templateId][feedId];
        }

        if( window.NPFEED_FacebookFeedFrontEndJson && window.NPFEED_FacebookFeedFrontEndJson[templateId] ){
            totalFeed = window.NPFEED_FacebookFeedFrontEndJson[templateId].length;
        }

        if( playMode === 'popup') {
            this.appendFeed($('body'), templateId, feedId);

            if( feedId === 0 ) {
                parent.find('.npfeed-fb-feed-prev-btn').addClass('npfeed-link-disable');
            }

            if( feedId === ( totalFeed - 1) ) {
                parent.find('.npfeed-fb-feed-next-btn').addClass('npfeed-link-disable');
            }

            let $that = this;
            $('body').off('click', '.npfeed-fb-feed-next-btn').on('click', '.npfeed-fb-feed-next-btn', function(e) {
                e.stopPropagation();
                feedId++;
                $that.renderPagination(e, parent, templateId, feedId);
    
                jQuery('iframe, img, video').on('load', function() {
                    jQuery('.npfeed-feed-popup-loader').removeClass('npfeed-popup-loading');
                });
    
                if (feedId === (totalFeed - 1)) {
                    parent.find('.npfeed-fb-feed-next-btn').addClass('npfeed-link-disable');
                }
            });

            $('body').off('click', '.npfeed-fb-feed-prev-btn').on('click', '.npfeed-fb-feed-prev-btn', function(e) {
                e.stopPropagation();
                feedId--;
                $that.renderPagination(e, parent, templateId, feedId);
    
                jQuery('iframe, img, video').on('load', function() {
                    jQuery('.npfeed-feed-popup-loader').removeClass('npfeed-popup-loading');
                });
    
                if (feedId === 0) {
                    parent.find('.npfeed-fb-feed-prev-btn').addClass('npfeed-link-disable');
                }
            });

            if (currentFeed.status_type === "shared_story") {
                return false;
            } else {
                jQuery('iframe, img, video').on('load', function() {
                    jQuery('.npfeed-feed-popup-loader').removeClass('npfeed-popup-loading');
                });
                this.activePopupCarousel();
            }
            e.preventDefault();
        }
    },
}
