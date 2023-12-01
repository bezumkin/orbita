<template>
  <div>
    <div v-if="currentLevel" class="levels">
      <div class="item">
        <div>{{ t('components.payment.subscription.level.current') }}</div>
        <div class="fw-bold py-1">{{ currentLevel.title }}</div>
        <div>{{ $price(currentLevel.price) }} {{ t('models.level.per_month') }}</div>
      </div>
      <div class="item active">
        <div>{{ t('components.payment.subscription.level.new') }}</div>
        <div class="fw-bold py-1">{{ level.title }}</div>
        <div>{{ $price(level.price) }} {{ t('models.level.per_month') }}</div>
      </div>
    </div>
    <div v-else class="subscription">
      <div class="fw-bold">{{ level.title }}</div>
      <div>{{ $price(level.price) }} {{ t('models.level.per_month') }}</div>
    </div>

    <div v-if="periods.length > 1" class="mt-4">
      <div class="fw-bold">{{ t('components.payment.subscription.period') }}</div>
      <div class="periods mt-1">
        <div v-for="i in periods" :key="i" :class="periodClass(i)" @click="onPeriod(i)">
          {{ t('components.payment.subscription.months', {amount: i}, i) }}
        </div>
      </div>
    </div>
    <div class="mt-2 small text-muted" v-text="description" />
  </div>
</template>

<script setup lang="ts">
import {addDays, addMonths, differenceInDays} from 'date-fns'
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

const {t, d} = useI18n()
const {$payment, $levels} = useNuxtApp()
const {user} = useAuth()
const periods = [1, 3, 6, 12]
const currentLevel: Ref<VespLevel | undefined> = ref()
const downgrading = ref(false)
const upgrading = ref(false)
const level: ComputedRef<VespLevel> = computed(() => $payment.value as unknown as VespLevel)

const myValue: WritableComputedRef<Record<string, any>> = computed({
  get() {
    return props.modelValue
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})

const description = computed(() => {
  const parts = []
  let date = addMonths(new Date(), myValue.value.period)
  if (user.value?.subscription?.active_until) {
    if (downgrading.value) {
      date = new Date(user.value?.subscription?.active_until)
    } else if (upgrading.value) {
      const days = Math.round(myValue.value.discount / costPerDay(level.value))
      date = addDays(new Date(), days)
    }
  }
  parts.push(t('components.payment.subscription.month_' + myValue.value.period, {date: d(date, 'short')}))
  if (downgrading.value || upgrading.value) {
    parts.push(t('components.payment.subscription.free'))
  } else {
    parts.push(t('components.payment.subscription.cancel'))
  }

  return parts.join('\n')
})

function costPerDay(level: VespLevel) {
  const cost = Math.round((level.price / 30) * 100) / 100
  return cost > 1 ? Math.round(cost) : cost
}

function periodClass(selected: number) {
  return {
    item: true,
    active: selected === myValue.value.period,
  }
}

function onPeriod(period: number) {
  myValue.value.period = period
  setPrice()
}

function setPrice() {
  if (downgrading.value) {
    myValue.value.price = 0
    return
  }
  upgrading.value = false

  myValue.value.price = level.value.price * myValue.value.period
  if (user.value?.subscription && user.value.subscription.active_until && currentLevel.value) {
    const days = differenceInDays(new Date(user.value.subscription.active_until), new Date())
    if (days > 0) {
      let cost = costPerDay(currentLevel.value)
      if (cost > 1) {
        cost = Math.round(cost)
      }
      myValue.value.discount = cost * days
      if (myValue.value.discount > myValue.value.price) {
        upgrading.value = true
      }
    }
  }
}

emit('title', t('components.payment.subscription.title'))

if (user.value?.subscription) {
  if (user.value.subscription.level_id === level.value.id) {
    useNuxtApp().$payment.value = undefined
  } else {
    emit('title', t('components.payment.subscription.change'))
    if (user.value && user.value.subscription) {
      currentLevel.value = $levels.value.find((i: VespLevel) => i.id === user.value?.subscription?.level_id)
      if (currentLevel.value) {
        downgrading.value = Boolean(currentLevel.value && currentLevel.value.price > level.value.price)
      }
    }
  }
}

myValue.value.period = periods[0]
setPrice()
</script>
