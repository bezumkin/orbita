<template>
  <div>
    <BRow class="mt-4">
      <BCol md="3">
        <BNav pills vertical>
          <!--<b-nav-item v-if="user && user.subscription" :to="{name: 'user-subscription'}">
            {{ $t('pages.user.subscription') }}
          </b-nav-item>-->
          <BNavItem :to="{name: 'user-profile'}">{{ $t('pages.user.profile') }}</BNavItem>
          <BNavItem :to="{name: 'user-payments'}">{{ $t('pages.user.payments') }}</BNavItem>
        </BNav>
      </BCol>
      <BCol md="9" class="mt-5 mt-md-0">
        <NuxtPage />
      </BCol>
    </BRow>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const {user} = useAuth()

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
