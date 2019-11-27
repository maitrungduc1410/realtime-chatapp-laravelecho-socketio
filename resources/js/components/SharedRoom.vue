<template>
  <div class="card">
    <div class="card-header msg_head">
      <div class="bd-highlight">
        <div class="user_info">
          <span>{{ currentRoom.name }}</span>
        </div>
        <div class="text-white ml-3">
          {{ currentRoom.description }}
        </div>
      </div>
    </div>
    <div class="card-body msg_card_body" id="shared_room">
      <MessageItem v-for="message in messages" :key="message.id" :message="message" />
    </div>
    <div class="card-footer">
      <div class="input-group">
        <textarea
          v-model="inputMessage"
          name=""
          class="form-control type_msg"
          placeholder="Type your message..."
          @keyup.enter="saveMessage"
        />
        <div class="input-group-append">
          <span class="input-group-text send_btn"><i class="fas fa-location-arrow"></i></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MessageItem from './MessageItem'

export default {
  props: {
    messages: {
      type: Array,
      required: true
    },
    currentRoom: {
      type: Object,
      required: true
    }
  },
  components: {
    MessageItem
  },
  data () {
    return {
      inputMessage: ''
    }
  },
  methods: {
    saveMessage () {
      this.$emit('saveMessage', this.inputMessage)
      this.inputMessage = ''
    }
  }
}
</script>

<style>

</style>
