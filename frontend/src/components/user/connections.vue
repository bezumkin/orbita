<template>
  <BOverlay :show="loading" opacity="0.5">
    <div v-for="(service, idx) in allServices" :key="idx" class="d-flex flex-wrap gap-3">
      <BButton v-if="isConnected(service)" variant="warning" @click="() => onDisconnect(service)">
        <span class="d-flex align-items-center gap-3">
          <img :src="'/connections/' + service + '.svg'" height="50" alt="" />
          <span v-html="$t('models.user_connection.disconnect_from', {service: formatServiceName(service)})" />
        </span>
      </BButton>
      <BButton v-else variant="light" @click="() => onConnect(service)">
        <span class="d-flex align-items-center gap-3">
          <img :src="'/connections/' + service + '.svg'" height="50" alt="" />
          <span v-html="$t('models.user_connection.connect_to', {service: formatServiceName(service)})" />
        </span>
      </BButton>
    </div>
  </BOverlay>
</template>

<script setup lang="ts">
const {$socket, $variables} = useNuxtApp()
const {user} = useAuth()

const loading = ref(false)
const userServices = ref<Record<string, any>[]>([])
const allServices = $variables.value.CONNECTION_SERVICES.split(',').map(formatServiceKey)

function formatServiceName(service: string) {
  return service.charAt(0).toUpperCase() + service.slice(1)
}

async function fetch() {
  loading.value = true
  try {
    userServices.value = await useGet('user/connections')
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function isConnected(service: string) {
  const userService = userServices.value.find((i: any) => formatServiceKey(i.service) === service)
  return userService ? userService.connected : false
}

function onConnect(service: string) {
  const userService = userServices.value.find((i: any) => formatServiceKey(i.service) === service)
  if (userService && 'link' in userService) {
    window.open(userService.link)
  }
}

async function onDisconnect(service: string) {
  loading.value = true
  try {
    await useDelete('user/connections/' + service)
  } catch (e) {
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetch()
  $socket.on('user-connections', ({id}: VespUser) => {
    if (id === user.value?.id) {
      fetch()
    }
  })
})

onUnmounted(() => {
  $socket.off('user-connections')
})
</script>
