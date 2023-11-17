<template>
  <vesp-modal
    v-model="record"
    v-bind="{url}"
    method="patch"
    update-key="admin-user-roles"
    :title="$t('models.user_role.title_one')"
  >
    <template #form-fields>
      <forms-user-role v-model="record" />
    </template>
  </vesp-modal>
</template>

<script setup lang="ts">
const record = ref({
  id: 0,
  title: '',
  scope: [],
})

const url = 'admin/user-roles/' + useRoute().params.id
try {
  record.value = await useGet(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}
</script>
