<template>
  <div>
    <b-nav tabs class="mt-4">
      <b-nav-item v-for="(section, idx) in sections" :key="idx" :to="{name: section.route}">
        {{ $t('pages.admin.' + section.title) }}
      </b-nav-item>
    </b-nav>
    <div class="mt-4">
      <nuxt-page />
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const {data: user} = useAuth()
const sections = computed(() => getAdminSections())

function checkAccess() {
  if (!user.value) {
    showError({statusCode: 401, statusMessage: 'Unauthorized'})
  } else if (!sections.value.length) {
    showError({statusCode: 403, statusMessage: 'Access Denied'})
  } else if (route.name === 'admin') {
    navigateTo({name: sections.value[0].route})
  } else {
    const section = sections.value.find((i) => i.route === route.name)
    if (section && section.scope && !hasScope(section.scope)) {
      showError({statusCode: 403, statusMessage: 'Access Denied'})
    }
  }
}

checkAccess()
watch(() => route.name, checkAccess)
</script>
