import axios from 'axios'
import { io } from 'socket.io-client'
import 'bootstrap/dist/css/bootstrap.min.css'
import "bootstrap-icons/font/bootstrap-icons.css"
import 'bootstrap'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

const app = createApp(App)

//const apiDomain = import.meta.env.VITE_API_DOMAIN
const apiDomain = "http://127.0.0.1:8000"

const wsConnection = import.meta.env.VITE_WS_CONNECTION

//app.provide('socket', io(wsConnection))

app.provide(
  'axios',
  axios.create({
    baseURL: apiDomain + '/api',
    headers: {
      'Content-type': 'application/json'
    }
  })
)
app.provide('serverBaseUrl', apiDomain)

app.use(router)

app.mount('#app')
