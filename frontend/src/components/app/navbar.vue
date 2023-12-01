<template>
  <b-navbar id="navbar" variant="light" sticky="top" :container="false">
    <b-container>
      <b-navbar-brand :to="{name: 'index'}" class="p-0" @click="hideSidebar">
        <b-img :src="logo" height="40" />
      </b-navbar-brand>

      <app-pages class="d-none d-md-flex" />

      <b-navbar-nav class="ms-auto">
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
            <fa v-if="!$sidebar" icon="bars" class="fa-fw" />
            <fa v-else icon="times" class="fa-fw" />
          </transition>
        </b-button>
      </b-navbar-nav>
    </b-container>
  </b-navbar>
</template>

<script setup lang="ts">
import type {BaseButtonVariant} from 'bootstrap-vue-next/src/types'
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
const {user} = useAuth()
const {$sidebar} = useNuxtApp()

function toggleSidebar() {
  $sidebar.value = !$sidebar.value
}

function hideSidebar() {
  $sidebar.value = false
}
</script>
