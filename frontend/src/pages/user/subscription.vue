<template>
  <div v-if="user && user.subscription">
    <div>
      <div class="fw-bold">{{ t('components.payment.subscription.level.current') }}</div>
      <div class="">{{ currentLevel?.title }}</div>
    </div>
    <div class="mt-3">
      <div class="fw-bold">{{ t('components.payment.subscription.paid_until') }}</div>
      <div class="">{{ paid }}</div>
    </div>
    <div v-if="nextLevel" class="mt-3">
      <div class="fw-bold">{{ t('components.payment.subscription.level.new') }}</div>
      <div class="">{{ nextLevel.title }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
const {t, d} = useI18n()
const {user} = useAuth()
const {$levels} = useNuxtApp()

if (!user.value || !user.value.subscription) {
  showError({statusCode: 404, statusMessage: 'Not Found'})
}

const currentLevel = computed(() => {
  return $levels.value.find((i: VespLevel) => i.id === user.value?.subscription?.level_id)
})
const nextLevel = computed(() => {
  return user.value?.subscription?.next_level_id
    ? $levels.value.find((i: VespLevel) => i.id === user.value?.subscription?.next_level_id)
    : undefined
})
const paid = computed(() => {
  return user.value?.subscription?.active_until ? d(user.value.subscription.active_until, 'long') : ''
})
</script>
