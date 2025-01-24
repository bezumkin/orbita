<template>
  <div>
    <VespTable ref="table" v-bind="{url, filters, sort, dir, fields, headerActions, tableActions, rowClass}">
      <template #header-middle>
        <div class="d-grid d-md-flex justify-content-md-center gap-2 mt-2 mt-md-0">
          <BButton :to="{name: 'admin-topics-categories'}">
            <VespFa icon="list" fixed-width /> {{ $t('models.category.title_many') }}
          </BButton>

          <BButton :to="{name: 'admin-topics-tags'}">
            <VespFa icon="tags" fixed-width /> {{ $t('models.tag.title_many') }}
          </BButton>
        </div>
      </template>

      <template #cell(title)="{item}">
        <div v-if="item.category" class="small">{{ item.category.title }} /</div>
        <BLink
          :to="{name: 'topics-uuid', params: {topics: item.category?.uri || 'topics', uuid: item.uuid}}"
          class="fw-bold"
          target="_blank"
        >
          {{ item.title }}
        </BLink>
        <div v-if="item.tags.length" class="mt-2 small">
          <VespFa icon="tags" fixed-width /> {{ item.tags.map((i) => i.title).join(', ') }}
        </div>
        <!--<div v-if="item.tags.length" class="mt-2 d-flex gap-1">
          <BBadge v-for="tag in item.tags" :key="tag.id">{{ tag.title }}</BBadge>
        </div>-->
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

      <!--      <template #cell(actions)="{item}">
        &lt;!&ndash;<BButton></BButton>&ndash;&gt;
        &lt;!&ndash;{route: {name: 'topics-uuid'}, map: {topics: 'category.uri', uuid: 'uuid'}, icon: 'eye', title: }, , icon: target="_blank"&ndash;&gt;
        <BButton size="sm" :to="{name: 'admin-topics-id-edit', params: {id: item.id}}" :title="t('actions.edit')">
          <VespFa icon="edit" />
        </BButton>
        <BButton size="sm" variant="danger" :title="t('actions.delete')" @click="table.delete(item)">
          <VespFa icon="times" />
        </BButton>
      </template>-->
    </VespTable>
    <NuxtPage />
  </div>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'

const {t} = useI18n()
const table = ref()
const url = 'admin/topics'
const filters = ref({query: ''})
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
</script>
