<template>
    <div class="wrap">
        <div class="npfeed_social_review_admin">
            <router-view />
        </div>
    </div>
</template>

<script>
export default {
    name: 'InstagramApp',
    data() {
        return {
            loading: true
        }
    },
    mounted() {
        this.loading = false;
    }
}
</script>
