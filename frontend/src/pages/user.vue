<template>
  <div>
    <b-row class="mt-4">
      <b-col md="3">
        <b-nav pills vertical>
          <b-nav-item :to="{name: 'user-profile'}">{{ $t('pages.user.profile') }}</b-nav-item>
          <b-nav-item>{{ $t('pages.user.payments') }}</b-nav-item>
        </b-nav>
      </b-col>
      <b-col md="9">
        <nuxt-page />
      </b-col>
    </b-row>
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
