<template>
  <div>
    <BFormGroup :label="$t('models.video.title')">
      <BFormInput v-model.trim="record.title" required autofocus />
    </BFormGroup>

    <BFormGroup :label="$t('models.video.description')">
      <BFormTextarea v-model.trim="record.description" :rows="record.description ? 6 : 3" />
    </BFormGroup>

    <BFormGroup :label="$t('models.video.image')">
      <BFormGroup>
        <FileUpload
          v-model="record.new_image"
          :placeholder="record.image"
          :height="265"
          :allow-removing="false"
          wrapper-class="rounded border"
        />
      </BFormGroup>
    </BFormGroup>

    <BFormGroup :label="$t('models.video.chapters')">
      <BFormTextarea v-model.trim="record.chapters" :rows="record.chapters ? 6 : 3" />
    </BFormGroup>

    <BFormGroup>
      <BFormCheckbox v-model="record.active">
        {{ $t('models.video.active') }}
      </BFormCheckbox>
    </BFormGroup>
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
    const value = props.modelValue
    const chapters: string[] = []
    if (value.chapters && typeof value.chapters === 'object') {
      Object.keys(value.chapters).forEach((k: string) => {
        chapters.push(k + ' ' + props.modelValue.chapters[k])
      })
      value.chapters = chapters.join('\n')
    }
    return reactive(value) as VespVideo
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})
</script>
