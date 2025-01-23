<template>
  <VespTable ref="table" v-bind="{url, fields, tableActions, rowClass, sort, dir}">
    <template #cell(user)="{value}: any">
      <BLink v-if="$scope('users/get')" :to="getUserLink(value)">{{ value.fullname }}</BLink>
      <template v-else>{{ value.fullname }}</template>
    </template>
    <template #cell(topic)="{value}: any">
      <BLink :to="getTopicLink(value)" target="_blank">{{ value.title }}</BLink>
    </template>
    <template #cell(preview)="{item}: any">
      <BLink v-if="item.comment" :to="getCommentLink(item.topic, item.comment)" class="small" target="_blank">
        {{ $contentPreview(item.comment.content, 50) }}
      </BLink>
      <div v-else-if="item.topic">
        <BLink :to="getTopicLink(item.topic)" target="_blank">
          {{ item.topic.title }}
        </BLink>
        <div class="small">{{ $contentPreview(item.topic.content, 30) }}</div>
      </div>
    </template>
  </VespTable>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'

const url = 'admin/notifications'
const {$contentPreview} = useNuxtApp()
const {t} = useI18n()
const sort = 'created_at'
const dir = 'desc'
const table = ref()

const fields = computed(() => [
  {key: 'created_at', label: t('models.user_notification.created_at'), formatter: formatDate, sortable: true},
  {key: 'type', label: t('models.user_notification.type.title'), formatter: formatType},
  {key: 'user', label: t('models.user_notification.user')},
  {key: 'preview', label: t('models.user_notification.preview')},
  {key: 'sent_at', label: t('models.user_notification.sent_at'), formatter: formatDate, sortable: true},
])

const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  // {function: (i: any) => send(i), icon: 'paper-plane', title: t('actions.submit'), isActive: (i: any) => i && !i.sent},
  // {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function rowClass(item: any) {
  return item && !item.active ? 'inactive' : undefined
}

function formatType(value: any) {
  return value ? t('models.user_notification.type.' + value) : ''
}

function getUserLink(user: VespUser) {
  return {name: 'admin-users-id-edit', params: {id: user.id}}
}

function getTopicLink(topic: VespTopic) {
  return {name: 'topics-uuid', params: {uuid: topic.uuid}}
}

function getCommentLink(topic: VespTopic, comment: VespComment) {
  return {...getTopicLink(topic), hash: '#comment-' + comment.id}
}

/* async function send(item: any) {
  try {
    await usePost(url + '/' + item.id)
    table.value.refresh()
  } catch (e) {}
} */
</script>
