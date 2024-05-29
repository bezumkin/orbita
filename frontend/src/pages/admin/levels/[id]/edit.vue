<template>
  <VespModal
    v-model="record"
    v-bind="{url}"
    method="patch"
    update-key="admin-levels"
    :title="$t('models.level.title_one')"
  >
    <template #form-fields>
      <FormsLevel v-model="record" />
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const record = ref()

const url = 'admin/levels/' + useRoute().params.id
try {
  record.value = await useGet(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}
</script>
