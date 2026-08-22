<template>
  <div class="block">
    <div class="block__card">
      <h2 class="block__hello">
        Welcome{{ telegramStore.user?.username ? `, ${telegramStore.user.username}` : '' }}
      </h2>
      <p class="block__subtitle">
        Share your favorite tracks with friends
      </p>
      <CommonButton
        :disabled="isLoading"
        @click="proceed"
      >
        {{ isLoading ? 'Loading...' : 'Continue' }}
      </CommonButton>
      <p v-if="error" class="block__error">{{ error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref, onMounted} from 'vue'
import {useTelegramStore} from '@/stores/telegram'
import {tg} from '@/services/telegram'
import http from '@/plugins/http'
import {useRouter} from 'vue-router'
import {useUserStore} from '@/stores/user.ts'
import CommonButton from "@/components/common/CommonButton.vue";

const router = useRouter()
const telegramStore = useTelegramStore()
const userStore = useUserStore()

const isLoading = ref(false)
const error = ref<string | null>(null)
const pendingInviteToken = ref<string | null>(null)

onMounted(() => {
  const startParam = tg.initDataUnsafe?.start_param
  console.log('start_param:', startParam)
  if (startParam) {
    pendingInviteToken.value = startParam
  }
})

async function proceed() {
  if (!telegramStore.user?.id) {
    error.value = 'No Telegram user data available'
    return
  }

  isLoading.value = true
  error.value = null

  try {
    await http.post('/users/add_user')
    userStore.isAuthenticated = true

    if (pendingInviteToken.value) {
      console.log(123)
      router.push({
        name: 'friends-accept-invite',
        params: {token: pendingInviteToken.value},
      })
    } else {
      router.push({name: 'profile'})
    }
  } catch (e) {
    console.error(e)
    error.value = 'Some error happened on backend side, please ping @qwrttqr.'
  } finally {
    isLoading.value = false
  }
}
</script>


<style lang="scss" scoped>
.block {
  width: 100%;
  min-height: 70vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
  box-sizing: border-box;

  &__card {
    width: 100%;
    max-width: 340px;
    display: flex;
    flex-direction: column;
    padding: 32px 24px;
    border-radius: 20px;
    justify-content: space-around;
    text-align: center;
    background: var(--tg-theme-secondary-bg-color);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  }

  &__hello {
    margin: 0 0 8px;
    color: rgba(255, 255, 255, 0.85);
    font-size: 22px;
    font-weight: 700;
  }

  &__subtitle {
    margin: 0 0 20px;
    color: rgba(255, 255, 255, 0.85);
    font-size: 14px;
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  &__error {
    margin-top: 12px;
    color: #ff6b6b;
    font-size: 13px;
  }
}


</style>
