<template>
  <div v-if="$levels.length" class="widget">
    <h5 class="widget-title">{{ $t('widgets.levels') }}</h5>
    <div class="widget-body subscriptions">
      <div v-for="level in $levels" :key="level.id" class="level">
        <div class="title">{{ level.title }}</div>
        <div class="price">{{ $price(level.price) }} {{ $t('models.level.per_month') }}</div>
        <div v-if="level.cover" class="cover">
          <b-img
            :src="$image(level.cover, {h: 150, fit: 'crop'})"
            :srcset="$image(level.cover, {h: 300, fit: 'crop'}) + ' 2x'"
            class="rounded"
            height="150"
          />
        </div>
        <div v-if="level.content" class="content">{{ level.content }}</div>
        <b-button v-bind="getBtnParams(level)">{{ getBtnLabel(level) }}</b-button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const {$levels, $payment, $i18n} = useNuxtApp()
const {user, loadUser} = useAuth()

function getBtnParams(level: VespLevel) {
  const params: Record<string, any> = {}

  params.onClick = () => onSubscribe(level)

  if (user.value && user.value.subscription) {
    // Cancel change level from next date
    if (level.id === user.value.subscription.next_level_id) {
      params.variant = 'outline-secondary'
      params.onClick = onCancelChange
    }
    // Unsubscribe or renew
    else if (level.id === user.value.subscription.level_id) {
      params.variant = 'outline-secondary'
      // params.disabled = true
      params.onClick = user.value.subscription.cancelled ? onRenew : onUnsubscribe
    }
  }

  return params
}

function getBtnLabel(level: VespLevel) {
  if (user.value && user.value.subscription && user.value.subscription.active_until) {
    const date = $i18n.d(user.value.subscription.active_until, 'short')

    if (user.value.subscription.next_level_id === level.id && user.value.subscription.active_until) {
      return $i18n.t('components.payment.level.active_from', {date})
    }
    if (user.value.subscription.level_id === level.id) {
      if (user.value.subscription.next_level_id || user.value.subscription.cancelled) {
        return $i18n.t('components.payment.level.active_until', {date})
      }
      return $i18n.t('components.payment.level.subscribed')
    }
  }
  return $i18n.t('components.payment.level.subscribe')
}

function onSubscribe(level: VespLevel) {
  if ($payment.value) {
    $payment.value = undefined
    nextTick(() => {
      $payment.value = level
    })
  } else {
    $payment.value = level
  }
}

async function onCancelChange() {
  await usePost('user/subscription/cancel-next')
  await loadUser()
}

async function onUnsubscribe() {
  await usePost('user/subscription/cancel')
  await loadUser()
}

async function onRenew() {
  await usePost('user/subscription/renew')
  await loadUser()
}
</script>
