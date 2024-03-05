import {defineNuxtModule} from '@nuxt/kit'

import {Server} from 'socket.io'
import {createClient} from 'redis'

export const SocketMessage = async (server: any, redisSecret: string) => {
  const SocketIO = new Server(server)
  const redis = createClient({socket: {host: 'redis'}})

  await redis.connect()
  await redis.subscribe('general', (message) => {
    const {event, data, secret} = JSON.parse(message)
    if (event && secret === redisSecret) {
      SocketIO.emit(event, data)
    }
  })
}

export default defineNuxtModule({
  setup(_, nuxt) {
    nuxt.hook('listen', (server) => {
      SocketMessage(server, nuxt.options.runtimeConfig.SOCKET_SECRET as string)
    })

    nuxt.hook('modules:done', () => {
      console.info('Socket.io module is ready')
    })
  },
})
