<template>
  <VespTable ref="table" v-bind="{url, sort, dir, fields, tableActions, rowClass}">
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
        <BLink :to="{name: 'topics-uuid', params: {uuid: item.topic.uuid}}">{{ item.topic.title }}</BLink>
      </div>
      <div v-else-if="item.metadata && item.metadata.until">
        {{
          t('models.payment.subscription_desc', {
            title: item.metadata.title,
            date: d(item.metadata.until, 'short'),
          })
        }}
      </div>
    </template>
    <template #cell(service)="{value}">
      <BImg :src="formatService(value)" style="max-height: 25px; max-width: 75px" />
    </template>
    <template #cell(paid)="{item, value}">
      <template v-if="value">
        <VespFa icon="check" class="text-success" :title="d(item.paid_at as string, 'long')" />
      </template>
      <VespFa v-else icon="hourglass-half" />
    </template>
  </VespTable>
</template>

<script setup lang="ts">
import slugify from 'slugify'
import type {VespTableAction} from '@vesp/frontend'

const {$price} = useNuxtApp()
const table = ref()
const {t, d} = useI18n()
const url = 'admin/payments'
const sort = 'created_at'
const dir = 'desc'
const fields = computed(() => [
  {key: 'created_at', label: t('models.payment.date'), formatter: formatDate, sortable: true},
  {key: 'user', label: t('models.payment.user')},
  {key: 'type', label: t('models.payment.type')},
  {key: 'amount', label: t('models.payment.amount'), formatter: formatPrice, sortable: true},
  {key: 'service', label: t('models.payment.service')},
  {key: 'paid', label: t('models.payment.paid'), class: 'text-center', sortable: true},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {
    function: (i: any) => table.value.delete(i),
    icon: 'times',
    title: t('actions.delete'),
    variant: 'danger',
    isActive: (item: any) => item && item.paid !== true,
  },
])

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
  return value ? $price(value) : ''
}

function formatDate(value: any) {
  return value ? d(value, 'long') : ''
}

function rowClass(item: any) {
  return !item || !item.paid ? 'inactive' : ''
}
</script>
