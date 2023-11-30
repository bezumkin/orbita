import Glightbox from 'glightbox'
import 'glightbox/dist/css/glightbox.min.css'

export default defineNuxtPlugin(() => {
  return {
    provide: {lightbox: Glightbox},
  }
})
