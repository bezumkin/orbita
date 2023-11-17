<template>
  <vesp-modal v-model="record" v-bind="{url}" method="patch" update-key="admin-videos" :title="record.title" size="lg">
    <template #form-fields>
      <forms-video v-model="record" />
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
