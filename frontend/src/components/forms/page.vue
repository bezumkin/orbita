<template>
  <div>
    <b-form-group :label="$t('models.page.title')">
      <b-form-input v-model.trim="record.title" required autofocus />
    </b-form-group>

    <b-form-group :label="$t('models.page.alias')">
      <vesp-input-alias v-model.trim="record.alias" :watch="record.title" required />
    </b-form-group>

    <b-row>
      <b-col md="6">
        <b-form-group :label="$t('models.page.position')">
          <b-form-select v-model="record.position" :options="positionOptions" />
        </b-form-group>
      </b-col>
      <b-col md="6">
        <b-form-group :label="$t('models.page.rank')">
          <b-form-input v-model="record.rank" type="number" min="0" />
        </b-form-group>
      </b-col>
    </b-row>

    <b-form-group :label="$t('models.page.content')">
      <editor-js v-model="record.content" :blocks="editorBlocks" />
    </b-form-group>

    <b-form-group>
      <b-form-checkbox v-model="record.active">
        {{ $t('models.page.active') }}
      </b-form-checkbox>
    </b-form-group>
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
const editorBlocks = String(useRuntimeConfig().public.EDITOR_TOPIC_BLOCKS).split(',') || []
</script>
