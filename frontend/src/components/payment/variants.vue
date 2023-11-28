<template>
  <div>
    <div v-if="level" class="variants">
      <div class="item">
        {{ t('components.payment.actions.subscribe') }}
        <div class="fw-bold py-1">{{ level.title }}</div>
        {{ $price(level.price) }} {{ t('models.level.per_month') }}

        <b-button variant="primary" class="mt-5" @click="onSubscribe">
          <fa icon="lock-open" />
          {{ $t('components.payment.level.subscribe') }}
        </b-button>
      </div>
      <div class="item">
        <div>
          {{ t('components.payment.topic.desc') }}
          <span class="fw-bold py-1">{{ $price($payment.price) }}</span>
        </div>

        <b-button variant="light" class="mt-5" @click="onPayment">
          <fa icon="wallet" />
          {{ $t('components.payment.actions.pay') }}
        </b-button>
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

const {$levels, $payment} = useNuxtApp()
const myValue: WritableComputedRef<Record<string, any>> = computed({
  get() {
    return props.modelValue
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})

const {t} = useI18n()
const level: Ref<VespLevel | undefined> = computed(() => {
  return $levels.value.find(
    (i: any) => $payment.value && 'level_id' in $payment.value && i.id === $payment.value.level_id,
  )
})

function onPayment() {
  myValue.value.mode = 'topic'
  myValue.value.price = $payment.value?.price
}

function onSubscribe() {
  myValue.value.mode = 'subscription'
  $payment.value = level.value
}

emit('title', t('components.payment.topic.title'))
</script>
