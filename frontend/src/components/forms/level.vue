<template>
  <div>
    <BRow>
      <BCol md="8">
        <BFormGroup :label="$t('models.level.title')">
          <BFormInput v-model="record.title" required autofocus />
        </BFormGroup>
      </BCol>
      <BCol md="4">
        <BFormGroup :label="$t('models.level.price')">
          <BFormInput v-model="record.price" type="number" min="0" />
        </BFormGroup>
      </BCol>
    </BRow>

    <BFormGroup :label="$t('models.level.cover')">
      <FileUpload v-model="record.new_cover" :placeholder="record.cover" :height="200" />
    </BFormGroup>

    <BFormGroup :label="$t('models.level.content')">
      <BFormTextarea v-model="record.content" rows="5" />
    </BFormGroup>

    <BFormGroup>
      <BFormCheckbox v-model="record.active">
        {{ $t('models.level.active') }}
      </BFormCheckbox>
    </BFormGroup>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespLevel>,
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
</script>
