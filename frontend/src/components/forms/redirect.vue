<template>
  <div>
    <BRow>
      <BCol md="8">
        <BFormGroup :label="$t('models.redirect.title')">
          <BFormInput v-model="record.title" />
        </BFormGroup>
      </BCol>
      <BCol md="4">
        <BFormGroup :label="$t('models.redirect.rank')">
          <BFormInput v-model.number="record.rank" type="number" min="0" />
        </BFormGroup>
      </BCol>
    </BRow>

    <BFormGroup :label="$t('models.redirect.from')">
      <template #description>
        <div v-html="$t('models.redirect.from_desc')" />
      </template>
      <BFormInput v-model="record.from" required />
    </BFormGroup>

    <BFormGroup :label="$t('models.redirect.to')">
      <template #description>
        <div v-html="$t('models.redirect.to_desc')" />
      </template>
      <BFormInput v-model="record.to" required />
    </BFormGroup>

    <BRow class="align-items-end">
      <BCol md="6">
        <BFormGroup :label="$t('models.redirect.code')">
          <BFormInput v-model.number="record.code" type="number" min="301" max="308" required />
        </BFormGroup>
      </BCol>
      <BCol cols="auto" class="ms-md-auto">
        <BFormGroup>
          <BFormCheckbox v-model="record.active">
            {{ $t('models.redirect.active') }}
          </BFormCheckbox>
        </BFormGroup>
      </BCol>
    </BRow>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespRedirect>,
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
