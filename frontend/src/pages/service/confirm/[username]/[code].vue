<template>
  <div class="p-5 text-center">
    <b-spinner size="lg" />
  </div>
</template>

<script setup lang="ts">
const {params} = useRoute()
const options = {replace: true, redirectCode: 302}
const route = ref({name: 'index'})

if (params.username && params.code) {
  const {data} = await usePost('security/activate', params)
  if (data.value?.token) {
    await useAuth().setToken(data.value.token)
    route.value = {name: 'user-profile'}
  }
}

navigateTo(route.value, options)
</script>
