<template>
  <footer class="mt-5 py-3">
    <b-container class="text-muted small">
      <b-row align-v="center">
        <b-col md="4" class="font-weight-bold text-center text-md-start">
          <a href="https://orbita.bezumkin.ru" target="_blank">
            {{ $t('project') }}
          </a>
        </b-col>
        <b-col md="4" class="my-2 my-md-0">
          <vesp-change-locale select-class="bg-light" />
        </b-col>
        <b-col md="4" class="text-center text-md-end mt-2 mt-md-0">
          {{ owner }} &copy; {{ Array.isArray(date) ? date.join(' &mdash; ') : date }}
        </b-col>
      </b-row>
    </b-container>
  </footer>
</template>

<script setup lang="ts">
const {locale} = useI18n()
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
  const owner = $settings.value.owner
  if (owner) {
    return owner
  }
  return locale.value === 'ru' ? 'Василий Наумкин' : 'Vasily Naumkin'
})
</script>
