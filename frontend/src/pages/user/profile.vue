<template>
  <b-col xl="10" class="m-auto">
    <b-form @submit.prevent="onSubmit">
      <forms-user v-model="form" :show-status="false" :show-group="false" />
      <div class="text-center mt-3">
        <b-button variant="primary" type="submit">{{ t('actions.save') }}</b-button>
      </div>
    </b-form>
  </b-col>
</template>

<script setup lang="ts">
const {t} = useI18n()
const {getSession} = useAuth()
const loading = ref(false)
const form = ref()
form.value = {id: 0, username: '', ...useAuth().data.value}

async function onSubmit() {
  loading.value = true
  try {
    form.value = await usePatch('user/profile', form.value)
    useToastSuccess(t('success.profile'))
    await getSession()
  } catch (e) {
  } finally {
    loading.value = false
  }
}

useHead({
  title: () => [t('pages.user.profile'), t('pages.user.title'), t('project')].join(' / '),
})
</script>
