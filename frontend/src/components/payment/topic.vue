<template>
  <div>
    <div class="item">
      <div>
        {{ t('components.payment.topic.desc') }}
        <span class="fw-bold py-1">{{ $price($payment.price) }}</span>

        <div class="mt-2 fw-bold">{{ $payment.title }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type {WritableComputedRef} from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default() {
      return {}
    },
  },
})
const emit = defineEmits(['update:modelValue', 'title'])

const {$payment} = useNuxtApp()
const {t} = useI18n()

const myValue: WritableComputedRef<Record<string, any>> = computed({
  get() {
    return props.modelValue
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})

myValue.value.price = $payment.value?.price
emit('title', t('components.payment.topic.title'))
</script>
