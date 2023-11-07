<template>
  <vesp-modal v-model="record" url="admin/topics" method="put" :title="$t('models.topic.title_one')" size="xl">
    <template #form-fields>
      <forms-topic v-model="record" />
    </template>
  </vesp-modal>
</template>

<script setup lang="ts">
const record = ref({
  id: 0,
  title: '',
  price: 0,
  content: {},
  active: true,
  closed: false,
})
</script>
