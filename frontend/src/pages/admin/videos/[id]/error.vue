<template>
  <VespModal :title="record.title" :cancel-title="$t('actions.close')">
    <BFormGroup :label="$t('models.video.error')">
      <BFormTextarea v-model="record.error" disabled rows="10" />
    </BFormGroup>
  </VespModal>
</template>

<script setup lang="ts">
const record: Ref<VespVideo> = ref({
  id: '',
})

const url = 'admin/videos/' + useRoute().params.id
try {
  record.value = await useGet(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}
</script>
