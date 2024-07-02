<template>
  <BNavbar id="navbar" sticky="top" :container="false">
    <BContainer>
      <BNavbarBrand :to="{name: 'index'}" class="p-0" @click="hideSidebar">
        <BImg :src="logo" class="logo" height="40" />
      </BNavbarBrand>

      <AppPages class="d-none d-md-flex" />

      <BNavbarNav class="ms-auto">
        <BButton variant="light" class="me-1" @click="changeColor">
          <VespFa :icon="colorIcon" fixed-width />
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
import {useColorMode, type BasicColorSchema} from '@vueuse/core'
import logo from '~/assets/images/logo-orbita.svg'

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
const {$sidebar} = useNuxtApp()
const {system, store} = useColorMode({attribute: 'data-bs-theme', selector: 'body'})
const saved: Ref<BasicColorSchema | undefined> = useCookie('colorMode')
const colorIcon = computed(() => {
  if (store.value === 'auto') {
    return 'circle-half-stroke'
  }
  return ['far', store.value === 'light' ? 'sun' : 'moon']
})

function toggleSidebar() {
  $sidebar.value = !$sidebar.value
}

function hideSidebar() {
  $sidebar.value = false
}

function changeColor() {
  if (store.value === 'auto') {
    store.value = system.value === 'light' ? 'dark' : 'light'
  } else if (store.value === 'light') {
    store.value = system.value === 'light' ? 'auto' : 'dark'
  } else if (store.value === 'dark') {
    store.value = system.value === 'dark' ? 'auto' : 'light'
  }
  saved.value = store.value
}

store.value = saved.value || 'auto'

function changeThemeColor(newValue: string) {
  const elem = document.querySelector('meta[name="theme-color"]')
  if (elem) {
    elem.setAttribute('content', newValue === 'dark' ? '#000' : '#fff')
  }
}

watch(system, (newValue) => {
  if (store.value === 'auto') {
    changeThemeColor(newValue)
  }
})

watch(store, (newValue) => {
  if (newValue === 'auto') {
    changeThemeColor(system.value)
  } else {
    changeThemeColor(newValue)
  }
})
</script>
