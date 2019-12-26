<template>
  <div class="row justify-content-center h-100">
    <div class="col-md-8 chat">
      <SharedRoom
        :messages="publicMessages"
        :currentRoom="currentRoom"
        @saveMessage="saveMessage"
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
      :privateChat="privateChat"
      :messages="privateMessages"
      @closePrivateChat="closePrivateChat"
      @saveMessage="saveMessage"
      @focusPrivateInput="focusPrivateInput"
    />
  </div>
</template>

<script>
import ListUser from '../components/ListUser'
import SharedRoom from '../components/SharedRoom'
import PrivateChat from '../components/PrivateChat'
import $ from 'jquery'

export default {
  components: {
    ListUser,
    SharedRoom,
    PrivateChat
  },
  data () {
    return {
      currentRoom: {},
      publicMessages: [],
      privateMessages: [],
      usersOnline: [],
      privateChat: {
        selectedReceiver: null,
        isPrivateChatExpand: false,
        isSelectedReceiverTyping: false,
        hasNewMessage: false,
        isSeen: null, // null: no new message, false: a message is waiting to be seen, true: user seen message (should display "Seen at..")
        seenAt: '',
        roomId: ''
      }
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
        })
        .leaving((user) => {
          const index = this.usersOnline.findIndex(item => item.id === user.id)
          if (index > -1) {
            this.usersOnline.splice(index, 1)
          }
        })
        .listen('MessagePosted', e => {
          this.publicMessages.push(e.message)
          this.scrollToBottom(document.getElementById('shared_room'), true)
        })
        .listen('BotNotification', e => {
          this.publicMessages.push({
            content: e.message,
            room: e.room,
            id: Date.now(),
            created_at: Date.now(),
            type: 'bot'
          })

          this.scrollToBottom(document.getElementById('shared_room'), true)
        })

      this.$Echo.private(`room.${this.$root.user.id}`) // listen to user's own room (in order to receive all private messages from other users)
        .listen('MessagePosted', e => {
          if (this.privateChat.selectedReceiver && e.message.sender.id === this.privateChat.selectedReceiver.id) {
            this.privateMessages.push(e.message)
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
    async getMessages (room) {
      try {
        const response = await this.$axios.get(`/messages?room=${room}`)
        if (room.toString().includes('__')) {
          this.privateMessages = response.data
          this.scrollToBottom(document.getElementById('private_room'), false)
        } else {
          this.publicMessages = response.data
          this.scrollToBottom(document.getElementById('shared_room'), false)
        }
      } catch (error) {
        console.log(error)
      }
    },
    async saveMessage (message, receiver = null) {
      try {
        if ((!receiver && !message.trim().length)) {
          return
        }

        const response = await this.$axios.post('/messages', {
          receiver,
          content: message,
          room: receiver ? null : this.currentRoom.id
        })
        if (receiver) {
          this.privateMessages.push(response.data.message)
          this.privateChat.isSeen = false // waiting for other to seen this message
          // send message indicate that user stop typing (incase Throttle function isn't called)
          this.$Echo.private(`room.${this.privateChat.roomId}`)
            .whisper('typing', {
              user: this.$root.user,
              isTyping: false
            })
        } else {
          this.publicMessages.push(response.data.message)
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
      await this.getMessages(roomId) // need to await until messages are loaded first then we are able to focus the input below
    },
    closePrivateChat () {
      this.privateChat.selectedReceiver = null
      this.privateChat.isPrivateChatExpand = false
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
