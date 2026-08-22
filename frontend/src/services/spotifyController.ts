export interface SpotifyPlaybackState {
  isBuffering: boolean
  isPaused: boolean
  duration: number
  position: number
}

export interface SpotifyEmbedOptions {
  uri: string
  width?: string | number
  height?: string | number
}


export interface SpotifyEmbedController {
  play(): void

  pause(): void

  togglePlay(): void

  seek(seconds: number): void

  loadUri(uri: string): void

  destroy(): void

  addListener(event: 'ready', callback: () => void): void

  addListener(event: 'playback_update', callback: (e: { data: SpotifyPlaybackState }) => void): void

  removeListener(event: string, callback?: (...args: unknown[]) => void): void

}

export interface SpotifyIframeAPI {
  createController(
    element: HTMLElement,
    options: SpotifyEmbedOptions,
    callback: (controller: SpotifyEmbedController) => void
  ): void
}

declare global {
  interface Window {
    spotifyIframeAPI?: SpotifyIframeAPI
    onSpotifyIframeApiReady?: (api: SpotifyIframeAPI) => void
  }
}

export function loadSpotifyIframeAPI(): Promise<SpotifyIframeAPI> {
  return new Promise((resolve) => {
    if (window.spotifyIframeAPI) return resolve(window.spotifyIframeAPI);
    window.onSpotifyIframeApiReady = (api) => {
      window.spotifyIframeAPI = api
      resolve(api)
    }
    const script = document.createElement('script')
    script.src = 'https://open.spotify.com/embed/iframe-api/v1'
    script.async = true
    document.body.appendChild(script)
  })

}
