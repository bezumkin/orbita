<template>
  <Transition name="fade" mode="out-in">
    <div v-if="editing" key="editing">
      <BForm @submit.prevent="emit('submit')">
        <template v-if="jsonTypes.includes(type) && typeof record === 'object'">
          <BFormInput v-if="type === 'string'" v-model="record[lang]" autofocus />
          <BFormTextarea v-if="type === 'text'" v-model="record[lang]" rows="3" autofocus />
          <div class="d-flex mt-1 gap-2">
            <BButton
              v-for="code in localeCodes"
              :key="code"
              :active="lang === code"
              size="sm"
              variant="light"
              class="px-2 py-0"
              @click="onLang(code)"
            >
              <BImg :src="getIcon(code)" height="16" />
            </BButton>
          </div>
        </template>
        <FileUpload
          v-else-if="type === 'image'"
          v-model="record"
          v-bind="imageProps"
          :placeholder="typeof record === 'object' ? {...record} : null"
          :placeholder-params="typeof record === 'object' ? imageParams : null"
        />
        <BFormInput v-else-if="type === 'date' && typeof record === 'string'" v-model="record" type="date" />
      </BForm>
    </div>
    <div v-else key="display">
      <template v-if="jsonTypes.includes(type) && typeof record === 'object'">
        <div v-for="code in localeCodes" :key="code" class="d-flex gap-2 align-items-center">
          <BImg :src="getIcon(code)" height="16" />
          <div v-html="record[code]" />
        </div>
      </template>
      <div v-else-if="type === 'image' && typeof record === 'object'" :class="'image image-' + key">
        <BImg :src="$image(record, imageParams)" fluid />
      </div>
      <template v-else-if="type === 'date' && typeof record === 'string'">
        {{ formatDate(record) }}
      </template>
      <template v-else>
        {{ record }}
      </template>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import ru from '~/assets/icons/ru.svg'
import en from '~/assets/icons/gb.svg'
import de from '~/assets/icons/de.svg'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  editing: {
    type: Boolean,
    default: false,
  },
})
const emit = defineEmits(['update:modelValue', 'submit'])
const type = computed(() => props.modelValue.type)
const key = computed(() => props.modelValue.key)
const record: Ref<string | Record<string, any>> = computed({
  get() {
    return props.modelValue.value || ''
  },
  set(newValue) {
    emit('update:modelValue', {...props.modelValue, value: newValue})
  },
})

const jsonTypes = ['string', 'text']
const {d, locale, localeCodes} = useI18n()
const lang: Ref<string> = ref(String(locale.value))

const imageProps = computed(() => {
  const width = key.value === 'poster' ? 225 : 'auto'
  const height = key.value === 'poster' ? 280 : 240
  const classes = ['image']
  if (key.value === 'poster') {
    classes.push('image-poster')
  } else {
    classes.push('image-background')
  }

  return {
    width,
    height,
    class: classes.join(' '),
  }
})
const imageParams = computed(() => {
  if (key.value === 'poster') {
    return {w: 450, h: 560, fit: 'crop'}
  }
  return {h: 480, fit: 'crop-center'}
})

function onLang(code: string) {
  lang.value = code
}

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

function formatDate(date: string) {
  return d(date)
}
</script>

<style scoped lang="scss">
:deep(.image) {
  img {
    height: 100%;
    object-fit: cover;
    width: 100%;
    border: 1px solid $border-color;
    border-radius: $border-radius;
  }

  &.image-poster {
    width: 225px;

    img {
      height: 280px;
    }
  }

  &.image-background {
    img {
      height: 240px;
      width: 100%;
      object-fit: cover;
    }
  }
}
</style>
