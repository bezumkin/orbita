<template>
  <div class="vesp-change-locale">
    <slot name="default" v-bind="{locale, locales, setLocale}">
      <b-form-select :model-value="locale" :class="selectClass" @change="setLocale">
        <b-form-select-option v-for="item in locales" :key="item.code" :value="item.code">
          {{ item.name }}
        </b-form-select-option>
      </b-form-select>
    </slot>
  </div>
</template>

<script setup lang="ts">
import {LocaleObject} from 'vue-i18n-routing'

defineProps({
  selectClass: {
    type: [Object, Array, String],
    default: undefined,
  },
})

const i18n = useI18n()
const locale = i18n.locale
const locales = computed(() => {
  return i18n.locales.value.map((i: string | LocaleObject) => {
    return typeof i === 'string' ? {code: i, name: i.toUpperCase()} : {code: i.code, name: i.name}
  })
})

function setLocale(lang: unknown) {
  if (typeof lang === 'string') {
    i18n.setLocale(lang)
  }
}
</script>
