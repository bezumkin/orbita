<template>
  <BDropdown :toggle-class="!$isMobile ? 'p-0' : ''" :variant="$isMobile ? 'light' : 'link'" split>
    <template #button-content>
      <span @click="onSort()">
        <VespFa :icon="reverse ? 'arrow-down-short-wide' : 'arrow-up-wide-short'" fixed-width />
        {{ $t('components.topics.sort.' + sort) }}
      </span>
    </template>
    <template v-for="key in sorting">
      <BDropdownItem v-if="key !== sort" :key="key" @click="onSort(key)">
        {{ $t('components.topics.sort.' + key) }}
      </BDropdownItem>
    </template>
  </BDropdown>
</template>

<script setup lang="ts">
const router = useRouter()
const route = useRoute()
const {$isMobile, $reactions} = useNuxtApp()

const sorting = ['date', 'views', 'comments']
if ($reactions.value.length) {
  sorting.push('reactions')
}
const query = ref(useTopicsStore().query)
const sort = ref(route.query.sort || 'date')
const reverse = ref(Boolean(route.query.reverse))

function onSort(key: string | undefined = undefined) {
  if (!key) {
    reverse.value = !reverse.value
  } else {
    sort.value = key
    reverse.value = false
  }

  query.value.sort = sort.value === 'date' ? undefined : sort.value
  query.value.reverse = reverse.value ? 'true' : undefined

  router.push({
    name: route.name,
    params: route.params,
    query: {...route.query, sort: query.value.sort, reverse: query.value.reverse},
  })
}

if (!sorting.includes(String(sort.value))) {
  navigateTo({name: route.name, params: route.params})
} else if (route.query.reverse && !route.query.sort) {
  navigateTo({
    name: route.name,
    params: route.params,
    query: {...route.query, sort: 'date', reverse: 'true'},
  })
}
</script>
