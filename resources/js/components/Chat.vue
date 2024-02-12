<script setup>
import MessageItem from "./MessageItem.vue";
import Emoji from "./Emoji.vue";
import {
  ref,
  onMounted,
  onBeforeUnmount,
  onBeforeMount,
  inject,
  nextTick,
  computed,
  watch,
} from "vue";
import { Tooltip } from "bootstrap";
import ColorPickerModal from "./ColorPickerModal.vue";
import { throttle } from "lodash";
import DOMPurify from "dompurify";

const props = defineProps({
  isPrivate: {
    type: Boolean,
    default: false,
  },
  roomId: {
    type: [String, Number],
    required: true,
  },
  roomName: String, // for shared room
  roomDescription: String, // for shared room
  receiver: Object, // for private room
});

defineEmits(["closeChat", "selectReceiver"]);

const inputMessage = ref("");
const messages = ref([]);
const isLoadingMessages = ref(false);
const rootEl = ref(null);
const messageContainer = ref(null);
const privateHasNewMessage = ref(false); // for private chat only
const privateInputEl = ref(null);
const isTyping = ref(false); // for private chat only
const isSeen = ref(false); // for private chat only
const seenAt = ref(""); // for private chat only
const msgColor = ref("#42e274"); // for private chat only
const isShowColorPicker = ref(false); // for private chat only
const isChatExpanded = ref(true); // for private chat only
const isBeingFocused = ref(false); // for private chat only
const emojiCoordinates = ref({
  x: 0,
  y: 0,
});
const isShowEmoji = ref(false);
const selectedMessage = ref(null);
const $Echo = inject("$Echo");
const user = inject("$user");

// metadata for pagination, not related to rendering so we just make it primitive JS vars
let currentPage = 0;
let lastPage = 0;

const seenAtFormatted = computed(() => {
  var d = new Date(seenAt.value);
  return d.toLocaleString();
});

// for private chat only
onBeforeMount(() => {
  const msgColorStorage = localStorage.getItem("msgColor");

  if (msgColorStorage) {
    msgColor.value = msgColorStorage;
  }
});

onBeforeMount(() => {
  initChat();
});

onMounted(() => {
  if (props.isPrivate) {
    focusPrivateInput();
  }

  $(messageContainer.value).on("scroll", async () => {
    const scroll = $(messageContainer.value).scrollTop();
    if (scroll < 1 && currentPage < lastPage) {
      getMessages(props.roomId, currentPage + 1, true);
    }
  });

  const tooltipTriggerList = rootEl.value.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new Tooltip(tooltipTriggerEl)
  );
});

onBeforeUnmount(() => {
  $(messageContainer.value).off("scroll");
  $Echo.leave(`room.${props.roomId}`);
});

watch(
  () => props.roomId,
  (newVal, oldVal) => {
    $Echo.leave(`room.${oldVal}`); // leave previous channel

    initChat();
  }
);

function initChat() {
  inputMessage.value = ''
  messages.value = [];
  privateHasNewMessage.value = false;
  isTyping.value = false;
  isSeen.value = false;
  
  getMessages(props.roomId);

  if (props.isPrivate) {
    $Echo
      .private(`room.${props.roomId}`) // this room to receive whisper events
      .listenForWhisper("typing", (e) => {
        isTyping.value = e.isTyping;
        scrollToBottom(messageContainer.value, true);
      })
      .listenForWhisper("seen", (e) => {
        if (isSeen.value === false) {
          // check if user waiting for his message to be seen
          isSeen.value = true;
          seenAt.value = e.time;
          scrollToBottom(messageContainer.value, true);
        }
      })
      .listen("MessagePosted", (e) => {
        messages.value.push(e.message);
        privateHasNewMessage.value = true;
        isSeen.value = false;
        scrollToBottom(messageContainer.value, true);
      })
      .listen("MessageReacted", (e) => {
        onReaction(e.reaction);
      });
  } else {
    $Echo
      .join(`room.${props.roomId}`) // listen to the shared room
      .listen("MessagePosted", (e) => {
        messages.value.push(e.message);
        scrollToBottom(messageContainer.value, true);
      })
      .listen("BotNotification", (e) => {
        messages.value.push({
          content: e.message,
          room: e.room,
          id: Date.now(),
          created_at: Date.now(),
          type: "bot",
          reactions: [],
        });
        scrollToBottom(messageContainer.value, true);
      })
      .listen("MessageReacted", (e) => {
        onReaction(e.reaction);
      });
  }
}

