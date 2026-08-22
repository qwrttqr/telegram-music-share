import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from '@/App.vue'
import router from '@/router'
import { initTelegram } from '@/services/telegram'
import './assets/styles/main.scss'
import http from './plugins/http'
import {loadSpotifyIframeAPI} from "@/services/spotifyController.ts";

const app = createApp(App)
app.use(createPinia())
app.use(router)
initTelegram()
await loadSpotifyIframeAPI()
app.config.globalProperties.$http = http
app.mount('#app')
