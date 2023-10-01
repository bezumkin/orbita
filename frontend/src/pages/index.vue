<template>
  <div class="p-5 text-center">{{ t('pages.index') }}</div>
</template>

<script setup lang="ts">
const {t} = useI18n()
useHead({
  title: () => [t('pages.index'), t('project')].join(' / '),
})
</script>
