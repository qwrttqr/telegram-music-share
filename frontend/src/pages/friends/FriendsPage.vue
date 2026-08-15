<template>
  <div class="friends-page">
    <section class="friends-invite">
      <CommonButton :type="'button'" :variant="'primary'" class="friends-invite__button" :disabled="tokenLoading" @click="generateInvite">
        {{ tokenLoading ? 'Generating...' : 'Invite a friend' }}
      </CommonButton>

      <div v-if="inviteLink" class="friends-invite__result">
        <div class="friends-invite__input-wrap">
          <input class="friends-invite__link" :value="inviteLink" readonly @click="selectLinkText"/>
          <CommonButton
            class="friends-invite__clear"
            type="button"
            variant="secondary"
            aria-label="Clear invite link"
            @click="clearLink"
          >
            <img src="/images/cross.svg" alt="">
          </CommonButton>
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
      <div class="friends-list__header">
        <div class="friends-list__title">Update friends list</div>
        <CommonButton
          class="friends-list__reload"
          type="button"
          variant="circle"
          aria-label="Reload friends"
          :disabled="friendsLoading"
          @click="loadFriends"
        >
          <span class="material-icons">refresh</span>
        </CommonButton>
      </div>

      <div class="friends-list__scroll">
        <div v-if="friendsLoading && !friends.length" class="friends-list__loader">
          <CommonSpinner/>
        </div>

        <p v-else-if="!friends.length" class="friends-list__placeholder">
          Your friends will show up here
        </p>

        <div v-for="friend in friends" :key="friend.telegram_id" class="friends-list__card">

          {{friend}}
          <ProfileHeader
            :contentJustify="'start'"
            :photo-url="friend.photo_url"
            :display-name="friendDisplayName(friend)"
            :username="friend.tg_username"
          >

            <template #action>
              <button @click="deleteFriend(friend.id)" class="friends-list__delete_friend">
                <span class="material-icons friends-list__delete_button">delete</span>
              </button>
            </template>
          </ProfileHeader>
        </div>
      </div>
    </section>

    <Transition name="modal">
      <div v-if="showAcceptModal" class="modal-backdrop" @click.self="closeModal">
        <div class="modal">
          <template v-if="acceptSuccess">
            <h3 class="modal__title">You're now friends!</h3>
          </template>
          <template v-else-if="acceptError">
            <h3 class="modal__title">The invite link is incorrect or already been used(</h3>
            <CommonButton variant="secondary" @click="closeModal">
              Ok
            </CommonButton>
          </template>
          <template v-else>
            <h3 class="modal__title">Accept friend request?</h3>
            <p class="modal__subtitle">You've been invited to connect.</p>

            <div class="modal__actions">
              <CommonButton variant="secondary" :disabled="acceptLoading" @click="closeModal">
                Decline
              </CommonButton>
              <CommonButton variant="primary" :loading="acceptLoading" @click="acceptInvite">
                Accept
              </CommonButton>
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
import axios from "axios"
import ProfileHeader from "@/components/profile/ProfileHeader.vue"
import CommonSpinner from "@/components/common/CommonSpinner.vue"
import CommonButton from "@/components/common/CommonButton.vue";

const route = useRoute()
const router = useRouter()

// --- invite generation ---
const tokenLoading = ref(false)
const tokenError = ref(false)
const inviteToken = ref<string | null>(null)
const copied = ref(false)

// -- friends list --
interface Friend {
  id: number
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
    const {data} = await http.get<{ friends: Friend[] }>('/friends/get_friends')
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

async function deleteFriend(friendId: Number) {
  const {data} = await http.post<{ success: boolean; message: string }>(
    '/friends/delete_friend',
    {friend_id: friendId})
  if (data.success) {
    friends.value = friends.value.filter((item: Friend) => item.id != friendId)
  } else {
  }
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
      setTimeout(loadFriends, 1300)
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
    width: 22px;
    height: 22px;
    border: none;
    border-radius: 50% !important;
    background: transparent !important;
    transition: background-color 0.15s ease;

    img {
      width: 20px;
      height: 20px;
      display: block;
    }

    &:hover {
      background: rgba(120, 120, 120, 0.25) !important;
    }

    &:active {
      background: rgba(120, 120, 120, 0.4);
    }
  }

  &__button {
    margin-top: 20px;
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

  &__header {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
  }

  &__title {
    margin: 0;
    color: var(--tg-theme-hint-color, #999);
    font-size: 15px;
    font-weight: 600;
  }

  &__reload {
    width: 32px !important;
    height: 32px !important;
    border: none;
    background: transparent;
    color: var(--tg-theme-hint-color, #999);
    border-radius: 50%;

    &:hover {
      background: rgba(120, 120, 120, 0.25);
    }

    &:active {
      background: rgba(120, 120, 120, 0.4);
    }

    &:disabled {
      cursor: not-allowed;
    }
  }

  &__delete_button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: rgba(255, 255, 255, 0.5);
    cursor: pointer;
  }

  &__delete_button:hover {
    color: rgba(255, 255, 255, 0.8);
  }

  &__delete_friend {
    background: none;
    border: none;
  }

  &__card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 10px 12px;
  }

  &__placeholder {
    text-align: center;
    color: var(--tg-theme-hint-color, #999);
    padding: 24px 0;
  }

  &__scroll {
    max-height: 355px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  &__loader {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 0;
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
