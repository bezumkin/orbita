<template>
  <b-navbar id="navbar" variant="light" sticky="top">
    <b-navbar-brand :to="{name: 'index'}" class="p-0" @click="hideSidebar">
      <b-img :src="logo" height="40" />
    </b-navbar-brand>

    <b-navbar-nav class="ms-auto">
      <!--<vesp-change-locale>
        <template #default="{locale, locales, setLocale}">
          <b-dropdown variant="light">
            <template #button-content>
              <fa icon="globe" class="fa-fw" />
              {{ locale.toUpperCase() }}
            </template>
            <b-dropdown-item
              v-for="i in locales"
              :key="i.code"
              link-class="d-flex align-items-center"
              @click="setLocale(i.code)"
            >
              <b-img :src="i.code === 'en' ? en : ru" height="16" class="me-1" />
              {{ i.name }}
            </b-dropdown-item>
          </b-dropdown>
        </template>
      </vesp-change-locale>-->

      <app-login @click="hideSidebar">
        <template #user-menu>
          <b-dropdown-item v-if="adminSections.length" :to="{name: 'admin'}">
            {{ $t('pages.admin.title') }}
          </b-dropdown-item>
          <b-dropdown-item :to="{name: 'user-profile'}">{{ $t('pages.user.profile') }}</b-dropdown-item>
        </template>
      </app-login>
      <b-button v-if="layout === 'layout-columns'" variant="light" class="d-md-none ms-1" @click.stop="toggleSidebar">
        <transition name="fade" mode="out-in">
          <fa v-if="!$sidebar" icon="bars" class="fa-fw" />
          <fa v-else icon="times" class="fa-fw" />
        </transition>
      </b-button>
    </b-navbar-nav>
  </b-navbar>
</template>

<script setup lang="ts">
import logo from '~/assets/images/logo-orbita.svg'
const adminSections = computed(() => getAdminSections())
const {$sidebar} = useNuxtApp()
const layout = computed(() => {
  return useRoute().meta.layout
})

function toggleSidebar() {
  $sidebar.value = !$sidebar.value
}
function hideSidebar() {
  $sidebar.value = false
}
// import ru from '~/assets/icons/ru.svg'
// import en from '~/assets/icons/gb.svg'
</script>
