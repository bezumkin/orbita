<template>
  <div>
    <ChartCommon
      v-if="chartEnabled"
      name="payments"
      endpoint="admin/payments/stat"
      event="payment"
      :date="filters.date"
      :formatter="(v: any) => $price(v, true)"
      class="mb-5"
    />

    <VespTable ref="table" v-bind="{url, sort, dir, fields, filters, tableActions, rowClass, onLoad}">
      <template #header-start>
        <VespInputDatePicker v-model="filters.date" />
      </template>
      <template #header-middle>
        <BFormSelect v-model="filters.status" class="mt-2 mt-md-0" :options="statuses" />
      </template>
      <template #cell(user)="{value}: any">
        <BLink :to="{name: 'admin-users-id-edit', params: {id: value.id}}" class="d-flex align-items-center">
          <UserAvatar :user="value" size="40" />
          <div class="ms-2">
            <div class="text-nowrap">{{ value.fullname }}</div>
            <div class="small text-muted">{{ value.username }}</div>
          </div>
        </BLink>
      </template>
      <template #cell(type)="{item}: any">
        <div v-if="item.topic">
          <BLink
            :to="{name: 'topics-uuid', params: {topics: item.topic.category?.uri || 'topics', uuid: item.topic.uuid}}"
          >
            {{ item.topic.title }}
          </BLink>
        </div>
        <div v-else-if="item.metadata && item.metadata.until">
          {{
            t('models.payment.subscription_desc', {
              title: item.metadata.title,
              date: formatDateShort(item.metadata.until),
            })
          }}
        </div>
      </template>
      <template #cell(service)="{value}">
        <BImg :src="formatService(value)" style="max-height: 25px; max-width: 75px" class="logo" />
      </template>
      <template #cell(paid)="{item, value}">
        <template v-if="value">
          <VespFa icon="check" class="text-success" :title="formatDate(item.paid_at as string)" />
        </template>
        <template v-else-if="value === false">
          <VespFa icon="times" class="text-danger" />
        </template>
        <VespFa v-else icon="hourglass-half" />
      </template>
      <template #pagination-data="{refresh, total, loading}">
        <BButton class="border-0" @click="() => refresh()">
          <BSpinner v-if="loading" :small="true" />
          <VespFa v-else icon="repeat" fixed-width />
        </BButton>
        <template v-if="sum">
          {{ t('models.payment.records', {total: formatBigNumber(total), sum: formatPrice(sum)}, total) }}
        </template>
        <template v-else>
          {{ t('components.table.records', {total}, total) }}
        </template>
      </template>

      <NuxtPage />
    </VespTable>

    <VespConfirm
      v-if="confirmVisible"
      :title="confirmTitle"
      :on-ok="confirmFunction"
      :ok-title="confirmAction"
      @hidden="onCancelConfirm"
    >
      <div v-html="confirmText" />
    </VespConfirm>
  </div>
</template>

<script setup lang="ts">
import slugify from 'slugify'
import type {VespTableAction} from '@vesp/frontend'

const {$price, $socket, $variables} = useNuxtApp()
const chartEnabled = $variables.value.CHART_PAYMENTS_DISABLE !== '1'

const table = ref()
const {t} = useI18n()
const url = 'admin/payments'
const sort = 'created_at'
const dir = 'desc'
const filters = ref({query: '', status: 1, date: []})
const fields = computed(() => [
  {key: 'created_at', label: t('models.payment.date'), formatter: formatDate, sortable: true},
  {key: 'user', label: t('models.payment.user')},
  {key: 'type', label: t('models.payment.type')},
  {key: 'amount', label: t('models.payment.amount'), formatter: formatPrice, sortable: true},
  {key: 'service', label: t('models.payment.service')},
  {key: 'paid', label: t('models.payment.status'), class: 'text-center', sortable: true},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {
    function: onRefundPayment,
    icon: 'undo',
    title: t('models.payment.refund.action'),
    variant: 'warning',
    isActive: (item: any) => item?.paid && !item?.metadata?.approved,
  },
  {
    function: onApprovePayment,
    icon: 'check',
    title: t('models.payment.approve.action'),
    variant: 'warning',
    isActive: (item: any) => !item?.paid && !item?.metadata?.refunded,
  },
  {
    route: {name: 'admin-payments-id'},
    icon: 'eye',
    title: t('actions.view'),
  },
  {
    function: (i: any) => table.value.delete(i),
    icon: 'times',
    title: t('actions.delete'),
    variant: 'danger',
    isActive: (item: any) => item?.paid !== true || item?.metadata?.approved,
  },
])
const statuses = ref([
  {value: null, text: t('models.payment.filter.all')},
  {value: 1, text: t('models.payment.filter.paid')},
  {value: 0, text: t('models.payment.filter.error')},
])
const sum = ref(0)

function formatService(value: any) {
  if (!value) {
    return ''
  }
  const name = slugify(
    value.replace(/[A-Z]/g, (s: string) => ' ' + s),
    {lower: true},
  )
  return '/payments/' + name + '.svg'
}

function formatPrice(value: any) {
  return $price(value, true)
}

function rowClass(item: any) {
  return !item || !item.paid ? 'inactive' : ''
}

function onLoad(items: any) {
  sum.value = items.sum || 0

  return items
}

const refundItem = ref<VespPayment | undefined>()
const approveItem = ref<VespPayment | undefined>()

const confirmVisible = computed(() => refundItem.value || approveItem.value)
const confirmTitle = computed(() => 'models.payment.' + (refundItem.value ? 'refund' : 'approve') + '.title')
const confirmAction = computed(() => 'models.payment.' + (refundItem.value ? 'refund' : 'approve') + '.action')
const confirmText = computed(() => t('models.payment.' + (refundItem.value ? 'refund' : 'approve') + '.confirm'))
const confirmFunction = computed(() => (refundItem.value ? cancelPayment : approvePayment))

function onRefundPayment(payment: VespPayment) {
  refundItem.value = payment
}

function onApprovePayment(payment: VespPayment) {
  approveItem.value = payment
}

function onCancelConfirm() {
  refundItem.value = undefined
  approveItem.value = undefined
}

async function cancelPayment() {
  if (!refundItem.value) {
    return
  }
  table.value.loading = true
  try {
    await usePost(url + '/' + refundItem.value.id + '/refund')
    table.value.refresh()
  } catch (e) {
    table.value.loading = false
  }
}

async function approvePayment() {
  if (!approveItem.value) {
    return
  }
  table.value.loading = true
  try {
    await usePost(url + '/' + approveItem.value.id + '/approve')
    table.value.refresh()
  } catch (e) {
    table.value.loading = false
  }
}

onMounted(() => {
  $socket.on('payment', () => {
    if (!filters.value.query && filters.value.status === 1 && !filters.value.date.length && table.value.page === 1) {
      table.value.refresh()
    }
  })
})

onBeforeUnmount(() => {
  $socket.off('payment')
})
</script>
