import {SocketMessage} from '~/modules/socket.dev'

let globalSocketIO: Promise<void> | undefined

export default defineEventHandler((event) => {
  if (!process.dev && !globalSocketIO) {
    const {SOCKET_SECRET} = useRuntimeConfig()
    // @ts-ignore
    globalSocketIO = SocketMessage(event.node.res.socket?.server, SOCKET_SECRET)
  }
})
