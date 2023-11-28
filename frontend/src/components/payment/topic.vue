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
const emit = defineEmits(['title'])

const {$payment} = useNuxtApp()
const {t} = useI18n()

emit('title', t('components.payment.topic.title'))
</script>
