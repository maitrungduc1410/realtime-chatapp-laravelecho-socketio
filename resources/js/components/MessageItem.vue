<template>
  <div class="d-flex justify-content-end mb-4" v-if="message.type === 'bot'">
    <div class="msg_container_send bot-notification" data-toggle="tooltip" data-placement="top" :title="message.created_at | toLocalTime">
      Bot: {{ message.content }}
    </div>
  </div>
  <div
    class="msg-item d-flex justify-content-end mb-4"
    :class="{'private': message.receiver }"
    v-else-if="message.sender.id === $root.user.id">
    <div class="msg-actions d-flex mr-2">
      <div class="d-flex align-items-center">
        <i class="fal fa-grin-alt" data-toggle="tooltip" data-placement="top" title="React" @click="showEmoji"></i>
      </div>
    </div>
    <div
      class="msg_container_send"
      data-toggle="tooltip"
      data-placement="top"
      :title="message.created_at | toLocalTime"
      :style="message.receiver ? `background-color: ${msgColor}` : ''"
    >
      <div v-html="highlight"></div>
      <Reaction
        v-if="message.reactions.length"
        :reactions="message.reactions"
      />
    </div>
    <div class="img_cont_msg" data-toggle="tooltip" data-placement="top" :title="`${message.sender.name} (${message.sender.email})`">
      <img src="/images/current_user.jpg" class="rounded-circle user_img_msg">
    </div>
  </div>
  <div
    class="msg-item d-flex justify-content-start mb-4"
    :class="{'private': message.receiver }"
    v-else
  >
    <div class="img_cont_msg bg-white rounded-circle d-flex justify-content-center align-items-center" data-toggle="tooltip" data-placement="top" :title="`${message.sender.name} (${message.sender.email})`">
      <span class="rounded-circle d-flex justify-content-center align-items-center" :style="`background-color: ${message.sender.color}`">{{ message.sender.name[0].toUpperCase() }}</span>
    </div>
    <div class="msg_container" :class="{'bg-gray': message.receiver}" data-toggle="tooltip" data-placement="top" :title="message.created_at | toLocalTime">
      <div v-html="highlight"></div>
      <Reaction
        v-if="message.reactions.length"
        :reactions="message.reactions"
      />
    </div>
    <div class="msg-actions d-flex ml-2">
      <div class="d-flex align-items-center">
        <i class="fal fa-grin-alt" data-toggle="tooltip" data-placement="top" title="React" @click="showEmoji"></i>
      </div>
    </div>
  </div>
</template>

<script>
import $ from 'jquery'
import Reaction from './Reaction'
import sanitizeHtml from 'sanitize-html' // we also need to clean data when display because old data has been cleaned

export default {
  components: {
    Reaction
  },
  props: {
    message: {
      required: true
    },
    msgColor: {
      type: String
    }
  },
  mounted () {
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
  },
  methods: {
    showEmoji (event) {
      this.$emit('showEmoji', this.message, event)
    }
  },
  computed: {
    highlight () {
      if (this.message.receiver) { // ignore if this is private message
        return this.message.content
      }
      const content = sanitizeHtml(this.message.content)
      return content.replace(new RegExp('chuc mung|congratulations|congrats', 'gi'), match => {
        return '<span class="highlightText">' + match + '</span>'
      })
    }
  }
}
</script>

<style lang="scss">
.bot-notification {
  max-width: 100% !important;
  width: 100%;
  border-radius: 4px;
  background-color: #043244;
}
.msg-item {
  &.private {
    .msg-actions {
      i {
        &:hover {
          color: #054760 !important;
        }
      }
    }
  }
  &:hover {
    .msg-actions {
      opacity: 1;
    }
  }
  .msg-actions {
    opacity: 0;
    transition: opacity .2s;
    i {
      color: lightgray;
      cursor: pointer;
      transition: color .2s;
      &:hover {
        color: white;
      }
    }
  }
}

.highlightText {
  color: #f1765e;
  font-weight: 600;
  cursor: pointer;
}

.bg-gray {
  background-color: #f1f0f0;
  color: #444950;
}
</style>
