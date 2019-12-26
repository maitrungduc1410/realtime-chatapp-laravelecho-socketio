<template>
  <div class="d-flex justify-content-end mb-4" v-if="message.type === 'bot'">
    <div class="msg_container_send bot-notification" data-toggle="tooltip" data-placement="top" :title="message.created_at | toLocalTime">
      Bot: {{ message.content }}
    </div>
  </div>
  <div class="d-flex justify-content-end mb-4" v-else-if="message.sender.id === $root.user.id">
    <div class="msg_container_send" data-toggle="tooltip" data-placement="top" :title="message.created_at | toLocalTime">
      {{ message.content }}
    </div>
    <div class="img_cont_msg" data-toggle="tooltip" data-placement="top" :title="`${message.sender.name} (${message.sender.email})`">
      <img src="/images/current_user.jpg" class="rounded-circle user_img_msg">
    </div>
  </div>
  <div class="d-flex justify-content-start mb-4" v-else>
    <div class="img_cont_msg bg-white rounded-circle d-flex justify-content-center align-items-center" data-toggle="tooltip" data-placement="top" :title="`${message.sender.name} (${message.sender.email})`">
      <span class="rounded-circle d-flex justify-content-center align-items-center" :style="`background-color: ${message.sender.color}`">{{ message.sender.name[0].toUpperCase() }}</span>
    </div>
    <div class="msg_container" data-toggle="tooltip" data-placement="top" :title="message.created_at | toLocalTime">
      {{ message.content }}
    </div>
  </div>
</template>

<script>
import $ from 'jquery'

export default {
  props: {
    message: {
      required: true
    }
  },
  mounted () {
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
  }
}
</script>

<style>
.bot-notification {
  max-width: 100% !important;
  width: 100%;
  border-radius: 4px;
  background-color: #043244;
}
</style>
