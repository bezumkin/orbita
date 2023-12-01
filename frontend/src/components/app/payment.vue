<template>
  <b-modal id="payment" v-model="showModal" v-bind="{title, size, hideFooter: isVariants}" @hidden="onCancel">
    <div v-if="$payment && !qr">
      <transition name="fade" mode="out-in">
        <payment-variants v-if="isVariants" v-model="paymentProperties" @title="(v: string) => (title = v)" />
        <payment-topic v-else-if="isTopic" v-model="paymentProperties" @title="(v: string) => (title = v)" />
        <payment-subscription v-else v-model="paymentProperties" @title="(v: string) => (title = v)" />
      </transition>
    </div>

    <transition name="fade" mode="out-in">
      <div v-if="qr" class="qr">
        <div class="text-center">{{ t('components.payment.service.scan_qr') }}</div>
        <b-img :src="qr.image" alt="QR" width="250" height="250" class="d-block m-auto" fluid />
      </div>
      <div v-else-if="canPay">
        <div v-if="services.length" class="mt-4">
          <div class="fw-bold">{{ t('components.payment.service.title') }}</div>
          <div class="services mt-1">
            <div v-for="i in services" :key="i" :class="serviceClass(i)" @click="onService(i)">
              <b-img :src="'/payments/' + i + '.svg'" height="50" />
            </div>
          </div>
          <transition name="fade" mode="out-in">
            <div v-if="subscriptionWarning" class="alert alert-warning mt-2 small p-2">
              {{ t('components.payment.service.no_subscription') }}
            </div>
          </transition>
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
    </transition>

    <template #footer>
      <b-button variant="light" @click="onCancel">{{ t('actions.cancel') }}</b-button>
      <b-button v-if="!qr" variant="primary" :disabled="loading" @click="onSubmit">
        <b-spinner v-if="loading" small />
        <template v-if="canPay">
          <fa icon="wallet" class="fa-fw" />
          {{ t('components.payment.actions.pay') }}
        </template>
        <template v-else>
          {{ t('actions.submit') }}
        </template>
      </b-button>
      <b-button v-else variant="primary" :disabled="loading" @click="onCheck">
        <b-spinner v-if="loading" small />
        <fa v-else icon="check" class="fa-fw" />
        {{ t('components.payment.actions.check') }}
      </b-button>
    </template>
  </b-modal>
</template>

<script setup lang="ts">
import type {BaseSize} from 'bootstrap-vue-next/src/types'
import slugify from 'slugify'

const {t} = useI18n()
const {$payment, $login} = useNuxtApp()
const {user, loadUser} = useAuth()
const loading = ref(false)
const size: Ref<keyof BaseSize> = ref('md')
const qr: Ref<undefined | Record<string, string>> = ref()
const services = (useRuntimeConfig().public.PAYMENT_SERVICES as string).split(',').map((i: string) => {
  return slugify(
    i.trim().replace(/[A-Z]/g, (s) => ' ' + s),
    {lower: true},
  )
})
const showModal = ref(false)
const canPay = computed(() => {
  return (
    paymentProperties.value.price > 0 &&
    (!paymentProperties.value.discount || paymentProperties.value.discount < paymentProperties.value.price)
  )
})

const paymentProperties: Ref<Record<string, any>> = ref({service: services[0], price: 0})
const isVariants = computed(() => {
  return Boolean(
    !paymentProperties.value.mode &&
      $payment.value &&
      'uuid' in $payment.value &&
      $payment.value.level_id &&
      $payment.value.price,
  )
})
const isTopic = computed(() => {
  return $payment.value && 'uuid' in $payment.value && $payment.value.price
})
const subscriptionWarning = computed(() => {
  const subscriptions = (useRuntimeConfig().public.PAYMENT_SUBSCRIPTIONS as string).split(',').map((i: string) => {
    return slugify(
      i.trim().replace(/[A-Z]/g, (s) => ' ' + s),
      {lower: true},
    )
  })
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
  paymentProperties.value = {service: services[0], price: 0}
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

watch(user, (newValue) => {
  if (newValue && $payment.value) {
    if (isTopic.value) {
      // Handle open topic dialog
    } else {
      showModal.value = Boolean(
        $payment.value &&
          user.value &&
          (!user.value.subscription || user.value.subscription.level_id !== $payment.value.id),
      )
    }
  } else {
    $payment.value = undefined
  }
})

onUnmounted(() => {
  $payment.value = undefined
})
</script>
