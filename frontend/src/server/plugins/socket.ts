import type {NitroApp} from 'nitropack'
import {Server as Engine} from 'engine.io'
import {Server} from 'socket.io'
import {defineEventHandler} from 'h3'
import {createClient} from 'redis'

export default defineNitroPlugin(async (nitroApp: NitroApp) => {
  const {SOCKET_SECRET} = useRuntimeConfig()

  const engine = new Engine()
  const io = new Server()
  const redis = createClient({socket: {host: 'redis'}})

  await redis.connect()
  await redis.subscribe('general', (message) => {
    const {event, data, secret} = JSON.parse(message)
    if (event && secret === SOCKET_SECRET) {
      io.emit(event, data)
    }
  })

  io.bind(engine)

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