function toggleColorPicker() {
  isShowColorPicker.value = !isShowColorPicker.value;
}

function selectColor(value) {
  localStorage.setItem("msgColor", value);
  msgColor.value = value;
  toggleColorPicker();
}

function showEmoji(message, event) {
  const rect = event.target.getBoundingClientRect();
  emojiCoordinates.value.x = rect.x;
  emojiCoordinates.value.y = rect.y;
  isShowEmoji.value = true;
  selectedMessage.value = message;
}
function hideEmoji() {
  isShowEmoji.value = false;
  selectedMessage.value = null;
}

async function selectEmoji(emoji) {
  try {
    const response = await axios.post("/reactions", {
      msg_id: selectedMessage.value.id,
      user_id: user.id,
      emoji_id: emoji.id,
    });
    const index = selectedMessage.value.reactions.findIndex(
      (item) => item.user_id === user.id
    );
    if (index > -1) {
      const reaction = selectedMessage.value.reactions[index];
      if (emoji.id === reaction.emoji_id) {
        // deactive
        selectedMessage.value.reactions.splice(index, 1);
      } else {
        reaction.emoji_id = emoji.id;
      }
    } else {
      // user first react
      const { reaction } = response.data;
      selectedMessage.value.reactions.push({
        ...reaction,
        user: user,
      });
    }
    hideEmoji();
  } catch (error) {
    console.log(error);
  }
}

function scrollToBottom(element, animate = true) {
  if (!element) {
    return;
  }
  nextTick(() => {
    // run after Vue finishes updating the DOM
    if (animate) {
      $(element).animate(
        { scrollTop: element.scrollHeight },
        { duration: "medium", easing: "swing" }
      );
    } else {
      $(element).scrollTop(element.scrollHeight);
    }
  });
}

async function saveMessage() {
  try {
    if (!inputMessage.value.trim().length) {
      return;
    }
    // clean data before save to DB
    const message = DOMPurify.sanitize(inputMessage.value, {}).trim();
    const response = await axios.post("/messages", {
      receiver: props.receiver ? props.receiver.id : undefined,
      content: message,
      room: props.receiver ? undefined : props.roomId,
    });
    if (props.isPrivate) {
      // send message indicate that user stop typing (incase Throttle function isn't called)
      $Echo.private(`room.${props.roomId}`).whisper("typing", {
        user,
        isTyping: false,
      });
    }

    messages.value.push(response.data.message);

    inputMessage.value = "";
    isSeen.value = false;
    scrollToBottom(messageContainer.value, true);
  } catch (error) {
    console.log(error);
  }
}

async function getMessages(room, page = 1, loadMore = false) {
  try {
    isLoadingMessages.value = true;
    const response = await axios.get(`/messages?room=${room}&page=${page}`);
    messages.value = [...response.data.data.reverse(), ...messages.value];
    currentPage = response.data.current_page;
    lastPage = response.data.last_page;

    if (loadMore) {
      nextTick(() => {
        const el = $(messageContainer.value);
        const lastFirstMessage = el
          .children()
          .eq(response.data.data.length - 1);
        el.scrollTop(lastFirstMessage.position().top - 10);
      });
    } else {
      scrollToBottom(messageContainer.value, false);
    }
  } catch (error) {
    console.log(error);
  } finally {
    isLoadingMessages.value = false;
  }
}

function onReaction(reaction) {
  const messageIndex = messages.value.findIndex(
    (m) => m.id === reaction.msg_id
  );
  if (messageIndex > -1) {
    const message = messages.value[messageIndex];
    const index = message.reactions.findIndex(
      (item) => item.user.id === reaction.user.id
    );
    if (index > -1) {
      const r = message.reactions[index];
      if (reaction.emoji_id === r.emoji_id) {
        // deactive
        message.reactions.splice(index, 1);
      } else {
        r.emoji_id = reaction.emoji_id;
      }
    } else {
      message.reactions.push({ ...reaction, user: reaction.user });
    }
  }
}

