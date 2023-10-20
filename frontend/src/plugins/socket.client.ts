import {io, Socket} from 'socket.io-client'

export default defineNuxtPlugin(() => {
  const socket: Socket = io()

  socket.on('connect', () => {
    console.info(`Socket.io ${socket.id}`)
  })

  return {
    provide: {socket},
  }
})
