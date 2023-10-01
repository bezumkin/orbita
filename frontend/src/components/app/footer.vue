<template>
  <footer class="bg-light mt-5 py-3">
    <b-container fluid class="text-muted small">
      <b-row align-v="center">
        <b-col md="4" class="font-weight-bold text-center text-md-start">{{ SITE_NAME }}</b-col>
        <b-col md="4" class="my-2 my-md-0">
          <vesp-change-locale select-class="bg-light" />
        </b-col>
        <b-col md="4" class="text-center text-md-end mt-2 mt-md-0" v-html="SITE_COPYRIGHT || copy" />
      </b-row>
    </b-container>
  </footer>
</template>

<script setup lang="ts">
const {SITE_NAME, SITE_COPYRIGHT} = useRuntimeConfig().public
const {locale} = useI18n()

const copy = computed(() => {
  const year = new Date().getFullYear()
  const author = locale.value === 'ru' ? 'Василий Наумкин' : 'Vasily Naumkin'
  return `<a href="https://bezumkin.ru" target="_blank">${author} &copy; 2020 &mdash; ${year}</a>`
})
</script>

<style scoped lang="scss">
:deep(.row) {
  .vesp-change-locale {
    max-width: 200px;
    margin: auto;
  }

  a {
    color: inherit;
  }
}
</style>
