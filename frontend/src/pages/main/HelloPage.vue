<script setup lang="ts">
import {useTelegramStore} from '@/stores/telegram'

const telegramStore = useTelegramStore()
</script>

<template>
  <div :style="{ background: telegramStore.themeParams.bg_color }" class="box">
    Hello {{ telegramStore.user?.first_name }}
  </div>
</template>

<style lang="scss" scoped>
@import './style.scss';
</style>
