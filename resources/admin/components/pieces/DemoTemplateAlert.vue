<template>
  <div class="npfeed-alert npfeed-alert-warning npfeed-d-flex npfeed-flex-align-center">
    <InfoIcon/>
    <p>
      This is a <strong>demo template</strong> preview. Please connect your account from the <strong>"Connection"</strong> tab to display the original content.
    </p>
  </div>
</template>

<script>
import InfoIcon from './icons/InfoIcon.vue';

export default {
  name: 'DemoTemplateAlert',
  components: {
    InfoIcon
  }
}
</script>