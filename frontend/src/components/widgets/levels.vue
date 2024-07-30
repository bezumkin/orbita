<template>
  <div v-if="$levels.length" class="widget">
    <h5 class="widget-title">{{ $t('widgets.levels') }}</h5>
    <div class="widget-body subscriptions">
      <BOverlay opacity="0.5" :show="loading">
        <div v-for="level in $levels" :key="level.id" class="level">
          <div class="title">{{ level.title }}</div>
          <div class="price">{{ $price(level.price) }} {{ $t('models.level.per_month') }}</div>
          <div v-if="level.cover" class="cover">
            <BImg
              :src="$image(level.cover, {h: 150, fit: 'crop'})"
              :srcset="$image(level.cover, {h: 300, fit: 'crop'}) + ' 2x'"
              class="rounded"
              height="150"
            />
          </div>
          <div v-if="level.content" class="content">{{ level.content }}</div>
          <BButton v-bind="getBtnParams(level)">{{ getBtnLabel(level) }}</BButton>
        </div>
      </BOverlay>
    </div>

    <VespConfirm v-if="confirmVisible" :on-ok="cancelAction" ok-title="actions.ok" @hidden="confirmVisible = false">
      <div v-html="cancelText" />
    </VespConfirm>
  </div>
</template>

<script setup lang="ts">
const {$levels, $payment, $i18n} = useNuxtApp()
const {user} = useAuth()

const loading = ref(false)
const confirmVisible = ref(false)
const cancelText = ref('')
const cancelAction = ref(() => {})

function getBtnParams(level: VespLevel) {
  const params: Record<string, any> = {}

  params.onClick = () => onSubscribe(level)

  if (user.value && user.value.subscription) {
    // Cancel change level from next date
    if (level.id === user.value.subscription.next_level_id) {
      params.variant = 'outline-secondary'
      params.onClick = confirmCancelChange
    }
    // Unsubscribe or renew
    else if (level.id === user.value.subscription.level_id) {
      params.variant = 'outline-secondary'
      // params.disabled = true
      params.onClick = user.value.subscription.cancelled ? onRenew : confirmUnsubscribe
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

function confirmCancelChange() {
  confirmVisible.value = true
  cancelText.value = $i18n.t('components.payment.subscription.cancel_change')
  cancelAction.value = onCancelChange
}

function confirmUnsubscribe() {
  confirmVisible.value = true
  cancelText.value = $i18n.t('components.payment.subscription.cancel_confirm')
  cancelAction.value = onUnsubscribe
}

async function onCancelChange() {
  loading.value = true
  try {
    await usePost('user/subscription/cancel-next')
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function onUnsubscribe() {
  loading.value = true
  try {
    await usePost('user/subscription/cancel')
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function onRenew() {
  loading.value = true
  try {
    await usePost('user/subscription/renew')
  } catch (e) {
  } finally {
    loading.value = false
  }
}
</script>
