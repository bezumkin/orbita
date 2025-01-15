<template>
  <BImg v-if="imgProps.src" v-bind="imgProps" lazy />
  <div v-else v-bind="imgProps">
    <VespFa icon="user" class="fa-fw m-auto" v-bind="iconProps" />
  </div>
</template>

<script setup lang="ts">
import type {BaseColorVariant} from 'bootstrap-vue-next'

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
  const data: Record<string, any> = {
    style: {width: props.size + 'px', height: props.size + 'px'},
    class: `user-avatar d-inline-flex rounded-circle flex-shrink-0 bg-${props.variant}`,
  }
  if (props.user.avatar) {
    data.src = $image(props.user.avatar, {w: props.size, h: props.size, fit: 'crop'})
    data.srcset = $image(props.user.avatar, {w: Number(props.size) * 2, h: Number(props.size) * 2, fit: 'crop'}) + ' 2x'
  }
  return data
})

const iconProps = computed(() => {
  const halfSize = Math.round(Number(props.size) / 2)
  return {
    style: {color: '#fff', width: halfSize + 'px', height: halfSize + 'px'},
  }
})
</script>
