import type {PluginOptions} from 'vue-toastification'
import Toast, {POSITION} from 'vue-toastification'

export default defineNuxtPlugin(({vueApp}) => {
  const options: PluginOptions = {
    position: POSITION.BOTTOM_RIGHT,
    maxToasts: 5,
    timeout: 5000,
    closeButton: false,
    closeOnClick: false,
    transition: 'Vue-Toastification__slideBlurred',
  }
  vueApp.use(Toast, options)

  return {
    provide: {
      toast: {
        info: useToastInfo,
        success: useToastSuccess,
        error: useToastError,
      },
    },
  }
})
