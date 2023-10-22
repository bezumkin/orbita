import {SocketMessage} from '~/modules/socket.dev'

let globalSocketIO: Promise<void> | undefined

export default defineEventHandler((event) => {
  if (!process.dev && !globalSocketIO) {
    const {REDIS_SECRET} = useRuntimeConfig()
    // @ts-ignore
    globalSocketIO = SocketMessage(event.node.res.socket?.server, REDIS_SECRET)
  }
})
