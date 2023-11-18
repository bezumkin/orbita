<template>
  <b-img v-if="source" :src="source" :srcset="srcSet" v-bind="imgProps" lazy />
  <div v-else :style="{width: size + 'px', height: size + 'px'}" v-bind="imgProps">
    <fa icon="user" class="m-auto" :style="{color: '#fff', width: halfSize + 'px', height: halfSize + 'px'}" />
  </div>
</template>

<script setup lang="ts">
import type {BaseColorVariant} from 'bootstrap-vue-next/src/types'

const props = defineProps({
  size: {
    type: [Number, String],
    default: 25,
  },
  user: {
    type: Object,
    default() {
      return {}
    },
  },
  variant: {
    type: String as PropType<keyof BaseColorVariant>,
    default: 'secondary',
  },
})

const {$image} = useNuxtApp()
const imgProps = computed(() => {
  return {
    style: {width: props.size + 'px', height: props.size + 'px'},
    alt: '',
    class: `user-avatar d-inline-flex rounded-circle bg-${props.variant}`,
  }
})
const source = computed(() => {
  return props.user.avatar ? $image(props.user.avatar, {w: props.size, h: props.size}) : undefined
})
const source2x = computed(() => {
  return props.user.avatar
    ? $image(props.user.avatar, {w: Number(props.size) * 2, h: Number(props.size) * 2})
    : undefined
})
const srcSet = computed(() => {
  return source.value ? [source.value, source2x.value + ' 2x'].join(', ') : undefined
})
const halfSize = computed(() => Math.round(Number(props.size) / 2))
</script>
