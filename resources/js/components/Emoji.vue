<template>
  <transition name="slide-fade">
    <div
      class="emoji-container d-flex align-items-center"
      :style="{left: `${emojiCoordinates.x}px`, top: `${emojiCoordinates.y - 60}px`}"
      v-if="isShow"
    >
      <div
        class="emoji-item"
        v-for="e in $root.emojis"
        :key="e.src"
        @click="selectEmoji(e)"
      >
        <div class="emoji-text">
          {{ e.name }}
        </div>
        <img :src="e.src" :alt="e.alt">
        <div class="dot" v-if="userReaction.emojiId === e.id"></div>
      </div>
      <div class="overlay"></div>
    </div>
  </transition>
</template>

<script>
import $ from 'jquery'

export default {
  props: ['emojiCoordinates', 'isShow', 'selectedMessage'],
  data () {
    return {

    }
  },
  mounted () {
    $(document).on('click', (e) => {
      const container = $('.emoji-container')

      if ($(event.target).attr('class') === 'fal fa-grin-alt') {
        return
      }

      // check if the clicked area is dropDown or not
      if (this.isShow && container.has(e.target).length === 0) {
        this.$emit('hideEmoji')
      }
    })

    $(window).scroll(() => {
      if (this.isShow) {
        this.$emit('hideEmoji')
      }
    })

    $(window).resize(() => {
      if (this.isShow) {
        this.$emit('hideEmoji')
      }
    })

    $('#shared_room').scroll(() => {
      if (this.isShow) {
        this.$emit('hideEmoji')
      }
    })
  },
  beforeDestroy () {
    $(document).off('click')
    $(window).off('scroll')
    $(window).off('resize')
    $('#shared_room').off('scroll')
  },
  methods: {
    selectEmoji (e) {
      this.$emit('selectEmoji', e)
    }
  },
  computed: {
    userReaction () {
      let emojiId = -1
      let reactionId = -1

      if (this.selectedMessage) {
        for (const r of this.selectedMessage.reactions) {
          if (r.user.id === this.$root.user.id) {
            emojiId = r.emoji_id
            reactionId = r.id
            break
          }
        }
      }

      return { emojiId, reactionId }
    }
  }
}
</script>

<style lang="scss">
.emoji-container {
  border-radius: 20px;
  box-shadow: 0 2px 4px 1px rgba(0, 0, 0, .1);
  background: white;
  padding: 7px 5px;
  position: fixed;
  transform: translateX(-50%);
  .emoji-item {
    cursor: pointer;
    transition: all .2s ease-in-out;
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
      transition: opacity .2s;
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

.slide-fade-enter-active, .slide-fade-leave-active {
  transition: opacity .25s, transform .25s;
}
.slide-fade-enter {
  opacity: 0;
  transform: translate(-50%, 20px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translate(-50%, 20px);
}
</style>
