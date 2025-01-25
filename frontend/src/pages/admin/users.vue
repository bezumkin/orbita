<template>
  <div>
    <ChartCommon
      v-if="chartEnabled"
      name="users"
      endpoint="admin/users/stat"
      event="user"
      :formatter="(v: number) => formatBigNumber(v)"
      class="mb-5"
    />

    <VespTable ref="table" v-bind="{url, fields, filters, headerActions, tableActions, rowClass, sort, dir}">
      <template #header-middle>
        <BFormSelect v-model="filters.role_id" class="mt-2 mt-md-0" :options="roles" />
      </template>
      <template #cell(fullname)="{item}: any">
        <div class="d-flex align-items-center">
          <UserAvatar :user="item" size="40" />
          <div class="ms-2">
            <div class="text-nowrap">{{ item.fullname }}</div>
            <div class="small text-muted">{{ item.username }}</div>
          </div>
        </div>
      </template>
      <template #cell(role)="{value, item}: any">
        <div :style="{color: value.color}">{{ value.title }}</div>
        <div v-if="item.current_subscription" class="small" :style="{color: item.current_subscription.level?.color}">
          {{ item.current_subscription.level?.title }}
        </div>
      </template>
    </VespTable>

    <NuxtPage />
  </div>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'

const {$socket, $variables} = useNuxtApp()
const chartEnabled = $variables.value.CHART_USERS_DISABLE !== '1'

const {t} = useI18n()
const table = ref()
const url = 'admin/users'
const filters = ref({query: '', role_id: 0})
const sort = 'id'
const dir = 'desc'
const fields = computed(() => [
  {key: 'id', label: t('models.user.id'), sortable: true},
  {key: 'fullname', label: t('models.user.fullname')},
  {key: 'email', label: t('models.user.email'), sortable: true},
  {key: 'role', label: t('models.user.role')},
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
const roles = ref([{value: 0, text: t('models.user_role.filter.all')}])

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

onMounted(async () => {
  try {
    const data = await useGet('admin/user-roles', {combo: true})
    data.rows.forEach((item: any) => {
      roles.value.push({value: item.id, text: item.title})
    })
  } catch (e) {}

  $socket.on('user', () => {
    if (!filters.value.query && table.value.page === 1) {
      table.value.refresh()
    }
  })
})

onBeforeUnmount(() => {
  $socket.off('user')
})
</script>
