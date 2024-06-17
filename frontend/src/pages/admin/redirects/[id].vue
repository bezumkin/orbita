<template>
  <VespModal v-model="record" v-bind="params">
    <template #form-fields>
      <FormsRedirect v-model="record" />
      <div class="alert alert-light" v-html="$t('models.redirect.description')" />
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const id = Number(useRoute().params.id)
const {t} = useI18n()

const record = ref({
  id: 0,
  title: '',
  from: '',
  to: '',
  code: 302,
  message: '',
  rank: 0,
  active: true,
})

const params = computed(() => {
  return {
    url: 'admin/redirects' + (id ? `/${id}` : ''),
    method: id ? 'patch' : 'put',
    title: t('models.redirect.title_one'),
    updateKey: 'admin-redirects',
  }
})

if (id) {
  try {
    record.value = await useGet(params.value.url)
  } catch (e: any) {
    showError({statusCode: e.statusCode, statusMessage: e.message})
  }
}
</script>
