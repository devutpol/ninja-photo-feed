<template>
  <div>
    <div class="settings_header">
      <div class="settings_left">
        <div class="npfeed_back_nav">
          <router-link class="npfeed-label-small npfeed-secondary-link" :to="{name: route_name}">{{route_title}}</router-link> 
          <span class="npfeed_sep"><ArrowRightIcon/></span>
        </div>
        
        <span class="section_title npfeed-label-small npfeed-heading-text">{{ template_title }}</span>
        <el-button class="npfeed_mini npfeed_light_edit npfeed_plain_btn npfeed_icon_button" @click="title_form_visible=true">
          <EditPenIcon />
        </el-button>
      </div>
      <div class="settings_right">
        <span v-if="(platform_section !== 'chats' && route_name !== 'notifications')" class="npfeed-copy-inner-text">
          <el-tooltip
              effect="dark"
              content="Click to copy shortcode"
              placement="bottom"
          >
            <code class="copy has-icon npfeed-paragraph-small npfeed-heading-text" :data-clipboard-text='`[${dynamicShortcodeName} id="${templateId}" platform="${platform_section}"]`'>
              <CopyDocumentIcon/> [{{dynamicShortcodeName}} id="{{ templateId }}" platform="{{platform_section}}"]
            </code>
          </el-tooltip>
        </span>
        <span class="npfeed_editor_full_screen">
          <el-button class="npfeed_mini npfeed_light_edit npfeed_default_btn npfeed_icon_btn" @click.prevent="handleFullScreen">
            <FullScreenIcon />
          </el-button>
        </span>
        <el-button class="npfeed_secondary_btn" @click.prevent="$emit('update-settings')" :loading="saving">
          {{ saving ? $t('Saving') : $t('Save') }}
        </el-button>
        <ColorMode/>
      </div>
    </div>
    <!-- Template Title Change Dialog -->
    <el-dialog
      v-model="title_form_visible"
      title="Change Template Title"
      width="30%"
      class="npfeed-modal npfeed-connection-modal"
    >
      <template #header>
        <div class="npfeed-connection-modal-header">
          <h4 class="npfeed-dialog-title">Change Template Title</h4>
        </div>
      </template>
      <div class="npfeed-connection-modal-body" style="padding: 0;">
        <el-input
          v-model="template_title"
          @keyup.enter="changeTitle"
          autocomplete="off"
          class="npfeed-text-input"
        />
      </div>

      <template #footer>
        <span class="dialog-footer npfeed-d-flex npfeed-jc-end">
          <el-button class="npfeed_default_btn" @click="title_form_visible = false">Cancel</el-button>
          <el-button class="npfeed_secondary_btn" type="primary" @click="changeTitle">Save</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script type="text/babel">
import ColorMode from "./ColorMode.vue";
import {
  EditPen,
  ArrowRight,
  DocumentCopy,
  FullScreen,
} from '@element-plus/icons-vue'
import CopyDocumentIcon from "./icons/CopyDocumentIcon.vue";
import FullScreenIcon from "./icons/FullScreenIcon.vue";
import EditPenIcon from "./icons/EditPenIcon.vue";
import ArrowRightIcon from "./icons/ArrowRightIcon.vue";
export default {
  components: {
    ColorMode,
    CopyDocumentIcon,
    FullScreenIcon,
    EditPenIcon,
    ArrowRightIcon
  },
  props: ['title', 'platform_section', 'shortcode_name', 'route_name', 'route_title'],
  data() {
    return {
      saving: false,
      templateId: this.$route.params.template_id,
      title_form_visible: false,
      template_title: this.title,
      count: 0,
      dynamicShortcodeName: this.shortcode_name || 'ninja_photo_feed'
    }
  },
  methods: {
    changeTitle() {
      if (!this.template_title) {
        let message = 'Template Title Should be Filled!';
        this.handleError(message);
      } else if (this.template_title) {
        let title_id = this.platform_section === 'chats' ? this.$route.params.widget_id : this.$route.params.template_id;
        this.$put(`templates/title/${title_id}/`, {
          template_title: this.template_title
        }).then(response => {
          if (response) {
            this.handleSuccess(response.message);
          }
        }).catch(error => {
          this.handleError(error);
        }).always(() => {
          this.title_form_visible = false;
        });
      }
    },
  },
  watch: {
    title(newTitle) {
      this.count++;
      if(this.count > 1){
        return;
      }
      this.template_title = newTitle;
    }
  },
}
</script>