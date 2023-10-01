<template>
  <vesp-table ref="table" v-bind="{url, fields, filters, headerActions, tableActions, rowClass, sort, dir}">
    <template #cell(avatar)="{value}: any">
      <b-img
        v-if="value?.id"
        :src="$image(value, {w: 50, h: 50, fit: 'crop'})"
        :srcset="$image(value, {w: 100, h: 100, fit: 'crop'}) + ' 2x'"
        width="50"
        height="50"
        class="rounded-circle"
      />
    </template>

    <nuxt-page />
  </vesp-table>
</template>

<script setup lang="ts">
import {VespTableAction} from '~/components/vesp/table.vue'

if (!hasScope('users/get')) {
  showError({statusCode: 403, statusMessage: 'Access Denied'})
}

const {t} = useI18n()
const table = ref()
const url = 'admin/users'
const filters = ref({query: ''})
const sort = 'id'
const dir = 'desc'
const fields = computed(() => [
  {key: 'id', label: t('models.user.id'), sortable: true},
  {key: 'avatar', label: ''},
  {key: 'username', label: t('models.user.username'), sortable: true},
  {key: 'fullname', label: t('models.user.fullname'), sortable: true},
  {key: 'email', label: t('models.user.email'), sortable: true},
])
const headerActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-users-create'}, icon: 'plus', title: t('actions.create')},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-users-id-edit'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

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

useHead({
  title: () => [t('pages.admin.users'), t('pages.admin.title'), t('project')].join(' / '),
})
</script>
