<template>
  <div>
    <VespTable ref="table" v-bind="{url, filters, sort, dir, fields, headerActions, tableActions, rowClass}">
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

const {t, d} = useI18n()
const table = ref()
const url = 'admin/topics'
const filters = ref({query: ''})
const sort = 'created_at'
const dir = 'desc'
const fields = computed(() => [
  // {key: 'id', label: t('models.topic.id')},
  {key: 'cover', label: t('models.topic.cover')},
  {key: 'title', label: t('models.topic.title')},
  {key: 'created_at', label: t('models.topic.created_at'), formatter: formatDate},
  {key: 'published_at', label: t('models.topic.published_at'), formatter: formatDate},
])
const headerActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-topics-create'}, icon: 'plus', title: t('actions.create')},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-topics-id-edit'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function formatDate(value: any) {
  return value ? d(value, 'long') : ''
}

function rowClass(item: any) {
  return item && !item.active ? 'inactive' : ''
}
</script>
