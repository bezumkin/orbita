<template>
  <div>
    <BFormGroup :label="$t('models.video.title')">
      <BFormInput v-model.trim="record.title" required autofocus />
    </BFormGroup>

    <BFormGroup :label="$t('models.video.description')">
      <BFormTextarea v-model.trim="record.description" />
    </BFormGroup>

    <BFormGroup :label="$t('models.video.image')">
      <BFormGroup>
        <FileUpload
          v-model="record.new_image"
          :placeholder="record.image"
          :height="400"
          :allow-removing="false"
          wrapper-class="rounded border"
        />
      </BFormGroup>
    </BFormGroup>

    <BFormGroup>
      <BFormCheckbox v-model="record.active">
        {{ $t('models.video.active') }}
      </BFormCheckbox>
    </BFormGroup>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespVideo>,
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
