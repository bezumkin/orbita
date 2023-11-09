import type {Options} from 'plyr'
import Plyr from 'plyr'
import sprite from 'assets/icons/plyr.svg'

export default defineNuxtPlugin(() => {
  const plyrOptions: Options = {
    iconUrl: sprite,
    storage: {enabled: false},
    volume: 1,
    speed: {
      selected: 1,
      options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2],
    },
  }

  function plyr(element: HTMLElement | string, options: Options = {}) {
    return new Plyr(element, {...plyrOptions, ...options})
  }

  return {
    provide: {
      plyrOptions,
      plyr,
    },
  }
})
