<template>
  <div class="col-10 col-lg-9 m-auto">
    <BNav tabs>
      <BNavItem :to="{name: 'user-profile'}">{{ $t('pages.user.profile') }}</BNavItem>
      <BNavItem v-if="$levels.length" :to="{name: 'user-subscription'}">
        {{ $t('pages.user.subscription') }}
      </BNavItem>
      <BNavItem :to="{name: 'user-payments'}">{{ $t('pages.user.payments') }}</BNavItem>
    </BNav>
    <div class="mt-4">
      <NuxtPage />
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const {user} = useAuth()
const {$levels} = useNuxtApp()

if (!user.value && !String(route.name).startsWith('user-confirm')) {
  showError({statusCode: 401, statusMessage: 'Unauthorized'})
}

if (route.name === 'user') {
  navigateTo({name: 'user-profile'})
}

watch(
  () => route.name,
  (newValue) => {
    if (newValue === 'user') {
      navigateTo({name: 'user-profile'})
    }
  },
)
</script>
