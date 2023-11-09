<template>
  <div class="footer border-top pt-2 d-flex">
    <div class="d-flex gap-3">
      <div>
        <fa icon="eye" class="fa-fw" />
        {{ topic.views_count }}
      </div>
      <div>
        <fa icon="comment" class="fa-fw" />
        {{ topic.comments_count }}
      </div>
    </div>

    <div v-if="topic.published_at" class="ms-auto">
      <fa icon="calendar" class="fa-fw" />
      {{ d(topic.published_at, 'long') }}
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps({
  topic: {
    type: Object as PropType<VespTopic>,
    default() {
      return {}
    },
  },
})

const {d} = useI18n()
</script>
