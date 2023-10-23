<template>
  <div></div>
</template>

<script setup lang="ts">
import {FetchError} from 'ofetch'

const {params} = useRoute()
const options = {replace: true, redirectCode: 302}

if (!params.username || !params.code) {
  navigateTo({name: 'index'}, options)
}

try {
  const {token} = await usePost('security/activate', params)
  if (token) {
    await useAuth().setToken(token)
    nextTick(() => {
      navigateTo({name: 'user-profile'}, options)
    })
  }
} catch (e: any) {
  if (e instanceof FetchError) {
    showError({
      statusCode: e.status,
      statusMessage: e.statusMessage,
      message: e.response?._data ? useI18n().t(e.response?._data) : '',
    })
  } else {
    showError({statusCode: 500, statusMessage: 'Internal Server Error'})
  }
}
</script>
