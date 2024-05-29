<template>
  <VespModal
    v-model="record"
    v-bind="{url}"
    method="patch"
    update-key="admin-topics"
    :title="$t('models.topic.title_one')"
    size="xl"
  >
    <template #form-fields>
      <FormsTopic v-model="record" />
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const record = ref()

const url = 'admin/topics/' + useRoute().params.id
try {
  record.value = await useGet(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}
</script>
