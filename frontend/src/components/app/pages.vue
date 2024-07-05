<template>
  <BNav class="pages" v-bind="props">
    <template v-for="page in pages" :key="page.id">
      <a v-if="page.external" :href="page.link" :target="page.blank ? '_blank' : '_self'" class="nav-link">
        {{ page.name }}&nbsp;<sup><VespFa icon="external-link" size="sm" /></sup>
      </a>
      <BNavItem v-else :to="{name: 'pages-alias', params: {alias: page.alias}}">
        {{ page.name }}
      </BNavItem>
    </template>
  </BNav>
</template>

<script setup lang="ts">
import {BNav} from 'bootstrap-vue-next'

const props = defineProps({
  ...BNav.props,
  position: {
    type: String,
    default: 'header',
  },
})

const {$pages} = useNuxtApp()

const pages = computed(() => {
  return $pages.value.filter((i: any) => ['both', props.position].includes(i.position))
})
</script>
