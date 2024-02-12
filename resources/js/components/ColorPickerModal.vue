<script setup>
import { ref, defineEmits } from 'vue'

defineEmits(['hide', 'selectColor'])

const colors = ref([
  {
    value: '#0084ff'
  },
  {
    value: '#44bec7'
  },
  {
    value: '#ffc300'
  },
  {
    value: '#fa3c4c'
  },
  {
    value: '#d696bb'
  },
  {
    value: '#42e274'
  },
  {
    value: '#ff7e28'
  },
  {
    value: '#e68585'
  },
  {
    value: '#7646ff'
  },
  {
    value: '#1fcef5'
  },
  {
    value: '#ff5ba1'
  }
])
</script>

<template>
  <div class="color-palette">
    <div class="color-palette-container">
      <h5 class="color-palette-header p-2">
        Customise your chat
        <i
          class="fal fa-times float-end"
          @click="$emit('hide')"
        ></i>
      </h5>
      <div class="color-palette-body d-flex flex-wrap p-4 justify-content-center">
        <div
          class="color-item-container"
          v-for="c in colors"
          :key="c.value"
        >
          <div
            class="color-item"
            :style="{ 'background-color': c.value }"
            @click="$emit('selectColor', c.value)"
          ></div>
        </div>
      </div>
    </div>
    <div
      class="color-palette-overlay"
      @click="$emit('hide')"
    ></div>
  </div>
</template>

<style lang="scss">
.color-palette {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;

  .color-palette-container {
    background-color: white;
    border-radius: 4px;
    width: 400px;
    z-index: 2;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    .color-palette-header {
      border-bottom: solid 1px #ddd;

      i {
        margin-top: 3px;
        cursor: pointer;
        color: #7b7b7b;

        &:hover {
          color: black;
        }
      }
    }

    .color-palette-body {
      .color-item-container {
        border-radius: 20px;
        cursor: pointer;
        margin-left: 4px;
        margin-right: 4px;
        padding: 8px;

        &:hover {
          background: #e6f2ff;
        }

        .color-item {
          border-radius: 50%;
          height: 50px;
          width: 50px;
        }
      }
    }
  }

  .color-palette-overlay {
    position: absolute;
    background-color: #00000066;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    z-index: 1;
  }
}
</style>
