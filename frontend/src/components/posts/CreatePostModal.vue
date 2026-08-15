<template>
  <Transition name="modal">
    <div class="modal-backdrop" @click.self="close">
      <div class="modal">
        <div class="modal__header">
          <h3 class="modal__title">Create post</h3>
          <button class="modal__close" type="button" aria-label="Close" @click="close">
            <img src="/images/cross.svg" alt=""/>
          </button>
        </div>

        <form class="modal__form" @submit.prevent="createPost">
          <div class="modal__field">
            <label for="post-vendor">Vendor</label>
            <select id="post-vendor" v-model="form.vendor" class="modal__input" :disabled="loading">
              <option value="spotify">Spotify</option>
              <option value="yandex">Yandex Music</option>
              <option value="vk">VK Music</option>
            </select>
          </div>

          <div class="modal__field">
            <label for="post-content">Content</label>
            <textarea
              id="post-content"
              v-model="form.link"
              class="modal__textarea"
              :placeholder="contentPlaceholder"
              :disabled="loading"
              rows="3"
            />

            <button
              v-if="spotifyEmbedUrl"
              type="button"
              class="modal__preview-toggle"
              @click="showPreview = !showPreview"
            >
              {{ showPreview ? 'Hide preview' : 'Preview track' }}
            </button>

            <div v-if="showPreview && spotifyEmbedUrl" class="modal__preview">
              <iframe
                :src="spotifyEmbedUrl"
                width="100%" height="352"
                allowfullscreen
                loading="lazy"
                allowtransparency="true"
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
              />
            </div>
          </div>

          <div class="modal__field">
            <label for="post-title">Title</label>
            <input
              id="post-title"
              v-model="form.title"
              class="modal__input"
              type="text"
              maxlength="255"
              placeholder="Post title"
              :disabled="loading"
            />
          </div>

          <div class="modal__field">
            <label for="post-comment">Comment</label>
            <div class="modal__comment-wrapper">
              <textarea
                id="post-comment"
                v-model="form.comment"
                class="modal__textarea"
                maxlength="500"
                placeholder="What do you think about this track?"
                rows="4"
                :disabled="loading"
              />
              <button type="button" class="modal__emoji-toggle"
                      @click="showEmojiPicker = !showEmojiPicker">
                😊
              </button>
              <div v-if="showEmojiPicker" class="modal__emoji-popover">
                <EmojiPicker :native="true" @select="onSelectEmoji"/>
              </div>
            </div>
          </div>

          <p v-if="error" class="modal__error">{{ error }}</p>

          <div class="modal__actions">
            <button class="modal__cancel" type="button" :disabled="loading" @click="close">Cancel
            </button>
            <button
              class="modal__create"
              type="submit"
              :disabled="loading || !canSubmit || vendorIsUnsupported"
            >
              {{ loading ? 'Creating...' : 'Create' }}
            </button>
          </div>
          <div class="modal__comment">{{ vendorIsUnsupportedComment }}</div>
        </form>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import {computed, ref, watch} from 'vue'
import http from '@/plugins/http'
import axios from 'axios'
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'
import type {CreatePostRequest, CreatePostResponse} from '@/types/post.ts'
import { toSpotifyEmbedUrl } from '@/plugins/useSpotify'

const showEmojiPicker = ref(false)
const showPreview = ref(false)

const emit = defineEmits<{
  close: []
  created: []
}>()

const form = ref<CreatePostRequest>({
  vendor: 'spotify',
  link: '',
  title: '',
  comment: '',
})

const loading = ref(false)
const error = ref('')

const spotifyEmbedUrl = computed(() => {
  if (form.value.vendor !== 'spotify') return null
  return toSpotifyEmbedUrl(form.value.link)
})

// Reset preview whenever the link changes so a stale iframe doesn't linger
watch(() => form.value.link, () => {
  showPreview.value = false
})

function onSelectEmoji(emoji: { i: string; n: string[]; r: string; t: string }) {
  form.value.comment += emoji.i
}

const canSubmit = computed(() => {
  return (
    form.value.link.trim().length > 0 &&
    form.value.title.trim().length > 0
  )
})

