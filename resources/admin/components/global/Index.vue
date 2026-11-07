<template>
    <div class="npfeed_social_review_global">
        <div class="npfeed_social_review_global_main_nav">
            <div class="npfeed-admin-logo">
                <img :src="assets_url + '/images/logo/ninja-photo-feed.svg'" alt="Ninja Photo Feed" width="26" height="26" />
            </div>

            <div class="npfeed-menu-items">
                <router-link
                    v-for="menuItem in topMenus"
                    :key="menuItem.route"
                    :to="{ name: menuItem.route }"
                    :class="getMenuItemClasses(menuItem)"
                    active-class="npfeed-tab-active"
                    exact-path
                >
                    {{ menuItem.title }}
                </router-link>
            </div>

            <div class="npfeed-nav-right">
                <el-tooltip
                    effect="dark"
                    content="Settings"
                    placement="bottom"
                >
                    <router-link
                        :to="{ name: 'global-feeds-settings' }"
                        class="npfeed-settings-icon"
                        active-class="npfeed-icon-active"
                    >
                        <el-icon size="18">
                            <Setting />
                        </el-icon>
                    </router-link>
                </el-tooltip>

                <ColorMode/>
            </div>
        </div>

        <router-view></router-view>
    </div>
</template>

<script type="text/babel">
import ColorMode from "../../components/pieces/ColorMode";
import { Setting } from '@element-plus/icons-vue';

export default {
    name: 'Index',
    components: {
        ColorMode,
        Setting
    },
    data() {
        return {
            topMenus: [
                {
                    route: 'platforms',
                    title: 'Instagram Feed',
                },
                {
                    route: 'templates',
                    title: 'Templates',
                },
            ],
        }
    },
    methods: {
        getMenuItemClasses(menuItem) {
            const classes = [];

            if (this.$route.fullPath.includes('/settings') && menuItem.route === 'platforms') {
                // Don't highlight platforms tab when on settings page
            }

            if (!this.$route.name?.includes('platforms') &&
                ['templates'].includes(menuItem.route)) {
                classes.push('router-link-exact-active');
            }

            return classes;
        },
    },
    mounted() {
        jQuery('.update-nag,.notice, #wpbody-content > .updated, #wpbody-content > .error').remove();
    }
}
</script>
