<template>
  <section class="vesp-modal">
    <b-modal ref="modal" v-model="showModal" v-bind="modalProps">
      <template #default>
        <b-overlay :opacity="0.5" :show="loading">
          <b-form ref="form" @submit.prevent="submit">
            <slot name="form-fields" v-bind="{record, loading, hide, submit}" />
            <input type="submit" class="d-none" />
          </b-form>
        </b-overlay>
      </template>
      <template #footer>
        <slot name="footer" v-bind="{record, loading, hide, submit, formSubmit}">
          <b-button :variant="props.cancelVariant" :disabled="loading" @click="hide">
            {{ t(cancelTitle) }}
          </b-button>
          <b-button v-if="url" :variant="props.okVariant" :disabled="loading" @click.prevent="formSubmit">
            {{ t(okTitle) }}
          </b-button>
        </slot>
      </template>
      <template v-for="slotName in Object.keys($slots)" #[slotName]="slotProps">
        <slot :name="slotName" v-bind="{...slotProps, record, loading, hide, submit, formSubmit}" />
      </template>
    </b-modal>
  </section>
</template>

<script setup lang="ts">
import {BModal} from 'bootstrap-vue-next'

const emit = defineEmits(['update:modelValue', 'after-submit'])
const modal = ref()
const form = ref()

const loading = ref(false)
const record = computed({
  get() {
    return props.modelValue
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})

const props = defineProps({
  ...BModal.props,
  url: {
    type: String,
    default: '',
  },
  modelValue: {
    type: Object,
    default() {
      return {}
    },
  },
  method: {
    type: String,
    default: 'POST',
  },
  updateKey: {
    type: String,
    default: '',
  },
  show: {
    type: Boolean,
    default: true,
  },
  footerClass: {
    type: [Object, Array, String],
    default: 'pt-2 justify-content-between',
  },
  okTitle: {
    type: String,
    default: 'actions.submit',
  },
  cancelTitle: {
    type: String,
    default: 'actions.cancel',
  },
  beforeSubmit: {
    type: Function,
    default(record: any) {
      return record
    },
  },
})

const {t} = useI18n()
const showModal = ref(false)
const modalProps = computed(() => {
  const values = {...useAttrs(), ...props}
  delete values.modelValue
  delete values.show
  delete values.url
  delete values.method
  delete values.beforeSubmit

  if (!values.onHidden) {
    values.onHidden = goBack
  }

  if (!values.onShown) {
    values.onShown = focusField
  }

  return values
})
const updateKey = props.updateKey || props.url?.split('/').join('-')

function formSubmit() {
  if (form.value && form.value.$el) {
    const submit = form.value.$el.querySelector('[type="submit"]')
    if (submit) {
      submit.click()
    } else {
      submit()
    }
  } else {
    submit()
  }
}

async function submit() {
  if (!props.url) {
    return
  }
  try {
    const values = props.beforeSubmit(JSON.parse(JSON.stringify(record.value)))
    if (values) {
      if (typeof values === 'string') {
        useToastError(values)
      } else {
        loading.value = true
        const data = await useApi(props.url, {method: props.method.toUpperCase(), body: values})
        emit('after-submit', data)
        hide()
        await refreshNuxtData(updateKey)
      }
    }
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function hide() {
  modal.value.$emit('hide')
  showModal.value = false
}

function goBack() {
  const route = useRoute()
  const idx = route.matched.findIndex((item) => item.name === route.name)
  if (idx > 0 && route.matched[idx - 1]) {
    navigateTo({name: route.matched[idx - 1].name})
  } else {
    useRouter().go(-1)
  }
  useNuxtApp().callHook('page:finish')
}

function focusField() {
  if (form.value && form.value.$el) {
    const input = form.value.$el.querySelector('input:not(:disabled)')
    if (input) {
      input.focus()
    }
  }
}

onMounted(() => {
  if (props.show) {
    showModal.value = true
  }
})

defineExpose({hide, submit, formSubmit})
</script>
