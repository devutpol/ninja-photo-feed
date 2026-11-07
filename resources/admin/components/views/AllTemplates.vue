<template>
  <div class="npfeed-page-container npfeed-all-templates-page">
    <div v-if="hasPermission(['npfeed_manage_templates', 'npfeed_full_access'])">
        <!-- Loading Skeleton -->
        <div v-if="loading" class="npfeed-page-section">
          <div class="npfeed-table-wrapper">
            <NpfeedSkeleton :rows="15" />
          </div>
        </div>

        <!-- Table Content -->
        <div v-else-if="total_items" class="npfeed-page-section">
          <!-- Header Section -->
          <div class="npfeed-table-header">
              <div class="npfeed-header-left">
                <h1 class="npfeed-page-title">{{ $t('All Templates') }}</h1>
                <p class="npfeed-page-subtitle">{{ $t('Your all templates are here') }}</p>
              </div>

              <div class="npfeed-header-right">
                <el-button v-if="availableSections.length === 1" type="primary" class="npfeed_primary_btn" :loading="creatingItem" @click="addNewItem(availableSections[0])">
                  <el-icon class="npfeed-mr-5"><Plus /></el-icon>{{ $t('Add %s Template', ucFirst(availableSections[0])) }}
                </el-button>
                <el-dropdown v-else-if="availableSections.length > 1" @command="addNewItem">
                  <el-button type="primary" class="npfeed_primary_btn" :loading="creatingItem">
                    <el-icon class="npfeed-mr-5"><Plus /></el-icon>{{ $t('Create Template') }}
                  </el-button>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item v-for="section in availableSections" :key="section" :command="section">
                        {{ $t('Add %s Template', ucFirst(section)) }}
                      </el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </div>
          </div>

          <!-- Filters and Actions -->
          <div class="npfeed-table-controls">
            <div class="npfeed-controls-left">
              <div class="npfeed-controls-bulk-actions">
               <el-select class="npfeed-select-field-primary" v-model="bulkAction" :placeholder="$t('Bulk Action')" size="default" style="width: 160px;">
                 <el-option :label="$t('Bulk Actions')" value="">
                  <span class="npfeed-d-flex npfeed-flex-align-center npfeed-gap-8">
                    <el-icon><Plus /></el-icon>
                    <span>{{ $t('Bulk Actions') }}</span>
                  </span>
                 </el-option>
                 <el-option :label="$t('Delete')" value="delete">
                   <span class="npfeed-d-flex npfeed-flex-align-center npfeed-gap-8">
                     <el-icon><Delete /></el-icon>
                     <span>{{ $t('Delete') }}</span>
                   </span>
                 </el-option>
                 <el-option :label="$t('Duplicate')" value="duplicate">
                   <span class="npfeed-d-flex npfeed-flex-align-center npfeed-gap-8">
                     <el-icon><DocumentCopy /></el-icon>
                     <span>{{ $t('Duplicate') }}</span>
                   </span>
                 </el-option>
               </el-select>
               <el-button @click="applyBulkAction" :disabled="!selectedItems.length">{{ $t('Apply') }}</el-button>
             </div>
              <div class="npfeed-controls-filter">
                <el-select class="npfeed-select-field-primary" v-model="filter_value" :placeholder="$t('All Platforms')" size="default" clearable
                           v-if="Object.entries(validPlatforms).length" @change="getItems" style="width: 220px;">
                  <el-option
                      v-for="item in validPlatforms"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value">
                  </el-option>
                </el-select>
              </div>
            </div>
            <div class="npfeed-controls-right">
              <el-input
                class="npfeed-input-default"
                v-model="search_string"
                :placeholder="$t('Search...')"
                size="default"
                style="width: 200px;"
                @keyup.enter="getItems()"
                @clear="handleClear"
                clearable>
                <template #prefix>
                  <el-icon class="el-input__icon" @click="getItems()"><Search /></el-icon>
                </template>
              </el-input>
            </div>
          </div>

          <!-- Templates Table -->
          <div class="npfeed-table-wrapper">
            <el-table
                class="npfeed-templates-table"
                :data="items"
                @selection-change="handleSelectionChange"
                v-loading="duplicatingItem"
                element-loading-text="Duplicating the template.. Please wait..."
            >
                <el-table-column type="selection" width="55" align="center"></el-table-column>

                <el-table-column :label="$t('ID')" width="70" prop="ID" sortable>
                  <template #default="scope">
                    <span class="npfeed-item-id npfeed-item-editable" @click.prevent="handleRowAction('edit', scope.row)">#{{ scope.row.ID }}</span>
                  </template>
                </el-table-column>

                <el-table-column
                    :label="$t('Title')"
                    min-width="180"
                    show-overflow-tooltip
                >
                  <template #default="scope" >
                    <div class="npfeed-item-title-cell npfeed-item-editable" @click.prevent="handleRowAction('edit', scope.row)">
                      <strong class="npfeed-item-title npfeed-d-flex npfeed-flex-align-center">
                        {{ $trimWords(scope.row.post_title, 3, true) }} <el-icon class="npfeed-ml-5 npfeed-display-on-hover"><Edit /></el-icon>
                      </strong>
                    </div>
                  </template>
                </el-table-column>

                <el-table-column :label="$t('Create Date')" min-width="140">
                  <template #default="scope">
                    <span class="npfeed-date">{{ wpsrDateFormat(scope.row.post_date_gmt, 'DD MMM, YYYY') }}</span>
                  </template>
                </el-table-column>

                <el-table-column :label="$t('ShortCode')" min-width="280">
                  <template #default="scope">
                    <div class="npfeed-shortcode-cell">
                      <template v-if="feedPlatforms.includes(scope.row.platform_name) || scope.row.platform_name === 'testimonial'">
                        <el-tooltip effect="dark" content="Click to copy shortcode" placement="top">
                          <code class="npfeed-shortcode copy"
                                :data-clipboard-text='`[${scope.row.platform_name === "social_wall" ? "ninja_photo_feed_wall_feed" : "ninja_photo_feed"} id="${scope.row.ID}" platform="${scope.row.platform_name}"]`'>
                            <el-icon class="npfeed-copy-icon"><DocumentCopy /></el-icon>
                            [{{ scope.row.platform_name === 'social_wall' ? 'ninja_photo_feed_wall_feed' : 'ninja_photo_feed' }} id="{{ scope.row.ID }}" platform="{{ scope.row.platform_name }}"]
                          </code>
                        </el-tooltip>
                      </template>
                      <template v-else>
                        <el-tooltip effect="dark" content="Click to copy shortcode" placement="top">
                          <code class="npfeed-shortcode copy"
                                :data-clipboard-text='`[ninja_photo_feed id="${scope.row.ID}" platform="reviews"]`'>
                            <el-icon class="npfeed-copy-icon"><DocumentCopy /></el-icon>
                            [ninja_photo_feed id="{{ scope.row.ID }}" platform="reviews"]
                          </code>
                        </el-tooltip>
                      </template>
                    </div>
                  </template>
                </el-table-column>

                <el-table-column width="70" align="center">
                  <template #default="scope">
                    <el-dropdown class="npfeed-actions" trigger="click" @command="(command) => handleRowAction(command, scope.row)">
                      <el-button text>
                        <el-icon size="16" color="var(--npfeed-svg-primary-color)"><MoreFilled /></el-icon>
                      </el-button>
                      <template #dropdown>
                        <el-dropdown-menu>
                          <el-dropdown-item command="edit" v-if="scope.row.platform_name">
                            <el-icon><Edit /></el-icon> Edit
                          </el-dropdown-item>
                          <el-dropdown-item command="duplicate">
                            <el-icon><DocumentCopy /></el-icon> Duplicate
                          </el-dropdown-item>
                          <el-dropdown-item command="clear_cache" v-if="feedPlatforms.includes(scope.row.platform_name)">
                            <el-icon><Coin /></el-icon> Clear Cache
                          </el-dropdown-item>
                          <el-dropdown-item class="npfeed-action-delete" command="delete">
                            <el-icon><Delete /></el-icon> Delete
                          </el-dropdown-item>
                        </el-dropdown-menu>
                      </template>
                    </el-dropdown>
                  </template>
                </el-table-column>
            </el-table>

            <!-- Pagination -->
            <div class="npfeed-table-pagination">
                <div class="npfeed-pagination-info">
                  <span class="npfeed-pagination-text">
                    {{ $t('Page %1$s of %2$s', page_number, Math.ceil(total / per_page)) }}
                  </span>
                </div>
                <el-pagination
                    background
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                    :current-page="page_number"
                    :page-size="per_page"
                    :page-sizes="pageSizes"
                    layout="sizes, prev, pager, next"
                    :total="total">
                </el-pagination>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!loading" class="npfeed-page-section">
          <div class="npfeed-empty-state">
            <div class="npfeed-empty-content">
              <span class="npfeed-empty-state-icon"><CreateTemplateIcon/></span>
              <h1>{{ $t('All Templates') }}</h1>
              <p v-if="Object.keys(validPlatforms).length">
                {{ $t('Start by creating a template to design your perfect layout.') }}
              </p>

              <template v-if="Object.keys(validPlatforms).length">
                <el-button v-if="availableSections.length === 1" class="npfeed_primary_btn" type="primary" :loading="creatingItem" @click="addNewItem(availableSections[0])">
                  <el-icon class="npfeed-mr-5"><Plus /></el-icon>{{ $t('Add %s Template', ucFirst(availableSections[0])) }}
                </el-button>
                <el-dropdown v-else-if="availableSections.length > 1" @command="addNewItem">
                  <el-button class="npfeed_primary_btn" type="primary" :loading="creatingItem">
                    <el-icon class="npfeed-mr-5"><Plus /></el-icon>{{ $t('Create Template') }}
                  </el-button>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item v-for="section in availableSections" :key="section" :command="section">
                        {{ $t('Add %s Template', ucFirst(section)) }}
                      </el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </template>

              <div v-if="!Object.keys(validPlatforms).length" class="npfeed-alert npfeed-alert-warning npfeed-d-flex npfeed-mt-20">
                <InfoIcon color="var(--npfeed-icon-info-warning-color)"/>
                <p>
                  <strong>{{ $t('Note:') }}</strong>
                  {{ $t('Before creating your template you need to configure your social platform.') }}
                  <router-link to="/">{{ $t('Click Here') }}</router-link>
                  {{ $t(' to configure your settings first!') }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!--Unified Delete Confirmation Modal-->
        <el-dialog
            class="npfeed-confirmation-modal"
            v-model="show_delete_modal"
            :close-on-click-modal="!loading"
            :show-close="!loading"
            width="40%"
            :before-close="handleDeleteClose">
          <div class="npfeed-delete-content">
            <h4 v-if="isBulkDelete">{{ $t('Are you sure you want to delete %s template(s)?', selectedItems.length) }}</h4>
            <h4 v-else>{{ $t('Are you sure you want to delete this template?') }}</h4>
            <p v-if="isBulkDelete">{{ $t('All the data associated with these templates will be deleted.') }}</p>
            <p v-else>{{ $t('All data associated with this template will be permanently deleted.') }}</p>

            <div class="npfeed-delete-details">
              <template v-if="isBulkDelete">
                <p><strong>{{ $t('Selected Templates:') }}</strong> {{ $t('%s item(s)', selectedItems.length) }}</p>
                <p><strong>{{ $t('IDs:') }}</strong> {{ selectedItems.map(item => '#' + item.ID).join(', ') }}</p>
              </template>
              <template v-else>
                <p><strong>{{ $t('Template ID:') }}</strong> #{{ deletingItem.ID }}</p>
                <p><strong>{{ $t('Template Title:') }}</strong> {{ deletingItem.post_title }}</p>
              </template>
            </div>
          </div>
          <template #footer>
            <div class="npfeed-d-flex npfeed-jc-end npfeed-mt-20">
              <el-button class="npfeed_default_btn" :disabled="loading" @click="show_delete_modal = false">{{ $t('Cancel') }}</el-button>
              <el-button class="npfeed_primary_btn" type="danger" :loading="loading" @click="confirmDelete()">{{ $t('Confirm') }}</el-button>
            </div>
          </template>
        </el-dialog>

        <!--Status Update Confirmation Modal-->
        <el-dialog
            class="npfeed-confirmation-modal"
            v-model="show_status_modal"
            :close-on-click-modal="!loading"
            :show-close="!loading"
            width="40%">
          <div class="npfeed-delete-content">
            <h4 v-if="bulkAction === 'enable'">{{ $t('Are you sure you want to enable %s template(s)?', selectedItems.length) }}</h4>
            <h4 v-else>{{ $t('Are you sure you want to disable %s template(s)?', selectedItems.length) }}</h4>
            <p v-if="bulkAction === 'enable'">{{ $t('This will activate the selected templates.') }}</p>
            <p v-else-if="bulkAction === 'disable'">{{ $t('This will deactivate the selected templates.') }}</p>

            <div class="npfeed-delete-details">
              <p><strong>{{ $t('Selected Templates:') }}</strong> {{ $t('%s item(s)', selectedItems.length) }}</p>
              <p><strong>{{ $t('IDs:') }}</strong> {{ selectedItems.map(item => '#' + item.ID).join(', ') }}</p>
            </div>
          </div>
          <template #footer>
            <div class="npfeed-d-flex npfeed-jc-end npfeed-mt-20">
              <el-button class="npfeed_default_btn" :disabled="loading" @click="show_status_modal = false">{{ $t('Cancel') }}</el-button>
              <el-button class="npfeed_primary_btn" :loading="loading" @click="performStatusUpdate()">{{ $t('Confirm') }}</el-button>
            </div>
          </template>
        </el-dialog>

        <!--Bulk Duplicate Confirmation Modal-->
        <el-dialog
            class="npfeed-confirmation-modal"
            v-model="show_bulk_duplicate_modal"
            :close-on-click-modal="!loading"
            :show-close="!loading"
            width="40%">
          <div class="npfeed-delete-content">
            <h4>{{ $t('Are you sure you want to duplicate %s template(s)?', selectedItems.length) }}</h4>
            <p>{{ $t('This will create copies of the selected templates.') }}</p>

            <div class="npfeed-delete-details">
              <p><strong>{{ $t('Selected Templates:') }}</strong> {{ $t('%s item(s)', selectedItems.length) }}</p>
              <p><strong>{{ $t('IDs:') }}</strong> {{ selectedItems.map(item => '#' + item.ID).join(', ') }}</p>
            </div>
          </div>
          <template #footer>
            <div class="npfeed-d-flex npfeed-jc-end npfeed-mt-20">
              <el-button class="npfeed_default_btn" :disabled="loading" @click="show_bulk_duplicate_modal = false">{{ $t('Cancel') }}</el-button>
              <el-button class="npfeed_primary_btn" :loading="loading" @click="performBulkDuplicate()">{{ $t('Confirm') }}</el-button>
            </div>
          </template>
        </el-dialog>

    </div>
    <PermissionsNotification v-if="!hasPermission(['npfeed_manage_templates', 'npfeed_full_access'])" />
  </div>
</template>

<script type="text/babel">
import {tableMixin} from "../../mixins/tableMixin";
import PermissionsNotification from "./advertise/PermissionsNotification.vue";
import CreateTemplateIcon from  '../pieces/icons/CreateTemplateIcon.vue';
import { Plus, Search, Edit, Delete, DocumentCopy, MoreFilled, WarningFilled, Coin } from '@element-plus/icons-vue';
import InfoIcon from "../pieces/icons/InfoIcon.vue";
import { PlatformIconMixin } from '../../mixins/PlatformIconMixin';

export default {
    name: 'AllTemplates',
    components: {
      InfoIcon,
      PermissionsNotification,
      Plus,
      Search,
      Edit,
      Delete,
      DocumentCopy,
      MoreFilled,
      WarningFilled,
      Coin,
      CreateTemplateIcon
    },
    mixins: [tableMixin, PlatformIconMixin],
    computed: {
      availableSections() {
        return this.valid_sections.filter(section => this.connectedPlatformSections && this.connectedPlatformSections[section]);
      }
    },
    data() {
      return {
        valid_sections: ['reviews', 'testimonial', 'youtube', 'instagram', 'facebook_feed', 'tiktok', 'twitter'],
        feedPlatforms: ['instagram', 'facebook_feed', 'youtube', 'tiktok', 'twitter'],
        selectedItems: [],
        bulkAction: '',
        show_status_modal: false,
        isBulkStatus: false,
        isBulkDelete: false,
        show_bulk_duplicate_modal: false,
        isBulkDuplicate: false,
        loading: false,
      }
    },
    methods: {
      getEditLink(row) {
        if(row.platform_name === null) {
          return { name: 'edit-template', params: { template_id: row.ID } };
        }

        if (this.getValidPlatforms(row.platform_name).length > 1 && (row.platform_name === 'tiktok' && this.is_custom_feed_for_tiktok_activated)) {
          return { name: 'edit-template', params: { template_id: row.ID } };
        } else {
          return { name: (this.feedPlatforms.includes(row.platform_name)) ? 'edit-' + row.platform_name.replace('_', '-') + '-template' : 'edit-template', params: { template_id: row.ID } };
        }
      },

      handleSelectionChange(selection) {
        this.selectedItems = selection;
      },

      handleRowAction(command, row) {
        switch(command) {
          case 'edit':
            this.$router.push(this.getEditLink(row));
            break;
          case 'duplicate':
            this.duplicateItem(row);
            break;
          case 'clear_cache':
            this.clearCache(row);
            break;
          case 'delete':
            this.isBulkDelete = false;
            this.beforeDeleteHandler(row);
            break;
        }
      },
      handleDeleteClose() {
        this.show_delete_modal = false;
        this.deletingItem = {};
        this.isBulkDelete = false;
      },
      confirmDelete() {
        if (this.isBulkDelete) {
          this.performBulkDelete();
        } else {
          this.deleteItems();
        }
      },
      beforeBulkDeleteHandler() {
        this.isBulkDelete = true;
        this.show_delete_modal = true;
      },
      applyBulkAction() {
        if (!this.bulkAction || !this.selectedItems.length) return;
        
        switch(this.bulkAction) {
          case 'delete':
            this.beforeBulkDeleteHandler();
            break;
          case 'duplicate':
            this.beforeBulkDuplicateHandler();
            break;
          case 'enable':
          case 'disable':
            this.bulkStatusItems();
            break;
          default:
            this.$notify.warning('Selected action is not supported yet');
        }
      },
      clearCache(row) {
        this.loading = true;

        this.$del('settings', {
          platform: row.platform_name,
          cacheType: 'template',
          templateId: row.ID
        }).then(response => {
          if(response){
            this.loading = false;
            this.handleSuccess(response.message);
          }
        }).catch(errors => {
          this.loading = false;
          this.handleError(errors);
        });
      },
    },
    mounted() {
        this.getAll('templates');
        this.clipboard();
    }
}
</script>