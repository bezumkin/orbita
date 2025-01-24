<template>
  <VespModal size="lg" no-footer :title="$t('models.category.title_many')">
    <VespTable ref="table" v-bind="{url, fields, filters, headerActions, sort, dir, rowClass, onLoad}">
      <template #cell(title)="{item}">
        {{ item.title }}
        <div class="small">
          <a :href="'/' + item.uri" target="_blank" class="text-secondary">/{{ item.uri }}</a>
        </div>
      </template>
      <template #cell(actions)="{item}">
        <BButton :to="{name: 'admin-topics-categories-id', params: {id: item.id}}" size="sm">
          <VespFa icon="edit" />
        </BButton>
        <template v-if="table?.sort === 'rank'">
          <BButton v-if="item.rank > 1" size="sm" variant="outline-secondary" @click="moveUp(item)">
            <VespFa :icon="table?.dir === 'asc' ? 'arrow-up' : 'arrow-down'" />
          </BButton>
          <BButton v-if="item.rank < total" size="sm" variant="outline-secondary" @click="moveDown(item)">
            <VespFa :icon="table?.dir === 'asc' ? 'arrow-down' : 'arrow-up'" />
          </BButton>
        </template>
        <BButton size="sm" variant="danger" @click="table.delete(item)">
          <VespFa icon="times" />
        </BButton>
      </template>

      <NuxtPage />
    </VespTable>
  </VespModal>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'

const {t} = useI18n()
const table = ref()
const url = 'admin/categories'
const filters = ref({query: ''})
const sort = 'rank'
const dir = 'asc'
const fields = computed(() => [
  {key: 'id', label: t('models.category.id'), sortable: true},
  {key: 'title', label: t('models.category.title'), sortable: true},
  {key: 'topics_count', label: t('models.topic.title_many'), sortable: true},
  {key: 'created_at', label: t('models.category.created_at'), sortable: true, formatter: formatDate},
  {key: 'rank', label: t('models.category.rank'), sortable: true},
  {key: 'actions', label: '', class: 'actions'},
])
const headerActions = computed<VespTableAction[]>(() => [
  {route: {name: 'admin-topics-categories-id', params: {id: 0}}, icon: 'plus', title: t('actions.create')},
])

function rowClass(item: any) {
  return !item?.active ? 'inactive' : ''
}

const total = ref(0)
function onLoad(data: Record<string, any>) {
  total.value = data.total || 0
  return data
}

async function moveUp(item: VespCategory) {
  try {
    await usePost(url + '/' + item.id, {action: 'moveUp'})
    table.value.refresh()
  } catch (e) {}
}

async function moveDown(item: VespCategory) {
  try {
    await usePost(url + '/' + item.id, {action: 'moveDown'})
    table.value.refresh()
  } catch (e) {}
}
</script>
