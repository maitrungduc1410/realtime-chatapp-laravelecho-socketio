<template>
  <div class="row justify-content-center h-100">
    <div class="col-md-8 chat">
      <SharedRoom
        :messages="messages"
        :currentRoom="currentRoom"
        @saveMessage="saveMessage"
      />
    </div>
    <div class="col-md-4 chat">
      <ListUser
        :usersOnline="usersOnline"
      />
    </div>
  </div>
</template>

<script>
import ListUser from '../components/ListUser'
import SharedRoom from '../components/SharedRoom'
import $ from 'jquery'

export default {
  components: {
    ListUser,
    SharedRoom
  },
  data () {
    return {
      currentRoom: {},
      messages: [],
      usersOnline: []
    }
  },
  created () {
    this.getMessages()

    const index = this.$root.rooms.findIndex(item => item.id === parseInt(this.$route.params.roomId))
    if (index > -1) {
      this.currentRoom = this.$root.rooms[index]

      // eslint-disable-next-line no-undef
      Echo.join(`room.${this.currentRoom.id}`)
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
        .listen('MessagePosted', (e) => {
          this.messages.push(e.message)
          this.scrollToBottom(document.getElementById('shared_room'), true)
        })
    }
  },
  beforeDestroy () {
    // eslint-disable-next-line no-undef
    Echo.leave(`room.${this.currentRoom.id}`)
  },
  methods: {
    async getMessages () {
      try {
        const response = await this.$axios.get(`/messages?room=${this.$route.params.roomId}`)
        this.messages = response.data

        this.scrollToBottom(document.getElementById('shared_room'), false)
      } catch (error) {
        console.log(error)
      }
    },
    async saveMessage (content) {
      try {
        const response = await this.$axios.post('/messages', {
          room: this.$route.params.roomId,
          content
        })

        this.messages.push(response.data.message)

        this.scrollToBottom(document.getElementById('shared_room'), true)
      } catch (error) {
        console.log(error)
      }
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
    }
  }
}
</script>

<style>

</style>