const vendorIsUnsupported = computed(() => {
  return form.value.vendor === 'vk' || form.value.vendor === 'yandex'
})

const vendorIsUnsupportedComment = computed(() => {
  if (form.value.vendor === 'yandex') return 'Maybe i will add that later'
  if (form.value.vendor === 'vk') return 'Maybe i will add that never'
  return ''
})

const contentPlaceholder = computed(() => {
  switch (form.value.vendor) {
    case 'spotify':
      return 'https://open.spotify.com/track/...'
    case 'yandex':
      return 'https://music.yandex.ru/...'
    case 'vk':
      return 'https://vk.com/...'
  }
})

function close() {
  if (loading.value) return
  emit('close')
}

async function createPost() {
  if (!canSubmit.value || loading.value) return

  loading.value = true
  error.value = ''

  try {
    const {data} = await http.post<CreatePostResponse>('/posts/create_post', {
      vendor: form.value.vendor,
      link: form.value.link.trim(),
      title: form.value.title.trim(),
      comment: form.value.comment.trim(),
    })
    console.log(data)
    if (!data.success) {
      error.value = 'Failed to create post'
      return
    }

    emit('created')
  } catch (err) {
    if (axios.isAxiosError(err) && typeof err.response?.data?.detail === 'string') {
      error.value = err.response.data.detail
    } else {
      error.value = 'Failed to create post, ping @qwrttqr'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped lang="scss">
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
  padding: 16px;
}

.modal {
  width: 100%;
  max-width: 360px;
  max-height: calc(100vh - 32px);
  overflow-y: auto;
  padding: 20px;
  box-sizing: border-box;
  border-radius: 16px;
  background: var(--tg-theme-bg-color, #1c1c1e);

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
  }

  &__title {
    margin: 0;
    color: var(--tg-theme-text-color, #fff);
    font-size: 17px;
    font-weight: 600;
  }

  &__close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    cursor: pointer;

    img {
      width: 30px;
      height: 30px;
    }

    &:hover {
      background: rgba(120, 120, 120, 0.25);
    }

    &:active {
      background: rgba(120, 120, 120, 0.4);
    }
  }

  &__form {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  &__field {
    display: flex;
    flex-direction: column;
    gap: 6px;

    label {
      color: var(--tg-theme-hint-color, #999);
      font-size: 13px;
    }
  }

  &__input,
  &__textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    background: var(--tg-theme-secondary-bg-color, #f5f5f5);
    color: var(--tg-theme-text-color, #000);
    font-family: inherit;
    font-size: 14px;
    outline: none;

    &:focus {
      border-color: var(--tg-theme-button-color, #4a9eff);
    }

    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }

  &__input {
    height: 40px;
    padding: 0 12px;
  }

  &__textarea {
    padding: 10px 12px;
    resize: vertical;
  }

  &__error {
    margin: 0;
    color: #ff6b6b;
    font-size: 13px;
  }

  &__actions {
    display: flex;
    gap: 10px;
    margin-top: 4px;
  }

  &__cancel,
  &__create {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;

    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }

  &__cancel {
    background: var(--tg-theme-secondary-bg-color, #333);
    color: var(--tg-theme-text-color, #fff);
  }

  &__create {
    background: var(--tg-theme-button-color, #4a9eff);
    color: var(--tg-theme-button-text-color, #fff);
  }

  &__comment {
    text-align: center;
    color: orange;
  }

  &__comment-wrapper {
    position: relative;
  }

  &__emoji-toggle {
    position: absolute;
    bottom: 8px;
    right: 8px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 18px;
    z-index: 2;
  }

  &__emoji-popover {
    position: absolute;
    bottom: 100%;
    right: 0;
    z-index: 10;
    margin-bottom: 4px;
  }

  &__preview-toggle {
    align-self: flex-start;
    margin-top: 6px;
    padding: 4px 10px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    background: transparent;
    color: var(--tg-theme-link-color, #4a9eff);
    font-size: 12px;
    cursor: pointer;
  }

  &__preview {
    margin-top: 8px;

    iframe {
      border: none;
      display: block;
    }
  }
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.15s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
