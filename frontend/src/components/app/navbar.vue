<template>
  <b-navbar id="navbar" sticky="top" :container="false">
    <b-container>
      <b-navbar-brand :to="{name: 'index'}" class="p-0" @click="hideSidebar">
        <b-img :src="logo" class="logo" height="40" />
      </b-navbar-brand>

      <app-pages class="d-none d-md-flex" />

      <b-navbar-nav class="ms-auto">
        <b-button variant="light" class="me-1" @click="changeColor">
          <vesp-fa :icon="colorIcon" fixed-width />
        </b-button>
        <app-login :btn-variant="btnVariant" @click="hideSidebar">
          <template #user-menu>
            <b-dropdown-item v-if="hasAdmin" :to="{name: 'admin'}" link-class="border-bottom">
              {{ $t('pages.admin.title') }}
            </b-dropdown-item>
            <!--<b-dropdown-item v-if="user && user.subscription" :to="{name: 'user-subscription'}">
              {{ $t('pages.user.subscription') }}
            </b-dropdown-item>-->
            <b-dropdown-item :to="{name: 'user-profile'}">{{ $t('pages.user.profile') }}</b-dropdown-item>
            <b-dropdown-item :to="{name: 'user-payments'}">{{ $t('pages.user.payments') }}</b-dropdown-item>
          </template>
        </app-login>
        <b-button v-if="sidebar" :variant="btnVariant" class="d-md-none ms-1" @click.stop="toggleSidebar">
          <transition name="fade" mode="out-in">
            <vesp-fa v-if="!$sidebar" icon="bars" class="fa-fw" />
            <vesp-fa v-else icon="times" class="fa-fw" />
          </transition>
        </b-button>
      </b-navbar-nav>
    </b-container>
  </b-navbar>
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
</script>
