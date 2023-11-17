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
  const {token} = await usePost('security/activate', params)
  if (token) {
    await useAuth().setToken(token)
    route.value = {name: 'user-profile'}
  }
}

navigateTo(route.value, options)
</script>
