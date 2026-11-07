<template>
      <div class="npfeed-settings-switch">
        <span class="npfeed-element-label npfeed-editor-inside-left">
          {{ $t('Typography') }}
        </span>
        <el-popover
            placement="left"
            width="330"
            trigger="click"
            popper-class="npfeed-typography-popover"
            class="npfeed-editor-inside-right"
        >
          <template #default>
            <TypographyElement
              :typography="typography"
              @resetMe="resetMe"
              :identifier="identifier"
              @update:typography="$emit('update:typography', $event)"
            />
          </template>
          <template #reference>
            <el-button class="npfeed_default_btn" :disabled="!has_pro">
              <el-icon><Edit /></el-icon>
            </el-button>
          </template>
        </el-popover>
      </div>
</template>

<style>
.npfeed-typography-popover{
  padding: 0 !important;
}
</style>

<script>
import TypographyElement from './TypographyElement';

export default {
  name: "TypographyElementWrapper",
  props: {
    typography: {
      type: Object,
      required: true
    },
    identifier: {
      type: String,
      required: true
    }
  },
  components: {
    TypographyElement
  },
  emits: ['resetMe', 'update:typography'],
  computed: {},
  methods: {
    resetMe(identifier) {
      this.$emit('resetMe', identifier);
    }
  }
}
</script>