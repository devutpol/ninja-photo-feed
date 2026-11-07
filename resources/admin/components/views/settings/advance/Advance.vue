<template>
  <div>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="npfeed-general-settings-wrapper">
      <div class="npfeed-settings-container">
        <NpfeedSkeleton :rows="12" />
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="!loading && advance_settings">
      <div
        class="npfeed-gs-header npfeed-d-flex npfeed-jc-between npfeed-flex-align-center"
      >
        <h2 class="npfeed-gs-header-title">{{ $t("Advanced Settings") }}</h2>
        <el-button
          @click="saveAdvanceSettings"
          type="success"
          size="default"
          class="npfeed_primary_btn"
        >
          {{ $t("Save Settings") }}
        </el-button>
      </div>

      <div class="npfeed-general-settings-wrapper">
        <div class="npfeed-settings-container npfeed-feed-global-settings-wrapper">
          <div class="npfeed-settings-header">
            <h3 class="npfeed-feed-settings-title">
              Image Format for Optimized Images (Recommended)
            </h3>
          </div>
          <div class="npfeed-feed-settings-content">
            <div class="npfeed-setting-row">
              <div class="npfeed-setting-left">
                <h3 class="npfeed-setting-title">
                  {{ $t("Optimize Image Format Type") }}
                </h3>
                <p class="npfeed-setting-description">
                  Choose the best image format for your website. For optimal
                  performance, we recommend selecting the WebP format, which
                  balances quality and file size.
                </p>
              </div>
              <div class="npfeed-setting-right">
                <el-select
                  v-model="advance_settings.optimize_image_format"
                  class="npfeed-text-input"
                  clearable
                  placeholder="Select"
                >
                  <el-option
                    v-for="item in optimize_image_format_options"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  >
                  </el-option>
                </el-select>
              </div>
            </div>
          </div>
        </div>

        <div class="npfeed-settings-container">
          <div class="npfeed-setting-section npfeed-mb-24">
            <div class="npfeed-settings-header">
              <h3 class="npfeed-feed-settings-title">
                GDPR Compliance
              </h3>
            </div>
            <div class="npfeed-feed-settings-content">
              <div class="npfeed-setting-row">
                <div class="npfeed-setting-left">
                  <h3 class="npfeed-setting-title">
                    GDPR

                    <el-tooltip
                      class="box-item"
                      :raw-content="true"
                      effect="dark"
                      placement="bottom-start"
                      :content="getGDPRTooltipContent()"
                    >
                      <el-icon
                        size="16"
                        color="var(--npfeed-icon-info-secondary-color)"
                        ><InfoFilled
                      /></el-icon>
                    </el-tooltip>
                  </h3>

                  <p class="npfeed-setting-description">
                    Enable GDPR compliance to store images locally instead of loading them from external platforms. This helps protect user privacy and ensures compliance with data protection regulations.
                  </p>
                </div>
                <div class="npfeed-setting-right">
                  <el-select
                    v-model="advance_settings.has_gdpr"
                    class="npfeed-text-input"
                    clearable
                    placeholder="Select"
                  >
                    <el-option
                      v-for="item in gdpr_options"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="npfeed-settings-container" v-if="hasPermission(['npfeed_full_access'])">
          <div class="npfeed-setting-section npfeed-mb-24">
            <div class="npfeed-settings-header">
              <h3 class="npfeed-feed-settings-title">
                Manage Data
              </h3>
            </div>
            <div class="npfeed-feed-settings-content">
              <div class="npfeed-setting-row">
                <div class="npfeed-setting-left">
                  <h3 class="npfeed-setting-title">
                    {{ $t("Delete all Platform Data?") }}
                  </h3>
                  <p class="npfeed-setting-description">
                    Pressing this button will permanently erase all data from
                    Facebook, Instagram and other platforms including connected
                    accounts, optimized images, and cached posts.
                  </p>
                </div>

                <div class="npfeed-setting-right">
                  <RemoveConfirm
                    @confirm="deletePlatformData"
                    message="Would you like to delete all the Facebook, Instagram and other platforms data including all connected accounts, optimize images, cached posts?"
                    submit_button_type="text"
                    :displayRefreshLeft="'trash'"
                    button_text="Delete All"
                  ></RemoveConfirm>
                </div>
              </div>
            </div>
          </div>
        </div>
        <el-button
          class="npfeed_primary_btn"
          type="success"
          size="default"
          @click="saveAdvanceSettings"
          >Save Settings</el-button
        >
      </div>
    </div>
  </div>
</template>

<script>
import RemoveConfirm from '../../../core-ui/editor/RemoveConfirm';
import { InfoFilled } from '@element-plus/icons-vue';
export default {
  name: "Advance",
  components: {
    RemoveConfirm,
    InfoFilled
  },
  data() {
    return {
      loading: false,
      advance_settings: null,
      gdpr_options: [
        {
          label: 'No',
          value: 'false'
        },
        {
          label: 'Yes',
          value: 'true'
        },
      ],
      optimize_image_format_options: [
        {
          label: 'JPG',
          value: 'jpg'
        },
        {
          label: 'WebP',
          value: 'webp'
        },
      ],
    }
  },

  methods: {
    saveAdvanceSettings() {
      this.loading = true;
      this.$post('settings/advance-settings', {
        advance_settings: this.advance_settings,
      }).then(response => {
        if (response) {
          this.handleSuccess(response.message);
          this.getAdvanceSettings();
        }
      }).catch(error => {
        this.handleError(error);
      }).always(() => {
        this.loading = false;
      });
    },
    getAdvanceSettings() {
      this.loading = true;
      this.$get('settings/advance-settings')
          .then(response => {
            if (response) {
              this.advance_settings = response.advance_settings;
            }
          }).catch(error => {
            this.handleError(error);
          }).always(() => {
            this.loading = false;
          });
    },
    deletePlatformData(){
      this.$del('settings/delete-all-data').then(response => {
        if (response) {
          this.handleSuccess(response.message);
        }
      }).catch((errors) => {
        this.handleError(errors);
      }).always(() => {

      });
    },
    getGDPRTooltipContent() {
      return `<p>
        <b>Please select the option that best suits your needs.</b><br><br>

        When you select <b>"Yes"</b> for this option, it stops images and videos from loading directly from Instagram's servers (CDN).<br>
        This helps prevent your browser from making requests to external websites. However, it's important to note that <br> enabling this option may disable or limit certain features of the plugin.<br><br>

        If you choose <b>"No"</b> the plugin will continue to fetch and show images and videos directly from Instagram servers (CDN).<br> This means that some requests will still be made to load and display the media content.
      </p>`
    }
  },

  mounted() {
    this.getAdvanceSettings();
  },
};
</script>
