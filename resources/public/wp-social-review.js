import { appendReadMoreButton, carouselOptions, openPopupWindow, animateTextTypingEffect, processAISummaryList, wpsrInitInjectedPlayers, wpsrInitMasonry, wpsrWatchMasonryHeights } from './helper.js';
import TwitterCard from './twitter-card.js';

jQuery(document).ready(function ($) {
    const FeedPaginator = {
        init() {
            let that = this;

            $('.npfeed-loader').addClass('npfeed-loading');
            setTimeout(function(){
                jQuery('.npfeed-loader').removeClass('npfeed-loading');
            }, 500);

            $('.npfeed_more').on('click keypress', function (e) {
                e.preventDefault();
                that.fetchData($(this))
            });

            if (window.npfeed_ajax_params && window.npfeed_ajax_params.has_pro) {
                this.initPro();
            }

            $('.npfeed-social-wall-tab').on('click keypress', function (e) {
                e.preventDefault();
                let platform = $(this).data("platform");

                $('.npfeed-social-wall-tab').removeClass('npfeed-active');
                $(this).addClass('npfeed-active');

                $('.npfeed-social-wall-tab-content-wrapper .npfeed-social-wall-content')
                    .removeClass('npfeed-active')
                    .addClass('npfeed-deactivate');

                $('.npfeed-social-wall-tab-content-wrapper .npfeed-social-wall-content.npfeed-'+platform)
                    .removeClass('npfeed-deactivate')
                    .addClass('npfeed-active');


                const $activeContent = $(`.npfeed-social-wall-content.npfeed-${platform}.npfeed-active .npfeed_content`);
                $activeContent.append(`
                    <div class="npfeed-loader npfeed-loading">
                        <div class="npfeed-spinner-animation"></div>
                    </div>
                `);

                let masonrySelector = $('.npfeed-twitter-masonry, .npfeed-instagram-masonry-activate, .npfeed-facebook-feed-masonry-activate, .npfeed-tiktok-feed-masonry-activate');
                that.activeMasonryLayout(masonrySelector);

                setTimeout(() => {
                    $activeContent.find('.npfeed-loader').removeClass('npfeed-loading');
                }, 500);
            });
        },

        /**
         * Per-feed request state (lock, sequence token, requested vs applied order/rating/search), kept on the
         * feed's own element so two feeds on one page never block each other.
         */
        feedState($holder) {
            if (!$holder || !$holder.length) {
                return null;
            }

            let state = $holder.data('wpsrFeedState');

            if (!state) {
                const $toolbar = $holder.find('.npfeed-reviews-sort-toolbar').first();
                const rating = parseInt($toolbar.data('rating'), 10);

                const order = $toolbar.length ? String($toolbar.data('order') || '') : '';
                const exactRating = isNaN(rating) ? -1 : rating;

                state = {
                    seq: 0,
                    inflight: false,
                    xhr: null,
                    order: order,
                    rating: exactRating,
                    // Not read from the toolbar: the server never renders an applied search, so '' is correct.
                    search: '',
                    // The server rendered the page with these, so they are applied.
                    appliedOrder: order,
                    appliedRating: exactRating,
                    appliedSearch: '',
                    // What an open toolbar request is fetching; a load-more leaves it null, never changing it.
                    pendingKey: null,
                    // The one query that failed, so retrying exactly it gets past the toolbar's no-op guard.
                    failedKey: null
                };

                $holder.data('wpsrFeedState', state);
            }

            return state;
        },

        // templateType is passed in, not read from the load-more button, which a feed may not have.
        maybeMasonryFeed(templateType, $feedsWrap) {
            const column = $feedsWrap.data("column");

            if (templateType === 'masonry') {
                const $feedsElement = $feedsWrap.find('.npfeed_feeds');
                if ($feedsElement.length) {
                    // Only when an instance exists; a feed that rendered empty has none yet.
                    if ($feedsElement.data('masonry')) {
                        $feedsElement.masonry('reloadItems');
                    }
                    if (typeof $feedsElement.imagesLoaded === 'function') {
                        $feedsElement.imagesLoaded(function () {
                            if (typeof $feedsElement.masonry === 'function') {
                                $feedsElement.masonry({
                                    itemSelector: '.npfeed-col-' + column + '',
                                });
                            }
                        });
                    } else {
                        if (typeof $feedsElement.masonry === 'function') {
                            $feedsElement.masonry({
                                itemSelector: '.npfeed-col-' + column + '',
                            });
                        }
                    }

                    // The appended cards are new to the observer set up at page load.
                    wpsrWatchMasonryHeights($feedsElement, '.npfeed-col-' + column);
                }
            }
        },

        // The Swiper instance for one feed, or null. Swiper 8 hangs itself off its container element.
        sliderInstance($feedsWrap) {
            const container = $feedsWrap.find('.swiper-container').first()[0];

            return (container && container.swiper) ? container.swiper : null;
        },

        // tabindex is an attribute, not a delegated handler, so it must be re-applied after every slide swap.
        applySlideTabindex($feedsWrap) {
            const settings = $feedsWrap.data('slider_settings');

            if (settings && settings.autoplay === 'true') {
                $feedsWrap.find('.swiper-container .swiper-slide').attr('tabindex', '0');
            }
        },

        // Stop or start autoplay around a re-fetch. Test params.autoplay.enabled: params.autoplay is truthy on every slider.
        setSliderAutoplay($feedsWrap, running) {
            const swiper = this.sliderInstance($feedsWrap);

            if (!swiper || !swiper.autoplay || !swiper.params.autoplay
                || !swiper.params.autoplay.enabled) {
                return;
            }

            if (running) {
                swiper.autoplay.start();
            } else {
                swiper.autoplay.stop();
            }
        },

        // Re-measure a slider after its slides were replaced, and rewind: the old offset may not exist now.
        relayoutSlider($feedsWrap) {
            const swiper = this.sliderInstance($feedsWrap);

            if (!swiper) {
                return;
            }

            swiper.update();
            swiper.slideTo(0, 0);
            this.applySlideTabindex($feedsWrap);
        },
        activeMasonryLayout(masonrySelector){
            if(masonrySelector.length){
                masonrySelector.each(function () {
                    let id = '#' + $(this).attr('id');
                    let $element = $(id);
                    let column = $(this).data("column");

                    if ($element.length && typeof $element.imagesLoaded === 'function') {
                        $element.imagesLoaded(function () {
                            const $masonryContainer = $element.find('.npfeed-twitter-all-tweets, .npfeed-ig-all-feed, .npfeed-fb-all-feed, .npfeed-tt-all-feed');
                            if ($masonryContainer.length && typeof $masonryContainer.masonry === 'function') {
                                $masonryContainer.masonry({
                                    itemSelector: '.npfeed-col-' + column,
                                });
                            }
                        });
                    } else {
                        const $masonryContainer = $element.find('.npfeed-twitter-all-tweets, .npfeed-ig-all-feed, .npfeed-fb-all-feed, .npfeed-tt-all-feed');
                        if ($masonryContainer.length && typeof $masonryContainer.masonry === 'function') {
                            $masonryContainer.masonry({
                                itemSelector: '.npfeed-col-' + column,
                            });
                        }
                    }
                });
            }
        },
        /**
         * Append the next page of feed items. parseHTML, not $(), because a
         * plain-message response would be read as a selector.
         */
        fetchData($btn) {
            let templateId = $btn.data("template_id"),
                total = parseInt($btn.data("total")),
                prev_page = parseInt($btn.data("page")),
                platform = $btn.data("platform"),
                feed_id = $btn.data("feed_id"),
                feed_type = $btn.data("feed_type"),
                // Product scope, or the server falls back to every product in the store.
                wpsrProductId = $btn.data("product_id");
            
            // Use backend-resolved paginate (wp_is_mobile), same as total_posts_number
            let paginate = parseInt($btn.data("paginate"));

            // A searched total is only what has been seen, so the server's answer decides there instead.
            if ($btn.data('has_more') !== true && prev_page * paginate >= total) {
                $btn.hide();
                return false;
            }

            // Per-feed lock, so two feeds from the same template never block each other.
            const $stateWrap = $btn.closest('.npfeed-feed-wrap');
            const state = this.feedState($stateWrap.length ? $stateWrap : $btn);

            if (state.inflight) {
                return false;
            }

            state.inflight = true;

            // Captured, not bumped: a load-more continues the query already on screen.
            const seq = state.seq;
            let page = prev_page + 1;

            $btn.prop('disabled', true).addClass('npfeed-spinner-loading');
            $btn.closest('.npfeed-feed-wrap').find('.npfeed_feeds').addClass('npfeed-loading-feeds');

            const params = {
                action: 'npfeed_get_more_feeds',
                page: page,
                template_id: templateId,
                platform: platform,
                feed_type: feed_type,
                feed_id: feed_id,
                product_id: wpsrProductId || 0,
                security: npfeed_ajax_params.npfeed_nonce
            };

            // Carry the live query: the rendered attributes lag a sort, and a feed can search with no dropdown.
            if (platform === 'reviews' && (state.order || state.search)) {
                if (state.order && state.order !== 'random') {
                    params.order = state.order;
                }

                params.rating = state.rating;
                params.search = state.search;
                // Lets the server skip the COUNT; honoured on page > 1 only.
                params.total_reviews = total;
            }

            const wpsrXhr = jQuery.get(npfeed_ajax_params.ajax_url, params);

            // Published so a sort started mid-flight can abort this page.
            state.xhr = wpsrXhr;

            wpsrXhr.then(response => {
                    if (seq !== state.seq) {
                        return;
                    }

                    let $feedsWrap = $btn.closest('.npfeed-feed-wrap');
                    if (response.content.trim()) {
                        let $appended = $($.parseHTML(response.content, document, true));
                        if(feed_type === 'album_feed') {
                            $feedsWrap.find(`#npfeed-album-feed-${feed_id}`).append($appended);
                        } else {
                            $feedsWrap.find('.npfeed_feeds').append($appended);
                        }

                        try {
                            wpsrInitInjectedPlayers($appended, templateId);
                        } catch (e) {
                            console.error('wpsr: could not init injected players', e);
                        }
                    } else {
                        $btn.hide();
                    }

                    if (platform == 'instagram' || platform == 'twitter' || platform == 'facebook_feed' || platform == 'tiktok' || platform == 'reviews') {
                        $feedsWrap = $btn.closest('.npfeed-feed-wrap')
                        this.maybeMasonryFeed($btn.data('template_type'), $feedsWrap);
                    }

                    // A searched feed has no real total to count against; the server answers directly instead.
                    const wpsrHasMore = (typeof response.has_more === 'boolean') ? response.has_more : null;

                    if (wpsrHasMore === false || (wpsrHasMore === null && page * paginate >= total)) {
                        $btn.hide();
                    }

                    $btn.data('page', page).data('has_more', wpsrHasMore);

                    appendReadMoreButton(npfeed_ajax_params.read_more, npfeed_ajax_params.read_less);
                })
                .catch(errors => {
                    // A superseded request is not an error; the token identifies aborts exactly, unlike jqXHR.status.
                    if (seq !== state.seq) {
                        return;
                    }

                    console.error(errors);
                })
                .always(() => {
                    // A superseded page must not release the lock the newer request holds.
                    if (seq !== state.seq) {
                        return;
                    }

                    state.inflight = false;
                    state.xhr = null;
                    $btn.removeClass('npfeed-spinner-loading').prop('disabled', false);
                    $btn.closest('.npfeed-feed-wrap').find('.npfeed_feeds').removeClass('npfeed-loading-feeds');
                });
        },

        initPro() {
            const carousels = $(".npfeed-reviews-layout-slider, .npfeed-twitter-carousel, .npfeed-youtube-slider-activate, .npfeed-instagram-slider-activate, .npfeed-facebook-feed-slider-activate, .npfeed-tiktok-feed-slider-activate");
            if(carousels.length) {
                let swipedUp = window.Swiper;
                let hasEpro = !!window.elementorFrontend;
                carousels.each(function (index, target) {
                    let $this = $(this);
                    let id = '#' + $(this).attr('id');
                    let sliderSettings = $(this).data("slider_settings");
                    let sliderOptions = carouselOptions(sliderSettings, id, index, $this);
                    let swiper = null
                    if(hasEpro) {
                        setTimeout(() => {
                            if(window.elementorFrontend && window.elementorFrontend.utils && window.elementorFrontend.utils.swiper) {
                                swipedUp = window.elementorFrontend.utils.swiper;
                            }
                        }, 100);
                        swiper = new swipedUp(id + ' .swiper-container', sliderOptions);
                    } else {
                        swiper = new swipedUp(id + ' .swiper-container', sliderOptions);
                    }

                    $('.npfeed-feed-wrap').addClass('npfeed-slider-before-init');

                    setTimeout(() => {
                        $('.npfeed-ig-post-media, .npfeed-yt-video-preview .npfeed-animated-background, .npfeed-tiktok-feed-image .npfeed-animated-background, .npfeed-fb-feed-image .npfeed-animated-background').removeClass('npfeed-animated-background');
                    }, 2000);

                    if (sliderSettings.autoplay === 'true' && swiper) {
                        let container = $(id).find('.swiper-container');

                        FeedPaginator.applySlideTabindex($this);


                        container.on('focusin', function () {
                            swiper.autoplay.stop();
                        });

                        container.on('focusout mouseleave', function () {
                            swiper.autoplay.start();
                        });

                        // Delegated from the container, which the toolbar never replaces, unlike the slides.
                        container.on('focusin', '.swiper-slide *', function (e) {
                            swiper.autoplay.stop();
                        });

                        container.on('keydown', '.swiper-slide', function (e) {
                            if (e.key === 'ArrowLeft') {
                                swiper.slidePrev();
                            } else if (e.key === 'ArrowRight') {
                                swiper.slideNext();
                            }
                        });
                    }
                });
            }

            $('.npfeed-active-masonry-layout').each((index, element) => {
                wpsrInitMasonry($(element));
            });

            let masonrySelector = $('.npfeed-twitter-masonry, .npfeed-instagram-masonry-activate, .npfeed-facebook-feed-masonry-activate, .npfeed-tiktok-feed-masonry-activate');
            this.activeMasonryLayout(masonrySelector);

            // Write-a-review modal, delegated so drawers work too.
            $(document).on("click", '.npfeed-write-review-modal-btn', function (e) {
                e.preventDefault();
                let templateId = $(this).parents('.npfeed-reviews-wrapper').attr('id');
                $('#' + templateId).find('.npfeed-write-review-modal').toggleClass("active");
            });

            $(document).on("click", '.npfeed-reviews-form-popup-trigger', function (e) {
                e.preventDefault();


                let businessInfoWrapper = $(this).closest('.npfeed-business-info-wrapper');
                if (businessInfoWrapper.length && businessInfoWrapper.hasClass('npfeed-woocommerce-reviews-form')) {
                    return;
                }

                // closest(), not an ID: the same shortcode output can be in both the page and a drawer.
                var $wrapper = $(this).closest('.npfeed-reviews-wrapper');
                var $overlay = $wrapper.find('.npfeed-reviews-form-popup-overlay');

                // If inside a drawer, move overlay to body so it escapes overflow clipping
                if ($overlay.closest('.npfeed-product-review-drawer').length) {
                    $overlay.data('npfeed-original-parent', $overlay.parent());
                    $overlay.appendTo('body');
                }

                $overlay.toggleClass("npfeed_has_fluent_forms_shortcode");
            });

            $(document).on('click', '.npfeed-reviews-form-popup-overlay .npfeed-popup-close', function (e) {
                var $overlay = $(this).closest('.npfeed-reviews-form-popup-overlay');
                $overlay.removeClass('npfeed_has_fluent_forms_shortcode');

                var $originalParent = $overlay.data('npfeed-original-parent');
                if ($originalParent && $originalParent.length) {
                    $overlay.appendTo($originalParent);
                    $overlay.removeData('npfeed-original-parent');
                }
            });

            $(document).on('click', function (e) {
                if ($(e.target).has(".active").length) {
                    $('.npfeed-write-review-modal').removeClass('active');
                }
            });

            $('.npfeed-fb-feed-btn-share').on('click', function (e){
                e.preventDefault();
                let url = $(this).attr('href');
                openPopupWindow('https://facebook.com/sharer/sharer.php?u='+url);
            });

        }
    };

    // Published for the sort toolbar's separate bundle, which cannot import from this file. Swiper stays on this side.
    window.wpsrFeedApi = {
        feedState: ($holder) => FeedPaginator.feedState($holder),
        relayoutMasonry: (templateType, $wrap) => FeedPaginator.maybeMasonryFeed(templateType, $wrap),
        relayoutSlider: ($wrap) => FeedPaginator.relayoutSlider($wrap),
        setSliderAutoplay: ($wrap, running) => FeedPaginator.setSliderAutoplay($wrap, running),
        appendReadMoreButton: appendReadMoreButton
    };

    FeedPaginator.init();

    appendReadMoreButton(npfeed_ajax_params.read_more, npfeed_ajax_params.read_less);
    processAISummaryList($);
    animateTextTypingEffect('.npfeed-ai-review-summary p', 7);

    //twitter infinite scroll
    let $twitter_scope = $('.npfeed-twitter-standard, .npfeed-twitter-masonry');
    if ($twitter_scope.hasClass('npfeed-twitter-infinite-scroll-active')) {
        let windowHeight50 = $(window).outerHeight() / 1.25;
        $(window).scroll(function () {
            if (($(window).scrollTop() + windowHeight50) >= ($twitter_scope.find('.npfeed-twitter-tweet:last').offset().top)) {
                const $btn = $twitter_scope.find('.npfeed-twitter-loadmore');
                if ($btn.is(':visible') && !$btn.prop('disabled')) {
                    $twitter_scope.find('.npfeed-twitter-loadmore').trigger('click');
                }
            }
        });
    }

    TwitterCard.init();

    $('.npfeed-yt-prev-next .npfeed-pagi-prev, .npfeed-yt-prev-next .npfeed-pagi-next').on('click', function (e) {
        e.preventDefault();
        let that = $(this),
            wrap = that.closest('.npfeed-yt-prev-next'),
            pageNum = parseInt(wrap.data('pagenum')),
            templateId = parseInt(wrap.data('template-id')),
            totalPage = parseInt(wrap.data('total')),
            paginate = parseInt(wrap.data("paginate")),
            parent = $('#npfeed-yt-feed-' + templateId);

        if (!pageNum) {
            pageNum = 1;
        }

        if (that.hasClass('npfeed-pagi-prev')) {
            if (that.hasClass('npfeed-link-disable')) {
                return;
            } else {
                pageNum = pageNum - 1;

                if (pageNum <= 0) {
                    pageNum = 1;
                }
                wrap.data('pagenum', pageNum);
                wrap.find('.npfeed-pagi-prev, .npfeed-pagi-next').removeClass('npfeed-link-disable');
                if (pageNum <= 1) {
                    $(this).addClass('npfeed-link-disable');
                }
            }
        }

        if (that.hasClass('npfeed-pagi-next')) {
            if (that.hasClass('npfeed-link-disable')) {
                return;
            } else {
                pageNum++;
                wrap.data('pagenum', pageNum);
                wrap.find('.npfeed-pagi-prev, .npfeed-pagi-next').removeClass('npfeed-link-disable');
                if (pageNum === Math.ceil(totalPage / paginate)) {
                    $(this).addClass('npfeed-link-disable');
                }
            }
        }

        let data = {
            'action': 'npfeed_get_more_feeds',
            'page': pageNum,
            'template_id': templateId,
            platform: 'youtube',
            security: npfeed_ajax_params.npfeed_nonce
        };

        $.get(npfeed_ajax_params.ajax_url, data)
            .then(response => {
                parent.find('.npfeed-yt-all-feed').html(response.content);
            })
            .catch(errors => {
                console.error(errors);
            })
            .always(() => {

            });
    });

    $(document).on("click keypress", ".npfeed_read_more, .npfeed_read_less", function () {
        $(this).closest(".npfeed_add_read_more").toggleClass("npfeed_show_less_content npfeed_show_more_content");
        if (
            $(this).closest('.npfeed-row').hasClass('npfeed-active-masonry-layout')
            || $(this).closest('.npfeed-review-fixed-height-wrap').hasClass('npfeed-active-masonry-layout')
            || $(this).closest('.npfeed-feed-wrap').hasClass('npfeed-facebook-feed-masonry-activate')
        ) {
            let column = $('.npfeed-active-masonry-layout, .npfeed-facebook-feed-masonry-activate').data("column");
            $('.npfeed-active-masonry-layout').masonry({
                itemSelector: '.npfeed-col-' + column + '',
            });

            $('.npfeed-facebook-feed-masonry-activate').find('.npfeed-fb-all-feed').masonry({
                itemSelector: '.npfeed-col-' + column + '',
            });

            $('.npfeed-tiktok-feed-masonry-activate').find('.npfeed-tt-all-feed').masonry({
                itemSelector: '.npfeed-col-' + column + '',
            });
        }
    });

    //open tweet actions on popup window
    let popup_params = `scrollbars=no,status=no,location=no,toolbar=no,menubar=no,
    width=800,height=500,left=100,top=100`;
    let tweet_actions = $('.npfeed-tweet-actions').data('actions');

    if( tweet_actions === 'popup' ){
        $('.npfeed-tweet-actions').children('a').each(function (){
            let $this = $(this);
            let url = $this.attr('href');
            $this.on('click', function(event) {
                event.preventDefault();
                open(url, '_blank', popup_params);
            });
        });
    }

    // show/hide reviews badge template
    $('.npfeed-reviews-badge-btn').on('click', function (e) {
        let display_mode = $(this).data('display_mode');

        if(display_mode === 'popup' || display_mode === 'form_shortcode_id' || display_mode === 'native_form' || display_mode === 'none') {
            e.preventDefault();
            if(display_mode === 'form_shortcode_id' || display_mode === 'native_form'){
                let templateId = $(this).data('badge_id');
                $('.npfeed-reviews-badge-'+templateId+' .npfeed-reviews-form-popup-overlay').toggleClass("npfeed_has_fluent_forms_shortcode");
            }
            if(display_mode === 'popup') {
                let templateId = $(this).data('badge_id');
                $('#npfeed-reviews-grid-'+templateId+',#npfeed-reviews-badge-'+templateId).toggleClass('active');
            }
        }
    });

    // hide badge popup template on close btn click
    $('.npfeed-popup-close').on('click', function (e) {
        e.preventDefault();
        $(this).parents().find('.npfeed-reviews-layout-badge, .npfeed-reviews-badge-btn').removeClass('active');
        $('.npfeed-write-review-modal').removeClass('active');
    });

    // hide badge popup template on document click
    $(document).on('click', function (e) {
        if ($(e.target).has(".active").length) {
            $('.npfeed-reviews-layout-badge, .npfeed-reviews-badge-btn').removeClass('active');
            $('.npfeed-write-review-modal').removeClass('active');
        }
    });

    // Generic product review drawer: open on star rating click
    $(document).on('click', '[data-wpsr-action="open-review-drawer"]', function (e) {
        e.preventDefault();
        var target = $(this).data('npfeed-drawer-target');
        var $drawer = target ? $(target) : $('.npfeed-product-review-drawer').first();
        if ($drawer.length) {
            $drawer.addClass('active');
        }
    });

    // Generic product review drawer: close on close button click
    $(document).on('click', '.npfeed-product-review-drawer .npfeed-popup-close', function (e) {
        e.preventDefault();
        $(this).closest('.npfeed-product-review-drawer').removeClass('active');
    });

    // Generic product review drawer: close on click outside
    $(document).on('click', function (e) {
        var $drawers = $('.npfeed-product-review-drawer.active');
        if (!$drawers.length) return;
        if ($(e.target).closest('[data-wpsr-action="open-review-drawer"]').length) return;
        if (!$(e.target).closest('.npfeed-product-review-drawer').length) {
            $drawers.removeClass('active');
        }
    });

    $('.npfeed-woocommerce-context.npfeed-woocommerce-reviews-form .woocommerce .npfeed-write-review').on('click', function (e) {
        e.preventDefault();
        let $wooReviewsForm = $('#review_form_wrapper');
        
        if ($wooReviewsForm.length) {
            $('html, body').animate({
                scrollTop: $wooReviewsForm.offset().top - 100
            }, 800);
        } 
    });

    // Handle WooCommerce review link redirects when default form is hidden
    if (typeof window.wpsrWooSettings !== 'undefined' && window.wpsrWooSettings.reviewsForm !== 'woocommerce') {
        var reviewLinks = document.querySelectorAll('a[href*="#reviews"], .woocommerce-review-link, .star-rating a, .woocommerce-product-rating a');

        reviewLinks.forEach(function(link) {
            // Drawer links have their own handler.
            if (link.hasAttribute('data-wpsr-action')) return;
            link.addEventListener("click", function(e) {
                e.preventDefault();

                if (window.wpsrWooSettings.reviewsWidgetPlacement === 'display_outside_tabs') {
                    var reviewsSection = document.querySelector(".npfeed-reviews-outside-tabs");
                    if (reviewsSection) {
                        reviewsSection.scrollIntoView({ behavior: "smooth", block: "start" });
                    }
                } else {
                    var reviewsTab = document.querySelector(".reviews_tab a, li[role='tab'] a[href*='reviews']");
                    if (reviewsTab) {
                        reviewsTab.click();
                        setTimeout(function() {
                            var tabPanel = document.querySelector(".npfeed-reviews-wrapper");
                            if (tabPanel) {
                                tabPanel.scrollIntoView({ behavior: "smooth", block: "start" });
                            }
                        }, 150);
                    }
                }
            });
        });
    }

    // Anchors with no href carry tabindex instead, so Enter/Space must act like a click.
    // YouTube's 'youtube' play mode adds an href on first click; from then on the browser handles it.
    $(document).on('keydown', '.npfeed-ig-playmode[tabindex], .npfeed-yt-video-playmode[tabindex]', function (e) {
        if (this.hasAttribute('href')) {
            return;
        }
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
            e.preventDefault();
            $(this).trigger('click');
        }
    });

    // Media events do not bubble, so these listen on the capture phase.
    document.addEventListener('play', function (e) {
        let video = e.target;
        if (!video.classList || !video.classList.contains('npfeed-ig-post-video')) {
            return;
        }
        document.querySelectorAll('.npfeed-ig-post-video').forEach(function (other) {
            if (other !== video) {
                other.pause();
            }
        });
        let post = video.closest('.npfeed-ig-post');
        if (post) {
            post.classList.add('npfeed-ig-post-playing');
        }
    }, true);

    ['pause', 'ended'].forEach(function (eventName) {
        document.addEventListener(eventName, function (e) {
            let video = e.target;
            if (!video.classList || !video.classList.contains('npfeed-ig-post-video')) {
                return;
            }
            let post = video.closest('.npfeed-ig-post');
            if (post) {
                post.classList.remove('npfeed-ig-post-playing');
            }
        }, true);
    });

    $(document).on('click', '.npfeed-ig-playmode', function (e) {
        let playMode = $(this).data('playmode');
        let feedId = $(this).data('index');

        if (playMode !== 'inline') {
            return;
        }

        let clickedVideo = $('#npfeed-video-play-' + feedId).closest('.npfeed-ig-post').find('video').get(0);
        if (!clickedVideo) {
            return;
        }
        
        if (e.target === clickedVideo) {
            return;
        }

        e.preventDefault();

        if (clickedVideo.paused) {
            // Some browsers only allow play() after a user interaction.
            clickedVideo.play().catch((err) => {
            });
        } else {
            clickedVideo.pause();
        }
    });
});
