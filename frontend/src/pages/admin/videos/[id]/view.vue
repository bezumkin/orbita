<template>
  <vesp-modal :title="record.title" size="lg" :cancel-title="$t('actions.close')">
    <template #form-fields>
      <player-video :uuid="record.id" :status="record.status" :autoplay="true" class="my-2" />
    </template>
  </vesp-modal>
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
