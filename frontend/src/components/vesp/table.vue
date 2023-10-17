<template>
  <section class="vesp-table">
    <slot name="header">
      <b-row class="align-items-center mb-3">
        <b-col md="4">
          <slot name="header-start">
            <b-button
              v-for="(action, idx) in headerActions"
              :key="idx"
              :size="action.size || 'md'"
              :variant="action.variant"
              :class="action.class || (!idx ? 'col-12 col-md-auto' : 'col-12 col-md-auto ms-md-2 mt-2 mt-md-0')"
              :to="action.route"
              @click="action.function"
            >
              <fa v-if="action.icon" :icon="action.icon" /> {{ action.title }}
            </b-button>
          </slot>
        </b-col>

        <b-col md="4">
          <slot name="header-middle"></slot>
        </b-col>

        <b-col md="4" class="mt-2 mt-md-0">
          <slot name="header-end">
            <b-input-group v-if="hasQuery">
              <template #append>
                <b-button :disabled="!tFilters.query" @click="tFilters.query = null">
                  <fa icon="times" />
                </b-button>
              </template>
              <b-form-input v-model="tFilters.query" :placeholder="t('components.table.query')" />
            </b-input-group>
          </slot>
        </b-col>
      </b-row>
    </slot>

    <b-table
      :items="items"
      :fields="tFields"
      :class="tableClass"
      :per-page="tLimit"
      :sort-by="tSort"
      :sort-desc="tDir === 'desc'"
      :empty-text="t(emptyText)"
      :empty-filtered-text="t(emptyFilteredText)"
      :stacked="stacked"
      :responsive="responsive"
      :show-empty="showEmpty"
      :sort-internal="false"
      :busy="loading"
      :tbody-tr-class="rowClass"
      @sorted="onSort"
    >
      <template #cell(actions)="{item}">
        <template v-for="(action, idx) in tableActions">
          <b-button
            v-if="typeof action.isActive !== 'function' || action.isActive(item) === true"
            :key="idx"
            :size="action.size || 'sm'"
            :variant="action.variant"
            :class="action.class"
            :to="mapRouteParams(action, item)"
            @click="onClick(action, item)"
          >
            <fa v-if="action.icon" :icon="action.icon" />
            <template v-else>{{ action.title }}</template>
          </b-button>
        </template>
      </template>

      <template v-for="slotName in Object.keys($slots)" #[slotName]="slotProps">
        <slot v-if="slotName !== 'default'" :name="slotName" v-bind="slotProps" />
      </template>
    </b-table>

    <slot name="footer" v-bind="{update: loadTable, total, page: tPage, limit, loading}">
      <b-row class="mt-5 align-items-center justify-content-center justify-content-md-start gap-3" no-gutters>
        <b-col cols="12" md="auto" class="d-flex justify-content-center">
          <slot name="pagination" v-bind="{update: loadTable, total, page: tPage, limit, loading}">
            <b-pagination
              v-if="total > limit"
              v-model="tPage"
              :total-rows="total"
              :per-page="limit"
              :limit="pageLimit"
              class="m-0"
            />
          </slot>
        </b-col>
        <b-col cols="12" md="auto" class="d-flex align-items-center justify-content-center gap-2">
          <slot name="pagination-data" v-bind="{update: loadTable, total, page: tPage, limit, loading}">
            <b-button @click="loadTable">
              <b-spinner v-if="loading" :small="true" />
              <fa v-else icon="repeat" />
            </b-button>
            {{ t('components.table.records', {total}, total) }}
          </slot>
        </b-col>
      </b-row>
    </slot>

    <slot name="default" />

    <slot name="delete" v-bind="deleteProps">
      <b-modal v-model="deleteVisible" centered hide-footer hide-header @hidden="deleting = {}">
        <b-overlay :opacity="0.5" :show="deleteLoading">
          <slot name="delete-content" v-bind="deleteProps">
            <div class="text-center">
              <h5 class="mt-4 mb-0">{{ $t(deleteTitle) }}</h5>
              <div class="mt-4">{{ $t(deleteText) }}</div>
            </div>
          </slot>
          <slot name="delete-footer" v-bind="deleteProps">
            <div class="d-flex justify-content-between mt-4">
              <b-button @click="deleteVisible = false">{{ $t('actions.cancel') }}</b-button>
              <b-button variant="danger" @click="deleteItem">{{ $t('actions.delete') }}</b-button>
            </div>
          </slot>
        </b-overlay>
      </b-modal>
    </slot>
  </section>
