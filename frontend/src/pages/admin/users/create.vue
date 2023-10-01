<template>
  <vesp-modal v-model="record" url="admin/users" method="put" size="lg" :title="$t('models.user.title_one')">
    <template #form-fields>
      <forms-user v-model="record" />
    </template>
  </vesp-modal>
</template>

<script setup lang="ts">
const record = ref({
  id: 0,
  username: '',
  active: true,
})
</script>
