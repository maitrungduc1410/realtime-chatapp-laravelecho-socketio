<template>
  <div class="row justify-content-center h-100">
    <div class="col-md-8 chat">
      <SharedRoom
        :chat="publicChat"
        :currentRoom="currentRoom"
        :selectedMessage="selectedMessage"
        :isShowEmoji="isShowEmoji"
        :emojiCoordinates="emojiCoordinates"
        @saveMessage="saveMessage"
        @showEmoji="showEmoji"
        @hideEmoji="hideEmoji"
        @selectEmoji="selectEmoji"
        @getMessages="getMessages"
      />
    </div>
    <div class="col-md-4 chat">
      <ListUser
        :usersOnline="usersOnline"
        @selectReceiver="selectReceiver"
      />
    </div>
    <PrivateChat
      v-if="privateChat.selectedReceiver"
      :chat="privateChat"
      :selectedMessage="selectedMessage"
      :isShowEmoji="isShowEmoji"
      :emojiCoordinates="emojiCoordinates"
      @closePrivateChat="closePrivateChat"
      @saveMessage="saveMessage"
      @focusPrivateInput="focusPrivateInput"
      @showEmoji="showEmoji"
      @hideEmoji="hideEmoji"
      @selectEmoji="selectEmoji"
      @getMessages="getMessages"
    />
  </div>
</template>

<script>
import ListUser from '../components/ListUser'
import SharedRoom from '../components/SharedRoom'
import PrivateChat from '../components/PrivateChat'
import $ from 'jquery'
import sanitizeHtml from 'sanitize-html'

