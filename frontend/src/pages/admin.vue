<template>
  <div>
    <div v-if="user">
      <BNav :tabs="!$isMobile" :pills="$isMobile" class="mt-4 justify-content-between justify-content-md-start">
        <template v-for="(section, idx) in sections">
          <BNavItem v-if="section.disabled" :key="'d' + idx" class="d-none d-md-flex align-items-center" disabled>
            <span class="border-end" style="height: 1.25rem"></span>
          </BNavItem>
          <BNavItem v-else :key="'e' + idx" :to="{name: section.route}" :class="section.class">
            {{ $t('pages.admin.' + section.title) }}
          </BNavItem>
        </template>
      </BNav>
      <div class="mt-4">
        <NuxtPage />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const {user} = useAuth()
const {t} = useI18n()
const {$settings, $login, $isMobile} = useNuxtApp()
const sections = computed(() => getAdminSections())

function checkAccess() {
  if (!user.value) {
    showError({statusCode: 401, statusMessage: 'Unauthorized'})
    nextTick(() => {
      $login.value = true
    })
  } else if (!sections.value.length) {
    showError({statusCode: 403, statusMessage: 'Access Denied'})
  } else if (route.name === 'admin') {
    navigateTo({name: sections.value[0].route}, {replace: true})
  } else {
    const section = sections.value.find((i) => i.route === route.name)
    if (section && section.scope && !hasScope(section.scope)) {
      showError({statusCode: 403, statusMessage: 'Access Denied'})
    }
  }
}

function setTitle() {
  const routeName = route.name as string
  if (route.matched.length === 2) {
    const name = routeName.replace(/^admin-/, '').replace('-', '_')
    useHead({
      title: () => [t('pages.admin.' + name), t('pages.admin.title'), $settings.value.title].join(' / '),
    })
  }
}

checkAccess()
setTitle()

watch(
  () => route.name,
  () => {
    checkAccess()
    setTitle()
  },
)
</script>