function focusPrivateInput() {
  // incase we click close private chat, privateInputEl will be null
  if (privateInputEl.value) {
    isBeingFocused.value = true;
    privateInputEl.value.focus();
    if (privateHasNewMessage.value) {
      $Echo.private(`room.${props.roomId}`).whisper("seen", {
        user: user,
        seen: true,
        time: new Date(),
      });
    }
    privateHasNewMessage.value = false; // set this to false as now user is focusing the chat
  }
}

const onInputPrivateChange = throttle(function () {
  $Echo.private(`room.${props.roomId}`).whisper("typing", {
    user,
    isTyping: inputMessage.value.length > 0,
  });
}, 2000); // keep tell other that we're typing because other user may close the private chat window then re open during we're typing
</script>

<template>
  <div
    class="card"
    :class="{
      'private-message-container bg-white': props.isPrivate,
      expand: isChatExpanded,
    }"
    ref="rootEl"
    @click="focusPrivateInput"
  >
    <!-- @click="EXPANDDDD" -->
    <div
      v-if="props.isPrivate"
      class="chat-header d-flex"
      :class="{
        'blink-anim': privateHasNewMessage && isBeingFocused,
        'p-2 border-bottom': isChatExpanded,
        'pt-2 ps-2': !isChatExpanded,
      }"
      @click="isChatExpanded = !isChatExpanded"
    >
      <div class="img_cont">
        <img
          :src="
            props.receiver.id === user.id
              ? '/images/current_user.jpg'
              : '/images/other_user.jpg'
          "
          class="rounded-circle user_img"
          style="width: 40px; height: 40px"
        />
        <span
          class="online_icon"
          style="bottom: -3px"
          :class="props.receiver.isOnline ? 'online' : 'offline'"
        ></span>
      </div>
      <div class="user_info">
        <span style="color: black">{{
          `${props.receiver.name}${
            props.receiver.id === user.id ? " (You)" : ""
          }`
        }}</span>
        <!-- <p style="color: black;" class="mb-0">{{ chat.selectedReceiver.name }} left 50 mins ago</p> -->
      </div>
      <div class="color-picker">
        <i
          data-bs-toggle="tooltip"
          data-bs-placement="top"
          data-bs-title="Message Color"
          class="fas fa-circle"
          @click.stop="toggleColorPicker"
          style="cursor: pointer"
          :style="{ color: msgColor }"
        ></i>
      </div>
      <button class="btn-close" @click="$emit('closeChat')">
        <i class="fal fa-times"></i>
      </button>
    </div>
    <div v-else class="card-header msg_head">
      <div class="bd-highlight">
        <div class="user_info">
          <span>{{ props.roomName }}</span>
        </div>
        <div class="text-white ms-3">
          {{ props.roomDescription }}
        </div>
      </div>
    </div>
    <div
      class="card-body msg_card_body"
      ref="messageContainer"
      v-if="isChatExpanded"
    >
      <div class="loading mb-2 text-center" v-if="isLoadingMessages">
        <svg
          version="1.1"
          id="loader-1"
          xmlns="http://www.w3.org/2000/svg"
          xmlns:xlink="http://www.w3.org/1999/xlink"
          x="0px"
          y="0px"
          width="40px"
          height="40px"
          viewBox="0 0 50 50"
          style="enable-background: new 0 0 50 50"
          xml:space="preserve"
        >
          <path
            fill="#FF6700"
            d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z"
            transform="rotate(18.3216 25 25)"
          >
            <animateTransform
              attributeType="xml"
              attributeName="transform"
              type="rotate"
              from="0 25 25"
              to="360 25 25"
              dur="0.6s"
              repeatCount="indefinite"
            ></animateTransform>
          </path>
        </svg>
      </div>
      <MessageItem
        v-for="message in messages"
        :key="message.id"
        :isPrivate="props.isPrivate"
        :message="message"
        :msgColor="msgColor"
        @showEmoji="showEmoji"
        @selectReceiver="$emit('selectReceiver', $event)"
      />
      <div v-if="props.isPrivate">
        <div class="d-flex justify-content-end" v-if="isSeen">
          <i class="font-12px">Seen {{ seenAtFormatted }}</i>
        </div>
        <div class="d-flex justify-content-start mb-4" v-if="isTyping">
          <div class="img_cont_msg">
            <img
              src="/images/other_user.jpg"
              class="rounded-circle user_img_msg"
            />
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
    </div>
    <div class="text-input" v-if="props.isPrivate">
      <input
        v-model="inputMessage"
        v-if="isChatExpanded"
        id="private_input"
        type="text"
        class="w-100"
        placeholder="Type a message..."
        @keyup.enter="saveMessage"
        @input="onInputPrivateChange"
        ref="privateInputEl"
      />
    </div>
    <div class="card-footer" v-else>
      <div class="input-group">
        <textarea
          v-model="inputMessage"
          name=""
          class="form-control type_msg"
          placeholder="Type your message..."
          @keyup.enter="saveMessage"
          autofocus
        />
        <span class="input-group-text send_btn"
          ><i class="fas fa-location-arrow"></i
        ></span>
      </div>
    </div>
    <Emoji
      :emojiCoordinates="emojiCoordinates"
      :isShow="isShowEmoji"
      :selectedMessage="selectedMessage"
      @hideEmoji="hideEmoji"
      @selectEmoji="selectEmoji"
    />

    <Transition name="fade" v-if="props.isPrivate">
      <ColorPickerModal
        v-if="isShowColorPicker"
        :isShow="isShowColorPicker"
        @hide="toggleColorPicker"
        @selectColor="selectColor"
      />
    </Transition>
  </div>
