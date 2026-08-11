<template>
  <div class="friends-page">
    <section class="friends-invite">
      <button class="friends-invite__button" :disabled="tokenLoading" @click="generateInvite">
        {{ tokenLoading ? 'Generating...' : 'Invite a friend' }}
      </button>

      <div v-if="inviteLink" class="friends-invite__result">
        <div class="friends-invite__input-wrap">
          <input class="friends-invite__link" :value="inviteLink" readonly @click="selectLinkText"/>
          <button
            class="friends-invite__clear"
            type="button"
            aria-label="Clear invite link"
            @click="clearLink"
          >
            <img src="/images/cross.svg" alt="">
          </button>
        </div>
        <button class="friends-invite__copy" @click="copyLink">
          {{ copied ? 'Copied!' : 'Copy' }}
        </button>
      </div>

      <p v-if="tokenError" class="friends-invite__error">
        Failed to generate invite, ping @qwrttqr
      </p>
    </section>

    <section class="friends-list">
      <p v-if="!friendsLoading && !friends.length" class="friends-list__placeholder">
        Your friends will show up here
      </p>
      <ProfileHeader
        v-for="friend in friends"
        :key="friend.telegram_id"
        :photo-url="friend.photo_url"
        :display-name="friendDisplayName(friend)"
        :username="friend.tg_username"
      />
    </section>

    <Transition name="modal">
      <div v-if="showAcceptModal" class="modal-backdrop" @click.self="closeModal">
        <div class="modal">
          <template v-if="acceptSuccess">
            <h3 class="modal__title">You're now friends!</h3>
          </template>
          <template v-else-if="acceptError">
            <h3 class="modal__title">The invite link is incorrect or already been used(</h3>
            <button class="modal__decline" @click="closeModal">
              Ok
            </button>
          </template>
          <template v-else>
            <h3 class="modal__title">Accept friend request?</h3>
            <p class="modal__subtitle">You've been invited to connect.</p>

            <div class="modal__actions">
              <button class="modal__decline" :disabled="acceptLoading" @click="closeModal">
                Decline
              </button>
              <button class="modal__accept" :disabled="acceptLoading" @click="acceptInvite">
                {{ acceptLoading ? 'Accepting...' : 'Accept' }}
              </button>
            </div>
          </template>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import {ref, computed, onMounted} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import http from '@/plugins/http'
import axios from "axios";
import ProfileHeader from "@/components/profile/ProfileHeader.vue";

const route = useRoute()
const router = useRouter()

// --- invite generation ---
const tokenLoading = ref(false)
const tokenError = ref(false)
const inviteToken = ref<string | null>(null)
const copied = ref(false)

// -- friends list --
interface Friend {
  telegram_id: number
  tg_username: string | null
  first_name: string | null
  last_name: string | null
  photo_url: string | null
}

const friends = ref<Friend[]>([])
const friendsLoading = ref(false)


const inviteLink = computed(() => {
  if (!inviteToken.value) return ''
  return `https://t.me/music_share_qwrttqr_bot/qwrttqr_music_share?startapp=${inviteToken.value}`
})

async function generateInvite() {
  tokenLoading.value = true
  tokenError.value = false
  try {
    const {data} = await http.get<{ success: boolean; token: string }>(
      '/friends/create_friendship_token',
    )
    inviteToken.value = data.token
    copied.value = false
  } catch {
    tokenError.value = true
  } finally {
    tokenLoading.value = false
  }
}

function friendDisplayName(friend: Friend): string {
  const name = [friend.first_name, friend.last_name].filter(Boolean).join(' ')
  return name || friend.tg_username || 'Unknown'
}


async function loadFriends() {
  friendsLoading.value = true
  try {
    const { data } = await http.get<{ friends: Friend[] }>('/friends/get_friends')
    friends.value = data.friends
  } catch {
  } finally {
    friendsLoading.value = false
  }
}

function selectLinkText(e: Event) {
  ;(e.target as HTMLInputElement).select()
}

async function copyLink() {
  if (!inviteLink.value) return
  await navigator.clipboard.writeText(inviteLink.value)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}

// --- accept invite modal ---
const showAcceptModal = ref(false)
const acceptLoading = ref(false)
const acceptError = ref<string | null>(null)
const acceptSuccess = ref(false)

onMounted(() => {
  const tokenParam = route.params.token
  if (typeof tokenParam === 'string' && tokenParam.length > 0) {
    showAcceptModal.value = true
  }
  loadFriends()
})

function closeModal() {
  showAcceptModal.value = false
  acceptError.value = null
  router.replace({name: 'friends'})
}

function clearLink() {
  inviteToken.value = null
  copied.value = false
}

async function acceptInvite() {
  const tokenParam = route.params.token
  if (typeof tokenParam !== 'string') return

  acceptLoading.value = true
  acceptError.value = null

  try {
    const {data} = await http.post<{ success: boolean; message: string }>(
      '/friends/accept_invite',
      {token: tokenParam},
    )

    if (data.success) {
      acceptSuccess.value = true
      setTimeout(closeModal, 1200)
    } else {
      acceptError.value = data.message || 'Could not accept invite'
    }
  } catch (err) {
    if (axios.isAxiosError(err) && typeof err.response?.data?.detail === 'string') {
      acceptError.value = err.response.data.detail
    } else {
      acceptError.value = 'Something went wrong, ping @qwrttqr'
    }
  } finally {
    acceptLoading.value = false
  }
}
</script>

<style scoped lang="scss">
.friends-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.friends-invite {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 0 16px;

  &__input-wrap {
    position: relative;
    flex: 1;
    display: flex;
    align-items: center;
  }

  &__link {
    width: 100%;
    padding: 10px 32px 10px 12px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: var(--tg-theme-secondary-bg-color, #f5f5f5);
    color: var(--tg-theme-text-color, #000);
    font-size: 13px;
    box-sizing: border-box;
  }

  &__clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border: none;
    border-radius: 50%;
    background: transparent;
    padding: 0;
    cursor: pointer;
    transition: background-color 0.15s ease;

    img {
      width: 20px;
      height: 20px;
      display: block;
    }

    &:hover {
      background: rgba(120, 120, 120, 0.25);
    }

    &:active {
      background: rgba(120, 120, 120, 0.4);
    }
  }

  &__button {
    padding: 12px;
    margin-top: 20px;
    border: none;
    border-radius: 12px;
    background: var(--tg-theme-button-color, #4a9eff);
    color: var(--tg-theme-button-text-color, #fff);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;

    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }

  &__result {
    display: flex;
    gap: 8px;
  }

  &__copy {
    padding: 0 16px;
    border: none;
    border-radius: 10px;
    background: var(--tg-theme-secondary-bg-color, #f5f5f5);
    color: var(--tg-theme-text-color, #000);
    font-size: 13px;
    cursor: pointer;
  }

  &__error {
    color: #ff6b6b;
    font-size: 13px;
  }
}

.friends-list {
  padding: 0 16px;

  &__placeholder {
    text-align: center;
    color: var(--tg-theme-hint-color, #999);
    padding: 24px 0;
  }
}

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
  max-width: 320px;
  padding: 24px;
  border-radius: 16px;
  background: var(--tg-theme-bg-color, #1c1c1e);
  text-align: center;

  &__title {
    margin: 0 0 6px;
    color: var(--tg-theme-text-color, #fff);
    font-size: 17px;
  }

  &__subtitle {
    margin: 0 0 20px;
    color: var(--tg-theme-hint-color, #999);
    font-size: 13px;
  }

  &__actions {
    display: flex;
    gap: 10px;
  }

  &__accept,
  &__decline {
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

  &__accept {
    background: var(--tg-theme-button-color, #4a9eff);
    color: var(--tg-theme-button-text-color, #fff);
  }

  &__decline {
    background: var(--tg-theme-secondary-bg-color, #333);
    color: var(--tg-theme-text-color, #fff);
  }

  &__error {
    margin-top: 12px;
    color: #ff6b6b;
    font-size: 13px;
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
