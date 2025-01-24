<template>
  <div class="col-md-9 m-auto">
    <h1>{{ t('pages.pages') }}</h1>

    <BListGroup class="mt-5">
      <template v-for="page in $pages">
        <a
          v-if="page.external"
          :key="'e' + page.id"
          :href="page.link"
          :target="page.blank ? '_blank' : '_self'"
          class="list-group-item list-group-item-action p-3"
        >
          {{ page.name }}&nbsp;<sup><VespFa icon="external-link" size="sm" /></sup>
        </a>
        <BListGroupItem
          v-else
          :key="'l' + page.id"
          :to="{name: 'pages-alias', params: {alias: page.alias}}"
          class="p-3"
        >
          {{ page.name }}
        </BListGroupItem>
      </template>
    </BListGroup>
  </div>
</template>

<script setup lang="ts">
const {t} = useI18n()
const {$settings, $pages} = useNuxtApp()

if (!$pages.value.length) {
  showError({statusCode: 404, statusMessage: 'Not Found'})
}

useHead({
  title: () => [t('pages.pages'), $settings.value.title].join(' / '),
})
</script>
