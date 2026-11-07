<template>
  <transition name="fade">
    <div class="npfeed-onboard-popup">
      <div class="npfeed-onboard-popup-content">
        <div class="npfeed-onboard-popup-logo">
          <img :src="assetsUrl + '/images/logo/ninja-photo-feed.svg'" alt="">
        </div>
        <h3>{{ title }}</h3>
        <p>{{ description }}</p>
      </div>
    </div>
  </transition>
</template>

<script>
import { DarkModeMixin } from '../../mixins/DarkModeMixin';

export default {
  name: 'OnboardWelcomePopup',
  mixins: [DarkModeMixin],
  computed: {
    assetsUrl() {
      return this.assets_url || this.appVars?.assets_url || window.NinjaPhotoFeedAdmin?.assets_url || '';
    },
    isFromOnboarding() {
      return this.$route?.query?.onboarding === 'true';
    },
    title() {
      return this.isFromOnboarding 
        ? 'Welcome to Your Template Editor'
        : '';
    },
    description() {
      return this.isFromOnboarding
        ? 'Start customizing your template to match your brand and style with ease.'
        : 'Please wait while we load your template data...';
    }
  }
}
</script>