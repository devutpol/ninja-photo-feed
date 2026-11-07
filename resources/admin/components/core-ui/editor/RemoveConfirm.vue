<template>
    <span>
        <el-popover
            ref="popoverRef"
            placement="top"
            width="360"
            :visible="visible"
            @update:visible="visible = $event"
            trigger="click"
        >

          <template #default>
                <h4>{{message}}</h4>
                <div class="npfeed-d-flex npfeed-jc-end npfeed-mt-20">
                    <el-button class="npfeed_default_btn" type="default" size="small" @click="visible = false">{{$t('Cancel')}}</el-button>
                    <el-button class="npfeed_primary_btn" type="primary" size="small" @click="confirmAction">{{$t('Confirm')}}</el-button>
                </div>
            </template>
            <template #reference>
                <div v-if="submit_button_type === 'text'">
                    
                    <span class="npfeed-settings-clear-btn npfeed-d-flex npfeed-align-items-center" :class="displayRefreshLeft === 'trash' ? 'npfeed-gap-5' : ''">
                      <TrashcanIcon v-if="displayRefreshLeft == 'trash'"/> 
                      <el-icon v-if="displayRefreshLeft !== 'trash'"><RefreshLeft/></el-icon>
                      <span>{{button_text}}</span>
                    </span>
                </div>
                <el-button v-else class="npfeed-clear-btn npfeed_mini npfeed_light_edit npfeed_default_btn npfeed_icon_btn">
                    <TrashcanIcon />
                </el-button>
<!--                <el-icon v-else class="npfeed-clear-btn"><Close /></el-icon>-->
            </template>

        </el-popover>

    </span>
</template>

<script type="text/babel">

import {RefreshLeft} from '@element-plus/icons-vue';
import TrashcanIcon from '../../pieces/icons/TrashcanIcon.vue';
export default {
    name: 'RemoveConfirm',
    emits: ['confirm'],
    components: {
      RefreshLeft,
      TrashcanIcon
    },
    props: {
        displayRefreshLeft: {
          type: [Boolean, String],
          default: false
        },
        button_text: {
          type: String,
          default: ""
        },
        submit_button_type: {
          type: String,
          default: "icon"
        },
        message: {
          type: String,
          default: 'Are you sure you want to delete this account?'
        },
        plain: {
            type: Boolean,
            default: false
        },
        platform:{
            type: String,
            default: ''
        }
    },
    data() {
        return {
            visible: false
        }
    },
    methods: {
        confirmAction() {
            this.visible = false;
            this.$emit('confirm');
        }
    }
}
</script>