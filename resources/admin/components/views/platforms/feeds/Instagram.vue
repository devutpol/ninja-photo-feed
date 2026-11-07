<template>
  <el-dialog title="Instagram Configuration" width="50%" class="npfeed-connection-modal" v-model="instagram_modal">
    <template #header>
      <div class="npfeed-connection-modal-header">
        <h4 class="npfeed-dialog-title">Instagram Configuration</h4>
        <p v-if="!Object.keys(connected_accounts).length" class="npfeed-connection-modal-subheading">Choose the type of account to connect</p>
        <p v-else class="npfeed-connection-modal-subheading">{{Object.keys(connected_accounts).length}} Account Connected.</p>
      </div>
    </template>

   <div class="npfeed-connection-modal-body" :class="!Object.keys(connected_accounts).length ? 'npfeed-gap-0' : ''">
     <div v-loading="loading" element-loading-text="Please wait.">
       <el-dialog
           title="Facebook Authorized Pages"
           class="npfeed-inner-modal"
           v-model="businessAccountsModal"
           v-if="Object.keys(businessAccounts).length"
           width="30%"
           append-to-body
       >
         <template #header>
           <div class="npfeed-connection-modal-header">
             <h4 class="npfeed-dialog-title">Facebook Authorized Pages</h4>
             <p class="npfeed-connection-modal-subheading">Choose which pages you would like to connect:</p>
           </div>
         </template>
         <div class="npfeed-modal-body">
           <div v-loading="inner_loading" element-loading-text="Please wait.">
             <div :class="Object.keys(businessAccounts).length > 5 ? 'npfeed-scrollbar' : ''">
             <el-checkbox class="npfeed_primary_checkbox_or_radio" v-if="Object.keys(businessAccounts).length > 1" :indeterminate="isIndeterminate"
                          v-model="selectAll" @change="handleCheckAllChange">
               Select all
             </el-checkbox>
             <el-checkbox-group v-model="selectedAccounts" @change="handleSelectedAccountsChange">
               <el-checkbox
                   class="npfeed_primary_checkbox_or_radio"
                   v-for="(account, index) in businessAccounts"
                   :label="account"
                   :key="account.username || index"
               >
                 <template v-if="businessAccounts[index]">
                   <div class="npfeed-user-profile-pic">
                     <img v-if="account.profile_picture_url" :src="account.profile_picture_url">
                     <img v-else :src="assets_url+'/images/template/review-template/placeholder-image.png'"/>
                   </div>
                   <div class="npfeed-user-details">
                     <p>{{ $trimWords(account.name, 8, true) }}</p>
                     <span>@{{ account.username }}</span>
                   </div>
                 </template>

               </el-checkbox>
             </el-checkbox-group>
             </div>
             <div class="npfeed-alert npfeed-alert-warning npfeed-d-flex npfeed-mt-10">
               <el-icon><InfoFilled /></el-icon>
              <p>
                <strong>Note:</strong> Changing the password, updating privacy settings, or removing the page admins for the related Facebook page
                may require
                <a href="https://wpsocialninja.com/docs/how-to-reauthorize-the-wp-social-ninja-instagram-facebook-app/"
                   target="_blank">manually reauthorizing our app</a> to reconnect an account.
              </p>
             </div>
           </div>
         </div>
         <div class="npfeed-modal-footer">
           <el-button class="npfeed_default_btn" type="default" size="small" @click="businessAccountsModal = false">{{$t('Cancel')}}</el-button>
           <el-button :disabled="Object.keys(selectedAccounts).length === 0 ? true : false" class="npfeed_primary_btn"
                      size="default" type="success" @click="saveVerificationConfigs">{{ $t('Connect Accounts') }}
           </el-button>
         </div>
       </el-dialog>

       <div class="npfeed-user-accounts-heading npfeed-jc-between" v-if="connected">
         <h4>
           {{ $t('Manage Instagram Accounts') }}
           <el-tooltip
               :raw-content="true"
               effect="dark"
               placement="top-start"
               :content="`Add or remove connected Instagram accounts`"
           >
             <el-icon ><InfoFilled /></el-icon>
           </el-tooltip>
         </h4>
         <el-button
             v-if="Object.keys(businessAccounts).length"
             :class="{'display-form': !!businessAccountsModal}"
             class="npfeed_simple_btn"
             type="success"
             @click="businessAccountsModal = true">
           + {{ $t('Add More Page') }}
         </el-button>
       </div>

       <div v-if="connected_accounts" :class="Object.keys(connected_accounts).length > 5 ? 'npfeed-scrollbar' : ''">
         <div class="npfeed-connected-accounts">
          <div v-for="(account,index) in connected_accounts" :key="index">
           <div class="npfeed-user-profile">
             <div class="npfeed-user-profile-inner">
               <div class="npfeed-user-profile-pic">
                 <img v-if="account.user_avatar" :src="account.user_avatar">
                 <img v-else :src="assets_url+'/images/template/review-template/placeholder-image.png'"/>
               </div>
               <div class="npfeed-user-details">
                <div class="npfeed-user-details-inner">
                  <div class="npfeed-user-details-info npfeed-flex-column npfeed-flex-align-left">
                    <p>{{ account.username }}</p>
                    <span>{{ account.api_type === 'business' ? 'Business Advanced' : account.api_type.replace(/_/g, ' ') }}<strong v-if="account.is_private"> ( {{ 'Private' }} )</strong></span>
                  </div>
                  <div class="npfeed-user-actions">
                          <span class="npfeed-user-token-expire-time" v-if="account.is_private">
                            {{ accessTokenExpiredIn(account) }}
                            <el-tooltip class="item" effect="dark" placement="right-start">
                              <div slot="content">This account is a <strong>private</strong> account on Instagram. It needs to be manually reconnect every 60 days. If you don't refresh your account, then the backup cache will be displayed instead. <br/> Change this account to be <a
                                  href="https://www.instagram.com/accounts/privacy_and_security/" target="_blank"><strong>public</strong></a> [Account Privacy->Private Account] to have access token that are automatically refreshed.</div>
                              <el-icon><InfoFilled /></el-icon>
                            </el-tooltip>
                            <el-button size="small" @click.prevent="oAuthLogin()" type="primary" :loading="instagramAuthLoading" :disabled="instagramAuthLoading">Get Refresh Token</el-button>
                          </span>
                    <remove @confirm="clearVerificationConfigs(account.user_id)" platform="instagram"></remove>
                  </div>
                </div>

                <div v-if="account.has_critical_error" class="npfeed-account-error-status-wrapper">
                   <div class="npfeed-account-error-status">
                       <span>
                         <el-icon><InfoFilled /></el-icon>
                         {{ $t('Source Invalid') }}
                       </span>
                      <a href="#" @click.prevent="oAuthLogin(account.api_type)" :class="{'npfeed-link-disabled': instagramAuthLoading}">Reconnect</a>
                    </div>
                </div>
                 <div v-else-if="account.encryption_error" class="npfeed-account-error-status-wrapper">
                   <span  class="npfeed-account-error-status">
                      <span>
                        <el-icon><InfoFilled /></el-icon>
                        {{ $t('Encryption Error') }}
                      </span>
                     <a href="#" @click.prevent="oAuthLogin(account.api_type)" :class="{'npfeed-link-disabled': instagramAuthLoading}">Reconnect</a>
                    </span>
                 </div>
               </div>
             </div>
           </div>
         </div>
         </div>
       </div>

       <el-button
           v-if="Object.keys(connected_accounts).length"
           style="width: 100%"
           v-model="addNewAccount"
           :class="{'display-form': !!addNewAccount}"
           class="npfeed_primary_btn_outline npfeed-mt-15"
           type="success"
           @click.prevent="addAccount">
         <el-icon size="18"><Link /></el-icon>
         {{ $t('Connect More Accounts') }}
       </el-button>

       <div v-if="addNewAccount || !Object.keys(connected_accounts).length">
         <el-tabs v-model="credentialsType" class="npfeed-connections-tabs">
           <el-tab-pane name="business_basic">
             <template #label>
              <span class="npfeed-connections-tabs-label">
                <el-icon size="18"><Suitcase /></el-icon>
                <div class="npfeed-connections-tabs-label-group">
                  <span>{{ this.$t('Business Basic') }}</span>
                  <p>{{ this.$t('Connects via Instagram')}}</p>
                </div>
              </span>
             </template>
             <div class="npfeed-connection-info-list-wrapper">
                <ul>
                  <li><el-icon size="16" color="var(--npfeed-icon-info-color)"><InfoFilled /></el-icon>
                    Requires Instagram Creator or Business account
                  </li>
                  <li><el-icon size="16" color="var(--npfeed-icon-info-color)"><CircleCheckFilled /></el-icon>
                    Display profile Info, avatars, and posts
                  </li>
                  <li><el-icon size="16" color="var(--npfeed-icon-info-color)"><CircleCheckFilled /></el-icon>
                    Connect your Instagram account
                  </li>
                  <li><el-icon size="16" color="var(--npfeed-icon-info-color)"><CircleCheckFilled /></el-icon>
                    Does not require a Facebook page
                  </li>
                  <li><el-icon size="16" color="var(--npfeed-icon-disabled-color)"><CircleCloseFilled /></el-icon>
                    Does not display Hashtag feeds
                  </li>
                </ul>
             </div>
             <div class="npfeed-accounts-connect-wrapper">
               <el-button class="npfeed-login-with-ig-btn" @click.prevent="oAuthLogin()" size="default" type="primary" :loading="instagramAuthLoading" :disabled="instagramAuthLoading">
                 <img :src="assets_url+'/images/icon/icon-instagram-small.png'" alt="Instagram">
                 {{ $t('Connect Instagram Account') }}
               </el-button>
               <p class="npfeed-connect-redirect-note">{{ $t('You will be redirected to our secure login, then to Instagram to approve access.') }}</p>
             </div>
           </el-tab-pane>
           <el-tab-pane label="Business Advanced" name="business">
             <template #label>
              <span class="npfeed-connections-tabs-label">
                <el-icon size="18"><Briefcase /></el-icon>
                 <div class="npfeed-connections-tabs-label-group">
                    <span>Business Advanced</span>
                    <p>Connects via Facebook</p>
                 </div>
              </span>
             </template>
             <div class="npfeed-connection-info-list-wrapper">
               <ul>
                 <li><el-icon size="16" color="var(--npfeed-icon-info-color)"><InfoFilled /></el-icon>
                   Requires Instagram Creator or Business account
                 </li>
                 <li><el-icon size="16" color="var(--npfeed-icon-info-color)"><InfoFilled /></el-icon>
                   Requires a connected Facebook page
                 </li>
                 <li><el-icon size="16" color="var(--npfeed-icon-info-color)"><CircleCheckFilled /></el-icon>
                   Display profile Info, avatars, and posts
                 </li>
                 <li><el-icon size="16" color="var(--npfeed-icon-info-color)"><CircleCheckFilled /></el-icon>
                   Connect multiple Instagram accounts
                 </li>
                 <li><el-icon size="16" color="var(--npfeed-icon-info-color)"><CircleCheckFilled /></el-icon>
                   Displays Hashtag feeds
                 </li>
               </ul>
             </div>
             <div class="npfeed-accounts-connect-wrapper">
               <span v-if="credentialsType === 'business'">
                <el-button class="npfeed-login-with-facebook" @click.prevent="oAuthLogin()" size="default" type="primary" :loading="instagramAuthLoading" :disabled="instagramAuthLoading">
                  <FacebookMarkIcon/>
                  {{ $t('Connect Facebook Account') }}
                </el-button>
                <p class="npfeed-connect-redirect-note">{{ $t('You will be redirected to our secure login, then to Facebook to approve access.') }}</p>
              </span>
             </div>
           </el-tab-pane>

           <el-tab-pane label="Manual Setup" name="manually">
             <template #label>
              <span class="npfeed-connections-tabs-label">
                <el-icon size="18"><User /></el-icon>
                 <div class="npfeed-connections-tabs-label-group">
                     <span>{{ $t('Manual Setup') }}</span>
                     <p>Connect via Token</p>
                 </div>
              </span>
             </template>
             <div class="npfeed-accounts-connect-wrapper">
               <div class="npfeed-connection-modal-input-wrapper">
                 <div class="npfeed-connection-modal-input-heading-wrapper">
                   <h4 class="npfeed-connection-modal-input-heading">{{ $t('Access Token') }}
                     <el-tooltip
                         :raw-content="true"
                         effect="dark"
                         placement="right-start"
                         :content="$t('Please enter the valid Instagram Access Token.')"
                     >
                       <el-icon size="18" color="var(--npfeed-icon-info-secondary-color)"><InfoFilled /></el-icon>
                     </el-tooltip>
                   </h4>
                   <p class="npfeed-connection-modal-input-description">Connecting a client's account? Avoid sharing accounts password, please use our <a href="https://wpsocialninja.com/access-token-generator/?id=instagram-feed" target="_blank">access token generator</a>.</p>
                 </div>
                 <InputPassword class="npfeed-mb-10" type="text" size="default" v-model="instagramAccessToken"
                                placeholder="Enter a valid Access Token"></InputPassword>
               </div>
               <div class="npfeed-connection-modal-input-wrapper npfeed-mt-20">
                 <div class="npfeed-connection-modal-input-heading-wrapper">
                   <h4 class="npfeed-connection-modal-input-heading">
                     {{ $t('Instagram User ID') }}
                   </h4>
                 </div>
                 <el-input class="npfeed-input-default" type="text" size="default" v-model="ig_account_id" placeholder="Please enter the User ID for this Profile">
                   <template #prepend>
                     <el-icon size="18"><User /></el-icon>
                   </template>
                 </el-input>
               </div>

               <div class="npfeed-modal-group-btn npfeed-mt-20">
                 <el-button :disabled="!(instagramAccessToken && ig_account_id)" class="npfeed_primary_btn" size="default" type="success" @click="saveVerificationConfigs">
                   {{ $t('Connect this Account') }}
                 </el-button>
               </div>
             </div>
           </el-tab-pane>
         </el-tabs>
       </div>

       <div v-if="connected" class="npfeed-make-template-btn-wrap">
         <h3>Let’s create an awesome template to be added to your site!</h3>
         <p>Create a stunning, fully customizable template to perfectly match your website’s style.</p>
         <el-button class="npfeed_primary_btn" type="primary" size="default" @click.prevent="addNewFeedTemplate">
           <CreateTemplateIcon/>
           {{ $t('Add New Template') }}
         </el-button>
       </div>

     </div>
   </div>
  </el-dialog>
  <AuthRedirectOverlay
      :visible="instagramAuthLoading"
      :title="`${$t('Redirecting to')} ${authServiceHost}`"
      :subtitle="$t('You will be redirected to our authentication portal. This may take up to 5 seconds.')"
  />
