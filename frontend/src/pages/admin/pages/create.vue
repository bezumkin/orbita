<template>
  <vesp-modal v-model="record" url="admin/pages" method="put" :title="$t('models.page.title_one')" size="lg">
    <template #form-fields>
      <forms-page v-model="record" />
    </template>
  </vesp-modal>
</template>

<script setup lang="ts">
const record = ref({
  id: 0,
  title: '',
  content: {},
  alias: '',
  position: 'both',
  active: true,
  rank: 0,
})
</script>
