import {useToast, PluginOptions} from 'vue-toastification'

const toast = useToast()

export function useToastInfo(message: string, options: PluginOptions = {}) {
  toast.info(message, options)
}

export function useToastSuccess(message: string, options: PluginOptions = {}) {
  toast.success(message, options)
}

export function useToastError(message: string, options: PluginOptions = {}) {
  toast.error(message, options)
}

export function useToastsClear() {
  toast.clear()
}
