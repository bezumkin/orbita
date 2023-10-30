<template>
  <div>
    <b-form-group :label="$t('models.user_role.title')">
      <b-form-input v-model.trim="record.title" required autofocus />
    </b-form-group>

    <b-form-group :label="$t('models.user_role.scope')">
      <b-form-tags v-model.trim="record.scope" remove-on-delete placeholder="" :add-button-text="$t('actions.add')" />
    </b-form-group>
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
