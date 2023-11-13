<template>
  <vesp-modal
    v-model="record"
    v-bind="{url}"
    method="patch"
    update-key="admin-levels"
    :title="$t('models.level.title_one')"
  >
    <template #form-fields>
      <forms-level v-model="record" />
    </template>
  </vesp-modal>
</template>

<script setup lang="ts">
const record = ref()

const url = 'admin/levels/' + useRoute().params.id
try {
  record.value = await useApi(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}
</script>
