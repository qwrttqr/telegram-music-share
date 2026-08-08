<script setup lang="ts">
import { ref } from 'vue'
import { useTelegramStore } from '@/stores/telegram'
import http from '@/plugins/http'
import {useRouter} from "vue-router";
import {useUserStore} from "@/stores/user.ts";

const router = useRouter()

const telegramStore = useTelegramStore()
const userStore = useUserStore()

const isLoading = ref(false)
const error = ref<string | null>(null)

async function proceed() {
  if (!telegramStore.user?.id) {
    error.value = 'No Telegram user data available'
    return
  }

  isLoading.value = true
  error.value = null

  try {
    await http.post('/add_user', {
      telegram_id: telegramStore.user.id,
      tg_username: telegramStore.user.username ?? null,
    })
    userStore.isAuthenticated = true
    router.push({name: 'profile'})
  } catch {
    error.value = 'Some error happened on backend side, please ping @qwrttqr.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="block">
    <div class="block__card">
      <h2 class="block__hello">
        Welcome{{ telegramStore.user?.username ? `, ${telegramStore.user.username}` : '' }}
      </h2>
      <p class="block__subtitle">
        Share your favorite tracks with friends
      </p>
      <button
        class="block__button"
        :disabled="isLoading"
        @click="proceed"
      >
        {{ isLoading ? 'Loading...' : 'Continue' }}
      </button>
      <p v-if="error" class="block__error">{{ error }}</p>
    </div>
  </div>
</template>

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
    padding: 32px 24px;
    border-radius: 20px;
    text-align: center;
    background: rgb(120 117 117 / 0.27);
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

  &__button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: #4a9eff;
    color: white;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;

    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }

  &__error {
    margin-top: 12px;
    color: #ff6b6b;
    font-size: 13px;
  }
}
</style>
