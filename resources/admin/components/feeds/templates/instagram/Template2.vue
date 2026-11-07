<template>
    <div>
      <div v-if="configs.dynamic && configs.dynamic.error_message && configs.dynamic.error_message.length">
        <ErrorCard :error_message="configs.dynamic.error_message"/>
      </div>
        <div class="npfeed-ig-feed-wrapper npfeed-instagram-slider-activate npfeed-ig-feed-template2" :class="'npfeed-column-gap-'+configs.feed_settings.column_gaps+' npfeed-ig-feed-template-'+$route.params.template_id+' npfeed-ig-feed-'+configs.feed_settings.template+' npfeed-ig-scheme-'+(configs.feed_settings.color_scheme || 'light')+(configs.feed_settings.layout_type === 'list' ? ' npfeed-instagram-list-activate' : '')+(configs.feed_settings.layout_type === 'masonry' ? ' npfeed-instagram-masonry-activate' : '')">
            <div class="npfeed-container">
              <Header
                    v-if="configs.dynamic && configs.dynamic.header"
                    :connected_ids="configs.connected_ids"
                    :header="configs.dynamic.header"
                    :settings="configs.feed_settings"
                    :account_to_show="configs.feed_settings.header_settings.account_to_show"
                    :user_avatar="configs.dynamic && configs.dynamic.header && configs.dynamic.header.user_avatar"
                />
                <div
                    :class="configs.feed_settings.layout_type === 'carousel' && has_pro ? 'npfeed-ig-feed-wrapper-inner' : ''"
                    v-if="configs.dynamic && configs.dynamic.items"
                >
                  <div :class="[isCarouselLayout ? 'swiper-container' : '']">
                      <div :class="feedContainerClasses">
                          <div
                              :class="getItemWrapperClass()"
                              v-for="(feed, index) in configs.dynamic.items"
                              :key="index"
                          >
                            <div v-if="feed && (feed.media_url || feed.default_media || feed.thumbnail_url)" class="npfeed-ig-post" :class="['npfeed-image-ratio-'+configs.feed_settings.post_settings.aspect_ratio]" @click="setCurrentFeed(feed,index, configs.feed_settings, image_settings)" :data-post_id="feed.id" :data-user_name="feed.username" :data-image_size="configs.feed_settings.post_settings.resolution" :data-index="index">
                                  <div class="npfeed-ig-post-media-wrapper">
                                      <a class="npfeed-ig-playmode" :href="getFeedHref(feed)" :tabindex="getFeedTabindex(feed)" :role="getFeedRole(feed)" @keydown="activateFeedOnKey"
                                         :target="getFeedTarget(feed)"
                                         @click="postDisplayMode($event, feed, configs.feed_settings)"
                                      >
                                          <div class="npfeed-shoppable-icon_wrap" v-if="has_pro && configs.feed_settings.shoppable_settings.enable_shoppable === 'true'">
                                             <span class="npfeed-shoppable-icon npfeed-shoppable-icon-add"
                                                   v-if="!(configs.feed_settings.shoppable_settings.shoppable_feeds[feed.username] && configs.feed_settings.shoppable_settings.shoppable_feeds[feed.username][feed.id])"
                                                   @click.prevent.stop="$emit('add_selected_shoppable_feed', feed)"
                                                   >
                                              <el-icon><Link /></el-icon>
                                              Add
                                            </span>
                                            <div v-else>
                                              <span class="npfeed-shoppable-icon" @click.prevent.stop="$emit('edit_shoppable_feed', feed)">
                                               <el-icon><EditPen /></el-icon>
                                              Edit
                                              </span>
                                                <span class="npfeed-shoppable-icon" @click.prevent.stop="$emit('remove_shoppable_feed', feed)">
                                                 <el-icon><Delete /></el-icon>
                                                 Delete
                                              </span>
                                            </div>
                                          </div>
                                          <Media :feed="feed" :settings="configs.feed_settings" :image_settings="image_settings" :id="'npfeed-video-play-'+index"/>
                                      </a>
                                  </div>
                                  <Author v-if="configs.feed_settings.template === 'template5'" :feed="feed" :avatar="configs.dynamic && configs.dynamic.header ? configs.dynamic.header.user_avatar : ''" :show-date="configs.feed_settings.post_settings.display_date !== 'false'"/>
                                  <a v-if="configs.feed_settings.post_settings.display_mode !== 'popup' && feed.shoppable_options && feed.shoppable_options.show_shoppable"
                                     class="npfeed-shoppable-button" type="button" :href="feed.shoppable_options.url_settings.url"
                                     :target="feed.shoppable_options && feed.shoppable_options.url_settings.open_in_new_tab ? '_blank' : '_self'"
                                  >
                                    {{feed.shoppable_options.url_settings.text}}
                                    <i class="icon-angle-right"></i>
                                  </a>
                                  <div class="npfeed-ig-post-info" v-if="Object.keys(feed).length > 6">
                                      <Statistics v-if="has_pro" :feed="feed" :settings="configs.feed_settings"/>
                                      <span class="npfeed-ig-post-date" v-if="feed.timestamp && configs.feed_settings.post_settings.display_date !== 'false' && ['template5', 'template6'].indexOf(configs.feed_settings.template) === -1">{{ timeAgo(feed.timestamp) }}</span>
                                      <Caption :feed="feed" :settings="configs.feed_settings"/>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <swiper-navs v-if="configs.feed_settings.layout_type === 'carousel' && has_pro" :options="configs.feed_settings.carousel_settings"></swiper-navs>
                </div>
                <div v-else>
                  <img class="npfeed-placeholder-image" :src="assets_url+ '/images/template/instagram-placeholder-template.png'" alt="Placeholder Image">
                </div>
                <Footer
                    :connected_ids="configs.connected_ids"
                    :settings="configs.feed_settings"
                    :account_to_show="configs.feed_settings.header_settings.account_to_show"
                />
            </div>
            
            <Popup
              v-if="configs.feed_settings.post_settings.display_mode === 'popup'"
              :feed="selectedFeed"
              :user_avatar="popupUserAvatar"
              :feedLength="popupFeedLength"
              :currentIndex="currentIndex"
              :settings="configs.feed_settings"
              :connected_ids="configs.connected_ids"
              :account_to_show="configs.feed_settings.header_settings.account_to_show"
              @on-previous="getPreviousFeed"
              @on-next="getNextFeed"
            />
        </div>
    </div>
</template>
<script>
import Media from './elements/_media.vue';
import Statistics from './elements/_statistics.vue';
import Caption from './elements/_caption.vue';
import Author from './elements/_author.vue';
import Header from './elements/_header';
import Popup from './elements/_popup.vue';
import Footer from './elements/_footer';
import SwiperNavs from "../../../core-ui/editor/SwiperNavs";
import {popupMixin} from "../../../../mixins/popupMixin";
import {igMixin} from "../../../../mixins/igMixin";
import ErrorCard from "../ErrorCard";

export default {
  inheritAttrs: false,
  name: 'instagram_template2',
  props: ['configs','image_settings'],
  mixins: [popupMixin, igMixin],
  components: {
    Author,
    Media,
    Statistics,
    Caption,
    Header,
    Popup,
    Footer,
    SwiperNavs,
    ErrorCard
  },
  watch: {
    'configs.feed_settings.post_settings.display_mode': {
      handler: function (newVal, oldVal) {
        if (newVal !== oldVal){
          this.resetSelectedFeed();
        }
      },
      deep: true
    }
  }
}
</script>