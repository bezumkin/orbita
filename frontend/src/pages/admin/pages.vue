<template>
  <div>
    <vesp-table ref="table" v-bind="{url, filters, sort, dir, fields, headerActions, tableActions, rowClass}">
      <template #cell(title)="{item, value}">
        <div>{{ value }}</div>
        <div class="small text-nowrap">
          <b-link :to="{name: 'pages-alias', params: {alias: String(item.alias)}}">{{ item.alias }}</b-link>
        </div>
      </template>
    </vesp-table>
    <nuxt-page />
  </div>
</template>

<script setup lang="ts">
import type {VespTableAction} from '~/components/vesp/table.vue'

const {t} = useI18n()
const {$contentPreview} = useNuxtApp()
const table = ref()
const url = 'admin/pages'
const filters = ref({query: ''})
const sort = 'rank'
const dir = 'asc'
const fields = computed(() => [
  {key: 'title', label: t('models.page.title')},
  {key: 'content', label: t('models.page.content'), formatter: formatContent},
  {key: 'position', label: t('models.page.position'), formatter: formatPosition},
  {key: 'rank', label: t('models.page.rank')},
])
const headerActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-pages-create'}, icon: 'plus', title: t('actions.create')},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-pages-id-edit'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function formatContent(value: any) {
  if (!value || !value.blocks) {
    return ''
  }
  return $contentPreview(value, 200)
}

function formatPosition(value: any) {
  return t('models.page.position_' + String(value))
}

function rowClass(item: any) {
  return item && !item.active ? 'inactive' : ''
}
</script>
