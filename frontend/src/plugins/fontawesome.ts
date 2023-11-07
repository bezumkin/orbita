// @ts-ignore
import FontAwesomeIcon from '@fortawesome/vue-fontawesome/src/components/FontAwesomeIcon'
import {library, config} from '@fortawesome/fontawesome-svg-core'

import {
  faUser,
  faPowerOff,
  faGlobe,
  faEye,
  faEyeSlash,
  faFilter,
  faTimes,
  faRepeat,
  faPlus,
  faEdit,
  faCaretDown,
  faPause,
  faPlay,
  faUpload,
  faCheck,
  faQuestion,
  faImage,
  faVideo,
  faFile,
  faMusic,
  faCode,
} from '@fortawesome/free-solid-svg-icons'

library.add(
  faUser,
  faPowerOff,
  faGlobe,
  faEye,
  faEyeSlash,
  faFilter,
  faTimes,
  faRepeat,
  faPlus,
  faEdit,
  faCaretDown,
  faPause,
  faPlay,
  faUpload,
  faCheck,
  faQuestion,
  faImage,
  faVideo,
  faFile,
  faMusic,
  faCode,
)

config.autoAddCss = false

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.component('fa', FontAwesomeIcon)
})
