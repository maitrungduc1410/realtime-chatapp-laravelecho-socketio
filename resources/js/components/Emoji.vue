<script setup>
import { computed, inject, onBeforeUnmount, onMounted } from "vue";

const props = defineProps({
  emojiCoordinates: Object,
  isShow: {
    type: Boolean,
    default: false
  },
  selectedMessage: Object,
});

const emit = defineEmits(["hideEmoji", "selectEmoji"]);
const user = inject("$user");
const emojis = inject('$emojis')

function handDocumentClick(e) {
  const container = $(".emoji-container");
  if ($(e.target).attr("class") === "fal fa-grin-alt") {
    return;
  }

  // check if the clicked area is dropDown or not
  if (props.isShow && container.has(e.target).length === 0) {
    emit("hideEmoji");
  }
}

function handleHidingEmoji() {
  if (props.isShow) {
    emit("hideEmoji");
  }
}

onMounted(() => {
  $(document).on("click", handDocumentClick);
  $(window).scroll(handleHidingEmoji);
  $(window).resize(handleHidingEmoji);
  $("#shared_room").scroll(handleHidingEmoji);
});

onBeforeUnmount(() => {
  $(document).off("click", handDocumentClick);
  $(window).off("scroll", handleHidingEmoji);
  $(window).off("resize", handleHidingEmoji);
  $("#shared_room").off("scroll", handleHidingEmoji);
});

const userReaction = computed(() => {
  let emojiId = -1;
  let reactionId = -1;

  if (props.selectedMessage) {
    for (const r of props.selectedMessage.reactions) {
      if (r.user.id === user.id) {
        emojiId = r.emoji_id;
        reactionId = r.id;
        break;
      }
    }
  }

  return { emojiId, reactionId };
});
</script>

<template>
  <Transition name="slide-fade">
    <div
      class="emoji-container d-flex align-items-center"
      :style="{
        left: `${emojiCoordinates.x}px`,
        top: `${emojiCoordinates.y - 60}px`,
      }"
      v-if="isShow"
    >
      <div
        class="emoji-item"
        v-for="e in emojis"
        :key="e.src"
        @click="emit('selectEmoji', e)"
      >
        <div class="emoji-text">
          {{ e.name }}
        </div>
        <img :src="e.src" :alt="e.alt" />
        <div class="dot" v-if="userReaction.emojiId === e.id"></div>
      </div>
      <div class="overlay"></div>
    </div>
  </Transition>
</template>

<style lang="scss">
.emoji-container {
  border-radius: 20px;
  box-shadow: 0 2px 4px 1px rgba(0, 0, 0, 0.1);
  background: white;
  padding: 7px 5px;
  position: fixed;
  transform: translateX(-50%);

  .emoji-item {
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    padding: 0 4px;
    position: relative;

    &:hover {
      transform: scale(1.3);
      transform-origin: bottom;

      .emoji-text {
        opacity: 1;
      }
    }

    img {
      height: 32px;
      width: 32px;
    }

    .emoji-text {
      transition: opacity 0.2s;
      opacity: 0;
      position: absolute;
      top: -20px;
      background: rgba(0, 0, 0, 0.75);
      border-radius: 10px;
      color: #fff;
      font-size: 11px;
      padding: 0 6px;
    }

    .dot {
      width: 4px;
      height: 4px;
      background-color: #0084ff;
      border-radius: 50%;
      left: 50%;
      bottom: -5px;
      position: absolute;
    }
  }
}

.slide-fade-enter-active {
  transition: opacity 0.25s, transform 0.25s;
}

.slide-fade-leave-active {
  transition: opacity 0.25s, transform 0.25s;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translate(-50%, 20px);
  opacity: 0;
}
</style>
