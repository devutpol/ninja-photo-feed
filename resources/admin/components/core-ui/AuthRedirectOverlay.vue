<template>
  <!-- On <body> so the full-screen editor's .wrap stacking context cannot trap it under a dialog. -->
  <Teleport to="body">
    <div v-if="visible" class="npfeed-auth-redirect-overlay">
      <div class="npfeed-auth-redirect-modal">
        <div class="npfeed-auth-redirect-dots">
          <span class="npfeed-auth-redirect-dot"></span>
          <span class="npfeed-auth-redirect-dot"></span>
          <span class="npfeed-auth-redirect-dot"></span>
        </div>
        <h3 class="npfeed-auth-redirect-title">{{ title }}</h3>
        <p class="npfeed-auth-redirect-subtitle">{{ subtitle }}</p>
      </div>
    </div>
  </Teleport>
</template>

<script type="text/babel">
export default {
  name: 'AuthRedirectOverlay',
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
    title: {
      type: String,
      default: 'Redirecting...',
    },
    subtitle: {
      type: String,
      default: '',
    },
  },
}
</script>
