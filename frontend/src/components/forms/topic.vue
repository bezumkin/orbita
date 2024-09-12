<template>
  <div>
    <BFormGroup :label="$t('models.topic.title')">
      <BFormInput v-model.trim="record.title" required autofocus />
    </BFormGroup>
    <BFormGroup :label="$t('models.topic.content')">
      <EditorJs v-model="record.content" :blocks="editorBlocks" />
    </BFormGroup>

    <BRow>
      <BCol md="6">
        <BFormGroup :label="$t('models.topic.access.title')">
          <BFormRadioGroup v-model="accessLevel" :options="accessOptions" stacked />
        </BFormGroup>
      </BCol>
      <BCol md="6">
        <BFormGroup v-if="['subscribers', 'sub_payments'].includes(accessLevel)" :label="$t('models.topic.level')">
          <BFormSelect v-model="record.level_id" :options="levelOptions" required />
        </BFormGroup>
        <BFormGroup v-if="['payments', 'sub_payments'].includes(accessLevel)" :label="$t('models.topic.price')">
          <BInputGroup>
            <template #prepend>
              <BInputGroupText>{{ $price(1).replace('1', '').trim() }}</BInputGroupText>
            </template>
            <BFormInput v-model="record.price" type="number" min="1" required />
          </BInputGroup>
        </BFormGroup>
      </BCol>
    </BRow>

    <BRow>
      <BCol md="6">
        <BFormGroup :label="$t('models.topic.cover')">
          <FileUpload v-model="record.new_cover" :placeholder="record.cover" :height="200" />
        </BFormGroup>
      </BCol>
      <BCol md="6">
        <BFormGroup :label="$t('models.topic.teaser')">
          <BFormTextarea v-model="record.teaser" no-resize style="height: 200px" />
        </BFormGroup>
      </BCol>
    </BRow>

    <BFormGroup :label="$t('models.tag.title_many')">
      <TopicTags v-model="record.tags" />
    </BFormGroup>

    <BFormGroup v-if="changeAuthor" :label="$t('models.topic.author')" :description="$t('models.topic.author_desc')">
      <VespInputComboBox
        v-model="record.user_id"
        url="admin/users"
        text-field="fullname"
        :format-value="formatUser"
        sort="id"
        dir="asc"
      />
    </BFormGroup>

    <BRow>
      <BCol md="6">
        <BFormGroup>
          <BFormCheckbox v-model="record.active">
            {{ $t('models.topic.active') }}
          </BFormCheckbox>
        </BFormGroup>
      </BCol>
      <BCol md="6">
        <BFormGroup>
          <BFormCheckbox v-model="record.closed">
            {{ $t('models.topic.closed') }}
          </BFormCheckbox>
        </BFormGroup>
      </BCol>
    </BRow>

    <BRow v-if="!record.published_at" class="align-items-center">
      <BCol md="6">
        <BFormGroup class="py-md-2">
          <BFormCheckbox v-model="delayed" :disabled="record.active">
            {{ $t('models.topic.delayed') }}
          </BFormCheckbox>
        </BFormGroup>
      </BCol>
      <BCol md="6">
        <Transition name="fade">
          <BFormGroup v-if="delayed">
            <VespInputDatePicker v-model="record.publish_at" type="datetime" required>
              {{ $t('models.topic.publish_at') }}
            </VespInputDatePicker>
          </BFormGroup>
        </Transition>
      </BCol>
    </BRow>
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
const {$socket, $price, $scope, $variables} = useNuxtApp()
const levels: Ref<VespLevel[]> = ref([])
const levelOptions = computed(() => {
  const options: Record<string, any> = []
  levels.value.forEach((i: VespLevel) => {
    options.push({value: i.id, text: i.title + ', ' + $price(i.price), disabled: !i.active})
  })
  return options
})
const accessOptions = computed(() => {
  return [
    {value: 'free', text: t('models.topic.access.free')},
    {value: 'subscribers', text: t('models.topic.access.subscribers')},
    {value: 'sub_payments', text: t('models.topic.access.sub_payments')},
    {value: 'payments', text: t('models.topic.access.payments')},
  ]
})
const delayed = ref(props.modelValue.publish_at !== null)

const editorBlocks = $variables.value.EDITOR_TOPIC_BLOCKS || false
const changeAuthor = $scope('users/get') && $variables.value.TOPICS_CHANGE_AUTHOR === '1'

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
    record.value.level_id = 0
    record.value.price = 0
  } else if (value === 'subscribers') {
    record.value.price = 0
    if (levels.value.length) {
      record.value.level_id = levels.value[0].id
    }
  } else if (value === 'payments') {
    record.value.level_id = 0
    record.value.price = 0
  } else if (value === 'sub_payments' && levels.value.length) {
    record.value.level_id = levels.value[0].id
    record.value.price = 0
  }
})

async function loadLevels() {
  const {rows} = await useGet('admin/levels', {combo: true, limit: 0})
  levels.value = rows
}

function formatUser(user: VespUser) {
  return `${user.id}. ${user.fullname} (${user.username})`
}

onMounted(() => {
  $socket.on('level-create', loadLevels)
  $socket.on('level-update', loadLevels)
  loadLevels()
})

onUnmounted(() => {
  $socket.off('level-create', loadLevels)
  $socket.off('level-update', loadLevels)
})

watch(
  () => record.value.active,
  (newValue) => {
    if (newValue) {
      delayed.value = false
    }
  },
)
</script>
