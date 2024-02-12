<script setup>
import Reaction from "./Reaction.vue";
import DOMPurify from "dompurify"; // we also need to clean data when display because old data has been cleaned
import { Tooltip } from "bootstrap";
import { computed, inject, onMounted, ref } from "vue";
import confetti from "canvas-confetti";

const props = defineProps({
  isPrivate: {
    type: Boolean,
    default: false,
  },
  message: {
    type: Object,
    required: true,
  },
  msgColor: {
    type: String,
  },
});

const emit = defineEmits(["showEmoji", "selectReceiver"]);
const $el = ref(null);
const user = inject("$user");
const confettiWords = inject("$confettiWords");

onMounted(() => {
  // only find in this component HTML using $el
  const tooltipTriggerList = $el.value.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new Tooltip(tooltipTriggerEl)
  );
});

function showEmoji(event) {
  emit("showEmoji", props.message, event);
}

const highlight = computed(() => {
  if (props.isPrivate) {
    // ignore if this is private message
    return props.message.content;
  }

  const content = DOMPurify.sanitize(props.message.content);
  return content.replace(new RegExp(confettiWords, "gi"), (match) => {
    return '<span class="highlightText">' + match + "</span>";
  });
});

const createdAt = computed(() => {
  const d = new Date(props.message.created_at);
  return d.toLocaleString();
});

const isMyUser = computed(() => {
  return props.message.sender && props.message.sender.id === user.id;
});

function celebrate(event) {
  const highlightItem = $(event.currentTarget).find(".highlightText");
  if (highlightItem.length) {
    confetti({
      particleCount: 300,
      spread: 150,
      origin: {
        y: 1,
      },
    });
  }
}
</script>

<template>
  <div ref="$el">
    <div
      class="msg-item d-flex justify-content-end mb-4"
      :class="{
        private: props.isPrivate,
        'flex-row-reverse': !isMyUser,
      }"
    >
      <div
        v-if="message.type !== 'bot'"
        class="msg-actions d-flex"
        :class="{ 'me-2': isMyUser, 'ms-2': !isMyUser }"
      >
        <div class="d-flex align-items-center">
          <i
            class="fal fa-grin-alt"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            data-bs-title="React"
            @click="showEmoji"
          ></i>
        </div>
      </div>
      <div
        :class="{
          msg_container_send: isMyUser,
          msg_container: !isMyUser,
          'bot-notification': message.type === 'bot',
        }"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        :data-bs-title="createdAt"
        :style="
          message.receiver
            ? `background-color: ${isMyUser ? msgColor : ''}`
            : ''
        "
      >
        <div v-html="highlight" @click="celebrate"></div>
        <Reaction
          v-if="message.reactions.length"
          :reactions="message.reactions"
        />
      </div>
      <div
        v-if="message.type !== 'bot'"
        class="img_cont_msg"
        :class="{
          'bg-white rounded-circle d-flex justify-content-center align-items-center':
            !isMyUser,
        }"
        :data-bs-toggle="props.isPrivate ? undefined : 'tooltip'"
        :data-bs-placement="props.isPrivate ? undefined : 'top'"
        :data-bs-title="
          props.isPrivate
            ? undefined
            : `Click to chat with ${message.sender.name} (${message.sender.email})`
        "
        @click="props.isPrivate ? $emit('selectReceiver', message.sender) : 1"
      >
        <img
          src="/images/current_user.jpg"
          class="rounded-circle user_img_msg"
          v-if="isMyUser"
        />
        <span
          v-else
          class="rounded-circle d-flex justify-content-center align-items-center"
          :style="`background-color: ${message.sender.color}`"
          >{{ message.sender.name[0].toUpperCase() }}</span
        >
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.bot-notification {
  max-width: 100% !important;
  width: 100%;
  border-radius: 4px;
  background-color: #043244;
  font-size: 16px;
  font-style: italic;
}

.msg-item {
  &.private {
    .msg-actions {
      i {
        &:hover {
          color: #04a6e9 !important;
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
    transition: opacity 0.2s;

    i {
      color: lightgray;
      cursor: pointer;
      transition: color 0.2s;

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

.img_cont_msg {
  cursor: pointer;
}
</style>
