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
        <span class="online_icon" :class="privateChat.isOnline ? 'online' : 'offline'" style="bottom: -3px;"></span>
      </div>
      <div class="user_info">
        <span style="color: black;">{{ privateChat.selectedReceiver.name }}</span>
        <!-- <p style="color: black;" class="mb-0">{{ privateChat.selectedReceiver.name }} left 50 mins ago</p> -->
      </div>
      <div class="color-picker">
        <i
          data-toggle="tooltip"
          data-placement="top"
          title="Message Color"
          class="fas fa-circle"
          @click.stop="toggleColorPicker"
          style="cursor: pointer;"
          :style="{'color': msgColor}"
        ></i>
      </div>
      <button class="btn-close" @click="$emit('closePrivateChat')">
        <i class="fal fa-times"></i>
      </button>
    </div>
    <div class="private-chat-body p-2" v-if="privateChat.isPrivateChatExpand" id="private_room">
      <MessageItem
        v-for="message in messages"
        :key="message.id"
        :message="message"
        :msgColor="msgColor"
        @showEmoji="showEmoji"
      />
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
    <Emoji
      :emojiCoordinates="emojiCoordinates"
      :isShow="isShowEmoji"
      :selectedMessage="selectedMessage"
      @hideEmoji="hideEmoji"
      @selectEmoji="selectEmoji"
    />

    <transition name="fade">
    <ColorPickerModal
      v-if="isShowColorPicker"
      :isShow="isShowColorPicker"
      @hide="toggleColorPicker"
      @selectColor="selectColor"
    />
    </transition>
  </div>
</template>

<script>
import MessageItem from './MessageItem'
import { throttle } from 'lodash'
import Emoji from './Emoji'
import ColorPickerModal from './ColorPickerModal'
import $ from 'jquery'

export default {
  props: {
    privateChat: {
      type: Object,
      required: true
    },
    messages: {
      type: Array,
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
    Emoji,
    ColorPickerModal
  },
  data () {
    return {
      inputMessage: '',
      isShowColorPicker: false,
      msgColor: '#42e274'
    }
  },
  created () {
    const msgColor = localStorage.getItem('msgColor')

    if (msgColor) {
      this.msgColor = msgColor
    }
  },
  mounted () {
    this.$emit('focusPrivateInput')

    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
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
    }, 2000), // keep tell other that we're typing because other user may close the private chat window then re open during we're typing
    showEmoji (message, event) {
      this.$emit('showEmoji', message, event)
    },
    hideEmoji () {
      this.$emit('hideEmoji')
    },
    selectEmoji (emoji) {
      this.$emit('selectEmoji', emoji)
    },
    toggleColorPicker () {
      this.isShowColorPicker = !this.isShowColorPicker
    },
    selectColor (value) {
      localStorage.setItem('msgColor', value)
      this.msgColor = value
      this.toggleColorPicker()
    }
  },
  beforeDestroy () {
    this.$Echo.leave(`room.${this.privateChat.roomId}`)
  }
}
</script>

<style lang="scss">
.private-message-container {
  z-index: 1;
}
.color-picker {
  position: absolute;
  right: 45px;
  top: 17px;
  i {
    font-size: 22px;
  }
}

.fade-enter-active, .fade-leave-active {
  transition: opacity .3s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
</style>
