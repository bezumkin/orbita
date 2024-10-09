<template>
  <div>
    <BRow class="align-items-md-top">
      <BCol md="7">
        <BFormGroup :label="$t('models.page.name')">
          <BFormInput v-model="record.name" required autofocus />
        </BFormGroup>
      </BCol>
      <BCol md="5" class="mt-md-3 pt-md-4">
        <BFormCheckbox v-model="record.external" switch>
          {{ $t('models.page.external') }}
        </BFormCheckbox>
      </BCol>
    </BRow>

    <BRow class="mt-3 mt-md-0">
      <BCol md="7">
        <BFormGroup :label="$t('models.page.position')">
          <BFormSelect v-model="record.position" :options="positionOptions" />
        </BFormGroup>
      </BCol>
      <BCol md="5">
        <BFormGroup :label="$t('models.page.rank')">
          <BFormInput v-model="record.rank" type="number" min="0" />
        </BFormGroup>
      </BCol>
    </BRow>

    <template v-if="!record.external">
      <BFormGroup :label="$t('models.page.alias')" :description="$t('models.page.alias_desc')">
        <VespInputAlias v-model.trim="record.alias" :watch="!record.id ? record.name : ''" required />
      </BFormGroup>

      <BFormGroup :label="$t('models.page.title')" :description="$t('models.page.title_desc')">
        <BFormInput v-model="record.title" required />
      </BFormGroup>

      <BFormGroup :label="$t('models.page.content')">
        <EditorJs v-model="record.content" :blocks="editorBlocks" />
      </BFormGroup>
    </template>
    <template v-else>
      <BRow class="align-items-md-start">
        <BCol md="7">
          <BFormGroup :label="$t('models.page.link')" :description="$t('models.page.link_desc')">
            <BFormInput v-model.trim="record.link" required type="url" />
          </BFormGroup>
        </BCol>
        <BCol md="5" class="mt-md-3 pt-md-4">
          <BFormCheckbox v-model="record.blank">{{ $t('models.page.blank') }}</BFormCheckbox>
        </BCol>
      </BRow>
    </template>

    <BFormGroup>
      <BFormCheckbox v-model="record.active">{{ $t('models.page.active') }}</BFormCheckbox>
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
const editorBlocks = useNuxtApp().$variables.value.EDITOR_TOPIC_BLOCKS || false
</script>
