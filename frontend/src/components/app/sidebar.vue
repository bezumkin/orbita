<template>
  <b-offcanvas id="sidebar" v-model="$sidebar" placement="end" no-header @click="onClick">
    <div class="d-flex flex-column align-items-center">
      <app-author />
      <app-online v-if="showOnline" class="mt-4" />
      <app-levels class="mt-4" />
    </div>
  </b-offcanvas>
</template>

<script setup lang="ts">
defineProps({
  showOnline: {
    type: Boolean,
    default: false,
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
