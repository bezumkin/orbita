<template>
  <div>
    <b-row>
      <b-col md="8">
        <b-form-group :label="$t('models.level.title')">
          <b-form-input v-model.trim="record.title" required autofocus />
        </b-form-group>
      </b-col>
      <b-col md="4">
        <b-form-group :label="$t('models.level.price')">
          <b-form-input v-model="record.price" type="number" min="0" />
        </b-form-group>
      </b-col>
    </b-row>

    <b-form-group :label="$t('models.level.cover')">
      <file-upload v-model="record.new_cover" :placeholder="record.cover" :height="200" />
    </b-form-group>

    <b-form-group :label="$t('models.level.content')">
      <b-form-textarea v-model="record.content" rows="5" />
    </b-form-group>

    <b-form-group>
      <b-form-checkbox v-model="record.active">
        {{ $t('models.level.active') }}
      </b-form-checkbox>
    </b-form-group>
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
