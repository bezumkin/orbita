<template>
  <VespModal :title="record.title" size="lg" :cancel-title="$t('actions.close')">
    <div class="d-flex flex-column gap-2">
      <TopicBlockVideo v-bind="props" />
    </div>
  </VespModal>
</template>

<script setup lang="ts">
const record = ref<VespVideo>({
  id: '',
})

const url = 'admin/videos/' + useRoute().params.id
try {
  record.value = await useGet(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}

const props = {
  block: {
    data: {
      uuid: record.value.id,
      size: record.value.file.size,
      audio: record.value.audio?.uuid,
      audio_size: record.value.audio?.size,
    },
  },
  autoplay: true,
}
</script>
