<template>
  <BModal id="payment" v-model="showModal" v-bind="{title, size, hideFooter: isVariants}" @hidden="onCancel">
    <div v-if="$payment && !qr">
      <Transition name="fade" mode="out-in">
        <PaymentVariants v-if="isVariants" v-model="paymentProperties" @title="(v: string) => (title = v)" />
        <PaymentTopic v-else-if="isTopic" v-model="paymentProperties" @title="(v: string) => (title = v)" />
        <PaymentSubscription v-else v-model="paymentProperties" @title="(v: string) => (title = v)" />
      </Transition>
    </div>

    <Transition name="fade" mode="out-in">
      <div v-if="qr" class="qr">
        <div class="text-center">{{ t('components.payment.service.scan_qr') }}</div>
        <BImg :src="qr.image" alt="QR" width="250" height="250" class="d-block m-auto" fluid />
      </div>
      <div v-else-if="canPay">
        <div v-if="services.length" class="mt-4">
          <div class="fw-bold">{{ t('components.payment.service.title') }}</div>
          <div class="services mt-1">
            <div v-for="i in services" :key="i" :class="serviceClass(i)" @click="onService(i)">
              <BImg :src="'/payments/' + i + '.svg'" height="50" class="logo" />
            </div>
          </div>
          <Transition name="fade" mode="out-in">
            <div v-if="subscriptionWarning" class="alert alert-warning mt-2 small p-2">
              {{ t('components.payment.service.no_subscription') }}
            </div>
          </Transition>
        </div>

        <div class="mt-4">
          <template v-if="paymentProperties.discount">
            <div class="fw-bold">
              {{ t('components.payment.subscription.upgrade') }}
            </div>
            <div class="price">{{ $price(paymentProperties.price - paymentProperties.discount) }}</div>
          </template>
          <template v-else>
            <div class="fw-bold">
              {{ isTopic ? t('components.payment.topic.price') : t('components.payment.subscription.price') }}
            </div>
            <div class="price">{{ $price(paymentProperties.price) }}</div>
          </template>
        </div>
      </div>
    </Transition>

    <template #footer>
      <BButton variant="light" @click="onCancel">{{ t('actions.cancel') }}</BButton>
      <BButton v-if="!qr" variant="primary" :disabled="loading" @click="onSubmit">
        <BSpinner v-if="loading" small />
        <template v-if="canPay">
          <VespFa icon="wallet" class="fa-fw" />
          {{ t('components.payment.actions.pay') }}
        </template>
        <template v-else>
          {{ t('actions.submit') }}
        </template>
      </BButton>
      <BButton v-else variant="primary" :disabled="loading" @click="onCheck">
        <BSpinner v-if="loading" small />
        <VespFa v-else icon="check" class="fa-fw" />
        {{ t('components.payment.actions.check') }}
      </BButton>
    </template>
  </BModal>
</template>

<script setup lang="ts">
import type {BaseSize} from 'bootstrap-vue-next'

const {t} = useI18n()
const {$payment, $login, $variables} = useNuxtApp()
const {user, loadUser} = useAuth()
const loading = ref(false)
const size = ref<keyof BaseSize>('md')
const qr = ref<undefined | Record<string, string>>()
const services = computed(() => {
  return $variables.value?.PAYMENT_SERVICES?.split(',').map(formatServiceKey) || []
})
const showModal = ref(false)
const canPay = computed(() => {
  return (
    paymentProperties.value.price > 0 &&
    (!paymentProperties.value.discount || paymentProperties.value.discount < paymentProperties.value.price)
  )
})

const paymentProperties = ref<Record<string, any>>({service: services.value.length ? services.value[0] : '', price: 0})
const isTopic = computed(() => {
  return $payment.value && 'uuid' in $payment.value && $payment.value.price
})
const isVariants = computed(() => {
  return !paymentProperties.value.mode && isTopic.value && $payment.value.level_id
})
const subscriptionWarning = computed(() => {
  const subscriptions = $variables.value?.PAYMENT_SUBSCRIPTIONS?.split(',').map(formatServiceKey) || []
  return Boolean(
    !isTopic.value &&
      subscriptions.length > 0 &&
      paymentProperties.value.service &&
      !subscriptions.includes(paymentProperties.value.service),
  )
})
const title = ref('')

async function onSubmit() {
  if (!$payment.value) {
    return
  }
  loading.value = true
  try {
    if (!isTopic.value && !canPay.value) {
      await usePost('user/subscription/next/' + $payment.value.id, {period: paymentProperties.value.period})
      await loadUser()
      onCancel()
    } else {
      const params: Record<string, any> = {...paymentProperties.value}
      if ('uuid' in $payment.value) {
        params.topic = $payment.value.uuid
      } else {
        params.level = $payment.value.id
      }
      const data = await usePost('user/payments', params)
      // console.log(data)
      if (data.payment) {
        if (data.payment.qr) {
          qr.value = {id: data.id, image: data.payment.qr}
        } else if (data.payment.redirect) {
          onCancel()
          window.location = data.payment.redirect
        }
      } else {
        onCancel()
        await loadUser()
      }
    }
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function onCheck() {
  if (!qr.value) {
    return
  }
  loading.value = true
  try {
    const data = await useGet('user/payments/' + qr.value.id)
    // console.log(data)
    if (data.paid === true) {
      clearNuxtData()
      await clearError()
      await refreshNuxtData()
      onCancel()
    }
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function onService(service: string) {
  paymentProperties.value.service = service
}

function onCancel() {
  showModal.value = false
  qr.value = undefined
  $payment.value = undefined
  paymentProperties.value = {service: services.value[0], price: 0}
}

function serviceClass(selected: string) {
  return {
    item: true,
    active: selected === paymentProperties.value.service,
  }
}

watch($payment, (newValue) => {
  if (newValue) {
    if (user.value) {
      showModal.value = true
    } else {
      $login.value = true
    }
  }
})

watch(user, async (newValue) => {
  if (newValue && $payment.value) {
    if ('uuid' in $payment.value) {
      try {
        const topic = await useGet('web/topics/' + $payment.value.uuid)
        showModal.value = !topic.access
      } catch (e) {}
    } else {
      showModal.value = !newValue.subscription || newValue.subscription.level_id !== $payment.value.id
    }
  } else {
    $payment.value = undefined
  }
})

watch(services, () => {
  if (paymentProperties.value.service === '' && services.value.length) {
    paymentProperties.value.service = services.value[0]
  }
})

onUnmounted(() => {
  $payment.value = undefined
})
</script>
