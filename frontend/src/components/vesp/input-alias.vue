<template>
  <b-form-input v-model="record" v-bind="props" />
</template>

<script setup lang="ts">
import {BFormInput} from 'bootstrap-vue-next'
import Slugify from 'slugify'

const props = defineProps({
  ...BFormInput.props,
  modelValue: {
    type: String,
    default: '',
  },
  watch: {
    type: String,
    default: '',
  },
})
const emit = defineEmits(['update:modelValue'])
const record = computed({
  get() {
    return props.modelValue
  },
  set(newValue) {
    newValue = Slugify(newValue, {
      replacement: '-',
      remove: /[^\w\s-]+/g,
      lower: true,
      strict: true,
    })
    emit('update:modelValue', newValue)
  },
})

watch(
  () => props.watch,
  (value) => {
    record.value = value
  },
)
</script>
