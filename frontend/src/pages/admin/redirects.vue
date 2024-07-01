<template>
  <VespTable ref="table" v-bind="{url, headerActions, tableActions, fields, filters, sort, dir, rowClass}">
    <NuxtPage />
  </VespTable>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'

const {t} = useI18n()

const url = 'admin/redirects'
const headerActions = computed(() => [
  {route: {name: 'admin-redirects-id', params: {id: 0}}, icon: 'plus', title: t('actions.create')},
])
const sort = 'rank'
const dir = 'asc'
const table = ref()

const fields = computed(() => [
  {key: 'title', label: t('models.redirect.title'), sortable: true},
  {key: 'from', label: t('models.redirect.from'), sortable: true},
  {key: 'to', label: t('models.redirect.to'), sortable: true},
  {key: 'rank', label: t('models.redirect.rank'), sortable: true},
])

const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-redirects-id'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

const filters = ref({query: ''})

function rowClass(item: any) {
  return item && !item.active ? 'inactive' : ''
}
</script>
