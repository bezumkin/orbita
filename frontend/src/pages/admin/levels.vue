<template>
  <div>
    <ChartCommon
      v-if="chartEnabled"
      name="subscriptions"
      endpoint="admin/subscriptions/stat"
      :formatter="(v: number) => formatBigNumber(v)"
      class="mb-5"
    />

    <VespTable ref="table" v-bind="{url, filters, sort, dir, fields, headerActions, tableActions, rowClass}">
      <template #cell(title)="{value, item}: any">
        <span :style="{color: item.color}">{{ value }}</span>
      </template>
      <template #cell(price)="{value}: any">
        {{ $price(value) }}
      </template>
      <template #cell(cover)="{value}: any">
        <BImg
          v-if="value?.id"
          :src="$image(value, {w: 100, h: 50, fit: 'crop'})"
          :srcset="$image(value, {w: 200, h: 100, fit: 'crop'}) + ' 2x'"
          width="100"
          height="50"
          class="rounded"
        />
      </template>
    </VespTable>
    <NuxtPage />
  </div>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'
import {formatBigNumber} from '~/utils/vesp'

const {$variables} = useNuxtApp()
const chartEnabled = $variables.value.CHART_SUBSCRIPTIONS_DISABLE !== '1'

const {t} = useI18n()
const table = ref()
const url = 'admin/levels'
const filters = ref({query: ''})
const sort = 'price'
const dir = 'asc'
const fields = computed(() => [
  {key: 'id', label: t('models.level.id'), sortable: true},
  {key: 'cover', label: t('models.level.cover')},
  {key: 'title', label: t('models.level.title')},
  {key: 'price', label: t('models.level.price'), sortable: true},
  {key: 'active_users_count', label: t('models.user.title_many'), formatter: formatBigNumber, sortable: true},
])
const headerActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-levels-create'}, icon: 'plus', title: t('actions.create')},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-levels-id-edit'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function rowClass(item: any) {
  if (item) {
    const cls = []
    if (!item.active) {
      cls.push('inactive')
    }
    return cls
  }
}
</script>