</template>

<style lang="scss">
.card {
  z-index: 1;
  height: 500px;
  border-radius: 15px !important;
  background-color: rgba(0, 0, 0, 0.4) !important;
  &.bg-white {
    background-color: white !important;
  }

  .card-header {
    border-radius: 15px 15px 0 0 !important;

    .search_btn {
      border-radius: 0 15px 15px 0 !important;
      background-color: rgba(0, 0, 0, 0.3) !important;
      border: 0 !important;
      color: white !important;
      cursor: pointer;
    }

    .search {
      border-radius: 15px 0 0 15px !important;
      background-color: rgba(0, 0, 0, 0.3) !important;
      border: 0 !important;
      color: white !important;

      &:focus {
        box-shadow: none !important;
        outline: 0px !important;
      }
    }
  }

  .msg_head {
    position: relative;
  }

  .msg_card_body {
    overflow-y: auto;
  }

  .card-footer {
    border-radius: 0 0 15px 15px !important;
    border-top: 0 !important;

    .type_msg {
      background-color: rgba(0, 0, 0, 0.3) !important;
      border: 0 !important;
      color: white !important;
      height: 60px !important;
      overflow-y: auto;
      border-radius: 15px 0 0 15px !important;

      &:focus {
        box-shadow: none !important;
        outline: 0px !important;
      }
    }

    .send_btn {
      border-radius: 0 15px 15px 0 !important;
      background-color: rgba(0, 0, 0, 0.3) !important;
      border: 0 !important;
      color: white !important;
      cursor: pointer;
    }
  }
}

.private-message-container {
  border-radius: 15px 15px 0 0 !important;
  background-color: white;
  position: absolute;
  bottom: 0;
  right: 10px;
  width: 350px;
  height: 54px;
  z-index: 2;

  &.expand {
    height: 400px;
  }

  .chat-header {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    transition: background-color 0.2s;
    cursor: pointer;

    &:hover {
      background-color: #e6e5e5;
    }

    .img_cont {
      position: relative;
    }

    .btn-close {
      position: absolute;
      right: 15px;
      top: 15px;
      outline: none;
      border: none;
      background: none;

      i {
        font-size: 18px;
        transition: transform 0.2s;

        &:hover {
          transform: scale(1.2);
        }
      }
    }
  }

  .private-chat-body {
    height: calc(100% - 65px - 40px);
    overflow-y: scroll;

    .msg_container_send {
      padding: 5px 10px 5px 10px !important;
      border-radius: 15px !important;
      max-width: 165px;
    }

    .msg_container {
      padding: 5px 10px 5px 10px !important;
      border-radius: 15px !important;
      max-width: 165px;
    }
  }

  .text-input {
    input {
      height: 40px;
      border: none;
      border-top: solid 1px #ddd;
      outline: none;
      padding: 7px;
      font-size: 12px;
    }
  }

  .color-picker {
    position: absolute;
    right: 45px;
    top: 17px;
    i {
      font-size: 22px;
    }
  }
}
</style>
