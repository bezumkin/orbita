<template>
  <b-col xl="10" class="m-auto">
    <b-overlay :show="loading" opacity="0.5">
      <b-form @submit.prevent="onSubmit">
        <forms-user v-model="form" :show-status="false" :show-group="false" />
        <div class="text-center mt-3">
          <b-button variant="primary" type="submit">{{ t('actions.save') }}</b-button>
        </div>
      </b-form>
    </b-overlay>
  </b-col>
</template>

<script setup lang="ts">
const {t} = useI18n()
const {$settings} = useNuxtApp()
const {loadUser} = useAuth()
const loading = ref(false)
const form: Ref<VespUser> = ref({id: 0, username: '', ...useAuth().user.value})

async function onSubmit() {
  loading.value = true
  try {
    const {user} = await usePatch('user/profile', form.value)
    form.value = user
    useToastSuccess(t('success.profile'))
    await loadUser()
  } catch (e) {
  } finally {
    loading.value = false
  }
}

useHead({
  title: () => [t('pages.user.profile'), t('pages.user.title'), $settings.value.title].join(' / '),
})
</script>
