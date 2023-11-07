<template>
  <div>
    <b-row>
      <b-col md="8">
        <b-form-group :label="$t('models.topic.title')">
          <b-form-input v-model.trim="record.title" required autofocus />
        </b-form-group>
        <b-form-group :label="$t('models.topic.content')">
          <editor-js v-model="record.content" />
        </b-form-group>
      </b-col>
      <b-col md="4">
        <b-form-group :label="$t('models.topic.price')">
          <b-form-input v-model="record.price" type="number" min="0" />
        </b-form-group>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="8">
        <b-form-group :label="$t('models.topic.cover')">
          <file-upload v-model="record.new_cover" :placeholder="record.cover" :height="200" />
        </b-form-group>
      </b-col>
      <b-col md="4">
        <b-form-group :label="$t('models.topic.teaser')">
          <b-form-textarea v-model="record.teaser" no-resize style="height: 200px" />
        </b-form-group>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="6">
        <b-form-group>
          <b-form-checkbox v-model="record.active">
            {{ $t('models.topic.active') }}
          </b-form-checkbox>
        </b-form-group>
      </b-col>
      <b-col md="6">
        <b-form-group>
          <b-form-checkbox v-model="record.closed">
            {{ $t('models.topic.closed') }}
          </b-form-checkbox>
        </b-form-group>
      </b-col>
    </b-row>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespTopic>,
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
