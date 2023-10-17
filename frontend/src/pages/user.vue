<template>
  <div>
    <b-nav tabs class="mt-4">
      <!--<b-nav-item :to="{name: 'user-orders'}">{{ $t('models.order.title_many') }}</b-nav-item>-->
      <b-nav-item :to="{name: 'user-profile'}">{{ $t('pages.user.profile') }}</b-nav-item>
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
