<template>
  <VespModal v-model="record" url="admin/levels" method="put" :title="$t('models.level.title_one')">
    <template #form-fields>
      <FormsLevel v-model="record" />
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const record = ref({
  id: 0,
  title: '',
  price: 0,
  content: '',
  active: true,
})
</script>
