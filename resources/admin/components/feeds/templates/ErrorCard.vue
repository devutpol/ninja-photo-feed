<template>
    <div class="npfeed-feed-error-box" v-html="error_message">
    </div>
  </template>
  
  <script type="text/babel">
  export default {
    name: "ErrorCard",
    props: ['error_message']
  }
  </script>