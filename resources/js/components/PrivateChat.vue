<template>
  <div
    class="private-message-container"
    :class="privateChat.isPrivateChatExpand ? 'expand' : ''"
    @click="$emit('focusPrivateInput')"
  >
    <div
      class="chat-header d-flex p-2 border-bottom"
      :class="privateChat.hasNewMessage ? 'blink-anim' : ''"
      @click="privateChat.isPrivateChatExpand = !privateChat.isPrivateChatExpand"
    >
      <div class="img_cont">
        <img src="https://static.turbosquid.com/Preview/001292/481/WV/_D.jpg" class="rounded-circle user_img" style="width: 40px; height: 40px;">
        <span class="online_icon online" style="bottom: -3px;"></span>
      </div>
      <div class="user_info">
        <span style="color: black;">{{ privateChat.selectedReceiver.name }}</span>
        <!-- <p style="color: black;" class="mb-0">{{ privateChat.selectedReceiver.name }} left 50 mins ago</p> -->
      </div>
      <button class="btn-close" @click="$emit('closePrivateChat')">
        <i class="fal fa-times"></i>
      </button>
    </div>
    <div class="private-chat-body p-2" v-if="privateChat.isPrivateChatExpand" id="private_room">
      <MessageItem v-for="message in messages" :key="message.id" :message="message" />
      <div class="d-flex justify-content-end" v-if="privateChat.isSeen">
        <i class="font-12px">Seen {{ privateChat.seenAt | toLocalTime }}</i>
      </div>
      <div class="d-flex justify-content-start mb-4" v-if="privateChat.isSelectedReceiverTyping">
        <div class="img_cont_msg">
          <img src="https://static.turbosquid.com/Preview/001292/481/WV/_D.jpg" class="rounded-circle user_img_msg">
        </div>
        <div class="msg_container">
          <div id="wave">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
          </div>
        </div>
      </div>
    </div>
    <div class="text-input" v-if="privateChat.isPrivateChatExpand">
      <input
        v-model="inputMessage"
        id="private_input"
        type="text"
        class="w-100"
        placeholder="Type a message..."
        @keyup.enter="saveMessage"
        @input="onInputPrivateChange"
      >
    </div>
  </div>
</template>

<script>
import MessageItem from './MessageItem'
import { throttle } from 'lodash'
export default {
  props: {
    privateChat: {
      type: Object,
      required: true
    },
    messages: {
      type: Array,
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
  mounted () {
    this.$emit('focusPrivateInput')
  },
  methods: {
    saveMessage () {
      this.$emit('saveMessage', this.inputMessage, this.privateChat.selectedReceiver.id)
      this.inputMessage = ''
    },
    onInputPrivateChange: throttle(function () {
      this.$Echo.private(`room.${this.privateChat.roomId}`)
        .whisper('typing', {
          user: this.$root.user,
          isTyping: this.inputMessage.length > 0
        })
    }, 2000) // keep tell other that we're typing because other user may close the private chat window then re open during we're typing
  },
  beforeDestroy () {
    this.$Echo.leave(`room.${this.privateChat.roomId}`)
  }
}
</script>

<style>
.private-message-container {
  z-index: 1;
}
</style>
