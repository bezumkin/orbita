<template>
  <div>
    <VespTable ref="table" v-bind="{url, filters, sort, dir, fields, headerActions, tableActions, rowClass}">
      <template #cell(name)="{item, value}">
        <template v-if="item.external">
          <a :href="item.link" :target="item.blank ? '_blank' : '_self'">
            {{ value }}&nbsp;<sup><VespFa icon="external-link" size="sm" /></sup>
          </a>
        </template>
        <BLink v-else :to="{name: 'pages-alias', params: {alias: item.alias}}">{{ value }}</BLink>
      </template>
      <template #cell(content)="{item, value}">
        <template v-if="item.external">
          {{ item.link }}
        </template>
        <template v-else>
          <div>{{ item.title }}</div>
          <div class="small">{{ $contentPreview(value, 150) }}</div>
        </template>
      </template>
    </VespTable>
    <NuxtPage />
  </div>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'

const {t} = useI18n()
const {$contentPreview} = useNuxtApp()
const table = ref()
const url = 'admin/pages'
const filters = ref({query: ''})
const sort = 'rank'
const dir = 'asc'
const fields = computed(() => [
  {key: 'name', label: t('models.page.name'), sortable: true},
  {key: 'content', label: t('models.page.content')},
  {key: 'position', label: t('models.page.position'), formatter: formatPosition},
  {key: 'rank', label: t('models.page.rank'), sortable: true},
])
const headerActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-pages-create'}, icon: 'plus', title: t('actions.create')},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-pages-id-edit'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function formatPosition(value: any) {
  return t('models.page.position_' + String(value))
}

function rowClass(item: any) {
  return item && !item.active ? 'inactive' : ''
}
</script>
