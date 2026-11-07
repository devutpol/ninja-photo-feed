<template>
  <div class="npfeed_editor">
    <div class="npfeed_editor_wrapper npfeed-global-settings" :class="'npfeed-global-settings-collapse-'+activeNames">
      <el-container class="npfeed-global-settings-container">
        <el-aside width="290px" class="npfeed-global-settings-collapse-aside">
          <el-collapse class="npfeed-global-settings-collapse" v-model="activeNames" accordion @change="onCollapseItem">
            <template v-for="[key, menu] in filteredMenus" :key="key">
              <!-- Collapsible menu items with multiple routes -->
              <el-collapse-item v-if="menu.routes && menu.routes.length > 1" :name="key">
                <template #title="{ isActive }">
                  <span class="npfeed-settings-title-row">
                    <component :is="getMenuIcon(key)" :width="16" :height="16" class="menu-icon" />
                    <span class="npfeed-settings-title-text npfeed-label-small">{{ $t(menu.title) }}</span>
                  </span>
                  <span>
                    <svg v-if="!isActive" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M5.00005 3.87852L8.71255 0.166016L9.77305 1.22652L5.00005 5.99952L0.227051 1.22652L1.28755 0.166016L5.00005 3.87852Z" fill="#99A0AE"/>
                    </svg>
                    <svg v-else width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M5.00005 2.121L1.28755 5.8335L0.227051 4.773L5.00005 0L9.77305 4.773L8.71255 5.8335L5.00005 2.121Z" fill="#0E121B"/>
                    </svg>
                  </span>
                </template>
                <div class="npfeed-sub-menu-items">
                  <router-link
                      v-for="(menuItem, itemKey) in menu.routes"
                      :key="itemKey"
                      active-class="npfeed-tab-active"
                      exact
                      :class="['npfeed-tab']"
                      :to="{ name: menuItem.route }"
                  >
                    <img :src="menuItem.icon" alt="" class="sub-menu-icon">
                    <span class="npfeed-menu-title npfeed-label-small">{{ menuItem.title }}</span>
                  </router-link>
                </div>
              </el-collapse-item>

              <!-- Non-collapsible menu items with single route -->
              <div v-else-if="menu.routes && menu.routes.length === 1" :class="['npfeed-single-menu-item', { 'npfeed-single-menu-item-active': $route && $route.name === menu.routes[0].route }]">
                <router-link active-class="npfeed-tab-active" exact :class="['npfeed-single-tab']" :to="{ name: menu.routes[0].route }">
                  <component :is="getMenuIcon(key)" :width="16" :height="16" class="menu-icon" />
                  <span class="npfeed-menu-title npfeed-label-small">{{ menu.title }}</span>
                  <ProCrownIcon v-if="!has_pro && key === 'shoppable_by_hashtags'" :width="16" :height="16" class="pro-crown-icon" />
                </router-link>
              </div>
            </template>
          </el-collapse>
        </el-aside>
        <el-main class="npfeed-global-settings-main">
          <router-view></router-view>
        </el-main>
      </el-container>
    </div>
  </div>
</template>

<script type="text/babel">
import FeedIcon from '../pieces/icons/FeedIcon';
import ShoppableIcon from '../pieces/icons/ShoppableIcon';
import AdvancedSettingsIcon from '../pieces/icons/AdvancedSettingsIcon';
import GeneralSettingsIcon from '../pieces/icons/GeneralSettingsIcon';
import ProCrownIcon from '../pieces/icons/ProCrownIcon';

export default {
  name: 'Settings',
  components: {
    FeedIcon,
    ShoppableIcon,
    AdvancedSettingsIcon,
    GeneralSettingsIcon,
    ProCrownIcon
  },
  data() {
    return {
      sidebarMenus: {
        'feeds': {
          'title': this.$t('Feeds Platforms'),
          'routes': [
            {
              route: 'global-feeds-settings',
              title: 'Global Feeds Settings',
              icon: this.appVars.assets_url + '/images/icon/global-settings.svg'
            },
            {
              route: 'instagram-settings',
              title: 'Instagram Settings',
              icon: this.appVars.assets_url + '/images/icon/icon-instagram.png'
            }
          ]
        },
        'shoppable_by_hashtags': {
          'title': this.$t('Shoppable by Hashtags'),
          'routes': [
            {
              route: 'shoppable-by-hashtags',
              title: this.$t('Shoppable by Hashtags'),
              icon: this.appVars.assets_url + '/images/icon/icon-shoppable.png'
            }
          ]
        },
        'advance': {
          'title': this.$t('Advanced Settings'),
          'routes': [
            {
              route: 'advance-settings',
              title: 'Advanced Settings',
              icon: this.appVars.assets_url + '/images/icon/icon-translation.svg'
            }
          ]
        }
      },
      activeNames: 'feeds'
    }
  },
  computed: {
    filteredMenus() {
      return Object.entries(this.sidebarMenus).filter(([key, menu]) => {
        // Shoppable is a Pro feature; without Pro it is left out rather than shown locked.
        return menu && (key !== 'shoppable_by_hashtags' || !this.hide_pro_features);
      });
    }
  },
  methods: {
    getMenuIcon(menuKey) {
      const iconMap = {
        'feeds': 'FeedIcon',
        'shoppable_by_hashtags': 'ShoppableIcon',
        'advance': 'AdvancedSettingsIcon'
      };
      return iconMap[menuKey] || '';
    },
    onCollapseItem() {
      if (this.sidebarMenus[this.activeNames] &&
          this.sidebarMenus[this.activeNames].routes &&
          this.sidebarMenus[this.activeNames].routes.length > 1 &&
          this.sidebarMenus[this.activeNames].routes[0] &&
          this.$route.name !== this.sidebarMenus[this.activeNames].routes[0].route) {
        this.$router.push({ name: this.sidebarMenus[this.activeNames].routes[0].route });
      }
    },
    checkActiveNames() {
      let curRoute = this.$route.name;
      let data = this.sidebarMenus;
      Object.keys(data).forEach((key) => {
        let routes = data[key].routes;
        if (Array.isArray(routes)) {
          routes.forEach((router) => {
            if (router && router.route === curRoute) {
              this.activeNames = key;
            }
          });
        }
      });
    }
  },
  mounted() {
    this.checkActiveNames();
  }
}
</script>
