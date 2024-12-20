import type {NitroApp} from 'nitropack'
import {Server as Engine} from 'engine.io'
import {Server} from 'socket.io'
import {defineEventHandler} from 'h3'
import {createClient} from 'redis'
import type {VespUser} from '@vesp/frontend'
import {getApiUrl} from '#imports'

const specialRooms = ['videos', 'payments', 'users', 'levels'] // We can add more rooms here

function checkScope(required: string, user: VespUser) {
  const userScope = user.role?.scope
  if (!userScope) {
    return false
  }
  if (required.includes('/')) {
    return userScope.includes(required) || userScope.includes(required.replace(/\/.*/, ''))
  }
  return userScope.includes(required) || userScope.includes(`${required}/get`)
}

export default defineNitroPlugin(async (nitroApp: NitroApp) => {
  const {SOCKET_SECRET} = useRuntimeConfig()

  const engine = new Engine()
  const io = new Server()
  const redis = createClient({socket: {host: 'redis'}})

  function subscribe(message: string, room: string) {
    const {event, data, secret} = JSON.parse(message)
    if (event && secret === SOCKET_SECRET) {
      io.to(room).emit(event, data)
    }
  }

  await redis.connect()
  await redis.subscribe('general', (message) => subscribe(message, 'general'))
  specialRooms.forEach((room) => {
    redis.subscribe(room, (message) => subscribe(message, room))
  })

  io.bind(engine)
  io.on('connection', async (socket) => {
    socket.join('general')

    const headers = socket.handshake.headers
    if (headers.cookie && headers.cookie.includes('Bearer')) {
      try {
        const data = await $fetch<Record<string, any>>(getApiUrl() + 'user/profile', {
          method: 'GET',
          headers: {cookie: headers.cookie},
        })
        specialRooms.forEach((room) => {
          if (checkScope(room, data.user)) {
            // console.info(`${data.user.username} joined "${room}" room`)
            socket.join(room)
          }
        })
      } catch (e) {
        // console.error(e)
      }
    }
  })

  nitroApp.router.use(
    '/socket.io/',
    defineEventHandler({
      handler(event) {
        engine.handleRequest(event.node.req, event.node.res)
        event._handled = true
      },
      websocket: {
        open(peer) {
          const nodeContext = peer.ctx.node
          const req = nodeContext.req

          // @ts-expect-error private method
          engine.prepare(req)

          const rawSocket = nodeContext.req.socket
          const websocket = nodeContext.ws

          // @ts-expect-error private method
          engine.onWebSocket(req, rawSocket, websocket)
        },
      },
    }),
  )
})