</template>
<script>
import Remove from '../../../core-ui/editor/RemoveConfirm';
import InputPassword from "../../../core-ui/editor/InputPassword.vue";
import CreateTemplateIcon from  '../../../pieces/icons/CreateTemplateIcon.vue';
import AuthRedirectOverlay from '../../../core-ui/AuthRedirectOverlay.vue';
import FacebookMarkIcon from '../../../pieces/icons/FacebookMarkIcon.vue';
import { wpsrSubmitAuthForm } from "../../../../utils";

export default {
  props: ['instagramModal', 'access_token', 'expires_in', 'credentials_type'],
  emits: ['instagram_settings', 'findEnabledPlatforms', 'update:access_token'],
  data() {
    return {
      loading: false,
      inner_loading: false,
      instagramAuthLoading: false,
      _openBusinessModal: false,
      admin_page_url: this.appVars.admin_page_url,
      type: null,
      credentialsType: 'business_basic',
      apiType: 'business_basic',
      instagramAccessToken: this.access_token,
      connected: false,

      connected_accounts: [],
      addNewAccount: false,
      isIndeterminate: true,
      _businessAccountsModal: false,
      businessAccounts: [],
      selectAll: false,
      selectedAccounts: [],
      ig_account_id: null
    }
  },

  components: {
    FacebookMarkIcon,
    InputPassword,
    Remove,
    CreateTemplateIcon,
    AuthRedirectOverlay,
  },

  methods: {
    handleCheckAllChange(value) {
      this.selectedAccounts = value ? this.businessAccounts : [];
      this.isIndeterminate = false;
    },
    handleSelectedAccountsChange(value) {
      this.selectedAccounts = value;
      // if(Object.keys(this.selectedAccounts).length > 1 && !this.has_pro ){
      //   this.handlePro();
      // }
      this.instagramAccessToken = value.access_token;
      this.ig_account_id = value.id;
      this.credentialsType = 'business';
      let checkedCount = Object.keys(value).length;
      this.selectAll = checkedCount === Object.keys(this.businessAccounts).length;
      this.isIndeterminate = checkedCount > 0 && checkedCount < Object.keys(this.businessAccounts).length;
    },
    addAccount() {
      this.addNewAccount = !this.addNewAccount;
    },
    oAuthLogin(apiType = '') {
      if (this.instagramAuthLoading) return;
      this.credentialsType = apiType || this.credentialsType;
      this.instagramAuthLoading = true;

      try {
        const hash = window.location.hash;
        const hashMatch = hash.match(/edit-instagram-template\/?(\d+)/);
        if (hashMatch && hashMatch[1]) {
          window.sessionStorage.setItem('npfeed_auth_return_route', JSON.stringify({
            name: 'edit-instagram-template',
            templateId: hashMatch[1],
          }));
        }
      } catch (e) {}

      this.$post('platforms/feeds/configs/prepare', {
        platform:   'instagram',
        provider:   'instagram',
        flow:       this.credentialsType,
        return_url: this.admin_page_url,
      }).then(response => {
        if (response && response.data && response.data.action) {
          wpsrSubmitAuthForm(response.data.action, response.data.fields);
        } else {
          this.instagramAuthLoading = false;
        }
      }).catch(error => {
        this.instagramAuthLoading = false;
        this.handleError(error);
      });
    },

    saveVerificationConfigs() {
      if (this.instagramAccessToken !== '' || Object.keys(this.selectedAccounts).length) {
        this.loading = true;
        this.inner_loading = true;

        if((this.expires_in && this.credentialsType !== 'business') || (this.apiType === 'business_basic' && this.credentialsType === 'manually')){
          this.apiType = 'business_basic';
        } else {
          this.apiType = 'business';
        }

        let settings = {
          credentials_type: this.credentialsType,
          api_type: this.apiType,
          access_token: this.instagramAccessToken,
          expires_in: this.expires_in,
          user_id: this.ig_account_id,
          selectedAccounts: this.selectedAccounts
        }

        this.$post('platforms/feeds/configs', {
          settings: JSON.stringify(settings),
          platform: 'instagram'
        }).then(response => {
              if (this.businessAccounts && this.expires_in === null) {
                this.businessAccountsModal = true;
              }

              if (response.data) {
                if(response.data.settings){
                  this.businessAccountsModal = false;
                }
                this.instagramAccessToken = '';
                this.ig_account_id = null;
                this.$emit('update:access_token', '');
                this.getVerificationConfigs();
                this.handleSuccess(response.data.message);
              }
            }).catch(error => {
              this.handleError(error);
            }).always(() => {
              this.loading = false;
              this.inner_loading = false;
            });
      } else {
        let message = 'Field should not be empty!';
        this.handleError(message);
      }
    },

    getVerificationConfigs() {
      const queryString = window.location.search;
      const urlParams = new URLSearchParams(queryString);
      this.type = urlParams.get('type');

      this.connected = false;
      this.$get('platforms/feeds/configs', {
        platform: 'instagram'
      }).then(response => {
        if (response.data) {
          if (response.data.business_accounts) {
            this.businessAccounts = response.data.business_accounts;
          }

          if (this._openBusinessModal && response.data.business_accounts && Object.keys(response.data.business_accounts).length) {
            this._openBusinessModal = false;
            this.businessAccountsModal = true;
          }

          if (response.data.connected_accounts && response.data.connected_accounts.length !== 0) {
            this.connected = true;
          }

          this.connected_accounts = response.data.connected_accounts;

          this.$emit('findEnabledPlatforms');
        }

      }).catch(error => {
        this.handleError(error);
        this.businessAccounts = []
      }).always(() => {

      });
    },

    clearVerificationConfigs(user_id) {
      this.$del('platforms/feeds/configs', {
        platform: 'instagram',
        user_id: user_id
      }).then(response => {
        if (response.data) {
          this.getVerificationConfigs();
          this.$emit('update:access_token', '');
          this.apiType = 'business_basic';
          this.credentialsType = 'business_basic';
          this.instagramAccessToken = '';
          this.connected = false;
          this.handleSuccess(response.data.message);
        }
      }).catch(error => {

      }).always(() => {

      });
    },

    addNewFeedTemplate() {
      this.$post('templates', {
        platform: 'instagram'
      }).then(response => {
        if (response && response.template_id) {
          this.$router.push({
            name: 'edit-instagram-template',
            params: {
              template_id: response.template_id
            }
          })
          // close modal after successful template creation
          this.instagram_modal = false;
        }
      }).catch(error => {
        this.handleError(error);
      }).always(() => {

      });
    },

    accessTokenExpiredIn(account) {
      let expiredTime = (Number(account.expires_in) + Number(account.created_at));
      let currentTime = ((new Date()).getTime() / 1000);
      let days = (expiredTime - currentTime) / 86400;

      if (days <= 0) {
        return 'Access Token is Expired'
      }

      return 'Access Token Expires in ' + Math.floor(days) + ' Days';
    },

  },

  computed: {
    instagram_modal: {
      get() {
        return this.instagramModal
      },
      set(newVal) {
        this.$emit('instagram_settings', newVal);
      }
    },
    businessAccountsModal: {
      get() {
        return this._businessAccountsModal;
      },
      set(value) {
        if (!value) {
          this.selectedAccounts = [];
          this.selectAll = false;
          this.isIndeterminate = false;
        }
        this._businessAccountsModal = value;
      }
    },
    authServiceHost() {
      try { return new URL(this.appVars.auth_service_url).hostname; } catch (e) { return this.appVars.auth_service_url; }
    },
  },
  watch: {
    'instagramAccessToken': {
      handler(newVal) {
        if (this.credentialsType === 'manually') {
          if (newVal.indexOf('EAAGTJHJgNIMB') !== -1) {
            this.apiType = 'business';
          } else {
            this.apiType = 'business_basic';
          }
        }
      },
      deep: true
    },
  },
  mounted() {
    try {
      const consumed = JSON.parse(window.sessionStorage.getItem('npfeed_auth_consumed_result') || 'null');
      if (consumed && consumed.platform === 'instagram' && consumed.flow === 'business') {
        window.sessionStorage.removeItem('npfeed_auth_consumed_result');
        this._openBusinessModal = true;
      }
    } catch (e) {}

    this.getVerificationConfigs();
    this.addNewAccount = false;
  }
}
</script>
<style scoped>
.npfeed-link-disabled {
  pointer-events: none;
  opacity: 0.5;
}
</style>