export default {
  components: {
    ListUser,
    SharedRoom,
    PrivateChat
  },
  data () {
    return {
      currentRoom: {},
      publicChat: {
        message: {
          isLoading: false,
          list: [],
          currentPage: 0,
          perPage: 0,
          total: 0,
          lastPage: 0,
          newMessageArrived: 0 // number of new messages we just got (use for saving scroll position)
        }
      },
      usersOnline: [],
      privateChat: {
        selectedReceiver: null,
        isPrivateChatExpand: false,
        isSelectedReceiverTyping: false,
        hasNewMessage: false,
        isSeen: null, // null: no new message, false: a message is waiting to be seen, true: user seen message (should display "Seen at..")
        seenAt: '',
        roomId: '',
        isOnline: true,
        message: {
          isLoading: false,
          list: [],
          currentPage: 0,
          perPage: 0,
          total: 0,
          lastPage: 0,
          newMessageArrived: 0 // number of new messages we just got (use for saving scroll position)
        }
      },
      emojiCoordinates: {
        x: 0,
        y: 0
      },
      isShowEmoji: false,
      selectedMessage: null
    }
  },
  created () {
    const index = this.$root.rooms.findIndex(item => item.id === parseInt(this.$route.params.roomId))
    if (index > -1) {
      this.currentRoom = this.$root.rooms[index]
      this.getMessages(this.currentRoom.id)
      this.$Echo.join(`room.${this.currentRoom.id}`) // listen to the shared room
        .here((users) => {
          this.usersOnline = users
        })
        .joining((user) => {
          this.usersOnline.push(user)
          if (this.privateChat.selectedReceiver && this.privateChat.selectedReceiver.id === user.id) {
            this.privateChat.isOnline = true
          }
        })
        .leaving((user) => {
          const index = this.usersOnline.findIndex(item => item.id === user.id)
          if (index > -1) {
            this.usersOnline.splice(index, 1)
          }
          if (this.privateChat.selectedReceiver && this.privateChat.selectedReceiver.id === user.id) {
            this.privateChat.isOnline = false
          }
        })
        .listen('MessagePosted', e => {
          this.publicChat.message.list.push(e.message)
          this.scrollToBottom(document.getElementById('shared_room'), true)
        })
        .listen('BotNotification', e => {
          this.publicChat.message.list.push({
            content: e.message,
            room: e.room,
            id: Date.now(),
            created_at: Date.now(),
            type: 'bot'
          })
          this.scrollToBottom(document.getElementById('shared_room'), true)
        })
        .listen('MessageReacted', e => {
          this.onOtherUserReaction(e.reaction, 'share')
        })
      this.$Echo.private(`room.${this.$root.user.id}`) // listen to user's own room (in order to receive all private messages from other users)
        .listen('MessagePosted', e => {
          if (this.privateChat.selectedReceiver && e.message.sender.id === this.privateChat.selectedReceiver.id) {
            this.privateChat.message.list.push(e.message)
            this.privateChat.isSeen = null // when receive new private message, considered user have seen -> reset isSeen to inital state
            this.privateChat.hasNewMessage = true // notify user there's new message
            this.scrollToBottom(document.getElementById('private_room'), true)
          } else { // if private chat window doens't open, then we set the badge in ListUser
            const index = this.usersOnline.findIndex(item => item.id === e.message.sender.id)
            if (index > -1) {
              this.usersOnline[index].new_messages++
            }
          }
        })
    }
  },
  methods: {
    async getMessages (room, page = 1, loadMore = false) {
      const isPrivate = room.toString().includes('__')
      const chat = isPrivate ? this.privateChat : this.publicChat
      try {
        chat.message.isLoading = true
        const response = await this.$axios.get(`/messages?room=${room}&page=${page}`)

        chat.message.list = [...response.data.data.reverse(), ...chat.message.list]
        chat.message.currentPage = response.data.current_page
        chat.message.perPage = response.data.per_page
        chat.message.lastPage = response.data.last_page
        chat.message.total = response.data.total
        chat.message.newMessageArrived = response.data.data.length

        if (loadMore) {
          this.$nextTick(() => {
            const el = $(isPrivate ? '#private_room' : '#shared_room')
            const lastFirstMessage = el.children().eq(chat.message.newMessageArrived - 1)
            el.scrollTop(lastFirstMessage.position().top - 10)
          })
        } else {
          this.scrollToBottom(document.getElementById(isPrivate ? 'private_room' : 'shared_room'), false)
        }
      } catch (error) {
        console.log(error)
      } finally {
        chat.message.isLoading = false
      }
    },
    async saveMessage (message, receiver = null) {
      try {
        if ((!receiver && !message.trim().length)) {
          return
        }

        // clean data before save to DB
        message = sanitizeHtml(message).trim()

        if (!message.length) {
          return
        }

        const response = await this.$axios.post('/messages', {
          receiver,
          content: message,
          room: receiver ? null : this.currentRoom.id
        })
        if (receiver) {
          this.privateChat.message.list.push(response.data.message)
          this.privateChat.isSeen = false // waiting for other to seen this message
          // send message indicate that user stop typing (incase Throttle function isn't called)
          this.$Echo.private(`room.${this.privateChat.roomId}`)
            .whisper('typing', {
              user: this.$root.user,
              isTyping: false
            })
        } else {
          this.publicChat.message.list.push(response.data.message)
        }
        this.scrollToBottom(document.getElementById(`${receiver ? 'private' : 'shared'}_room`), true)
      } catch (error) {
        console.log(error)
      }
    },
    async selectReceiver (receiver) {
      if (this.$root.user.id === receiver.id) {
        return
      }
      this.resetPrivateChat()
      const roomId = this.$root.user.id > receiver.id ? `${receiver.id}__${this.$root.user.id}` : `${this.$root.user.id}__${receiver.id}`
      this.privateChat.selectedReceiver = receiver
      this.privateChat.isPrivateChatExpand = true
      this.privateChat.roomId = roomId
      this.$Echo.private(`room.${roomId}`) // this room to receive whisper events
        .listenForWhisper('typing', (e) => {
          this.privateChat.isSelectedReceiverTyping = e.isTyping
          this.scrollToBottom(document.getElementById('private_room'), true)
        })
        .listenForWhisper('seen', (e) => {
          if (this.privateChat.isSeen === false) { // check if user waiting for his message to be seen
            this.privateChat.isSeen = true
            this.privateChat.seenAt = e.time
            this.scrollToBottom(document.getElementById('private_room'), true)
          }
        })
        .listen('MessageReacted', e => {
          this.onOtherUserReaction(e.reaction, 'private')
        })
      await this.getMessages(roomId) // need to await until messages are loaded first then we are able to focus the input below
    },
    closePrivateChat () {
      this.resetPrivateChat()
    },
    scrollToBottom (element, animate = true) {
      if (!element) {
        return
      }
      this.$nextTick(() => { // run after Vue finishes updating the DOM
        if (animate) {
          $(element).animate(
            { scrollTop: element.scrollHeight },
            { duration: 'medium', easing: 'swing' }
          )
        } else {
          $(element).scrollTop(element.scrollHeight)
        }
      })
    },
    focusPrivateInput () {
      const input = document.getElementById('private_input')
      if (input) { // incase we toggle private chat then this input will be removed
        input.focus()
        this.$Echo.private(`room.${this.privateChat.roomId}`)
          .whisper('seen', {
            user: this.$root.user,
            seen: true,
            time: new Date()
          })
        this.privateChat.hasNewMessage = false // set this to false as now user is focusing the chat
        const index = this.usersOnline.findIndex(item => item.id === this.privateChat.selectedReceiver.id)
        if (index > -1) {
          this.usersOnline[index].new_messages = 0
        }
      }
    },
    resetPrivateChat () { // reset private chat when change to another user
      this.privateChat.message.list = []
      this.privateChat.selectedReceiver = null
      this.privateChat.isPrivateChatExpand = false
      this.privateChat.isSelectedReceiverTyping = false
      this.privateChat.hasNewMessage = false
      this.privateChat.isSeen = null // null: no new message, false: a message is waiting to be seen, true: user seen message (should display "Seen at..")
      this.privateChat.seenAt = ''
      this.privateChat.roomId = ''
      this.privateChat.isOnline = true
    },
    showEmoji (message, event) {
      this.emojiCoordinates.x = event.clientX
      this.emojiCoordinates.y = event.clientY
      this.isShowEmoji = true
      this.selectedMessage = message
    },
    hideEmoji () {
      this.isShowEmoji = false
      this.selectedMessage = null
    },
    async selectEmoji (emoji) {
      try {
        const response = await this.$axios.post('/reactions', {
          msg_id: this.selectedMessage.id,
          user_id: this.$root.user.id,
          emoji_id: emoji.id
        })
        const index = this.selectedMessage.reactions.findIndex(item => item.user_id === this.$root.user.id)
        if (index > -1) {
          const reaction = this.selectedMessage.reactions[index]
          if (emoji.id === reaction.emoji_id) { // deactive
            this.selectedMessage.reactions.splice(index, 1)
          } else {
            reaction.emoji_id = emoji.id
          }
        } else { // user first react
          const { reaction } = response.data
          this.selectedMessage.reactions.push({ ...reaction, user: this.$root.user })
        }
        this.hideEmoji()
      } catch (error) {
        console.log(error)
      }
    },
    onOtherUserReaction (reaction, room) {
      let listMessage = []

      listMessage = room === 'share' ? this.publicChat.message.list : this.privateChat.message.list

      const messageIndex = listMessage.findIndex(m => m.id === reaction.msg_id)
      if (messageIndex > -1) {
        const message = listMessage[messageIndex]
        const index = message.reactions.findIndex(item => item.user.id === reaction.user.id)
        if (index > -1) {
          const r = message.reactions[index]
          if (reaction.emoji_id === r.emoji_id) { // deactive
            message.reactions.splice(index, 1)
          } else {
            r.emoji_id = reaction.emoji_id
          }
        } else {
          message.reactions.push({ ...reaction, user: reaction.user })
        }
      }
    }
  },
  computed: {
    totalUnreadPrivateMessages () {
      let count = 0
      this.usersOnline.forEach(item => {
        count += item.new_messages
      })
      return count
    }
  },
  watch: {
    totalUnreadPrivateMessages () {
      if (this.totalUnreadPrivateMessages > 0) {
        document.title = `${this.totalUnreadPrivateMessages > 0 ? '(' + this.totalUnreadPrivateMessages + ')' : ''} - ${this.$root.appName}`
      } else {
        document.title = this.$root.appName
      }
    }
  },
  beforeDestroy () {
    if (this.selectedReceiver) { // leave private chat if current has
      this.$Echo.leave(`room.${this.privateChat.roomId}`)
    }
    this.$Echo.leave(`room.${this.currentRoom.id}`) // leave the shared room
  }
}
</script>

<style>
</style>
