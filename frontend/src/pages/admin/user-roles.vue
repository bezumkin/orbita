<template>
  <div>
    <vesp-table ref="table" v-bind="{url, fields, filters, headerActions, tableActions, sort, dir}" />
    <nuxt-page />
  </div>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'

const {t} = useI18n()
const table = ref()
const url = 'admin/user-roles'
const filters = ref({query: ''})
const sort = 'id'
const dir = 'asc'
const fields = computed(() => [
  {key: 'id', label: t('models.user_role.id'), sortable: true},
  {key: 'title', label: t('models.user_role.title'), sortable: true},
  {key: 'scope', label: t('models.user_role.scope'), formatter: formatTags},
  {key: 'users_count', label: t('models.user_role.users'), sortable: true},
])
const headerActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-user-roles-create'}, icon: 'plus', title: t('actions.create')},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-user-roles-id-edit'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function formatTags(value?: any) {
  return value ? value.join(', ') : ''
}
</script>
