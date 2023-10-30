<template>
  <div>
    <b-form-group :label="$t('models.video.title')">
      <b-form-input v-model.trim="record.title" required autofocus />
    </b-form-group>

    <b-form-group :label="$t('models.video.description')">
      <b-form-textarea v-model.trim="record.description" />
    </b-form-group>

    <b-form-group :label="$t('models.video.image')">
      <b-form-group>
        <file-upload
          v-model="record.new_image"
          :placeholder="record.image"
          :height="400"
          :allow-removing="false"
          wrapper-class="rounded border"
        />
      </b-form-group>
    </b-form-group>

    <b-form-group>
      <b-form-checkbox v-model="record.active">
        {{ $t('models.video.active') }}
      </b-form-checkbox>
    </b-form-group>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespVideo>,
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
