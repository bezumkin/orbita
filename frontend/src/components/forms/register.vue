<template>
  <div>
    <b-form-group :label="$t('models.user.username')">
      <b-form-input v-model.trim="record.username" required autofocus />
    </b-form-group>

    <b-form-group :label="$t('models.user.fullname')">
      <b-form-input v-model.trim="record.fullname" required />
    </b-form-group>

    <b-form-group :label="$t('models.user.email')">
      <b-form-input v-model.trim="record.email" type="email" required />
    </b-form-group>

    <b-form-group :label="$t('models.user.password')">
      <vesp-input-password v-model.trim="record.password" />
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
    return props.modelValue || {username: '', fullname: '', email: '', password: ''}
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})
</script>
