import {checkBFScrollbar, formatText} from './helper';

let $ = jQuery;
let templateId = null;
let feedId = null;
let totalFeed = null;
let currentFeed = null;
let currentPopupSettings = null;
let additionalSettings = null;
let feedType = null;
let optimizedImages = null;
let imageSize = null;
let uploadUrl = null;
let hasGdpr = null;

function getWpsrImageFormat() {
    let imageFormat = window.npfeed_ajax_params
        && window.npfeed_ajax_params.image_settings
        && window.npfeed_ajax_params.image_settings.image_format
        ? window.npfeed_ajax_params.image_settings.image_format
        : '';

    return ['jpg', 'webp'].includes(imageFormat) ? imageFormat : 'jpg';
}

export default {
    init: function (e, $this){
        this.checkTiktokFeedType(e, $this);

        // Checks and ensures compatibility of the Bricksforge custom scrollbar functionality
        checkBFScrollbar();
    },

    renderPopupHtml() {
        let className = '';
        if( currentPopupSettings.display_sidebar === 'false' ) {
            className = 'npfeed-feed-popup-box-no-sidebar';
        }
        let image_format = getWpsrImageFormat();
        currentFeed.media_url =  uploadUrl +'/tiktok' +'/' +  currentFeed.user.name + '/' + currentFeed.id + '_' + imageSize + '.'+ image_format;
        this.renderAvatar();

        return `<div class="npfeed-feed-popup-overlay npfeed-tiktok-feed-popup npfeedm-overlay npfeed_content npfeed-feed-popup-open">
                    <div class="npfeed-feed-popup-box-wraper">
                        <div class="npfeed-prev-btn npfeed-tiktok-feed-prev-btn"><span class="icon-angle-left"></span></div>
                        <div class="npfeed-next-btn npfeed-tiktok-feed-next-btn"><span class="icon-angle-right"></span></div>
                        
                        <div class="npfeed-feed-popup-close-btn npfeedm_close"></div>
                        
                      <div class="npfeed-feed-popup-box-wraper-inner npfeedm_inner ${className} npfeed-feed-${feedType}">
                                              
                         ${currentPopupSettings.display_video === 'true' ?
                            `<div class="npfeed-feed-popup-media">
                                <div>
                                ${optimizedImages ? 
                                `<a href="${'https://www.tiktok.com/@' + currentFeed.user.id + '/video/' + currentFeed.id}" target="_blank">
                                    <img src="${currentFeed.media_url}" alt="Video">
                                    <div class="npfeed-tiktok-feed-video-play">
                                        <div class="npfeed-tiktok-feed-video-play-icon"></div>
                                    </div>
                                 </a>` : 
                                `
                                    <iframe id="npfeed-feed-popup-video-iframe" src="https://www.tiktok.com/embed/v2/${currentFeed.id}" allowtransparency="true" allowfullscreen="true" allow="${this.getAllowValue()}" >
                                    </iframe>
                                `
                                }
                                </div>
                            </div>` : ``
                        }
                        ${currentPopupSettings.display_sidebar === 'true' ?
                            `<div class="npfeed-feed-popup-box-content">
                                ${currentFeed.user ?
                                    `<div class="npfeed-feed-popup-user-info">
                                        ${this.renderAvatar()}
                                        <div class="npfeed-feed-popup-group">
                                          ${this.renderUserName()}
                                          ${ this.renderDate() }
                                        </div>
                                    </div>` : ``
                                }
                          
                                  <div class="npfeed-feed-popup-comments-wrapper">
                                    <div class="npfeed-feed-popup-comments-wrapper-inner">
                                        ${currentPopupSettings.display_caption === 'true' ?
                                            `<div class="npfeed-feed-popup-comment">
                                                <div class="npfeed-feed-popup-comment-inner">
                                                    <div class="npfeed-feed-popup-comment-text">
                                                        ${this.renderMessage()}
                                                    </div>
                                                </div>
                                            </div>` : ``
                                        }
                                    </div>
                                  </div>
                          
                                   ${currentPopupSettings.display_cta_btn === 'true' ?
                                        `<div class="npfeed-feed-popup-view-post-cta">
                                        <a href="${'https://www.tiktok.com/@' + currentFeed.user.id + '/video/' + currentFeed.id}" target="_self">
                                          <span class="npfeed-icon-tiktok-black"></span>
                                          <span> ${window.npfeed_ajax_params.view_on_tiktok} </span>
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
    getAllowValue() {
        const allow = ['clipboard-write', 'encrypted-media', 'picture-in-picture'];
        return allow.join('; ');
    },
    renderAvatar() {
        let picture = currentFeed.user && currentFeed.user.profile_image_url ? currentFeed.user.profile_image_url : '';
        picture = optimizedImages ? currentFeed.user_avatar : picture;
        let user_name = currentFeed.user ? currentFeed.user.name : '';

        return currentPopupSettings.display_profile_photo === 'true' ?
            `<a class="npfeed-feed-popup-user-photo" href="${picture}" target="_blank" title="${user_name}">
              <img src="${picture}" alt=${user_name} />
            </a>` : '';
    },

    renderUserName() {
        let user_name = currentFeed.user ? currentFeed.user.name : '';
        let id = currentFeed.user ? currentFeed.user.id : '';
        let user_url = currentFeed.user ? currentFeed.user.profile_url : '';

        return currentPopupSettings.display_username === 'true' ?
            `<div class="npfeed-feed-popup-user-name">
                <a href="${user_url}" target="_blank" title="${user_name}">${user_name}</a>
              </div>` : '';
    },

    renderDate(){
        return (currentPopupSettings.display_date === 'true' && currentFeed.created_at ) ?
            `<div class="npfeed-feed-popup-date">
               <time datetime="${currentFeed.time_ago}" title="${currentFeed.time_ago}">${currentFeed.time_ago}</time>
            </div>` : '';
    },

    renderMessage() {
        return currentFeed.text !== undefined ? ` <p>${formatText(currentFeed.text, 'https://www.tiktok.com/tag/')}</p>` : ``;
    },

    // renderName()
    // {
    //     return currentFeed.title !== undefined ? ` <p>${currentFeed.title}</p>` : ``;
    // },

     appendFeed(parent, templateId, feedId) {
         if( window.NPFEED_TiktokFrontEndJson && window.NPFEED_TiktokFrontEndJson[templateId] && window.NPFEED_TiktokFrontEndJson[templateId][feedId] ) {
            currentFeed = window.NPFEED_TiktokFrontEndJson[templateId][feedId];
         }

        if( window.NPFEED_TiktokPopupSettings && window.NPFEED_TiktokPopupSettings[templateId] ) {
            currentPopupSettings = window.NPFEED_TiktokPopupSettings[templateId];
        }

        if( window.NPFEED_TiktokAdditionalSettings && window.NPFEED_TiktokAdditionalSettings[templateId] ) {
            additionalSettings = window.NPFEED_TiktokAdditionalSettings[templateId];
        }

        if( currentPopupSettings ) {

            let data = this.renderPopupHtml(currentFeed, additionalSettings, currentPopupSettings);
            parent.append(data);
            let commentBoxHeight = parent.find('.npfeed-feed-popup-comments-wrapper-inner').innerHeight();
            if (commentBoxHeight && commentBoxHeight > 450) {
                parent.find('.npfeed-feed-popup-comments-wrapper').toggleClass('npfeed-feed-popup-scrollbar');
            }

            let video = jQuery("#npfeed-feed-popup-video");
            if(video.length) {
                video[0].play();
            }
        }
    },

    renderPagination(e, parent, templateId, feedId){
        e.preventDefault();
        parent.find('.npfeed-feed-popup-overlay').remove();

        this.appendFeed(parent, templateId, feedId);
    },

    checkTiktokFeedType(e, identifier) {
        let parent = $('body');
        let playMode = $(identifier).data('playmode');
        templateId = $(identifier).data('template-id');
        feedId = $(identifier).data('index');
        feedType = $(identifier).data('feed_type');
        hasGdpr = $(identifier).data('has_gdpr');
        optimizedImages = $(identifier).data('optimized_images');
        imageSize = $(identifier).data('image_size');
        uploadUrl = window.npfeed_ajax_params.upload_url;

        if( window.NPFEED_TiktokFrontEndJson && window.NPFEED_TiktokFrontEndJson[templateId] && window.NPFEED_TiktokFrontEndJson[templateId][feedId] ) {
            currentFeed = window.NPFEED_TiktokFrontEndJson[templateId][feedId];
        }

        if( window.NPFEED_TiktokFrontEndJson && window.NPFEED_TiktokFrontEndJson[templateId] ){
            totalFeed = window.NPFEED_TiktokFrontEndJson[templateId].length;
        }
        if( playMode === 'popup') {
            this.appendFeed($('body'), templateId, feedId);

            if( feedId === 0 ) {
                parent.find('.npfeed-tiktok-feed-prev-btn').addClass('npfeed-link-disable');
            }

            if( feedId === ( totalFeed - 1) ) {
                parent.find('.npfeed-tiktok-feed-next-btn').addClass('npfeed-link-disable');
            }

            let $that = this;
            $('body').off('click').on( 'click', '.npfeed-tiktok-feed-next-btn', function(e){
                feedId++
                $that.renderPagination(e, parent, templateId, feedId);
                jQuery('iframe, img').on('load', function (){
                    jQuery('.npfeed-feed-popup-loader').removeClass('npfeed-popup-loading');
                });
                if( feedId === ( totalFeed - 1) ) {
                    parent.find('.npfeed-tiktok-feed-next-btn').addClass('npfeed-link-disable');
                }
            });

            $('body').on( 'click', '.npfeed-tiktok-feed-prev-btn', function(e){
                feedId--;
                $that.renderPagination(e, parent, templateId, feedId);
                jQuery('iframe, img').on('load', function (){
                    jQuery('.npfeed-feed-popup-loader').removeClass('npfeed-popup-loading');
                });
                if( feedId === 0 ){
                    parent.find('.npfeed-tiktok-feed-prev-btn').addClass('npfeed-link-disable');
                }
            });

            jQuery('iframe, img').on('load', function (){
                jQuery('.npfeed-feed-popup-loader').removeClass('npfeed-popup-loading');
            });
            // jQuery('.npfeed-feed-popup-overlay').toggleClass('npfeed-feed-popup-open');
            jQuery('.npfeed-feed-link').removeAttr("href");

        }
    },
}
