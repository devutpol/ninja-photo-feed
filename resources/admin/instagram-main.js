import { createApp } from 'vue';
import App from './InstagramApp.vue';
import { routes } from './instagram-routes';
import { createRouter, createWebHashHistory } from 'vue-router';
import ElementPlus from './elements';
import moment from 'moment';
import 'moment-duration-format';
import NinjaPhotoFeed from './NinjaPhotoFeed';
import NpfeedSkeleton from './components/global/skeleton.vue';

const ninjaPhotoFeed = new NinjaPhotoFeed();

const router = createRouter({
    history: createWebHashHistory(),
    routes: ninjaPhotoFeed.applyFilters('npfeed_global_vue_routes', routes),
    linkActiveClass: 'active'
});

router.beforeEach((to, from, next) => {
    if (to.meta) {
        document.title = 'Ninja Photo Feed :: ' + to.meta.title;
    } else {
        document.title = 'Ninja Photo Feed';
    }
    next();
});

// Create the app
const app = createApp(App);

// Use Element Plus
app.use(ElementPlus);

// Add global properties
app.config.globalProperties.$rest = ninjaPhotoFeed.$rest;
app.config.globalProperties.$get = ninjaPhotoFeed.$get;
app.config.globalProperties.$post = ninjaPhotoFeed.$post;
app.config.globalProperties.$del = ninjaPhotoFeed.$del;
app.config.globalProperties.$put = ninjaPhotoFeed.$put;
app.config.globalProperties.$patch = ninjaPhotoFeed.$patch;
app.config.globalProperties.appVars = window.NinjaPhotoFeedAdmin;
app.config.globalProperties.has_pro = !!window.NinjaPhotoFeedAdmin.has_pro;
app.config.globalProperties.hide_pro_features = !!window.NinjaPhotoFeedAdmin.hide_pro_features;
app.config.globalProperties.instagram_pro_templates = window.NinjaPhotoFeedAdmin.instagram_pro_templates || [];
app.config.globalProperties.assets_url = window.NinjaPhotoFeedAdmin.assets_url;
app.config.globalProperties.hasPermission = ninjaPhotoFeed.hasPermission;

// Add mixin with all methods
app.mixin({
    data() {
        return {
            pro_purchase_url: 'https://wpsocialninja.com/ninja-photo-feed/?utm_source=wp_site&utm_medium=plugin&utm_campaign=upgrade',
        }
    },
    computed: {
        hasPro() {
            return !!this.has_pro;
        }
    },
    methods: {
        // WordPress hooks
        addFilter: ninjaPhotoFeed.addFilter,
        applyFilters: ninjaPhotoFeed.applyFilters,
        doAction: ninjaPhotoFeed.doAction,
        addAction: ninjaPhotoFeed.addAction,

        // Event handling methods
        $on: ninjaPhotoFeed.$on,
        $off: ninjaPhotoFeed.$off,
        $emit: ninjaPhotoFeed.$emit,

        // Translation
        $t: ninjaPhotoFeed.$t,

        // HTTP methods
        $get: ninjaPhotoFeed.$get,
        $post: ninjaPhotoFeed.$post,
        $del: ninjaPhotoFeed.$del,
        $put: ninjaPhotoFeed.$put,
        $patch: ninjaPhotoFeed.$patch,

        // Utility methods
        handleError: ninjaPhotoFeed.handleError,
        convertToText: ninjaPhotoFeed.convertToText,
        slugify: ninjaPhotoFeed.slugify,
        ucFirst: ninjaPhotoFeed.ucFirst,
        wpsrDateFormat: ninjaPhotoFeed.wpsrDateFormat,
        handleFullScreen: ninjaPhotoFeed.handleFullScreen,
        checkFullScreen: ninjaPhotoFeed.checkFullScreen,
        hasPermission: ninjaPhotoFeed.hasPermission,
        handleSuccess: ninjaPhotoFeed.handleSuccess,
        handlePro: ninjaPhotoFeed.handlePro,
        clipboard: ninjaPhotoFeed.clipboard,
        isUrl: ninjaPhotoFeed.isUrl,
        nl2br: ninjaPhotoFeed.nl2br,
        parseDuration: ninjaPhotoFeed.parseDuration,
        shortNumberFormat: ninjaPhotoFeed.shortNumberFormat,
        generateURLsFromHashTag: ninjaPhotoFeed.generateURLsFromHashTag,
        escapeHtml: ninjaPhotoFeed.escapeHtml,
        sanitizeHtml: ninjaPhotoFeed.sanitizeHtml,
        generateURLsFromText: ninjaPhotoFeed.generateURLsFromText,
        numberWithCommas: ninjaPhotoFeed.numberWithCommas,
        $getFirstWord: ninjaPhotoFeed.$getFirstWord,
        $excerpt: ninjaPhotoFeed.$excerpt,
        $trimWords: ninjaPhotoFeed.$trimWords,
        $contentLanguage: ninjaPhotoFeed.$contentLanguage,
        $safeContentLanguage: ninjaPhotoFeed.$safeContentLanguage,
        $addReadMoreBtn: ninjaPhotoFeed.$addReadMoreBtn,
        truncateHTML: ninjaPhotoFeed.truncateHTML,
        $showAjaxError: ninjaPhotoFeed.$showAjaxError,
        get_platform_icon: ninjaPhotoFeed.get_platform_icon,
        show_platform_icon: ninjaPhotoFeed.show_platform_icon,
        convertToPercentage: ninjaPhotoFeed.convertToPercentage,

        // Moment.js methods
        moment: moment,
    }
});

// Register global components
app.component('NpfeedSkeleton', NpfeedSkeleton);

app.use(router);
const vueApp = app.mount('#ninjaphotofeedapp');
window.NinjaPhotoFeed = vueApp;
