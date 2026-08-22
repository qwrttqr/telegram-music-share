import {defineStore} from "pinia";
import type {SpotifyEmbedController} from "@/services/spotifyController.ts";

export const useSpotifyControllerStore = defineStore('spotifyController', () => {
  const controllers = new Map<string, SpotifyEmbedController>()

  function register(id: string, controller: SpotifyEmbedController) {
    controllers.set(id, controller)
  }

  function unset(id: string) {
    controllers.get(id)?.destroy()
    controllers.delete(id)
  }

  function pauseAllExcept(id: string) {
    for (const [otherId, controller] of controllers) {
      if (id !== otherId) {
        controller.pause()
      }
    }
  }
  return {controllers, register, unset, pauseAllExcept}
})
