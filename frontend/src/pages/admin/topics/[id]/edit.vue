<template>
  <vesp-modal
    v-model="record"
    v-bind="{url}"
    method="patch"
    update-key="admin-topics"
    :title="$t('models.topic.title_one')"
    size="xl"
  >
    <template #form-fields>
      <forms-topic v-model="record" />
    </template>
  </vesp-modal>
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
