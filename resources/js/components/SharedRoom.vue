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
      <div class="loading mb-2 text-center" v-if="chat.message.isLoading">
        <svg
          version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
          <path fill="#FF6700" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z" transform="rotate(18.3216 25 25)">
            <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"></animateTransform>
          </path>
        </svg>
      </div>
      <MessageItem
        v-for="message in chat.message.list"
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
import $ from 'jquery'

export default {
  props: {
    chat: {
      type: Object,
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
  mounted () {
    $('#shared_room').on('scroll', async () => {
      const scroll = $('#shared_room').scrollTop()
      if (scroll < 1 && this.chat.message.currentPage < this.chat.message.lastPage) {
        this.$emit('getMessages', this.currentRoom.id, this.chat.message.currentPage + 1, true)
      }
    })
  },
  beforeDestroy () {
    $('#shared_room').off('scroll')
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
