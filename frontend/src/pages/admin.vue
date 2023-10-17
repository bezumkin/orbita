<template>
  <div>
    <b-nav tabs class="mt-4">
      <b-nav-item :to="{name: 'admin-users'}">{{ $t('pages.admin.users') }}</b-nav-item>
      <b-nav-item :to="{name: 'admin-user-roles'}">{{ $t('pages.admin.user_roles') }}</b-nav-item>
      <b-nav-item v-if="$scope('videos/get')" :to="{name: 'admin-videos'}">{{ $t('pages.admin.videos') }}</b-nav-item>
    </b-nav>
    <div class="mt-4">
      <nuxt-page />
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const {data: user} = useAuth()
if (!user.value) {
  showError({statusCode: 401, statusMessage: 'Unauthorized'})
} else if (!hasScope(['users/get', 'payments/get'])) {
  showError({statusCode: 403, statusMessage: 'Access Denied'})
}

if (route.name === 'admin') {
  navigateTo({name: 'admin-users'})
}

watch(
  () => route.name,
  (newValue) => {
    if (newValue === 'admin') {
      navigateTo({name: 'admin-users'})
    }
  },
)
</script>
