<template>
  <div>
    <b-row class="mt-4">
      <b-col md="3">
        <b-nav pills vertical>
          <!--<b-nav-item v-if="user && user.subscription" :to="{name: 'user-subscription'}">
            {{ $t('pages.user.subscription') }}
          </b-nav-item>-->
          <b-nav-item :to="{name: 'user-profile'}">{{ $t('pages.user.profile') }}</b-nav-item>
          <b-nav-item :to="{name: 'user-payments'}">{{ $t('pages.user.payments') }}</b-nav-item>
        </b-nav>
      </b-col>
      <b-col md="9" class="mt-5 mt-md-0">
        <nuxt-page />
      </b-col>
    </b-row>
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
