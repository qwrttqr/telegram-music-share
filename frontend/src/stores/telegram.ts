import {defineStore} from "pinia";
import {ref} from "vue";
import {tg} from "@/services/telegram.ts";

export const useTelegramStore = defineStore('telegram', () => {
  const user = ref(tg.initDataUnsafe.user)
  const colorScheme = ref(tg.colorScheme)
  const themeParams = ref(tg.themeParams)
  const viewportHeight = ref(tg.viewportStableHeight)

  // action — a named, exported function
  function syncTheme() {
    colorScheme.value = tg.colorScheme
    themeParams.value = tg.themeParams
  }

  function syncViewport() {
    viewportHeight.value = tg.viewportStableHeight
  }

  tg.onEvent('themeChanged', syncTheme)
  tg.onEvent('viewportChanged', syncViewport)

  return {user, colorScheme, themeParams, viewportHeight, syncTheme, syncViewport}
})
