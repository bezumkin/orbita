<template>
  <div>
    <BFormGroup :label="$t('models.payment.id')">
      <BFormInput :model-value="record.id" readonly />
    </BFormGroup>

    <BRow>
      <BCol md="6">
        <BFormGroup :label="$t('models.payment.service')">
          <BFormInput :model-value="record.service" readonly />
        </BFormGroup>
      </BCol>
      <BCol md="6">
        <BFormGroup :label="$t('models.payment.amount')">
          <BFormInput :model-value="formatPrice(record.amount)" readonly />
        </BFormGroup>
      </BCol>
    </BRow>

    <BFormGroup :label="$t('models.payment.created_at')">
      <BFormInput :model-value="formatDate(record.created_at)" readonly />
    </BFormGroup>

    <BFormCheckbox :model-value="record.paid" disabled>
      {{ $t('models.payment.paid') }}
    </BFormCheckbox>

    <BFormGroup v-if="record.paid_at">
      <BFormInput :model-value="formatDate(record.paid_at)" readonly />
    </BFormGroup>

    <div v-if="record.topic" class="my-3 p-2 rounded">
      <h5>{{ $t('models.topic.title_one') }}</h5>

      <BFormGroup>
        <BFormInput :model-value="record.topic.title" readonly />
      </BFormGroup>
    </div>

    <div v-if="record.subscription" class="my-3 p-2 rounded">
      <h5>{{ $t('models.subscription.title_one') }}</h5>

      <BFormGroup v-if="currentLevel" :label="$t('models.subscription.level')">
        <BFormInput :model-value="currentLevel?.title" readonly />
      </BFormGroup>

      <BFormGroup>
        <BRow>
          <BCol cols="6">
            <BFormCheckbox :model-value="record.subscription.active" disabled>
              {{ $t('models.subscription.active') }}
            </BFormCheckbox>
          </BCol>
          <BCol cols="6">
            <BFormCheckbox :model-value="record.subscription.cancelled" disabled>
              {{ $t('models.subscription.cancelled') }}
            </BFormCheckbox>
          </BCol>
        </BRow>
      </BFormGroup>

      <BFormGroup v-if="record.subscription.active_until" :label="$t('models.subscription.active_until')">
        <BFormInput :model-value="formatDate(record.subscription.active_until)" readonly />
      </BFormGroup>

      <BFormGroup v-if="record.subscription.warned_at" :label="$t('models.subscription.warned_at')">
        <BFormInput :model-value="formatDate(record.subscription.warned_at)" readonly />
      </BFormGroup>

      <BFormGroup v-if="nextLevel" :label="$t('models.subscription.next_level')">
        <BFormInput :model-value="nextLevel?.title" readonly />
      </BFormGroup>
    </div>

    <div v-if="record.data">
      <BFormGroup>
        <h5>{{ $t('models.payment.remote_data', {service: record.service}) }}</h5>

        <pre class="remote-data">{{ record.data }}</pre>
      </BFormGroup>
    </div>

    <BFormGroup>
      <BRow>
        <BCol md="6">
          <BFormCheckbox :model-value="record.metadata?.approved" disabled>
            {{ $t('models.payment.approved') }}
          </BFormCheckbox>
        </BCol>
        <BCol md="6">
          <BFormCheckbox :model-value="record.metadata?.refunded" disabled>
            {{ $t('models.payment.refunded') }}
          </BFormCheckbox>
        </BCol>
      </BRow>
    </BFormGroup>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespPayment>,
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

const {$levels} = useNuxtApp()

const currentLevel = computed(() => {
  return $levels.value.find((i: VespLevel) => i.id === record.value?.subscription?.level_id)
})
const nextLevel = computed(() => {
  return record.value?.subscription?.next_level_id
    ? $levels.value.find((i: VespLevel) => i.id === record.value?.subscription?.next_level_id)
    : undefined
})
</script>

<style scoped lang="scss">
:deep(.form-check) {
  input,
  label {
    opacity: 1 !important;
  }
}

:deep(.remote-data) {
  white-space: pre;
  max-height: 500px;
  overflow: auto;
  margin-bottom: 0;
}

:deep(.rounded) {
  background-color: var(--bs-tertiary-bg);
}
</style>
