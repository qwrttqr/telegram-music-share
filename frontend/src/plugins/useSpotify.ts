export function extractSpotifyTrackId(link: string): string | null {
  const match = link.match(/open\.spotify\.com\/track\/([a-zA-Z0-9]+)/)
  return match ? match[1]! : null
}

export function toSpotifyEmbedUrl(link: string): string | null {
  const trackId = extractSpotifyTrackId(link)
  return trackId ? `https://open.spotify.com/embed/track/${trackId}` : null
}
