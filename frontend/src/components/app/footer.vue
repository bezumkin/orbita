<template>
  <footer id="footer" class="mt-5 py-3">
    <BContainer class="text-muted small">
      <BRow align-v="center">
        <BCol md="3" class="font-weight-bold text-center text-md-start">
          {{ owner }} &copy; {{ Array.isArray(date) ? date.join(' &mdash; ') : date }}
        </BCol>
        <BCol md="6" :class="middleClasses">
          <AppPages position="footer" />
          <AppLanguage v-if="locales.length > 1" />
        </BCol>
        <BCol md="3" class="text-center text-md-end">
          <a href="https://github.com/bezumkin/orbita" target="_blank" v-html="$t('made_with')" />
        </BCol>
      </BRow>
    </BContainer>
  </footer>
</template>

<script setup lang="ts">
const {locale, locales} = useI18n()
const {$settings} = useNuxtApp()

const date = computed(() => {
  const year = new Date().getFullYear()
  const started = $settings.value.started as string
  if (started) {
    const tmp = new Date(started).getFullYear()
    if (tmp !== year) {
      return [tmp, year]
    }
  }
  return year
})
const owner = computed(() => {
  const owner = $settings.value.copyright
  if (owner) {
    return owner
  }
  return locale.value === 'ru' ? 'Василий Наумкин' : 'Vasily Naumkin'
})
const middleClasses = [
  'my-2',
  'my-md-0',
  'd-flex',
  'flex-column',
  'flex-md-row',
  'align-items-center',
  'justify-content-center',
  'justify-content-md-between',
]

if (!locales.value.filter((i) => i.code === locale.value).length) {
  locale.value = locales.value[0].code
}
</script>
