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
      <MessageItem
        v-for="message in messages"
        :key="message.id"
        :message="message"
        @showEmoji="showEmoji"
      />
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
    <Emoji
      :emojiCoordinates="emojiCoordinates"
      :isShow="isShowEmoji"
      :selectedMessage="selectedMessage"
      @hideEmoji="hideEmoji"
      @selectEmoji="selectEmoji"
    />
  </div>
</template>

<script>
import MessageItem from './MessageItem'
import Emoji from './Emoji'

export default {
  props: {
    messages: {
      type: Array,
      required: true
    },
    currentRoom: {
      type: Object,
      required: true
    },
    selectedMessage: {
      type: Object
    },
    isShowEmoji: {
      type: Boolean
    },
    emojiCoordinates: {
      type: Object
    }
  },
  components: {
    MessageItem,
    Emoji
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
    },
    showEmoji (message, event) {
      this.$emit('showEmoji', message, event)
    },
    hideEmoji () {
      this.$emit('hideEmoji')
    },
    selectEmoji (emoji) {
      this.$emit('selectEmoji', emoji)
    }
  }
}
</script>

<style>

</style>
