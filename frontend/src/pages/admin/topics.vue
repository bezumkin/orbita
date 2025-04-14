<template>
  <div>
    <VespTable ref="table" v-bind="{url, filters, sort, dir, fields, headerActions, tableActions, rowClass, onLoad}">
      <template #header-middle>
        <div class="d-grid d-md-flex justify-content-md-center gap-2 mt-2 mt-md-0">
          <BButton :to="{name: 'admin-topics-categories'}">
            <VespFa icon="list" fixed-width /> {{ t('models.category.title_many') }}
          </BButton>

          <BButton :to="{name: 'admin-topics-tags'}">
            <VespFa icon="tags" fixed-width /> {{ t('models.tag.title_many') }}
          </BButton>
        </div>
      </template>

      <template #header-end>
        <BInputGroup>
          <template #append>
            <BButton :disabled="!filters.query" @click="filters.query = ''">
              <VespFa icon="times" fixed-width />
            </BButton>
          </template>
          <BFormInput v-model="filters.query" :placeholder="t('components.table.query')" />
        </BInputGroup>
        <div v-if="typeOptions.length" class="d-flex gap-1 mt-1 small">
          <BBadge
            v-for="(type, idx) in typeOptions"
            :key="idx"
            :variant="type === filters.type ? 'primary' : 'secondary'"
            style="cursor: pointer"
            @click="onType(type)"
          >
            {{ t('models.topic.type.' + type) }}
          </BBadge>
        </div>
      </template>

      <template #cell(title)="{item}">
        <div v-if="item.category" class="small">{{ item.category.title }} /</div>
        <span v-if="item.type" class="me-1" :title="t('models.topic.type.' + item.type)">
          <VespFa :icon="item.type === 'text' ? 'file' : item.type" fixed-width />
        </span>
        <BLink
          :to="{name: 'topics-uuid', params: {topics: item.category?.uri || 'topics', uuid: item.uuid}}"
          class="fw-bold"
          target="_blank"
        >
          {{ item.title }}
        </BLink>
        <div v-if="item.tags.length" class="mt-2 small">
          <VespFa icon="tags" fixed-width /> {{ item.tags.map((i: VespTag) => i.title).join(', ') }}
        </div>
        <!--<div v-if="item.tags.length" class="mt-2 d-flex gap-1">
          <BBadge v-for="tag in item.tags" :key="tag.id">{{ tag.title }}</BBadge>
        </div>-->
      </template>
      <template #cell(cover)="{value}: any">
        <BImg
          v-if="value?.id"
          :src="$image(value, {w: 100, h: 50, fit: 'crop'})"
          :srcset="$image(value, {w: 100, h: 50, fit: 'crop', dpr: 2}) + ' 2x'"
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

const {t} = useI18n()
const table = ref()
const url = 'admin/topics'
const filters = ref<Record<string, string | null>>({query: '', type: null})
const sort = 'created_at'
const dir = 'desc'
const fields = computed(() => [
  // {key: 'id', label: t('models.topic.id')},
  {key: 'cover', label: t('models.topic.cover')},
  {key: 'title', label: t('models.topic.title'), sortable: true},
  {key: 'views_count', label: t('models.topic_view.title_many'), sortable: true},
  {key: 'comments_count', label: t('models.comment.title_many'), sortable: true},
  {key: 'created_at', label: t('models.topic.created_at'), formatter: formatDate, sortable: true},
  {key: 'published_at', label: t('models.topic.published_at'), formatter: formatDate, sortable: true},
  {key: 'actions', label: '', class: 'actions'},
])
const headerActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-topics-create'}, icon: 'plus', title: t('actions.create')},
  // {route: {name: 'admin-topics-categories'}, icon: 'list', title: t('models.category.title_many')},
  // {route: {name: 'admin-topics-tags'}, icon: 'tags', title: t('models.tag.title_many')},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-topics-id-edit'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function rowClass(item: any) {
  return item && !item.active ? 'inactive' : ''
}

const types = getTopicTypes()
const typeOptions = ref<string[]>([])
function onLoad(items: any) {
  typeOptions.value = items.types?.sort((a: string, b: string) => types.indexOf(a) - types.indexOf(b)) || []
  if (table.value) {
    if (items.types) {
      table.value.$el.classList.add('with-types')
    } else {
      table.value.$el.classList.remove('with-types')
    }
  }
  if (filters.value.type && !typeOptions.value.includes(filters.value.type)) {
    filters.value.type = null
  }
  return items
}

function onType(type: string) {
  filters.value.type = filters.value.type === type ? null : type
}
</script>

<style scoped lang="scss">
:deep(.vesp-table) {
  &.with-types {
    .align-items-center:first-child {
      align-items: flex-start !important;
    }
  }
}
</style>
