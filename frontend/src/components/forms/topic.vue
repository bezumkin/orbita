<template>
  <div>
    <b-row>
      <b-col md="8">
        <b-form-group :label="$t('models.topic.title')">
          <b-form-input v-model.trim="record.title" required autofocus />
        </b-form-group>
        <b-form-group :label="$t('models.topic.content')">
          <editor-js v-model="record.content" />
        </b-form-group>
      </b-col>
      <b-col md="4">
        <b-form-group :label="$t('models.topic.access.title')">
          <b-form-radio-group v-model="accessLevel" :options="accessOptions" />
        </b-form-group>
        <b-form-group v-if="['subscribers', 'sub_payments'].includes(accessLevel)" :label="$t('models.topic.level')">
          <b-form-select v-model="record.level_id" :options="levelOptions" required />
        </b-form-group>
        <b-form-group v-if="['payments', 'sub_payments'].includes(accessLevel)" :label="$t('models.topic.price')">
          <b-input-group>
            <template #prepend>
              <b-input-group-text>{{ $price(1).replace('1', '').trim() }}</b-input-group-text>
            </template>
            <b-form-input v-model="record.price" type="number" min="1" required />
          </b-input-group>
        </b-form-group>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="6">
        <b-form-group :label="$t('models.topic.cover')">
          <file-upload v-model="record.new_cover" :placeholder="record.cover" :height="200" />
        </b-form-group>
      </b-col>
      <b-col md="6">
        <b-form-group :label="$t('models.topic.teaser')">
          <b-form-textarea v-model="record.teaser" no-resize style="height: 200px" />
        </b-form-group>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="6">
        <b-form-group>
          <b-form-checkbox v-model="record.active">
            {{ $t('models.topic.active') }}
          </b-form-checkbox>
        </b-form-group>
      </b-col>
      <b-col md="6">
        <b-form-group>
          <b-form-checkbox v-model="record.closed">
            {{ $t('models.topic.closed') }}
          </b-form-checkbox>
        </b-form-group>
      </b-col>
    </b-row>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespTopic>,
    required: true,
  },
})
const emit = defineEmits(['update:modelValue'])
const record = computed({
  get() {
    return props.modelValue
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})

const {t} = useI18n()
const {$socket, $price} = useNuxtApp()
const {data, refresh} = useGet('admin/levels', {combo: true, limit: 0})
const levels: Ref<VespLevel[]> = computed(() => data.value?.rows || [])
const levelOptions = computed(() => {
  const options: Record<string, any> = []
  levels.value?.forEach((i: VespLevel) => {
    options.push({value: i.id, text: i.title + ', ' + $price(i.price), disabled: !i.active})
  })
  return options
})
const accessOptions = [
  {value: 'free', text: t('models.topic.access.free')},
  {value: 'subscribers', text: t('models.topic.access.subscribers')},
  {value: 'sub_payments', text: t('models.topic.access.sub_payments')},
  {value: 'payments', text: t('models.topic.access.payments')},
]

const accessLevel = ref('free')
if (record.value.id) {
  if (record.value.level_id && record.value.price) {
    accessLevel.value = 'sub_payments'
  } else if (record.value.level_id) {
    accessLevel.value = 'subscribers'
  } else if (record.value.price) {
    accessLevel.value = 'payments'
  }
}

watch(accessLevel, (value: string) => {
  if (value === 'free') {
    record.value.level_id = undefined
    record.value.price = undefined
  } else if (value === 'subscribers') {
    record.value.price = undefined
    if (levels.value.length) {
      record.value.level_id = levels.value[0].id
    }
  } else if (value === 'payments') {
    record.value.level_id = undefined
    record.value.price = 0
  } else if (value === 'sub_payments' && levels.value.length) {
    record.value.level_id = levels.value[0].id
    record.value.price = 0
  }
})

onMounted(() => {
  $socket.on('levels', refresh)
})

onUnmounted(() => {
  $socket.off('levels', refresh)
})
</script>
