<template>
  <div>
    <b-form-group :label="$t('models.user.username') + ' / ' + $t('models.user.email')">
      <b-form-input v-model.trim="record.username" required autofocus />
    </b-form-group>
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
