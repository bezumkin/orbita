<template>
  <VespModal v-model="record" url="admin/user-roles" method="put" :title="$t('models.user_role.title_one')">
    <template #form-fields>
      <FormsUserRole v-model="record" />
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const record = ref({
  id: 0,
  title: '',
  scope: [],
})
</script>
