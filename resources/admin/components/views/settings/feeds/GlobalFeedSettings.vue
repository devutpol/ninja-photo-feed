<template>
  <div>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="npfeed-general-settings-wrapper">
      <div class="npfeed-settings-container">
        <NpfeedSkeleton :rows="10" />
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="!loading && advance_settings">
        <div class="npfeed-gs-header npfeed-d-flex npfeed-jc-between npfeed-flex-align-center">
          <h2 class="npfeed-gs-header-title">{{ $t('Global Feed Settings') }}</h2>
          <el-button
              @click="saveAdvanceSettings"
              type="success"
              size="default"
              class="npfeed_primary_btn"
          >
            {{ $t('Save Settings')}}
          </el-button>
        </div>
  
        <div class="npfeed-general-settings-wrapper">
          <div class="npfeed-settings-container">
            <div class="npfeed-settings-header">
              <h2 class="npfeed-feed-settings-title">{{ $t('Feed Issue Email Reports') }}</h2>
              <p class="npfeed-feed-settings-description">
                If the <strong>Instagram</strong> and <strong>Facebook</strong> feeds are down due to a critical issue, we'll seamlessly switch to a cached version and notify you based on these settings.
              </p>
            </div>
            <div class="npfeed-feed-settings-content">
              <div class="npfeed-setting-row">
                <div class="npfeed-setting-left">
                  <h3 class="npfeed-setting-title">{{ $t('Enable Email Reports') }}</h3>
                  <p class="npfeed-setting-description">{{ $t('If the feed is down due to a critical issue, we will notify you based on these settings.') }}</p>
                </div>
                <div class="npfeed-setting-right">
                  <el-switch
                        v-model="advance_settings.email_report.status"
                        active-color="#5c8df6"
                        inactive-color="#b7b7b9"
                        active-value="true"
                        inactive-value="false"
                    >
                  </el-switch>
                </div>
              </div>
              <div v-if="advance_settings.email_report.status === 'true'" class="npfeed-setting-row">
                <div class="npfeed-setting-left">
                  <h3 class="npfeed-setting-title">{{ $t('Deliver Reports Every') }}</h3>
                  <p class="npfeed-setting-description">{{ $t('Pick any day for your weekly email summary') }}</p>
                </div>
                <div class="npfeed-setting-right">
                  <el-select v-model="advance_settings.email_report.sending_day" class="npfeed-text-input npfeed-select" :placeholder="$t('Select')" size="default">
                    <el-option
                        v-for="(sendDay, dayKey) in sending_days"
                        :key="dayKey"
                        :label="sendDay"
                        :value="dayKey">
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div v-if="advance_settings.email_report.status === 'true'" class="npfeed-setting-row-no-border">
                <div class="npfeed-setting-left">
                    <h3 class="npfeed-setting-title">{{ $t('Recipients') }}</h3>
                    <p class="npfeed-setting-description">{{ $t('Please specify who will receive the email reports.') }}</p>
                </div>
                <div class="npfeed-setting-right">
                  <el-input
                      class="npfeed-text-input npfeed-input-border"
                      :placeholder="$t('Recipient Email Address')"
                      v-model="advance_settings.email_report.recipients">
                  </el-input>
                </div>
              </div>
              <p class="npfeed-ml-20 npfeed-pb-20"><strong>Note:</strong> Currently, this feature is available for <strong>Instagram</strong> and <strong>Facebook</strong> feeds. More platforms rolling out near future.</p>
            </div>
          </div>
          <el-button class="npfeed_primary_btn" type="success" size="default" @click="saveAdvanceSettings">Save Settings</el-button>
        </div>
    </div>
  </div>
</template>
  
  <script>
  import { InfoFilled } from '@element-plus/icons-vue';
  export default {
    name: "GlobalFeedSettings",
    components: {
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
        sending_days:{
          Mon: 'Monday',
          Tue: 'Tuesday',
          Wed: 'Wednesday',
          Thu: 'Thursday',
          Fri: 'Friday',
          Sat: 'Saturday',
          Sun: 'Sunday'
        },
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
    },
  
    mounted() {
      this.getAdvanceSettings();
    }
  }
  </script>