<template>
  <BOffcanvas id="sidebar" v-model="$sidebar" placement="end" no-header @click="onClick">
    <div class="d-grid gap-4 align-items-center">
      <AppPages v-if="showPages" position="header" vertical pills class="w-100" />
      <WidgetsAuthor v-if="showAuthor" />
      <WidgetsSearch v-if="showSearch && route.name !== 'search'" />
      <WidgetsOnline v-if="showOnline" />
      <WidgetsLevels v-if="showLevels" />
      <WidgetsCategories v-if="showCategories" />
      <WidgetsTags v-if="showTags" />
    </div>
  </BOffcanvas>
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
  showSearch: {
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
  showCategories: {
    type: Boolean,
    default: true,
  },
  showTags: {
    type: Boolean,
    default: true,
  },
})
const {$sidebar} = useNuxtApp()
const route = useRoute()

function onClick(e: MouseEvent) {
  const elem = e.target as HTMLElement
  if (elem && elem.tagName === 'A') {
    $sidebar.value = false
  }
}
</script>
