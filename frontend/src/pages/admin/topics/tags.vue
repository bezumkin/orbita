<template>
  <VespModal size="lg" no-footer :title="$t('models.tag.title_many')">
    <VespTable ref="table" v-bind="{url, fields, tableActions, limit, filters, sort, dir}">
      <template #cell(title)="{item}">
        <BForm @submit.prevent="onSave">
          <BFormInput v-if="editingTag && editingTag?.id === item.id" v-model="editingTag.title" size="sm" autofocus />
          <template v-else>{{ item.title }}</template>
        </BForm>
      </template>
      <template #header-start>
        <BOverlay :show="loading" opacity="0.5">
          <BForm @submit.prevent="onAdd">
            <BInputGroup>
              <BFormInput v-model="newTag" :placeholder="$t('actions.add')" />
              <template #append>
                <BButton :disabled="!canAdd" @click="onAdd"><VespFa icon="check" class="fa-fw" /></BButton>
              </template>
            </BInputGroup>
          </BForm>
        </BOverlay>
      </template>
    </VespTable>
  </VespModal>
</template>

<script setup lang="ts">
import type {VespTableAction} from '@vesp/frontend'

const {t} = useI18n()
const url = 'admin/tags'
const limit = 20
const filters = ref({query: ''})
const sort = ref('id')
const dir = ref('desc')

const fields = computed(() => [
  {key: 'id', label: t('models.tag.id'), sortable: true},
  {key: 'title', label: t('models.tag.title_one'), sortable: true},
  {key: 'topics_count', label: t('models.topic.title_many'), sortable: true},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {
    function: (i: any) => editTag(i),
    icon: 'edit',
    title: t('actions.edit'),
    isActive: (i: any) => {
      return editingTag.value?.id !== i.id
    },
  },
  {
    function: onSave,
    icon: 'check',
    title: t('actions.submit'),
    variant: 'success',
    isActive: (i: any) => {
      return editingTag.value?.id === i.id
    },
  },
  {
    function: (i: any) => table.value.delete(i),
    icon: 'times',
    title: t('actions.delete'),
    variant: 'danger',
    isActive: (i: any) => {
      return editingTag.value?.id !== i.id
    },
  },
  {
    function: onCancel,
    icon: 'times',
    title: t('actions.cancel'),
    isActive: (i: any) => {
      return editingTag.value?.id === i.id
    },
  },
])

const table = ref()
const newTag = ref('')
const canAdd = computed(() => {
  return newTag.value.length > 0
})
const editingTag: Ref<VespTag | undefined> = ref()
const loading = ref(false)

function editTag(tag: VespTag) {
  editingTag.value = tag
}

async function onAdd() {
  loading.value = true
  try {
    await usePut(url, {title: newTag.value})
    newTag.value = ''
    table.value.refresh()
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function onSave() {
  table.value.loading = true
  try {
    await usePatch(url, editingTag.value)
    editingTag.value = undefined
    table.value.refresh()
  } catch (e) {
  } finally {
    table.value.loading = false
  }
}

function onCancel() {
  editingTag.value = undefined
}
</script>
