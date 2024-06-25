<template>
  <div>
    <BFormGroup :label="$t('models.page.title')">
      <BFormInput v-model.trim="record.title" required autofocus />
    </BFormGroup>

    <BFormGroup :label="$t('models.page.alias')">
      <VespInputAlias v-model.trim="record.alias" :watch="record.title" required />
    </BFormGroup>

    <BRow>
      <BCol md="6">
        <BFormGroup :label="$t('models.page.position')">
          <BFormSelect v-model="record.position" :options="positionOptions" />
        </BFormGroup>
      </BCol>
      <BCol md="6">
        <BFormGroup :label="$t('models.page.rank')">
          <BFormInput v-model="record.rank" type="number" min="0" />
        </BFormGroup>
      </BCol>
    </BRow>

    <BFormGroup :label="$t('models.page.content')">
      <EditorJs v-model="record.content" :blocks="editorBlocks" />
    </BFormGroup>

    <BFormGroup>
      <BFormCheckbox v-model="record.active">
        {{ $t('models.page.active') }}
      </BFormCheckbox>
    </BFormGroup>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespPage>,
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
const positionOptions = [
  {value: 'both', text: t('models.page.position_both')},
  {value: 'header', text: t('models.page.position_header')},
  {value: 'footer', text: t('models.page.position_footer')},
  {value: null, text: t('models.page.position_null')},
]
const editorBlocks = String(useRuntimeConfig().public.EDITOR_TOPIC_BLOCKS)
</script>
