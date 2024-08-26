<template>
  <VespModal v-model="record" url="admin/topics" method="put" :title="$t('models.topic.title_one')" size="xl">
    <template #form-fields>
      <FormsTopic v-model="record" />
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const {user} = useAuth()
const record = ref({
  id: 0,
  title: '',
  price: 0,
  content: {},
  user_id: user.value?.id,
  active: false,
  closed: false,
  delayed: false,
  publish_at: null,
  published_at: null,
  tags: [],
})
</script>
