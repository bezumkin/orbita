<template>
  <div>
    <BFormGroup :label="$t('models.user_role.title')">
      <BFormInput v-model.trim="record.title" required autofocus />
    </BFormGroup>

    <BFormGroup :label="$t('models.user_role.scope')">
      <BFormTags v-model.trim="record.scope" remove-on-delete placeholder="" :add-button-text="$t('actions.add')" />
    </BFormGroup>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespUserRole>,
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