</template>

<script setup lang="ts">
import {BaseSize, Breakpoint, TableFieldObject, BaseButtonVariant} from 'bootstrap-vue-next/src/types'
import {RouteLocationNamedRaw} from 'vue-router'
import {TableItem} from 'bootstrap-vue-next'

export type VespTableAction = {
  size?: keyof BaseSize
  variant?: keyof BaseButtonVariant
  class?: String | Array<string> | Object
  route?: RouteLocationNamedRaw
  function?: Function
  icon?: String | Array<string>
  title?: String
  map?: Record<string, string>
  key?: string
  isActive?: Function
}

export type VespTableOnLoad = (data: {total: number; rows: any[]; [key: string]: any}) => {
  total: number
  rows: any[]
  [key: string]: any
}

// export type VespTableGetActions = (actions: VespTableAction[], item: Record<string, any>) => VespTableAction[]

const emit = defineEmits(['update:modelValue', 'update:sort', 'update:dir', 'update:limit', 'update:filters', 'delete'])
const props = defineProps({
  modelValue: {
    type: Number,
    default: null,
  },
  url: {
    type: String,
    required: true,
  },
  fields: {
    type: Array as PropType<TableFieldObject[]>,
    default() {
      return []
    },
  },
  filters: {
    type: Object as PropType<Record<string, any>>,
    default() {
      return {}
    },
  },
  updateKey: {
    type: String,
    default: '',
  },
  primaryKey: {
    type: [String, Array],
    default: 'id',
  },
  sort: {
    type: String,
    default: null,
  },
  dir: {
    type: String,
    default: 'asc',
  },
  limit: {
    type: Number,
    default: 20,
  },
  stacked: {
    type: String as PropType<Breakpoint>,
    default: undefined,
  },
  responsive: {
    type: Boolean,
    default: true,
  },
  showEmpty: {
    type: Boolean,
    default: true,
  },
  headerActions: {
    type: Array as PropType<VespTableAction[]>,
    default() {
      return []
    },
  },
  tableActions: {
    type: Array as PropType<VespTableAction[]>,
    default() {
      return []
    },
  },
  rowClass: {
    type: Function as PropType<(item: TableItem | null, type: string) => string | any[] | null | undefined>,
    default() {
      return ''
    },
  },
  tableClass: {
    type: [String, Array, Object],
    default: 'mt-3 mt-md-0',
  },
  pageLimit: {
    type: Number,
    default: 7,
  },
  onLoad: {
    type: Function as PropType<VespTableOnLoad>,
    default(data: any) {
      return data
    },
  },
  /* getTableActions: {
    type: Function as PropType<VespTableGetActions>,
    default(data: any) {
      return data
    },
  }, */
  emptyText: {
    type: String,
    default: 'components.table.no_data',
  },
  emptyFilteredText: {
    type: String,
    default: 'components.table.no_results',
  },
  deleteTitle: {
    type: String,
    default: 'components.table.delete.title',
  },
  deleteText: {
    type: String,
    default: 'components.table.delete.confirm',
  },
})

