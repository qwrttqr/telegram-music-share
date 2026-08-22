<template>
  <header class="header">
    <div class="header__left">
      <ProfileHeader
        :photo-url="telegramStore.user?.photo_url"
        :display-name="[telegramStore.user?.first_name, telegramStore.user?.last_name].filter(Boolean).join(' ')"
        :username="telegramStore.user?.username"
      />
    </div>

    <div class="header__right">
      <button
        class="burger"
        :class="{ 'burger--active': isMenuOpen }"
        :style="{ cursor: !burgerDisabled ? 'pointer' : 'default' }"
        aria-label="Menu"
        :disabled="burgerDisabled"
        @click="toggleMenu"
      >
        <span class="burger__line" />
        <span class="burger__line" />
        <span class="burger__line" />
      </button>

      <Transition name="menu">
        <nav v-if="isMenuOpen" class="menu">
          <button
            v-for="item in availableMenuItems"
            :key="item.routeName"
            class="menu__item"
            :class="{ 'menu__item--active': route.name === item.routeName }"
            @click="goTo(item.routeName)"
          >
            {{ item.label }}
          </button>
        </nav>
      </Transition>

      <div v-if="isMenuOpen" class="menu-backdrop" @click="closeMenu" />
    </div>
  </header>
</template>

<script setup lang="ts">
import {computed, ref} from 'vue'
import { useRouter, useRoute } from 'vue-router'
import ProfileHeader from "@/components/profile/ProfileHeader.vue";
import {useTelegramStore} from "@/stores/telegram.ts";

const router = useRouter()
const route = useRoute()

const telegramStore = useTelegramStore()
const isMenuOpen = ref(false)

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value
}

function closeMenu() {
  isMenuOpen.value = false
}

function goTo(name: string) {
  router.push({ name })
  closeMenu()
}
const burgerDisabled = computed(() => {
  return route.name === 'hello'
})

interface MenuItem {
  label: string
  routeName: string
}

const menuItems: MenuItem[] = [
  { label: 'My Profile', routeName: 'profile' },
  { label: 'Feed', routeName: 'feed' },
  { label: 'Friends', routeName: 'friends' },
]

const availableMenuItems = computed(() => {
  return menuItems.filter(el => el.routeName != route.name)
})
</script>

<style scoped lang="scss">
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 5px 10px;
  box-sizing: border-box;
  background: var(--tg-theme-secondary-bg-color);
  border-radius: 5px;
  color: var(--tg-theme-text-color, #000);
  position: relative;
  z-index: 100;
  &__left {
    display: flex;
    align-items: center;
    img {
      width: 40px;
      height: 40px;
    }
  }

  &__right {
    display: flex;
    margin-right: 10px;
    align-items: center;
    position: relative;
  }
}

.burger {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 4px;
  width: 28px;
  height: 28px;
  padding: 0;
  border: none;
  background: transparent;
  z-index: 101;

  &__line {
    display: block;
    width: 100%;
    height: 2px;
    background: var(--tg-theme-text-color, #000);
    border-radius: 2px;
    transition: transform 0.2s ease, opacity 0.2s ease;
  }

  &--active &__line {
    &:nth-child(1) {
      transform: translateY(6px) rotate(45deg);
    }
    &:nth-child(2) {
      opacity: 0;
    }
    &:nth-child(3) {
      transform: translateY(-6px) rotate(-45deg);
    }
  }
}

.menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 180px;
  display: flex;
  flex-direction: column;
  background: var(--tg-theme-secondary-bg-color, #f5f5f5);
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 101;

  &__item {
    padding: 12px 16px;
    text-align: left;
    border: none;
    background: transparent;
    color: var(--tg-theme-text-color, #000);
    font-size: 15px;
    cursor: pointer;

    &:not(:last-child) {
      border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }

    &:hover {
      background: rgba(0, 0, 0, 0.04);
    }

    &--active {
      font-weight: 600;
      color: var(--tg-theme-link-color, #2481cc);
    }
  }
}

.menu-backdrop {
  position: fixed;
  inset: 0;
  z-index: 99;
}

.menu-enter-active,
.menu-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.menu-enter-from,
.menu-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
