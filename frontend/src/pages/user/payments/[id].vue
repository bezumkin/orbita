<template>
  <div>
    <!--<vesp-modal>
    <pre>{{ data }}</pre>
  </vesp-modal>-->
  </div>
</template>

<script setup lang="ts">
import type {RouteLocationRaw} from 'vue-router'

// const {params} = useRoute()

const route: Ref<RouteLocationRaw> = ref({name: 'user-payments'})

// const data = await useGet('user/payments/' + params.id)
// if (data.paid) {
//   useAuth().loadUser()
//   if (data.topic) {
//     route.value = {name: 'topics-uuid', params: {topics: data.topic.category?.uri || 'topics', uuid: data.topic.uuid}}
//   }
// }

onMounted(() => {
  navigateTo(route.value, {replace: true, redirectCode: 302})
})
</script>
