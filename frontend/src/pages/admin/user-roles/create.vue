<template>
  <vesp-modal v-model="record" url="admin/user-roles" method="put" :title="$t('models.user_role.title_one')">
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
</script>
