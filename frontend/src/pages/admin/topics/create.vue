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
  teaser: '',
  type: null,
  price: 0,
  content: {},
  user_id: user.value?.id,
  active: false,
  hide_comments: false,
  hide_views: false,
  hide_reactions: false,
  delayed: false,
  publish_at: null,
  published_at: null,
  tags: [],
})
</script>
