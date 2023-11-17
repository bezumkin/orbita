<template>
  <vesp-table ref="table" v-bind="{url, fields, tableActions, rowClass, sort, dir}">
    <template #cell(user)="{value}: any">
      <b-link :to="getUserLink(value)">{{ (value as VespUser).fullname }}</b-link>
    </template>
    <template #cell(topic)="{value}: any">
      <b-link :to="getTopicLink(value)" target="_blank">{{ value.title }}</b-link>
    </template>
    <template #cell(comment)="{item, value}: any">
      <b-link :to="getCommentLink(item.topic, value)" target="_blank">{{ $contentPreview(value.content) }}</b-link>
    </template>
  </vesp-table>
</template>

<script setup lang="ts">
import type {VespTableAction} from '~/components/vesp/table.vue'

const url = 'admin/notifications'
const {$contentPreview} = useNuxtApp()
const {t, d} = useI18n()
const sort = 'created_at'
const dir = 'desc'
const table = ref()

const fields = computed(() => [
  {key: 'created_at', label: t('models.user_notification.created_at'), formatter: formatDate, sortable: true},
  {key: 'user', label: t('models.user_notification.user')},
  {key: 'topic', label: t('models.user_notification.topic')},
  {key: 'comment', label: t('models.user_notification.comment'), class: 'small'},
  {key: 'sent_at', label: t('models.user_notification.sent_at'), formatter: formatDate, sortable: true},
])

const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {function: (i: any) => send(i), icon: 'paper-plane', title: t('actions.submit'), isActive: (i: any) => i && !i.sent},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function rowClass(item: any) {
  return item && !item.active ? 'text-muted' : undefined
}

function formatDate(value: any) {
  return value ? d(value, 'long') : ''
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

async function send(item: any) {
  try {
    await useApi(url + '/' + item.id, {method: 'POST'})
    table.value.refresh()
  } catch (e) {}
}
</script>
