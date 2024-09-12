<template>
  <BNavbar id="navbar" sticky="top" :container="false">
    <BContainer>
      <BNavbarBrand :to="{name: 'index'}" class="p-0" @click="hideSidebar">
        <BImg :src="logo" class="logo" height="40" />
      </BNavbarBrand>

      <AppPages class="d-none d-md-flex" />

      <BNavbarNav class="ms-auto">
        <BButton variant="light" class="me-1" @click="() => changeColorTheme()">
          <VespFa :key="colorIcon[1]" :icon="colorIcon" fixed-width />
        </BButton>
        <AppLogin :btn-variant="btnVariant" @click="hideSidebar">
          <template #user-menu>
            <BDropdownItem v-if="hasAdmin" :to="{name: 'admin'}" link-class="border-bottom">
              {{ $t('pages.admin.title') }}
            </BDropdownItem>
            <!--<b-dropdown-item v-if="user && user.subscription" :to="{name: 'user-subscription'}">
              {{ $t('pages.user.subscription') }}
            </b-dropdown-item>-->
            <BDropdownItem :to="{name: 'user-profile'}">{{ $t('pages.user.profile') }}</BDropdownItem>
            <BDropdownItem :to="{name: 'user-payments'}">{{ $t('pages.user.payments') }}</BDropdownItem>
          </template>
        </AppLogin>
        <BButton v-if="sidebar" :variant="btnVariant" class="d-md-none ms-1" @click.stop="toggleSidebar">
          <Transition name="fade" mode="out-in">
            <VespFa v-if="!$sidebar" icon="bars" class="fa-fw" />
            <VespFa v-else icon="times" class="fa-fw" />
          </Transition>
        </BButton>
      </BNavbarNav>
    </BContainer>
  </BNavbar>
</template>

<script setup lang="ts">
import type {BaseButtonVariant} from 'bootstrap-vue-next'
import {type BasicColorSchema, useColorMode} from '@vueuse/core'
import logo from '~/public/project/logo.svg'

defineProps({
  sidebar: {
    type: Boolean,
    default: false,
  },
  btnVariant: {
    type: String as PropType<keyof BaseButtonVariant>,
    default: 'light',
  },
})

const hasAdmin = computed(() => getAdminSections().length)
const {loggedIn} = useAuth()
const {$sidebar} = useNuxtApp()
const {system, store} = useColorMode({attribute: 'data-bs-theme', selector: 'body'})
const saved = useCookie<BasicColorSchema | undefined>('colorMode', {maxAge: useRuntimeConfig().public.JWT_EXPIRE})
const colorIcon = computed(() => {
  return ['fas', store.value === 'dark' ? 'moon' : 'sun']
})

function toggleSidebar() {
  $sidebar.value = !$sidebar.value
}

function hideSidebar() {
  $sidebar.value = false
}

function changeColorTheme(newValue: BasicColorSchema | undefined = undefined) {
  if (newValue) {
    store.value = newValue
  } else {
    store.value = store.value === 'light' ? 'dark' : 'light'
  }
  saved.value = store.value

  if (process.client) {
    const elem = document.querySelector('meta[name="theme-color"]')
    if (elem) {
      elem.setAttribute('content', store.value === 'dark' ? '#000' : '#fff')
    }
  }
}

// Reset default theme for all users
store.value = 'light'
if (saved.value) {
  if (saved.value === 'auto') {
    saved.value = 'light'
  }

  // Set theme tags for authorized users
  if (loggedIn.value) {
    store.value = saved.value
    useHead({
      bodyAttrs: {
        'data-bs-theme': saved.value === 'dark' ? 'dark' : 'light',
      },
      meta: [{name: 'theme-color', content: saved.value === 'dark' ? '#000' : '#fff'}],
    })
  } else {
    // Change theme after page load for guests
    onMounted(() => {
      changeColorTheme(saved.value === 'auto' ? 'light' : saved.value)
    })
  }
}

watch(system, changeColorTheme)
</script>
