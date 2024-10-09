<template>
  <div>
    <BFormGroup :label="$t('models.user.username')">
      <BFormInput v-model.trim="record.username" required autofocus />
    </BFormGroup>

    <BFormGroup :label="$t('models.user.fullname')">
      <BFormInput v-model="record.fullname" required />
    </BFormGroup>

    <BFormGroup :label="$t('models.user.email')">
      <BFormInput v-model.trim="record.email" type="email" required />
    </BFormGroup>

    <BFormGroup :label="$t('models.user.password')">
      <VespInputPassword v-model.trim="record.password" />
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
    return props.modelValue || {username: '', fullname: '', email: '', password: ''}
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})
</script>
