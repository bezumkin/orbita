<template>
  <VespModal :title="record.title" size="lg" :cancel-title="$t('actions.close')">
    <template #form-fields>
      <PlayerVideo :uuid="record.id" :autoplay="true" class="my-2" />
    </template>
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
