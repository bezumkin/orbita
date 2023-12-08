<template>
  <vesp-change-locale>
    <template #default="{locale, locales, setLocale}">
      <b-dropdown :variant="null" size="sm">
        <template #button-content> <fa icon="globe" class="fa-fw" /> {{ locale.toUpperCase() }} </template>
        <b-dropdown-item
          v-for="i in locales"
          :key="i.code"
          link-class="d-flex align-items-center"
          @click="setLocale(i.code)"
        >
          <b-img :src="getIcon(i.code)" height="16" class="me-1" />
          {{ i.name }}
        </b-dropdown-item>
      </b-dropdown>
    </template>
  </vesp-change-locale>
</template>

<script setup lang="ts">
import ru from '~/assets/icons/ru.svg'
import en from '~/assets/icons/gb.svg'
import de from '~/assets/icons/de.svg'

function getIcon(code: string) {
  if (code === 'ru') {
    return ru
  }
  if (code === 'en') {
    return en
  }
  if (code === 'de') {
    return de
  }
  return undefined
}
</script>