const {t} = useI18n()
const internalValue = ref(1)
const loading = ref(false)
const total = ref(0)
const items = ref([])
const tSort = ref(props.sort)
const tDir = ref(props.dir)
const tLimit = ref(props.limit)
const tFilters = ref(props.filters)
const tPage = computed({
  get() {
    return props.modelValue === null ? internalValue.value : props.modelValue
  },
  set(newValue) {
    internalValue.value = newValue
    emit('update:modelValue', newValue)
  },
})
const tFields = computed(() => {
  const fields = [...props.fields]
  if (props.tableActions.length && fields.findIndex((item) => item.key === 'actions') === -1) {
    fields.push({key: 'actions', label: '', class: 'actions'})
  }
  return fields
})
const hasQuery = computed(() => {
  return Object.keys(tFilters.value).includes('query')
})
const updateKey = props.updateKey || props.url.split('/').join('-')

const deleteVisible = ref(false)
const deleteLoading = ref(false)
const deleting: Ref<Record<any, any>> = ref({})
const deleteProps = {item: deleting, visible: deleteVisible, loading: deleteLoading, deleteItem}

async function loadTable() {
  loading.value = true
  try {
    const params: Record<string, any> = {
      limit: props.limit,
      page: tPage.value,
      ...getParams(true),
    }
    const data = props.onLoad(await useGet(props.url, params))
    total.value = data.total || 0
    items.value = (data.rows as []) || []
  } catch (e) {
    return Promise.reject(e)
  } finally {
    loading.value = false
  }
}

function onSort(field: string, desc: boolean) {
  tSort.value = field
  tDir.value = desc ? 'desc' : 'asc'
  loadTable()
}

function mapRouteParams(action: VespTableAction, item: Record<string, any>): RouteLocationNamedRaw | undefined {
  if (!action.route) {
    return undefined
  }
  if (!action.map) {
    action.map = {}
    if (typeof props.primaryKey === 'string') {
      action.map[props.primaryKey] = props.primaryKey
    } else {
      props.primaryKey.forEach((i: any) => {
        // @ts-ignore
        action.map[i] = i
      })
    }
  }
  const params: Record<string, any> = {}
  for (const key of Object.keys(action.map)) {
    const val = action.map[key]
    if (/\./.test(val)) {
      const keys = val.split('.')
      let local = {...item}
      for (const i of keys) {
        local = local[i]
      }
      params[key] = local
    } else {
      params[key] = item[val]
    }
  }
  return {...action.route, params}
}

function onClick(action: VespTableAction, item: Record<string, any>) {
  if (action.function) {
    action.function(JSON.parse(JSON.stringify(item)))
  }
}

function getParams(asObject = false) {
  const params: Record<string, any> = {}
  Object.keys(props.filters).forEach((i) => {
    if (props.filters[i] !== '' && props.filters[i] !== null) {
      params[i] =
        typeof props.filters[i] === 'object' && !asObject ? JSON.stringify(props.filters[i]) : props.filters[i]
    }
  })
  if (tSort.value) {
    params.sort = tSort.value
    params.dir = tDir.value
  }

  return asObject ? JSON.parse(JSON.stringify(params)) : new URLSearchParams(params).toString()
}

function onDelete(item: any) {
  deleteVisible.value = true
  deleting.value = item
}
async function deleteItem() {
  deleteLoading.value = true
  const item = deleting.value
  try {
    if (typeof props.primaryKey === 'string' && item[props.primaryKey]) {
      await useDelete(props.url + '/' + item[props.primaryKey])
    } else if (Array.isArray(props.primaryKey)) {
      const params: Record<string, any> = {}
      props.primaryKey.forEach((i: any) => {
        params[i] = item[i]
      })
      await useDelete(props.url, params)
    } else {
      await useDelete(props.url, item)
    }
    deleteVisible.value = false
    await loadTable()
  } catch (e) {
  } finally {
    deleteLoading.value = false
  }
}

defineExpose({getParams, page: tPage, sort: tSort, dir: tDir, loading, delete: onDelete, refresh: loadTable, items})

provide('refreshVespTable', (key: string) => {
  if (key.startsWith(updateKey)) {
    loadTable()
  }
})

watch(tPage, loadTable)
watch(
  tFilters,
  () => {
    if (tPage.value !== 1) {
      tPage.value = 1
    }
    loadTable()
  },
  {deep: true},
)

onMounted(() => {
  loadTable()
})
</script>
