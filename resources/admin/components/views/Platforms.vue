<template>
  <div class="npfeed-platforms-wrapper" v-if="hasPermission(['npfeed_manage_platforms', 'npfeed_full_access'])">

    <div class="npfeed-platforms-wrapper-inner">
      <!-- Loading state -->
      <div v-if="isLoading" class="npfeed-platforms-loading">
        <div class="npfeed-platforms-skeleton-wrapper">
          <NpfeedSkeleton :rows="12" />
        </div>
      </div>

      <!-- Platforms Content -->
      <div v-if="!isLoading" class="npfeed-all-platforms">
        <div class="npfeed-platforms-group">
          <el-row :gutter="20" class="npfeed-reviews-platforms">
            <el-col :span="6">
              <div class="npfeed-platform">
                <div class="npfeed-platform-body">
                  <div class="npfeed-platform-info" @click="openInstagramModal">
                    <img :src="instagramIcon" alt="Instagram" class="image">
                    <div class="npfeed-platform-status-tag">
                      <el-icon v-if="!isInstagramEnabled" class="icon-ban"></el-icon>
                      <el-icon v-if="isInstagramEnabled"><Check /></el-icon>
                    </div>
                  </div>
                  <div class="npfeed-platform-setting-tabs">
                    <instagram-config
                        v-if="instagramSettingModal"
                        :instagramModal="instagramSettingModal"
                        :access_token.sync="verificationData"
                        :credentials_type="credentialsType"
                        :expires_in="expiresIn"
                        @instagram_settings="handleInstagramModal"
                        @findEnabledPlatforms="getEnabledPlatforms"
                    />
                  </div>
                </div>
                <div class="npfeed-platform-footer">
                  <h3 class="npfeed-platform-name">Instagram</h3>
                  <div class="npfeed-platform-icon">
                    <el-icon class="el-icon-setting" @click="openInstagramModal"><Setting /></el-icon>
                  </div>
                </div>
              </div>
            </el-col>
          </el-row>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import InstagramConfig from './platforms/feeds/Instagram';
import { Check, Setting } from '@element-plus/icons-vue';

export default {
  name: 'Platforms',
  components: {
    InstagramConfig,
    Check,
    Setting
  },
  data() {
    return {
      isLoading: true,
      instagramSettingModal: false,
      verificationData: '',
      expiresIn: '',
      credentialsType: 'oauth',
      is_enabled_platform: false,
      admin_page_url: this.appVars.admin_page_url,
      instagramIcon: this.appVars.assets_url + '/images/icon/icon-instagram.png',
    };
  },
  computed: {
    isInstagramEnabled() {
      return this.is_enabled_platform && this.is_enabled_platform.instagram;
    }
  },
  created() {
    const urlParams = new URLSearchParams(window.location.search);
    // The connect service appends these params itself, so they keep the wpsn_ names.
    const npfeedAuthPayload = urlParams.get('wpsn_auth_payload');
    const npfeedAuthPlatform = urlParams.get('wpsn_auth_platform');

    if (npfeedAuthPayload && npfeedAuthPlatform === 'instagram') {
      window.history.replaceState({}, null, this.admin_page_url + '#/');
      this.npfeedConsumeAuthResult(urlParams.get('wpsn_auth_flow'), npfeedAuthPayload, npfeedAuthPlatform);
      return;
    }

    if (this.$route.query.open_modal === 'instagram') {
      this.instagramSettingModal = true;
    }
  },
  mounted() {
    this.getEnabledPlatforms();
  },
  methods: {
    getEnabledPlatforms() {
      this.$get('platforms/enabled')
          .then(response => {
            if (response) {
              this.is_enabled_platform = response.platforms;
            }
          })
          .catch(error => {
            this.handleError(error);
          })
          .always(() => {
            this.isLoading = false;
          });
    },
    npfeedGetAuthEditorTarget(authState = {}) {
      let returnRoute = null;
      try {
        returnRoute = JSON.parse(window.sessionStorage.getItem('npfeed_auth_return_route') || 'null');
        window.sessionStorage.removeItem('npfeed_auth_return_route');
      } catch (e) {}

      if (!returnRoute || returnRoute.name !== 'edit-instagram-template' || !returnRoute.templateId) {
        return null;
      }

      return {
        name: 'edit-instagram-template',
        params: { template_id: returnRoute.templateId },
        query: { configuration: 'true', platform: 'instagram', platform_type: 'feeds', ...authState }
      };
    },
    npfeedConsumeAuthResult(flowId, payload, authPlatform) {
      this.$post('platforms/feeds/configs/consume', {
        platform: authPlatform,
        flow_id: flowId,
        payload: payload
      }).then(response => {
        if (!response || !response.data || !response.data.result) {
          const message = response && response.data && response.data.message
            ? response.data.message
            : this.$t('Authentication could not be completed. Please try reconnecting the platform.');
          this.handleError(message);
          const failure = this.npfeedGetAuthEditorTarget({ auth_error: 'true' });
          this.$router.push(failure || '/');
          return;
        }

        const result = response.data.result;
        const editorTarget = this.npfeedGetAuthEditorTarget({ auth_consumed: 'true' });

        if (editorTarget) {
          try {
            window.sessionStorage.setItem('npfeed_auth_consumed_result', JSON.stringify({
              ok: !!result.ok,
              platform: result.platform || '',
              account: result.account || {},
              locations: result.locations || {},
              settings: result.settings || {},
              business_info: result.business_info || {},
              load_existing: !!result.load_existing
            }));
          } catch (e) {}
          this.$router.push(editorTarget);
          return;
        }

        if (result.flow === 'business') {
          try {
            window.sessionStorage.setItem('npfeed_auth_consumed_result', JSON.stringify({
              ok: true,
              platform: 'instagram',
              flow: 'business'
            }));
          } catch (e) {}
        }

        this.instagramSettingModal = false;
        this.$nextTick(() => {
          this.instagramSettingModal = true;
        });
        this.getEnabledPlatforms();
      }).catch(error => {
        this.handleError(error);
      });
    },
    openInstagramModal() {
      this.instagramSettingModal = true;
    },
    handleInstagramModal(val) {
      this.instagramSettingModal = val;
      if (!val) {
        this.getEnabledPlatforms();
      }
    }
  }
};
</script>

<style scoped>
/* A single card needs no row gap, so the panel padding alone sets the bottom spacing. */
.npfeed-all-platforms .npfeed-platform {
  margin-bottom: 0;
}
</style>
