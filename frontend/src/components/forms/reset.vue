<template>
  <div>
    <BFormGroup :label="$t('models.user.username') + ' / ' + $t('models.user.email')">
      <BFormInput v-model.trim="record.username" required autofocus />
    </BFormGroup>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
})
const emit = defineEmits(['update:modelValue'])
const record = computed({
  get() {
    return props.modelValue || {username: ''}
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})
</script>
