<template>
  <vesp-table ref="table" v-bind="{url, filters, sort, dir, fields, headerActions, tableActions, rowClass}">
    <template #cell(price)="{value}: any">
      {{ $price(value) }}
    </template>
    <template #cell(cover)="{value}: any">
      <b-img
        v-if="value?.id"
        :src="$image(value, {w: 100, h: 50, fit: 'crop'})"
        :srcset="$image(value, {w: 200, h: 100, fit: 'crop'}) + ' 2x'"
        width="100"
        height="50"
        class="rounded"
      />
    </template>

    <nuxt-page />
  </vesp-table>
</template>

<script setup lang="ts">
import type {VespTableAction} from '~/components/vesp/table.vue'

const {t} = useI18n()
const table = ref()
const url = 'admin/levels'
const filters = ref({query: ''})
const sort = 'price'
const dir = 'asc'
const fields = computed(() => [
  {key: 'id', label: t('models.level.id')},
  {key: 'cover', label: t('models.level.cover')},
  {key: 'title', label: t('models.level.title')},
  {key: 'price', label: t('models.level.price')},
])
const headerActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-levels-create'}, icon: 'plus', title: t('actions.create')},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {route: {name: 'admin-levels-id-edit'}, icon: 'edit', title: t('actions.edit')},
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function rowClass(item: any) {
  if (item) {
    const cls = []
    if (!item.active) {
      cls.push('inactive')
    }
    return cls
  }
}
</script>
