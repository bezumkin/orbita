<template>
  <div>
    <VespTable ref="table" v-bind="{url, fields, filters, headerActions, tableActions, rowClass, sort, dir}">
      <template #cell(fullname)="{item}: any">
        <div class="d-flex align-items-center">
          <UserAvatar :user="item" size="40" />
          <div class="ms-2">
            <div class="text-nowrap">{{ item.fullname }}</div>
            <div class="small text-muted">{{ item.username }}</div>
          </div>
        </div>
      </template>
    </VespTable>

    <NuxtPage />
  </div>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'

const {t, d} = useI18n()
const table = ref()
const url = 'admin/users'
const filters = ref({query: ''})
const sort = 'id'
const dir = 'desc'
const fields = computed(() => [
  {key: 'id', label: t('models.user.id'), sortable: true},
  {key: 'fullname', label: t('models.user.fullname')},
  {key: 'email', label: t('models.user.email'), sortable: true},
  {key: 'created_at', label: t('models.user.created_at'), sortable: true, formatter: formatDate},
  {key: 'active_at', label: t('models.user.active_at'), sortable: true, formatter: formatDate},
])
const headerActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-users-create'}, icon: 'plus', title: t('actions.create')},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-users-id-edit'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function formatDate(value: any) {
  return value ? d(value, 'long') : ''
}

function rowClass(item: any) {
  if (item) {
    const cls = []
    if (!item.active) {
      cls.push('inactive')
    }
    if (item.blocked) {
      cls.push('blocked')
    }
    return cls
  }
}
</script>
