<template>
  <div class="npfeed-editor-view-info-bar npfeed-responsive-bar">
    <span class="npfeed-editor-info npfeed-body-regular npfeed-body-regular-color">{{ editorInfo }}</span>
    <!-- Device Manager Component - handles all device switching logic and UI -->
    <ResponsiveBar 
      :field="field"
      :type="type"
      :active-device="activeDeviceName" 
      @handle-device="onHandleDevice"
      @handle-device-responsive="onHandleDeviceResponsive"
    />
    <span class="npfeed-template-selector">
        <span class="npfeed-template-selector-icon">
            <TemplateIcon
                class="npfeed-editor-icon"
                width="16"
                height="16"
            />
            <span class="npfeed-template-selector-label npfeed-paragraph-small npfeed-heading-text">{{ $t('Template') }}:</span>
        </span>

        <span class="npfeed-label-small npfeed-heading-text">{{ (templateName || 'Default').charAt(0).toUpperCase() + (templateName || 'Default').slice(1) }}</span>
    </span>
  </div>
</template>
<script type="text/babel">
import ResponsiveBar from "./ResponsiveBar.vue";
import ChatEditorAccordionsIconPack from "../../pieces/icons/ChatEditorAccordionsIconPack.vue";
import TemplateIcon from "../../pieces/icons/TemplateIcon.vue";
export default {
  name: 'EditorViewInfoBar',
  components: { 
    ResponsiveBar,
    ChatEditorAccordionsIconPack,
    TemplateIcon
  },
  props: {
    field: {
      type: Object,
      default: () => ({})
    },
    type: {
      type: String,
      default: ''
    },
    activeDevice: {
      type: String,
      default: ''
    },
    templateName: {
      type: String,
      default: ''
    },
    editorInfo:{
      type: String,
      default: ''
    }
  },
  emits: ['handleDevice', 'handleDeviceResponsive'],
  data() {
    return {
      activeDeviceName: this.activeDevice ? this.activeDevice : 'dashicons-desktop'
    }
  },
  methods: {
    onHandleDevice(deviceType, activeDevice) {
      // this.$emit('handleDevice', deviceType, activeDevice);
    },
    onHandleDeviceResponsive(deviceType, activeDevice) {
      // this.$emit('handleDeviceResponsive', deviceType, activeDevice);
    }
  },
  watch: {
    activeDevice: {
      handler(newVal) {
        this.activeDeviceName = newVal;
      }
    }
  }
}
</script>