import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from '@/App.vue'
import router from '@/router'
import { initTelegram } from '@/services/telegram'
import './assets/styles/main.scss'
import http from './plugins/http'

initTelegram()
const app = createApp(App)
app.use(createPinia())
app.use(router)
app.config.globalProperties.$http = http
app.mount('#app')
