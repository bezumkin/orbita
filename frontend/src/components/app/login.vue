<template>
  <div>
    <transition name="fade" mode="out-in">
      <div v-if="user" key="user">
        <slot name="default" v-bind="{user, logout: onLogout}">
          <b-dropdown v-if="user" :variant="btnVariant">
            <template #button-content>
              <slot name="button" v-bind="{user}">
                <user-avatar :user="user" size="25" />
              </slot>
            </template>
            <slot name="user-menu" v-bind="{user}" />
            <b-dropdown-divider />
            <b-dropdown-item @click="onLogout">
              <vesp-fa icon="power-off" class="fa-fw" />
              {{ $t('security.logout') }}
            </b-dropdown-item>
          </b-dropdown>
        </slot>
      </div>
      <div v-else key="guest">
        <slot name="guest">
          <b-button @click="showModal = true">
            <span class="d-none d-md-inline">
              <template v-if="registerEnabled">{{ $t('security.login') }} / {{ $t('security.register') }}</template>
              <template v-else>{{ $t('security.login') }}</template>
            </span>
            <span class="d-md-none">
              <vesp-fa icon="right-to-bracket" class="fa-fw" />
            </span>
          </b-button>
        </slot>
      </div>
    </transition>

    <b-modal v-model="showModal" hide-footer :auto-focus="false" @shown="onShown">
      <b-overlay :show="loading" :opacity="0.5">
        <b-tabs ref="tabs" pills justified content-class="mt-3" @update:model-value="onTab">
          <b-tab :title="$t('security.login')">
            <b-form @submit.prevent="onLogin">
              <forms-login v-model="formLogin" />
              <div class="text-center">
                <b-button variant="primary" type="submit">{{ $t('actions.submit') }}</b-button>
              </div>
            </b-form>
          </b-tab>
          <b-tab v-if="registerEnabled" :title="$t('security.register')">
            <b-form @submit.prevent="onRegister">
              <forms-register v-model="formRegister" />
              <div class="text-center">
                <b-button variant="primary" type="submit">{{ $t('actions.submit') }}</b-button>
              </div>
            </b-form>
          </b-tab>
          <b-tab :title="$t('security.reset')">
            <b-form @submit.prevent="onReset">
              <forms-reset v-model="formReset" />
              <div class="alert alert-light">{{ $t('security.reset_desc') }}</div>
              <div class="text-center">
                <b-button variant="primary" type="submit">{{ $t('actions.submit') }}</b-button>
              </div>
            </b-form>
          </b-tab>
        </b-tabs>
      </b-overlay>
    </b-modal>
  </div>
</template>

<script setup lang="ts">
import type {BaseButtonVariant} from 'bootstrap-vue-next/src/types'

defineProps({
  btnVariant: {
    type: String as PropType<keyof BaseButtonVariant>,
    default: 'light',
  },
})

const {t} = useI18n()
const {user, login, logout} = useAuth()
const {$login} = useNuxtApp()
const showModal = computed({
  get() {
    return $login.value
  },
  set(newValue) {
    $login.value = newValue
  },
})
const loading = ref(false)
const form = ref()
const tabs = ref()
const registerEnabled = useRuntimeConfig().public.REGISTER_ENABLED === '1'

const formLogin = ref({username: '', password: ''})
const formRegister = ref({username: '', password: '', fullname: '', email: ''})
const formReset = ref({username: ''})

async function onLogin() {
  loading.value = true
  try {
    await login(formLogin.value.username, formLogin.value.password)
    showModal.value = false
    formLogin.value = {username: '', password: ''}
    useToastInfo(t('success.login'))
    clearNuxtData()
    await clearError()
    await refreshNuxtData()
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function onLogout() {
  try {
    await logout()
    useToastInfo(t('success.logout'))
    clearNuxtData()

    const name = String(useRoute().name)
    if (name.startsWith('user') || name.startsWith('admin')) {
      navigateTo('/')
    } else {
      await refreshNuxtData()
    }
  } catch (e) {
    console.error(e)
  }
}

async function onRegister() {
  loading.value = true
  try {
    await usePost('security/register', formRegister.value)
    showModal.value = false
    formRegister.value = {username: '', password: '', fullname: '', email: ''}
    useToastInfo(t('success.register'))
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function onReset() {
  loading.value = true
  try {
    await usePost('security/reset', formReset.value)
    showModal.value = false
    formReset.value = {username: ''}
    useToastInfo(t('success.reset'))
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function onShown() {
  if (tabs.value) {
    onTab()
  } else if (form.value && form.value.$el) {
    nextTick(() => {
      const input = form.value.$el.querySelector('input:not(:disabled)')
      if (input) {
        input.focus()
      }
    })
  }
}

function onTab() {
  if (tabs.value && tabs.value.$el) {
    nextTick(() => {
      const tab = tabs.value?.$el.querySelector('.tab-pane.active')
      if (tab) {
        const form = tab.querySelector('form')
        if (form) {
          const input = form.querySelector('input:not(:disabled)')
          if (input) {
            input.focus()
          }
        }
      }
    })
  }
}
</script>
