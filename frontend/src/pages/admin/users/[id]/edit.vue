<template>
  <VespModal
    v-model="record"
    v-bind="{url}"
    method="patch"
    update-key="admin-users"
    size="lg"
    :title="$t('models.user.title_one')"
  >
    <template #form-fields>
      <FormsUser v-model="record" />
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const record = ref({
  id: 0,
  username: '',
})

const url = 'admin/users/' + useRoute().params.id
try {
  record.value = await useGet(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}
</script>
