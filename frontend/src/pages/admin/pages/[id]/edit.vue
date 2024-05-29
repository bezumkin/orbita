<template>
  <VespModal
    v-model="record"
    v-bind="{url}"
    method="patch"
    update-key="admin-pages"
    :title="$t('models.page.title_one')"
    size="lg"
  >
    <template #form-fields>
      <FormsPage v-model="record" />
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const record = ref()

const url = 'admin/pages/' + useRoute().params.id
try {
  record.value = await useGet(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}
</script>
