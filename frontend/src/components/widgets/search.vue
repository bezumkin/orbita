<template>
  <div class="widget">
    <BForm @submit.prevent="onSearch">
      <BInputGroup>
        <BFormInput v-model="query" :placeholder="$t('pages.search.placeholder')" required />
        <template #append>
          <BButton type="submit" :disabled="!query">
            <VespFa icon="magnifying-glass" />
          </BButton>
        </template>
      </BInputGroup>
    </BForm>
  </div>
</template>

<script setup lang="ts">
const {$sidebar} = useNuxtApp()
const router = useRouter()
const query = ref('')

function onSearch() {
  if ($sidebar.value) {
    $sidebar.value = false
  }
  if (query.value) {
    router.push({name: 'search', query: {query: query.value}})
  }
}
</script>
