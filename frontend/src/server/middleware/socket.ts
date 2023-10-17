import {SocketMessage} from '~/modules/socket.dev'

declare global {
  const SocketIO
}

export default defineEventHandler((event) => {
  if (!process.dev && !global.SocketIO) {
    const {REDIS_SECRET} = useRuntimeConfig()
    global.SocketIO = SocketMessage(event.node.res.socket?.server, REDIS_SECRET)
  }
})
