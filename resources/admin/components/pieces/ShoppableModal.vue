<template>
  <div>
      <el-form label-position="top">
        <div class="npfeed-shoppable-promo-modal">
          <div class="npfeed-d-flex npfeed-jc-between npfeed-flex-column">
            <div class="npfeed-modal-field-group npfeed-mb-15">
              <label class="npfeed-field-label">{{$t('Select Source')}}</label>
              <el-select class="npfeed-modal-select npfeed-select-input-field" v-model="shoppable_fields.source_type" placeholder="Select" size="large" @change="$emit('on_post_source_change')">
                <el-option
                    v-for="(item, index) in post_types"
                    :key="index"
                    :label="item.title"
                    :value="item.name">
                </el-option>
              </el-select>
            </div>
            <div v-if="shoppable_fields.source_type.length" class="npfeed-modal-field-group npfeed-mb-15">
              <label v-if="shoppable_fields.source_type === 'custom_url'" class="npfeed-field-label">{{$t('URL')}}</label>
              <label v-else class="npfeed-field-label">{{$t('Link To')}}</label>

              <AsyncMultipleSelect
                  v-if="shoppable_fields.source_type !== 'custom_url'"
                  v-model="shoppable_fields.url_settings.id"
                  searchRoute="pages/search"
                  :multiple="false"
                  :includeEverywhere="false"
                  :extraParams="{ post_type: shoppable_fields.source_type }"
                  :key="shoppable_fields.source_type"
                  class="npfeed-modal-select npfeed-select-input-field"
              />
              <error-view :errors="errors" :field="'id'" />

              <el-input v-if="shoppable_fields.source_type === 'custom_url'" type="text" size="small" placeholder="Enter your URL" v-model="shoppable_fields.url_settings.url" class="npfeed-modal-input npfeed-input-default npfeed-text-input npfeed-input-default npfeed-border-all-around"></el-input>

              <error-view :errors="errors" :field="'url'" />
            </div>

            <div class="npfeed-modal-field-group npfeed-mb-15">
              <el-checkbox v-model="shoppable_fields.url_settings.open_in_new_tab" class="npfeed-modal-checkbox">{{$t('Open in a new tab')}}</el-checkbox>
            </div>

            <div class="npfeed-modal-field-group">
              <label class="npfeed-field-label">Button Text</label>
              <el-input
                  class="npfeed-modal-input npfeed-input-default npfeed-text-input npfeed-input-default npfeed-border-all-around"
                  placeholder="Button Text"
                  v-model="shoppable_fields.url_settings.text"
                  size="small">
              </el-input>
            </div>
          </div>
        </div>
       </el-form>
       
       <div class="npfeed-shoppable-promo-footer dialog-footer">
         <el-button type="default" class="npfeed_default_btn" @click="$emit('close_shoppable_modal')">{{ $t('Cancel') }}</el-button>
         <el-button type="primary" class="npfeed_primary_btn" @click="$emit('add_template_settings')">
           {{ $t('Apply') }}
         </el-button>
       </div>
  </div>
</template>

<script>
import ErrorView from './../../errors/errorView'
import AsyncMultipleSelect from './../core-ui/editor/AsyncMultipleSelect'

export default {
  name: 'ShoppableModal',
  components: {
    ErrorView,
    AsyncMultipleSelect
  },
  props: {
    shoppable_fields: {
      type: Object,
      required: true
    },
    post_types: {
      type: Array,
      default: () => []
    },
    errors: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['on_post_source_change', 'add_template_settings', 'close_shoppable_modal']
}
</script>