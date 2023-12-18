<template>
  <b-offcanvas id="sidebar" v-model="$sidebar" placement="end" no-header @click="onClick">
    <div class="d-grid gap-4 align-items-center">
      <app-pages v-if="showPages" position="header" vertical pills class="w-100" />
      <widgets-author v-if="showAuthor" />
      <widgets-online v-if="showOnline" />
      <widgets-levels v-if="showLevels" />
      <widgets-tags v-if="showTags" />
    </div>
  </b-offcanvas>
</template>

<script setup lang="ts">
defineProps({
  showPages: {
    type: Boolean,
    default: true,
  },
  showAuthor: {
    type: Boolean,
    default: true,
  },
  showOnline: {
    type: Boolean,
    default: true,
  },
  showLevels: {
    type: Boolean,
    default: true,
  },
  showTags: {
    type: Boolean,
    default: true,
  },
})
const {$sidebar} = useNuxtApp()

function onClick(e: MouseEvent) {
  const elem = e.target as HTMLElement
  if (elem && elem.tagName === 'A') {
    $sidebar.value = false
  }
}
</script>
