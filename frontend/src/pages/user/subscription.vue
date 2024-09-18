<template>
  <div>
    <BRow v-if="user.subscription" class="d-flex mb-5 row-gap-4">
      <BCol md="4">
        <div class="fw-bold">{{ t('components.payment.subscription.level.current') }}</div>
        <div class="">{{ currentLevel?.title }}</div>
      </BCol>
      <BCol md="4">
        <div class="fw-bold">{{ t('components.payment.subscription.paid_until') }}</div>
        <div class="">{{ paid }}</div>
      </BCol>
      <BCol v-if="nextLevel" md="4">
        <div class="fw-bold">{{ t('components.payment.subscription.level.new') }}</div>
        <div class="">{{ nextLevel.title }}</div>
      </BCol>
    </BRow>

    <div v-if="$levels.length">
      <h4 v-if="user.subscription" class="pt-4 border-bottom">
        {{ $t('widgets.levels') }}
      </h4>
      <WidgetsLevels user-page />
    </div>
  </div>
</template>

<script setup lang="ts">
const {t, d} = useI18n()
const {user} = useAuth()
const {$levels} = useNuxtApp()

if (!user.value || !$levels.value.length) {
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
