export const tg = window.Telegram.WebApp

export function initTelegram() {
  tg.ready()
  tg.expand() // full height
  tg.enableClosingConfirmation()
}
