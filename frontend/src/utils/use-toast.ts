import * as pkg from 'vue-toastification'
const {useToast} = pkg

export function useToastInfo(message: string, options = {}) {
  useToast().info(translateServerMessage(message), options)
}

export function useToastSuccess(message: string, options = {}) {
  useToast().success(translateServerMessage(message), options)
}

export function useToastError(message: string, options = {}) {
  useToast().error(translateServerMessage(message), options)
}

export function useToastsClear() {
  useToast().clear()
}
