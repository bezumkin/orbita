<template>
  <VespModal v-model="record" v-bind="{url}" method="patch" update-key="admin-videos" :title="record.title" size="lg">
    <template #form-fields>
      <FormsVideo v-model="record" />
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
