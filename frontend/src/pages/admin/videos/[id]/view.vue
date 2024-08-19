<template>
  <VespModal :title="record.title" size="lg" :cancel-title="$t('actions.close')">
    <div class="my-2">
      <PlayerVideo :uuid="record.id" :poster="poster" :autoplay="true" />
    </div>
    <div v-if="useAudio && record.audio" class="my-2">
      <PlayerAudio :uuid="record.audio.uuid" :title="$t('models.video.audio')" />
    </div>
  </VespModal>
</template>

<script setup lang="ts">
const useAudio = Number(useRuntimeConfig().public.EXTRACT_VIDEO_AUDIO_ENABLED)
const record: Ref<VespVideo> = ref({
  id: '',
})
const poster = ref()

const url = 'admin/videos/' + useRoute().params.id
try {
  record.value = await useGet(url)
  if (record.value.image) {
    poster.value = getImageLink(record.value.image)
  }
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}
</script>
