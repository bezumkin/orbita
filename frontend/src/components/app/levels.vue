<template>
  <div>
    <div class="subscriptions">
      <div v-for="level in levels" :key="level.id" class="level">
        <div class="title">{{ level.title }}</div>
        <div class="price">{{ $price(level.price) }} {{ $t('models.level.per_month') }}</div>
        <div v-if="level.cover" class="cover">
          <b-img
            :src="$image(level.cover, {h: 150, fit: 'crop'})"
            :srcset="$image(level.cover, {h: 300, fit: 'crop'}) + ' 2x'"
            class="rounded"
            height="150"
          />
        </div>
        <div v-if="level.content" class="content">{{ level.content }}</div>
        <b-button v-if="isSubscribed" disabled>{{ $t('actions.levels.subscribed') }}</b-button>
        <b-button v-else>{{ $t('actions.levels.subscribe') }}</b-button>
      </div>
    </div>
    <div v-if="$scope('levels/patch')" class="p-3 border-top">
      <b-button :to="{name: 'admin-levels'}" variant="primary" class="w-100">
        {{ $t('actions.levels.manage') }}
      </b-button>
    </div>
  </div>
</template>

<script setup lang="ts">
const {$socket} = useNuxtApp()
const levels: Ref<VespLevel[]> = ref([])
const isSubscribed = computed(() => {
  return false
})

async function fetch() {
  try {
    const {rows} = await useGet('web/levels')
    levels.value = {...rows}
  } catch (e) {}
}

await fetch()

onMounted(() => {
  $socket.on('levels', fetch)
})

onUnmounted(() => {
  $socket.off('levels', fetch)
})
</script>
