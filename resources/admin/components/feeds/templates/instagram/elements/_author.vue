<template>
    <div class="npfeed-ig-post-author" v-if="feed.username">
        <img v-if="avatar" class="npfeed-ig-post-author-avatar" :src="avatar" :alt="feed.username" loading="lazy"/>
        <div class="npfeed-ig-post-author-details">
            <span class="npfeed-ig-post-author-name">{{ feed.username }}</span>
            <span class="npfeed-ig-post-author-time" v-if="feed.timestamp && showDate !== false">{{ timeAgo(feed.timestamp) }}</span>
        </div>
    </div>
</template>

<script type="text/babel">
import {igMixin} from "../../../../../mixins/igMixin";
export default {
    name: 'instagram_author',
    props: ['feed', 'avatar', 'showDate'],
    mixins: [igMixin]
}
</script>
