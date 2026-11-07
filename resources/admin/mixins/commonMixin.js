export const commonMixin = {
    data() {
        return {
            template_title: '',
            isEditing: false
        }
    },
    methods: {
        checkAndEditorWelcomePopup() {
            if (this.$route.query.onboarding === 'true') {
                this.showWelcomePopup = true;
                // Prevent loading watcher from showing popup again
                if (this.isInitialLoad !== undefined) {
                    this.isInitialLoad = true;
                }
                this.removeOnboardingQuery();
                setTimeout(() => {
                    this.showWelcomePopup = false;
                }, 800);
            }
        },
        removeOnboardingQuery() {
            const { onboarding, ...rest } = this.$route.query;
            this.$router.replace({ query: { ...rest } });
        },
        showMoreLess() {
            jQuery(document).off("click", ".npfeed_read_more, .npfeed_read_less");
            jQuery(document).on("click", ".npfeed_read_more, .npfeed_read_less", function() {
                jQuery(this).closest(".npfeed_add_read_more, .npfeed-yt-popup-video-meta-description, .npfeed-yt-popup-video-comment-text-inner").toggleClass("npfeed_show_less_content npfeed_show_more_content");
            });
        },
        preventNav(event) {
            if (!this.isEditing) return
            event.preventDefault()
            event.returnValue = ""
        },
        get_image_class(media_url, image_settings, platform = '') {
            if(image_settings.optimized_images  === 'true'){
                return media_url && !media_url.includes('placeholder') ? 'npfeed-show' : 'npfeed-hide';
            }else{
                return '';
            }
        },
        isPlaceholder(url) {            
            const placeholderText = 'placeholder';
            
            if(url && url.includes(placeholderText)){
                return true;
            }
                        
            return false;
        }
    },
    beforeMount() {
        window.addEventListener("beforeunload", this.preventNav)
    },
    beforeUnmount() {
        window.removeEventListener("beforeunload", this.preventNav);
    },
    beforeRouteLeave(to,from,next) {
        const $body = jQuery('body');
        let wasFullScreen = $body.hasClass('npfeed_full_screen');
        let wasWpsrEditor= $body.hasClass('is-wpsr-editor');
        if (wasWpsrEditor){
            $body.removeClass('is-wpsr-editor');
        }
        if (wasFullScreen) {
            $body.removeClass('npfeed_full_screen');
        }
        if (this.isEditing) {
            if (!window.confirm("You have unsaved changes. If you leave now, your changes will be lost.")) {
                return;
            }
        }
        next();
    },
    mounted() {
        this.clipboard();
        this.checkFullScreen();
        this.isEditing   = false;
        const $body = jQuery('body');
        $body.addClass('is-wpsr-editor');
    }
}