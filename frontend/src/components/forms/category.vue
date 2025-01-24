<template>
  <div>
    <BFormGroup :label="$t('models.category.title')">
      <BFormInput v-model.trim="record.title" required autofocus />
    </BFormGroup>

    <BFormGroup :label="$t('models.category.uri')" :description="previewUri">
      <VespInputAlias v-model.trim="record.uri" :watch="!record.id ? record.title : undefined" required />
    </BFormGroup>

    <BFormGroup :label="$t('models.category.description')">
      <BFormTextarea v-model="record.description" rows="3" />
    </BFormGroup>

    <BFormCheckbox v-model="record.active">
      {{ $t('models.category.active') }}
    </BFormCheckbox>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespCategory>,
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

const {$variables} = useNuxtApp()
const previewUri = computed(() => {
  return record.value.uri ? [$variables.value.SITE_URL.slice(0, -1), record.value.uri].join('/') : ''
})
</script>
