<template>
  <vesp-modal :title="record.title" :cancel-title="$t('actions.close')">
    <b-form-group :label="$t('models.video.error')">
      <b-form-textarea v-model="record.error" disabled rows="10" />
    </b-form-group>
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
