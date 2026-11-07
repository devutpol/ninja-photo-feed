<template>
    <el-tooltip
        effect="dark"
        :content="minimized ? $t('Show settings') : $t('Hide settings')"
        placement="left"
    >
        <button
            type="button"
            class="npfeed-editor-sidebar-toggle npfeed_icon_button"
            :class="{ 'npfeed-editor-sidebar-toggle--minimized': minimized }"
            :aria-label="minimized ? $t('Show settings') : $t('Hide settings')"
            @click="$emit('toggle')"
        >
            <SidebarToggleIcon class="npfeed-editor-sidebar-toggle-icon" />
        </button>
    </el-tooltip>
</template>
<script>
import SidebarToggleIcon from './icons/SidebarToggleIcon';
export default {
    name: 'SidebarToggleButton',
    components: { SidebarToggleIcon },
    props: {
        minimized: {
            type: Boolean,
            default: false
        }
    },
    emits: ['toggle']
}
</script>
