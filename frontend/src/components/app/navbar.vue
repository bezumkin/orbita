<template>
  <b-navbar variant="light">
    <b-navbar-brand :to="{name: 'index'}" class="p-0">
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

      <app-login>
        <template #user-menu>
          <b-dropdown-item v-if="adminSections.length" :to="{name: 'admin'}">
            {{ $t('pages.admin.title') }}
          </b-dropdown-item>
          <b-dropdown-item :to="{name: 'user-profile'}">{{ $t('pages.user.profile') }}</b-dropdown-item>
        </template>
      </app-login>
    </b-navbar-nav>
  </b-navbar>
</template>

<script setup lang="ts">
import logo from '~/assets/images/logo-orbita.svg'
const adminSections = computed(() => getAdminSections())
// import ru from '~/assets/icons/ru.svg'
// import en from '~/assets/icons/gb.svg'
</script>
